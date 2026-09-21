<?php

namespace App\Livewire;

use App\Models\VcardPaymentLink;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;


class VcardPaymentLinkTable extends LivewireTableComponent
{
    protected $model = VcardPaymentLink::class;

    public bool $showButtonOnHeader = true;

    public string $buttonComponent = 'vcards.payment-link.add-button';

    protected $listeners = ['refresh' => '$refresh', 'resetPageTable'];

    public $vcardId;

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setPageName('vcard-payment-link-table');
        $this->setDefaultSort('created_at', 'desc');
        $this->setColumnSelectStatus(false);
        $this->setQueryStringStatus(false);
        $this->resetPage('vcard-payment-link-table');

        $this->setThAttributes(function (Column $column) {
            if ($column->isField('id')) {
                return [
                    'class' => 'd-flex justify-content-center',
                ];
            }

            return [];
        });
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.vcard.image'), 'created_at')->view('vcards.payment-link.columns.icon'),
            Column::make(__('messages.vcard.label'), 'label')
                ->sortable()->searchable(),
            Column::make(__('messages.vcard.display_type'), 'display_type')
                ->view('vcards.payment-link.columns.display_type')
                ->sortable()->searchable(),
            Column::make(__('messages.common.action'), 'id')->view('vcards.payment-link.columns.action'),
        ];
    }

    public function builder(): Builder
    {
        return VcardPaymentLink::whereVcardId($this->vcardId)->select('vcard_payment_links.*');
    }

    public function resetPageTable($pageName = 'vcard-payment-link-table')
    {
        $rowsPropertyData = $this->getRows()->toArray();
        $prevPageNum = $rowsPropertyData['current_page'] - 1;
        $prevPageNum = $prevPageNum > 0 ? $prevPageNum : 1;
        $pageNum = count($rowsPropertyData['data']) > 0 ? $rowsPropertyData['current_page'] : $prevPageNum;

        $this->setPage($pageNum, $pageName);
    }

    public function placeholder()
    {
        return view('lazy_loading.without-filter-skelecton');
    }
}
