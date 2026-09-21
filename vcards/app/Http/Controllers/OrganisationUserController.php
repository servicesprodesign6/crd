<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Models\OrganisationUserPermission;
use App\Http\Requests\UpdateUserRequest;
use App\Models\CustomDomain;
use App\Models\Role;
use App\Models\User;
use App\Models\Vcard;
use App\Models\WhatsappStore;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laracasts\Flash\Flash;

class OrganisationUserController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! canManageOrganisationUsers()) {
                abort(404);
            }

            return $next($request);
        });
    }

    public function index(): \Illuminate\View\View
    {
        return view('organisation_users.index');
    }

    public function create(): \Illuminate\View\View|RedirectResponse
    {
        if (! canAddMoreOrganisationUsers()) {
            Flash::error(__('messages.organization.max_limit') . ' ' . getOrganisationUserPlanLimit(getLogInTenantId()) . ' ' . __('messages.users'));

            return redirect()->route('organisation.users.index');
        }

        $remainingLimits = $this->getRemainingLimits();

        return view('organisation_users.create', compact('remainingLimits'));
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        if (! canAddMoreOrganisationUsers()) {
            Flash::error(__('messages.organization.max_limit') . ' ' . getOrganisationUserPlanLimit(getLogInTenantId()) . ' ' . __('messages.users'));

            return redirect()->route('organisation.users.index');
        }

        $remainingLimits = $this->getRemainingLimits();

        $request->validate([
            'no_of_vcards' => 'nullable|integer|min:0|max:' . $remainingLimits['remainingVcardLimit'],
            'no_of_whatsapp_store' => 'nullable|integer|min:0|max:' . $remainingLimits['remainingWhatsappStoreLimit'],
        ], [
            'no_of_vcards.max' => __('messages.organization.max_limit') . ' ' . $remainingLimits['remainingVcardLimit'] . ' ' . __('messages.vcards'),
            'no_of_whatsapp_store.max' => __('messages.organization.max_limit') . ' ' . $remainingLimits['remainingWhatsappStoreLimit'] . ' ' . __('messages.whatsapp_stores.whatsapp_stores'),
        ]);
        if ($request->no_of_whatsapp_store > $remainingLimits['remainingWhatsappStoreLimit']) {
            Flash::error(__('messages.organization.max_limit') . ' ' . $remainingLimits['remainingWhatsappStoreLimit'] . ' ' . __('messages.whatsapp_stores.whatsapp_stores'));
            return redirect()->back()->withInput();
        }

        $organisationId = $this->getOrganisationOwnerId();

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'contact' => $request->contact,
            'region_code' => $request->region_code,
            'password' => Hash::make($request->password),
            'tenant_id' => getLogInTenantId(),
            'language' => getLogInUser()->language,
            'is_active' => true,
            'email_verified_at' => Carbon::now(),
            'organisation_id' => $organisationId,
            'is_organisation' => false,
        ]);

        $user->assignRole(Role::ROLE_ADMIN);

        OrganisationUserPermission::updateOrCreate(
            ['user_id' => $user->id],
            [
                'no_of_vcards' => $request->no_of_vcards,
                'can_create_vcard' => $request->boolean('can_create_vcard'),
                'can_edit_vcard' => $request->boolean('can_edit_vcard'),
                'no_of_whatsapp_store' => $request->no_of_whatsapp_store,
                'can_create_whatsapp_store' => $request->boolean('can_create_whatsapp_store'),
                'can_edit_whatsapp_store' => $request->boolean('can_edit_whatsapp_store'),
            ]
        );

        if ($request->hasFile('profile')) {
            $user->addMedia($request->file('profile'))->toMediaCollection(User::PROFILE, config('app.media_disc'));
        }

        Flash::success(__('messages.organization.organization_user_created'));

        return redirect(route('organisation.users.index'));
    }

    public function show(User $user): \Illuminate\View\View
    {
        return view('organisation_users.show', compact('user'));
    }

    public function edit(User $user): \Illuminate\View\View
    {
        abort_if(! $this->canAccessOrganisationUser($user), 404);
        $remainingLimits = $this->getRemainingLimits($user);

        return view('organisation_users.edit', compact('user', 'remainingLimits'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        abort_if(! $this->canAccessOrganisationUser($user), 404);

        $remainingLimits = $this->getRemainingLimits($user);

        $request->validate([
            'no_of_vcards' => 'nullable|integer|min:0|max:' . $remainingLimits['remainingVcardLimit'],
            'no_of_whatsapp_store' => 'nullable|integer|min:0|max:' . $remainingLimits['remainingWhatsappStoreLimit'],
        ], [
            'no_of_vcards.max' => __('messages.organization.max_limit') . ' ' . $remainingLimits['remainingVcardLimit'] . ' ' . __('messages.vcards'),
            'no_of_whatsapp_store.max' => __('messages.organization.max_limit') . ' ' . $remainingLimits['remainingWhatsappStoreLimit'] . ' ' . __('messages.whatsapp_stores.whatsapp_stores'),
        ]);

        if ($request->no_of_whatsapp_store > $remainingLimits['remainingWhatsappStoreLimit']) {
            Flash::error(__('messages.organization.max_limit') . ' ' . $remainingLimits['remainingWhatsappStoreLimit'] . ' ' . __('messages.whatsapp_stores.whatsapp_stores'));
            return redirect()->back()->withInput();
        }

        $user->update($request->only([
            'first_name',
            'last_name',
            'email',
            'contact',
            'region_code',
        ]));

        OrganisationUserPermission::updateOrCreate(
            ['user_id' => $user->id],
            [
                'no_of_vcards' => $request->no_of_vcards,
                'can_create_vcard' => $request->boolean('can_create_vcard'),
                'can_edit_vcard' => $request->boolean('can_edit_vcard'),
                'no_of_whatsapp_store' => $request->no_of_whatsapp_store,
                'can_create_whatsapp_store' => $request->boolean('can_create_whatsapp_store'),
                'can_edit_whatsapp_store' => $request->boolean('can_edit_whatsapp_store'),
            ]
        );

        if ($request->hasFile('profile')) {
            $user->clearMediaCollection(User::PROFILE);
            $user->addMedia($request->file('profile'))->toMediaCollection(User::PROFILE, config('app.media_disc'));
        }

        Flash::success(__('messages.organization.organization_user_updated'));

        return redirect(route('organisation.users.index'));
    }

    public function destroy(User $user): JsonResponse
    {
        if (! $this->canAccessOrganisationUser($user)) {
            return $this->sendError(__('messages.organization.not_allowed_access'));
        }

        $this->deleteOrganisationUser($user);

        return $this->sendSuccess(__('messages.organization.organization_user_deleted'));
    }

    public function emailVerified(User $user): JsonResponse
    {
        abort_if(! $this->canAccessOrganisationUser($user), 404);

        DB::table('users')->where('id', $user->id)->update(['email_verified_at' => Carbon::now()]);

        return $this->sendSuccess(__('messages.flash.verified_email'));
    }

    public function updateStatus(User $user): JsonResponse
    {
        abort_if(! $this->canAccessOrganisationUser($user), 404);

        $organisationUserLimit = getOrganisationUserPlanLimit(getLogInTenantId());

        if (! $user->is_active && $organisationUserLimit <= 0) {
            return $this->sendError(__('messages.organization.max_limit') . ' 0 ' . __('messages.users'));
        }

        if (! $user->is_active && getActiveOrganisationUserCount($this->getOrganisationOwnerId(), getLogInTenantId()) >= $organisationUserLimit) {
            return $this->sendError(__('messages.organization.max_limit') . ' ' . $organisationUserLimit . ' ' . __('messages.users'));
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return $this->sendSuccess(__('messages.flash.user_status'));
    }

    public function changePassword(User $user): JsonResponse
    {
        abort_if(! $this->canAccessOrganisationUser($user), 404);

        request()->validate([
            'new_password' => 'required|min:8|same:confirm_password',
            'confirm_password' => 'required|min:8',
        ]);

        $user->update([
            'password' => Hash::make(request('new_password')),
        ]);

        return $this->sendSuccess(__('messages.flash.password_update'));
    }

    private function getOrganisationOwnerId(): int
    {
        return getLogInUser()->organisation_id ?: getLogInUserId();
    }

    private function canAccessOrganisationUser(User $user): bool
    {
        if (! canManageOrganisationUsers()) {
            return false;
        }

        return (int) $user->organisation_id === $this->getOrganisationOwnerId()
            && ! $user->is_organisation;
    }

    private function getRemainingLimits(User $user = null): array
    {
        $subscription = getCurrentSubscription();
        $totalVcardLimit = $subscription->no_of_vcards;
        $totalWhatsappStoreLimit = $subscription->no_of_whatsapp_store;

        $owner = getLogInUser();
        $tenantId = $owner->tenant_id;

        $ownerCreatedVcards = Vcard::where('tenant_id', $tenantId)
            ->whereDoesntHave('assignedUser', function ($q) use ($owner) {
                $q->where('organisation_id', $owner->id);
            })->count();

        $ownerCreatedWhatsappStores = WhatsappStore::where('tenant_id', $tenantId)
            ->whereDoesntHave('assignedUser', function ($q) use ($owner) {
                $q->where('organisation_id', $owner->id);
            })->count();

        $orgUserPermissions = OrganisationUserPermission::whereHas('user', function ($q) use ($owner) {
            $q->where('organisation_id', $owner->id);
        })->get();

        $allocatedVcardsCount = 0;
        $allocatedWhatsappStoresCount = 0;

        foreach ($orgUserPermissions as $permission) {
            if ($user && $permission->user_id == $user->id) {
                continue;
            }

            $actualVcards = Vcard::whereHas('assignedUser', function ($q) use ($permission) {
                $q->where('users.id', $permission->user_id);
            })->count();

            $actualWhatsappStores = WhatsappStore::whereHas('assignedUser', function ($q) use ($permission) {
                $q->where('users.id', $permission->user_id);
            })->count();

            $allocatedVcardsCount += max($permission->no_of_vcards, $actualVcards);
            $allocatedWhatsappStoresCount += max($permission->no_of_whatsapp_store, $actualWhatsappStores);
        }

        return [
            'remainingVcardLimit' => max(0, $totalVcardLimit - $ownerCreatedVcards - $allocatedVcardsCount),
            'remainingWhatsappStoreLimit' => max(0, $totalWhatsappStoreLimit - $ownerCreatedWhatsappStores - $allocatedWhatsappStoresCount),
        ];
    }

    public function deleteOrganisationUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            Vcard::where('tenant_id', $user->tenant_id)->each(function (Vcard $vcard) use ($user) {
                $vcard->assignedUser()->detach($user->id);
            });

            WhatsappStore::where('tenant_id', $user->tenant_id)->each(function (WhatsappStore $whatsappStore) use ($user) {
                $whatsappStore->assignedUser()->detach($user->id);
            });

            OrganisationUserPermission::where('user_id', $user->id)->delete();
            CustomDomain::where('user_id', $user->id)->delete();

            $user->clearMediaCollection(User::PROFILE);
            $user->syncRoles([]);
            $user->delete();
        });
    }
}
