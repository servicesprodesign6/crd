<div class="card-toolbar custom-toolbar ms-auto">
    <div class="d-flex justify-content-end">
        @if (count($this->getSelected()) > 0)
        <a href="javascript:void(0)" wire:click.prevent="bulkDelete" wire:key="bulk-action-bulkDelete"
        class="btn btn-danger me-2 ms-0 ms-md-2 mb-md-2 mb-2 mb-sm-2 mb-lg-0 delete-selected-users">{{__('messages.vcard.delete_multiple')}}</a>
        @endif
    </div>
</div>


<div class="card-toolbar custom-toolbar">
    <div class="d-flex justify-content-end new-vcard">
         <div class="btn-group d-flex align-items-end me-2" role="group" aria-label="Basic mixed styles example" wire:ignore>
                  <button type="button" class="btn border-white table-view-show {{ (isset(getLogInUser()->vcard_table_view_type) && getLogInUser()->vcard_table_view_type == 0) ? 'btn-primary' : 'btn-white'}}" data-value="0"><i class="fa fs-2 fa-table" aria-hidden="true"></i></button>
                  <button type="button" class="btn border-white table-view-show {{ (isset(getLogInUser()->vcard_table_view_type) && getLogInUser()->vcard_table_view_type == 1) ? 'btn-primary' : 'btn-white'}}" data-value="1"><i class="fa-solid fs-2 fa-image"></i></button>
         </div>
        @if(getLogInUser()->canCreateVcards() && checkTotalVcard())
        <div class="d-flex gap-2">
            <button type="button" class="btn text-white ai-btn" id="aiVcardBtn">
                <i class="fa-solid fa-robot me-1"></i>
                {{ __('messages.vcard.ai_vcard') }}
            </button>

            <a type="button" class="btn btn-primary border-remove" href="{{ route('vcards.create')}}">{{__('messages.vcard.new_vcard')}}</a>
        </div>
        @endif
    </div>
</div>
