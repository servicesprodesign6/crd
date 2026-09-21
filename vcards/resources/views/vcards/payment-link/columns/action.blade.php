<div class="justify-content-center d-flex">
    <a title="{{__('messages.common.view')}}" class="btn px-1 text-info payment-link-view-btn fs-3" href="javascript:void(0)"  data-id="{{$row->id}}">
        <i class="fa-solid fa-eye"></i>
    </a>
    <a href="javascript:void(0)" class="btn px-1 text-primary payment-link-edit-btn fs-3" data-id="{{$row->id}}" title="{{__('messages.common.edit')}}">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    <a href="#" title="{{__('messages.common.delete')}}" data-id="{{$row->id}}" class="payment-link-delete-btn btn px-1 text-danger fs-3" data-turbo="false">
        <i class="fa-solid fa-trash-can"></i>
    </a>
</div>
