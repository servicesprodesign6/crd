let paymentLinkId = null;
let originalPaymentLinkData = null;

const UPI_TOOLTIP_HTML = `${Lang.get('js.upi_tooltip')}`;

function initTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        bootstrap.Tooltip.getInstance(el)?.dispose();
        new bootstrap.Tooltip(el);
    });
}

$(document).on('click', '#addPaymentLinkBtn', function () {
    $('#addPaymentLinkForm')[0].reset();
    $('#paymentLinkIconPreview').css('background-image', 'url("' + ($('#addPaymentLinkForm').data('default-icon') || asset('assets/images/default_service.png')) + '")');
    $('#content-container').find('textarea[name="description"]').val('');
    $('#payment_link_display_type_create').val('1').trigger('change');
    $('#addPaymentLinkModal').modal('show');
    setTimeout(function () { initTooltips(); }, 100);
});

$(document).on('change', '#paymentLinkImg', function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const previewElement = document.getElementById('paymentLinkIconPreview');
            if (previewElement) {
                previewElement.style.backgroundImage = `url(${e.target.result})`;
            }
        };
        reader.readAsDataURL(file);
    }
});

$(document).on('change', '#editPaymentLinkImg', function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const previewElement = document.getElementById('editPaymentLinkPreview');
            if (previewElement) {
                previewElement.style.backgroundImage = `url(${e.target.result})`;
            }
        };
        reader.readAsDataURL(file);
    }
});

$(document).on('change', '.image-upload[data-preview-id="paymentLinkContentPreview"]', function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const previewElement = document.getElementById('paymentLinkContentPreview');
            if (previewElement) {
                previewElement.style.backgroundImage = `url(${e.target.result})`;
            }
        }
        reader.readAsDataURL(file);
    }
});

$(document).on('change', '.image-upload[data-preview-id="editPaymentLinkContentPreview"]', function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const previewElement = document.getElementById('editPaymentLinkContentPreview');
            if (previewElement) {
                previewElement.style.backgroundImage = `url(${e.target.result})`;
            }
        };
        reader.readAsDataURL(file);
    }
});

$(document).on('change', '#payment_link_display_type_create', function () {
    toggleContentField($(this));
});

$(document).on('select2:select', '#payment_link_display_type_create', function () {
    toggleContentField($(this));
});

$(document).on('change', '#payment_link_display_type_edit', function () {
    toggleContentField($(this));
});

$(document).on('select2:select', '#payment_link_display_type_edit', function () {
    toggleContentField($(this));
});

