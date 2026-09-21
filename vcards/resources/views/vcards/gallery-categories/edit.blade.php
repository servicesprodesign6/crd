<div class="modal fade" id="editGalleryCategoryModal" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{{ __('messages.common.edit') }} {{ __('messages.whatsapp_stores.category') }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {!! Form::open(['id' => 'editGalleryCategoryForm']) !!}
                {{ Form::hidden('category_id', null, ['id' => 'galleryCategoryId']) }}
                {{ Form::hidden('vcard_id', $vcard->id) }}
                <div class="mb-5">
                    {{ Form::label('name', __('messages.whatsapp_stores.category') . ':', ['class' => 'form-label required']) }}
                    {{ Form::text('name', null, ['class' => 'form-control', 'id' => 'editGalleryCategoryName', 'required', 'placeholder' => __('messages.whatsapp_stores.category_placeholder')]) }}
                </div>
                <div class="d-flex">
                    {{ Form::button(__('messages.common.save'), ['type' => 'submit', 'class' => 'btn btn-primary me-3', 'id' => 'editGalleryCategorySaveBtn']) }}
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.common.discard') }}</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
