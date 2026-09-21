listenClick("#addGalleryBtn", function () {
    $("#addGalleryModal").modal("show");
});

let isCategoryOpening = false;
listenClick("#addGalleryCategoryBtn", function () {
    isCategoryOpening = true;
    if ($("#addGalleryModal").hasClass("show")) {
        window.openModalId = "#addGalleryModal";
    } else if ($("#editGalleryModal").hasClass("show")) {
        window.openModalId = "#editGalleryModal";
    }
    $(window.openModalId).modal("hide");
    $("#addGalleryCategoryModal").modal("show");
});

listenHiddenBsModal("#addGalleryCategoryModal", function () {
    $("#addGalleryCategoryForm")[0].reset();
    if (isCategoryOpening && window.openModalId) {
        $(window.openModalId).modal("show");
        window.openModalId = null;
        isCategoryOpening = false;
    }
});

listenHiddenBsModal("#addGalleryModal", function (e) {
    if (isCategoryOpening) return;
    $("#addGalleryForm")[0].reset();
    $("#typeId").val(null).trigger("change");
    $("#galleryCategory").val(null).trigger("change");
    $("#videoUploadFileName").text("");
    $("#audioUploadFileName").text("");
    $("#addGalleryPreview").css(
        "background-image",
        "url(" + defaultGalleryUrl + ")"
    );
    $(".youtube_link").addClass("d-none");
    $(".image_link").removeClass("d-none");
    $("#createUploadFileName").text("");
    $(".cancel-gallery").hide();
});

listenClick(".cancel-gallery", function () {
    $("#addGalleryPreview").css(
        "background-image",
        "url(" + defaultGalleryUrl + ")"
    );
});

listenChange("#typeId", function () {
    let type = $(this).val();
    if (type == 0) {
        $(".youtube_link").addClass("d-none");
        $(".image_link").removeClass("d-none");
        $(".file_upload_button").addClass("d-none");
        $("#linkRequired").attr("required", false);
        $(".video_upload_button").addClass("d-none");
        $(".audio_upload_button").addClass("d-none");
    } else if (type == 1) {
        $(".image_link").addClass("d-none");
        $(".youtube_link").removeClass("d-none");
        $(".file_upload_button").addClass("d-none");
        $("#linkRequired").attr("required", true);
        $(".video_upload_button").addClass("d-none");
        $(".audio_upload_button").addClass("d-none");
    } else if (type == 2) {
        $(".image_link").addClass("d-none");
        $(".youtube_link").addClass("d-none");
        $(".file_upload_button").removeClass("d-none");
        $(".video_upload_button").addClass("d-none");
        $("#linkRequired").attr("required", false);
        $(".audio_upload_button").addClass("d-none");
    } else if (type == 3) {
        $(".image_link").addClass("d-none");
        $(".youtube_link").addClass("d-none");
        $(".file_upload_button").addClass("d-none");
        $(".video_upload_button").removeClass("d-none");
        $(".audio_upload_button").addClass("d-none");
        $("#linkRequired").attr("required", false);
    } else if (type == 4) {
        $(".image_link").addClass("d-none");
        $(".youtube_link").addClass("d-none");
        $(".file_upload_button").addClass("d-none");
        $(".video_upload_button").addClass("d-none");
        $(".audio_upload_button").removeClass("d-none");
        $("#linkRequired").attr("required", false);
    }
});