function toggleContentField(selectElement) {
    const selectedValue = selectElement.val();

    let container;
    if (selectElement.attr('id') === 'payment_link_display_type_create') {
        container = $('#content-container');
    } else if (selectElement.attr('id') === 'payment_link_display_type_edit') {
        container = $('#edit-content-container');
    }

    if (!container || container.length === 0) {
        return;
    }

    if (selectElement.attr('id') === 'payment_link_display_type_create') {
        if (selectedValue == '2') {
            $('#upi-note-container-create').removeClass('d-none');
        } else {
            $('#upi-note-container-create').addClass('d-none');
        }
    }

    if (selectElement.attr('id') === 'payment_link_display_type_edit') {
        if (selectedValue == '2') {
            $('#upi-note-container-edit').removeClass('d-none');
        } else {
            $('#upi-note-container-edit').addClass('d-none');
        }
    }

    let currentDescription = '';
    if (selectElement.attr('id') === 'payment_link_display_type_edit') {
        currentDescription = $('#editDescription').val() || '';
    } else if (container.find('textarea[name="description"]').length > 0) {
        currentDescription = container.find('textarea[name="description"]').val() || '';
    }
    if (!currentDescription && selectElement.attr('id') === 'payment_link_display_type_edit' && window.currentPaymentLinkDescription) {
        currentDescription = window.currentPaymentLinkDescription;
    }

    if (selectedValue == '4') {
        // For image type, create consistent UI for both create and edit
        if (selectElement.attr('id') === 'payment_link_display_type_create') {
            container.html(`
                <label for="paymentLinkContentPreview" class="form-label required fs-6 fw-bolder text-gray-700 mb-3">${Lang.get('js.image')}:</label>
                <div class="d-block">
                    <div class="image-picker">
                        <div class="image previewImage" id="paymentLinkContentPreview" style="background-image: url('../../../../assets/images/default_service.png')">
                        </div>
                        <span class="picker-edit rounded-circle text-gray-500 fs-small" data-bs-toggle="tooltip" data-placement="top" data-bs-original-title="${Lang.get('js.image')}">
                            <label>
                                <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                                <input type="file" id="paymentLinkContent" name="image" class="image-upload file-validation d-none crop-image-input" accept="image/*" data-preview-id="paymentLinkContentPreview" />
                            </label>
                        </span>
                    </div>
                    <div class="form-text">${Lang.get('js.allowed_file_types')}.</div>
                </div>`);
        } else {
            let previewImageUrl = '../../../../assets/images/default_service.png';

            if (originalPaymentLinkData && originalPaymentLinkData.display_type == '4' && originalPaymentLinkData.media) {
                const contentMedia = originalPaymentLinkData.media.find(media =>
                    media.collection_name === 'vcards/payment_link_description'
                );
                if (contentMedia) {
                    previewImageUrl = contentMedia.original_url;
                }
            }

            container.html(`
                <label for="editPaymentLinkContentPreview" class="form-label required fs-6 fw-bolder text-gray-700 mb-3">${Lang.get('js.image')}:</label>
                <div class="d-block">
                    <div class="image-picker">
                        <div class="image previewImage" id="editPaymentLinkContentPreview" style="background-image: url('${previewImageUrl}')">
                        </div>
                        <span class="picker-edit rounded-circle text-gray-500 fs-small" data-bs-toggle="tooltip" data-placement="top" data-bs-original-title="${Lang.get('js.image')}">
                            <label>
                                <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                                <input type="file" id="editPaymentLinkContent" name="image" class="image-upload file-validation d-none crop-image-input" accept="image/*" data-preview-id="editPaymentLinkContentPreview" />
                            </label>
                        </span>
                    </div>
                    <div class="form-text">${Lang.get('js.allowed_file_types')}.</div>
                </div>`);
        }
    } else {
        const isUPI = selectedValue == '2';
        container.html(`
            <label class="form-label fs-6 fw-bolder text-gray-700 mb-3">
                ${Lang.get('js.payment_link_description')}
                <span class="text-danger">*</span>
            </label>
            <textarea name="description"
                    class="form-control"
                    rows="5"
                    placeholder="${Lang.get('js.payment_link_description')}"
                    required>${currentDescription}</textarea>
        `);
        if (isUPI) {
            initTooltips();
        }
    }
}

$(document).on('click', '.payment-link-edit-btn', function (event) {
    if (paymentLinkId !== null && paymentLinkId !== '') {
        $('#editPaymentLinkForm')[0].reset();
    }
    paymentLinkId = $(event.currentTarget).data('id');
    renderPaymentLinkData(paymentLinkId);
});

