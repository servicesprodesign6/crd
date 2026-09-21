<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\AffiliateUser;
use App\Models\CustomDomain;
use App\Models\MultiTenant;
use App\Models\NfcOrderTransaction;
use App\Models\NfcOrders;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Vcard;
use App\Models\Withdrawal;
use App\Models\WithdrawalTransaction;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laracasts\Flash\Flash;

class OrganisationController extends AppBaseController
{
    public function __construct(private readonly UserRepository $userRepo)
    {
    }

    public function index(): \Illuminate\View\View
    {
        return view('organisation.index');
    }

    public function create(): \Illuminate\View\View
    {
        return view('organisation.create');
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        $request->validate([
            'organisation_name' => 'required|string|max:180',
        ]);

        $input = $request->all();
        $input['is_organisation'] = true;
        $this->userRepo->store($input);

        Flash::success(__('messages.organization.organization_created'));

        return redirect(route('organisation.index'));
    }

    public function show(User $organisation): \Illuminate\View\View
    {
        abort_if(! $organisation->is_organisation, 404);

        return view('organisation.show', ['user' => $organisation]);
    }

    public function edit(User $organisation): \Illuminate\View\View
    {
        abort_if(! $organisation->is_organisation, 404);

        $subscription = Subscription::with(['plan'])
            ->whereTenantId($organisation->tenant_id)
            ->where('status', Subscription::ACTIVE)
            ->latest()
            ->first();

        return view('organisation.edit', ['user' => $organisation, 'subscription' => $subscription]);
    }

    public function update(UpdateUserRequest $request, User $organisation): RedirectResponse
    {
        abort_if(! $organisation->is_organisation, 404);

        $request->validate([
            'organisation_name' => 'required|string|max:180',
        ]);

        $input = $request->all();
        $input['is_organisation'] = true;
        $this->userRepo->update($input, $organisation);

        Flash::success(__('messages.organization.organization_updated'));

        return redirect(route('organisation.index'));
    }

    public function destroy(User $organisation): JsonResponse
    {
        if ($organisation->getRoleNames()[0] == 'admin' && $organisation->is_organisation) {
            $affiliateUsers = AffiliateUser::whereUserId($organisation->id)
                ->orWhere('affiliated_by', $organisation->id)
                ->get();
            $withdrawals = Withdrawal::whereUserId($organisation->id)->get();

            foreach ($withdrawals as $withdrawal) {
                $withdrawalTransactions = WithdrawalTransaction::where('withdrawal_id', $withdrawal->id)->get();
                foreach ($withdrawalTransactions as $transaction) {
                    $transaction->delete();
                }

                $withdrawal->delete();
            }

            foreach ($affiliateUsers as $affiliateUser) {
                $affiliateUser->delete();
            }

            NfcOrderTransaction::where('user_id', $organisation->id)->delete();
            NfcOrders::where('user_id', $organisation->id)->delete();
            Vcard::where('tenant_id', $organisation->tenant_id)->delete();
            CustomDomain::where('tenant_id', $organisation->tenant_id)->delete();
            MultiTenant::where('id', $organisation->tenant_id)->delete();
            $organisation->delete();

            return $this->sendSuccess(__('messages.organization.organization_deleted'));
        }

        return $this->sendError(__('messages.organization.not_allowed_access'));
    }
}
