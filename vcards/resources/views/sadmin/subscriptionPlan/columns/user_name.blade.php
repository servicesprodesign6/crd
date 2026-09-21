@if (!empty($row->tenant->organisationUser))
    {{ $row->tenant->organisationUser->organisation_name ?: $row->tenant->organisationUser->full_name }}
@elseif (!empty($row->tenant->user))
    {{ $row->tenant->user->full_name }}
@endif
