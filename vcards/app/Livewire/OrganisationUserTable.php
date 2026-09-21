<?php

namespace App\Livewire;

use App\Http\Controllers\OrganisationUserController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Rappasoft\LaravelLivewireTables\Views\Column;

class OrganisationUserTable extends LivewireTableComponent
{
    protected $model = User::class;
    public bool $showButtonOnHeader = true;
    public string $buttonComponent = 'organisation_users.add-button';

    protected $listeners = ['refresh' => '$refresh', 'statusFilter', 'resetPageTable', 'deleteOrganisationUser'];
    public $status = '';
    public $selectedRecordId;

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setPageName('organisation-user-table');
        $this->setDefaultSort('created_at', 'desc');
        $this->setSortingPillsStatus(false);
        $this->setQueryStringStatus(false);
        $this->resetPage('organisation-user-table');
        $this->setThAttributes(function (Column $column) {
            if ($column->isField('organisation_name') || $column->isField('email_verified_at') || $column->isField('is_active') || $column->isField('created_at')) {
                return [
                    'class' => $column->isField('organisation_name') ? 'bg-red' : 'text-center',
                ];
            }

            return [];
        });
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.user.full_name'), 'first_name')
                ->searchable(function (Builder $query, $searchTerm) {
                    $query->whereRaw("TRIM(CONCAT(first_name,' ',last_name,' ')) like ?", ["%{$searchTerm}%"]);
                })
                ->sortable()
                ->view('organisation_users.columns.name'),
            Column::make(__('messages.user.full_name'), 'last_name')->sortable()->searchable()->hideIf(1),
            Column::make('email', 'email')->hideIf(1)->searchable(),
            Column::make(__('messages.user.email_verified'), 'email_verified_at')->view('organisation_users.columns.email_verified'),
            Column::make(__('messages.user.impersonate'), 'id')->view('organisation_users.columns.impersonate'),
            Column::make(__('messages.common.is_active'), 'is_active')->view('organisation_users.columns.is_active'),
            Column::make(__('messages.common.action'), 'created_at')->view('organisation_users.columns.action'),
        ];
    }

    public function statusFilter($status)
    {
        $this->status = $status;
        $this->setBuilder($this->builder());
    }

    public function builder(): Builder
    {
        $status = $this->status;
        $organisationId = getLogInUser()->organisation_id ?: getLogInUserId();

        $query = User::role(Role::ROLE_ADMIN)
            ->with(['media'])
            ->where('users.tenant_id', getLogInTenantId())
            ->where('users.organisation_id', $organisationId)
            ->where('users.is_organisation', false);

        if ($status === (string) User::IS_ACTIVE || $status === User::IS_ACTIVE) {
            $query->where('is_active', User::IS_ACTIVE);
        }

        if ($status === (string) User::DEACTIVATE || $status === User::DEACTIVATE) {
            $query->where('is_active', User::DEACTIVATE);
        }

        return $query->select('users.*');
    }

    public array $bulkActions = [
        'bulkDelete' => 'Delete',
    ];

    public function setSelectedRecord($recordId)
    {
        $this->selectedRecordId = $recordId;
    }

    public function bulkDelete()
    {
        if (count($this->getSelected()) > 0) {
            $this->dispatch('bulk-delete-organisation-user', $this->getSelected());
        } else {
            $this->dispatch('bulk-delete-error');
        }
    }

    public function deleteOrganisationUser($userIds)
    {
        try {
            $organisationId = getLogInUser()->organisation_id ?: getLogInUserId();

            User::whereIn('id', $userIds)
                ->where('tenant_id', getLogInTenantId())
                ->where('organisation_id', $organisationId)
                ->where('is_organisation', false)
                ->each(function (User $user) {
                    app(OrganisationUserController::class)->deleteOrganisationUser($user);
                });

            $this->dispatch('delete-organisation-user-success');
            $this->clearSelected();
        } catch (\Exception $e) {
            Log::error('Bulk organisation user deletion failed: '.$e->getMessage());
        }
    }

    public function resetPageTable($pageName = 'organisation-user-table')
    {
        $rowsPropertyData = $this->getRows()->toArray();
        $prevPageNum = $rowsPropertyData['current_page'] - 1;
        $prevPageNum = $prevPageNum > 0 ? $prevPageNum : 1;
        $pageNum = count($rowsPropertyData['data']) > 0 ? $rowsPropertyData['current_page'] : $prevPageNum;

        $this->setPage($pageNum, $pageName);
    }

    public function placeholder()
    {
        return view('lazy_loading.listing-skelecton');
    }
}