listenChange("#editTypeId", function () {
    let type = $(this).val();
    $(".file_upload_button").addClass("d-none");
    if (type == 0) {
        $("#editGalleryPreview").css(
            "background-image",
            "url(" + defaultGalleryUrl + ")"
        );
        $(".editYouTubeLink").addClass("d-none");
        $(".edit-image").removeClass("d-none");
        $(".video_upload_button").addClass("d-none");
        $(".audio_upload_button").addClass("d-none");
        $("#editYouTube_Link").attr("required", false);
    } else if (type == 1) {
        $(".editYouTubeLink").removeClass("d-none");
        $(".edit-image").addClass("d-none");
        $(".video_upload_button").addClass("d-none");
        $(".audio_upload_button").addClass("d-none");
        $("#editYouTube_Link").attr("required", true);
    } else if (type == 2) {
        $(".editYouTubeLink").addClass("d-none");
        $(".edit-image").addClass("d-none");
        $(".video_upload_button").addClass("d-none");
        $(".file_upload_button").removeClass("d-none");
        $(".audio_upload_button").addClass("d-none");
        $("#editYouTube_Link").attr("required", false);
        $("#uploadFileName").text("");
        $("#editGalleryUploadFile").val("");
    } else if (type == 3) {
        $(".editYouTubeLink").addClass("d-none");
        $(".edit-image").addClass("d-none");
        $(".video_upload_button").removeClass("d-none");
        $(".audio_upload_button").addClass("d-none");
        $("#editYouTube_Link").attr("required", false);
        $("#videoUploadFileName").text("");
        $("#editVideoUploadFileName").val("");
    } else if (type == 4) {
        $(".editYouTubeLink").addClass("d-none");
        $(".edit-image").addClass("d-none");
        $(".video_upload_button").addClass("d-none");
        $(".audio_upload_button").removeClass("d-none");
        $("#editYouTube_Link").attr("required", false);
        $("#audioUploadFileName").text("");
        $("#editAudioUploadFileName").val("");
    }
});

