<?php

namespace App\Livewire;

use App\Models\GalleryCategory;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;

class VcardGalleryCategoryTable extends LivewireTableComponent
{
    protected $model = GalleryCategory::class;

    public bool $showButtonOnHeader = true;

    public string $buttonComponent = 'vcards.gallery-categories.add-button';

    protected $listeners = ['refresh' => '$refresh', 'resetPageTable'];

    public $vcardId;

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setPageName('vcard-gallery-category-table');
        $this->setDefaultSort('created_at', 'desc');
        $this->setColumnSelectStatus(false);
        $this->setQueryStringStatus(false);
        $this->resetPage('vcard-gallery-category-table');

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
            Column::make(__('messages.common.name'), 'name')->sortable()->searchable(),
            Column::make(__('messages.common.action'), 'id')->view('vcards.gallery-categories.columns.action'),
        ];
    }

    public function builder(): Builder
    {
        return GalleryCategory::query()->where('vcard_id', $this->vcardId);
    }

    public function placeholder()
    {
        return view('lazy_loading.without-filter-skelecton');
    }
}
