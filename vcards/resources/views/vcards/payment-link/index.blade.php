<div class="overflow-auto">
    <div class="table-striped w-100">
        <livewire:vcard-payment-link-table lazy :vcard-id="$vcard->id" />
    </div>
</div>
@include('vcards.payment-link.create')
@include('vcards.payment-link.edit')
@include('vcards.payment-link.show')