listenSubmit("#addGalleryForm", function (e) {
    e.preventDefault();

    if ($("#galleryUploadFile").val() == 0 && $("#typeId").val() == 2) {
        displayErrorMessage(Lang.get("js.upload_required"));
        return false;
    }

    $("#GallerySave").prop("disabled", true);
    $.ajax({
        url: route("gallery.store"),
        type: "POST",
        data: new FormData(this),
        contentType: false,
        processData: false,
        success: function (result) {
            if (result.success) {
                displaySuccessMessage(result.message);
                $("#createUploadFileName").text("");
                $("#addGalleryModal").modal("hide");
                $("#addGalleryForm").trigger("reset");
                $("#GallerySave").prop("disabled", false);
                $("#videoUploadFileName").text("");
                $("#audioUploadFileName").text("");
                Livewire.dispatch("refresh");
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
            $("#GallerySave").prop("disabled", false);
        },
    });
});

listenClick(".gallery-edit-btn", function (event) {
    let GalleryId = $(event.currentTarget).data("id");
    editGalleryRenderData(GalleryId);
});

listenChange("#galleryUploadFile", function (event) {
    let uploadFileName = event.target.files[0].name;
    $("#createUploadFileName").text(uploadFileName);
});
listenChange("#editGalleryUploadFile", function (event) {
    let uploadFileName = event.target.files[0].name;
    $("#uploadFileName").text(uploadFileName);
});

listenChange("#videoUploadFile", function (event) {
    let uploadFileName = event.target.files[0].name;
    $("#videoUploadFileName").text(uploadFileName);
});
listenChange("#editVideoUploadFile", function (event) {
    let uploadFileName = event.target.files[0].name;
    $("#editVideoUploadFileName").text(uploadFileName);
});

listenChange("#audioUploadFile", function (event) {
    let uploadFileName = event.target.files[0].name;
    $("#audioUploadFileName").text(uploadFileName);
});
listenChange("#editAudioUploadFile", function (event) {
    let uploadFileName = event.target.files[0].name;
    $("#editAudioUploadFileName").text(uploadFileName);
});

let galleryUrl = "";

function appendGalleryCategoryOption(selector, categoryId, categoryName, shouldSelect = false) {
    if (!categoryId || !categoryName) {
        return;
    }

    let trimmedCategoryName = categoryName.trim();
    if (trimmedCategoryName === "") {
        return;
    }

    if ($(selector + ' option[value="' + categoryId + '"]').length === 0) {
        $(selector).append(new Option(trimmedCategoryName, categoryId, false, false));
    }

    if (shouldSelect) {
        $(selector).val(categoryId).trigger("change");
    } else {
        $(selector).trigger("change");
    }
}

listenSubmit("#addGalleryCategoryForm", function (event) {
    event.preventDefault();
    $("#saveGalleryCategoryBtn").prop("disabled", true);

    $.ajax({
        url: route("gallery.category.store"),
        type: "POST",
        data: $(this).serialize(),
        success: function (result) {
            if (result.success) {
                let categoryName = $("#addGalleryCategoryForm input[name='name']").val();
                appendGalleryCategoryOption("#galleryCategory", result.data.id, categoryName, true);
                appendGalleryCategoryOption("#editGalleryCategory", result.data.id, categoryName, true);
                $("#addGalleryCategoryModal").modal("hide");
                $("#saveGalleryCategoryBtn").prop("disabled", false);
                displaySuccessMessage(result.message);
                Livewire.dispatch("refresh");
            }
        },
        error: function (result) {
            $("#saveGalleryCategoryBtn").prop("disabled", false);
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenClick(".gallery-category-edit-btn", function (event) {
    let galleryCategoryId = $(event.currentTarget).data("id");

    $.ajax({
        url: route("gallery.category.edit", galleryCategoryId),
        type: "GET",
        success: function (result) {
            if (result.success) {
                $("#galleryCategoryId").val(result.data.id);
                $("#editGalleryCategoryName").val(result.data.name);
                $("#editGalleryCategoryModal").modal("show");
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenSubmit("#editGalleryCategoryForm", function (event) {
    event.preventDefault();
    let galleryCategoryId = $("#galleryCategoryId").val();
    $("#editGalleryCategorySaveBtn").prop("disabled", true);

    $.ajax({
        url: route("gallery.category.update", galleryCategoryId),
        type: "POST",
        data: $(this).serialize(),
        success: function (result) {
            if (result.success) {
                $("#editGalleryCategoryModal").modal("hide");
                $("#editGalleryCategorySaveBtn").prop("disabled", false);
                displaySuccessMessage(result.message);
                Livewire.dispatch("refresh");
            }
        },
        error: function (result) {
            $("#editGalleryCategorySaveBtn").prop("disabled", false);
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenClick(".gallery-category-delete-btn", function (event) {
    let recordId = $(event.currentTarget).data("id");
    deleteItem(route("gallery.category.destroy", recordId), Lang.get("js.gallery_category"));
});

function editGalleryRenderData(id) {
    $.ajax({
        url: route("gallery.edit", id),
        type: "GET",
        success: function (result) {
            if (result.success) {
                $("#galleryId").val(result.data.id);
                $("#editTypeId").val(result.data.type).trigger("change");
                appendGalleryCategoryOption(
                    "#editGalleryCategory",
                    result.data.category_id,
                    result.data.category_name ? result.data.category_name : "",
                    true
                );
                $("#editGalleryPreview").css(
                    "background-image",
                    'url("' + result.data.gallery_image + '")'
                );
                $("#editYouTube_Link").val(result.data.link);
                let fileType = result.data.type;
                if (fileType == 2) {
                    $("#uploadFileName").text(result.data.media[0].file_name);
                }
                if (fileType == 3) {
                    $("#editVideoUploadFileName").text(
                        result.data.media[0].file_name
                    );
                }
                if (fileType == 4) {
                    $("#editAudioUploadFileName").text(
                        result.data.media[0].file_name
                    );
                }
                $("#editGalleryModal").modal("show");
                galleryUrl = result.data.gallery_image;
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
}

listenHiddenBsModal("#editGalleryModal", function () {
    if (isCategoryOpening) return;
    $("#editGalleryCategory").val(null).trigger("change");
    $(".edit-cancel-gallery").hide();
});

listenClick(".edit-cancel-gallery", function () {
    $("#editGalleryPreview").css("background-image", "url(" + galleryUrl + ")");
});

listenSubmit("#editGalleryForm", function (event) {
    event.preventDefault();
    if ($("#editGalleryUploadFile").val() == undefined) {
        displayErrorMessage("Please upload file");
        return false;
    }

    $("#editGallerySave").prop("disabled", true);
    let galleryId = $("#galleryId").val();
    $.ajax({
        url: route("gallery.update", galleryId),
        type: "POST",
        data: new FormData(this),
        contentType: false,
        processData: false,
        success: function (result) {
            if (result.success) {
                displaySuccessMessage(result.message);
                $("#editGalleryModal").modal("hide");
                $("#editGalleryForm").trigger("reset");
                $("#editGallerySave").prop("disabled", false);
                Livewire.dispatch("refresh");
                $(".edit-cancel-gallery").hide();
            }
        },
        error: function (result) {
            $("#editGallerySave").prop("disabled", false);
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenClick(".gallery-delete-btn", function (event) {
    let recordId = $(event.currentTarget).attr("data-id");
    deleteItem(route("gallery.destroy", recordId), Lang.get("js.gallery"));
});