window.renderPaymentLinkData = function (id) {
    $.ajax({
        url: route('payment-link.edit', id),
        type: 'GET',
        success: function (result) {
            if (result.success) {
                let paymentLink = result.data;
                originalPaymentLinkData = paymentLink;
                $('#paymentLinkId').val(paymentLink.id);
                $('#editLabel').val(paymentLink.label);

                $('#payment_link_display_type_edit').val(paymentLink.display_type);
                $('#editDescription').val(paymentLink.description);
                window.currentPaymentLinkDescription = paymentLink.description || '';

                $('#editPaymentLinkModal').modal('show');

                setTimeout(function () {
                    $('#payment_link_display_type_edit').trigger('change');

                    if (paymentLink.display_type == '4') {
                        if (paymentLink.media && paymentLink.media.length > 0) {
                            const contentMedia = paymentLink.media.find(media =>
                                media.collection_name === 'vcards/payment_link_description'
                            );
                            if (contentMedia) {
                                $('#editPaymentLinkContentPreview').css('background-image', `url(${contentMedia.original_url})`);
                            }
                        }
                    }
                }, 150);

                if (paymentLink.media && paymentLink.media.length > 0) {
                    $('#editPaymentLinkPreview').css('background-image', 'url("' + paymentLink.media[0].original_url + '")');
                } else {
                    $('#editPaymentLinkPreview').css('background-image', 'url("' + asset('assets/images/default_service.png') + '")');
                }
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
};

$(document).on('click', '.payment-link-delete-btn', function (event) {
    let paymentLinkId = $(event.currentTarget).data('id');
    deleteItem(route('payment-link.destroy', paymentLinkId), Lang.get('js.payment_link'));
});

$(document).on('submit', '#addPaymentLinkForm', function (event) {
    event.preventDefault();

    $('#paymentLinkSave').prop('disabled', true);

    let formData = new FormData(this);

    $.ajax({
        url: route('payment-link.store'),
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,

        success: function (result) {
            if (result.success) {
                displaySuccessMessage(result.message);
                $('#addPaymentLinkModal').modal('hide');
                Livewire.dispatch('refresh');
                $('#addPaymentLinkForm')[0].reset();
            }
        },

        error: function (xhr) {
            let errorMessage = xhr.responseJSON?.message || 'Failed to save payment link';

            if (xhr.responseJSON?.errors) {
                $.each(xhr.responseJSON.errors, function(field, messages) {
                    errorMessage = messages[0];
                    return false;
                });
            }

            displayErrorMessage(errorMessage);
        },

        complete: function () {
            $('#paymentLinkSave').prop('disabled', false);
        }
    });
});

$(document).on('submit', '#editPaymentLinkForm', function (event) {
    event.preventDefault();
    $('#paymentLinkUpdate').prop('disabled', true);

    let formData = new FormData($(this)[0]);
    formData.append('_method', 'POST');

    $.ajax({
        url: route('payment-link.update', paymentLinkId),
        type: 'POST',
        dataType: 'json',
        data: formData,
        processData: false,
        contentType: false,
        success: function success(result) {
            if (result.success) {
                displaySuccessMessage(result.message);
                $('#editPaymentLinkModal').modal('hide');
                Livewire.dispatch('refresh');
            }
        },
        error: function error(result) {
            let errorMessage = result.responseJSON?.message || 'Failed to update payment link';

            if (result.responseJSON?.errors) {
                $.each(result.responseJSON.errors, function(field, messages) {
                    errorMessage = messages[0];
                    return false;
                });
            }

            displayErrorMessage(errorMessage);
        },
        complete: function complete() {
            $('#paymentLinkUpdate').prop('disabled', false);
        }
    });
});

listenClick(".payment-link-view-btn", function (event) {
    let paymentLinkId = $(event.currentTarget).data("id");
    paymentLinkRenderDataShow(paymentLinkId);
});

function paymentLinkRenderDataShow(id) {
    $.ajax({
        url: route("payment-link.edit", id),
        type: "GET",
        success: function (result) {
            if (result.success) {
                $("#showLabel").text("");
                $("#showDisplayType").text("");
                $("#showDescription").text("");

                $("#showLabel").append(result.data.label);
                $("#showDisplayType").append(result.data.display_type_label);

                //discription image
                if (result.data.display_type == '4') {
                    $('#description-field').addClass('d-none');
                    $('#image-field').removeClass('d-none');

                    if (result.data.media && result.data.media.length > 1) {
                        const contentMedia = result.data.media.find(media => media.collection_name === 'vcards/payment_link_description');
                        if (contentMedia) {
                            $('#showPaymentLinkContentImage').attr('src', contentMedia.original_url);
                        } else {
                            $('#showPaymentLinkContentImage').attr('src', result.data.media[1]?.original_url || result.data.media[0]?.original_url || asset('assets/images/default_service.png'));
                        }
                    } else {
                        $('#showPaymentLinkContentImage').attr('src', asset('assets/images/default_service.png'));
                    }
                } else {
                    $('#image-field').addClass('d-none');
                    $('#description-field').removeClass('d-none');

                    let element = document.createElement("textarea");
                    element.innerHTML = result.data.description ?? "";
                    $("#showDescription").append(element.value);
                }

                //icon image
                const iconMedia = result.data.media.find(media =>
                    media.collection_name === 'vcards/payment_link_image'
                );

                if (iconMedia) {
                    $("#showPaymentLinkIcon").css(
                        "background-image",
                        'url("' + iconMedia.original_url + '")'
                    );
                } else {
                    $("#showPaymentLinkIcon").css(
                        "background-image",
                        'url("' + asset("assets/images/default_service.png") + '")'
                    );
                }
                $("#showPaymentLinkModal").modal("show");
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
}
