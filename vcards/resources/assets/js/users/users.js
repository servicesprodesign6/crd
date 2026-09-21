Livewire.hook("element.init", () => {
    loadUserfilter();
});

listenClick(".user-is-verified", function () {
    let userId = $(this).data("id");
    let updateUrl = route("users.email-verified", userId);
    $.ajax({
        type: "get",
        url: updateUrl,
        success: function (response) {
            Livewire.dispatch("refresh");
            displaySuccessMessage(response.message);
        },
    });
});

listenClick(".user-resend-verification-email", function () {
    let $button = $(this);
    let userId = $(this).data("id");
    let resendUrl = route("users.resend-verification-email", userId);

    if ($button.prop("disabled")) {
        return;
    }

    $button.prop("disabled", true);
    $button.addClass("disabled");

    $.ajax({
        type: "POST",
        url: resendUrl,
        success: function (response) {
            displaySuccessMessage(response.message);
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
        complete: function () {
            $button.prop("disabled", false);
            $button.removeClass("disabled");
        },
    });
});

listenClick(".user-active", function () {
    let userId = $(this).data("id");
    let updateUrl = route("users.status", userId);
    $.ajax({
        type: "get",
        url: updateUrl,
        success: function (response) {
            displaySuccessMessage(response.message);
            Livewire.dispatch("refresh");
        },
    });
});
listenClick(".organisation-user-is-verified", function () {
    let userId = $(this).data("id");
    let updateUrl = route("organisation.users.email-verified", userId);
    $.ajax({
        type: "get",
        url: updateUrl,
        success: function (response) {
            Livewire.dispatch("refresh");
            displaySuccessMessage(response.message);
        },
    });
});

listenClick(".organisation-user-active", function () {
    let checkbox = $(this);
    let previousState = !checkbox.is(":checked");
    let userId = $(this).data("id");
    let updateUrl = route("organisation.users.status", userId);
    $.ajax({
        type: "get",
        url: updateUrl,
        success: function (response) {
            displaySuccessMessage(response.message);
            Livewire.dispatch("refresh");
        },
        error: function (result) {
            checkbox.prop("checked", previousState);
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenClick(".vcards-verified", function () {
    let userId = $(this).data("id");
    let updateUrl = route("vcard.verified", userId);
    $.ajax({
        type: "get",
        url: updateUrl,
        success: function (response) {
            Livewire.dispatch("refresh");
            displaySuccessMessage(response.message);
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenClick(".user-delete-btn", function (event) {
    let recordId = $(event.currentTarget).data("id");
    let name = $(event.currentTarget).data("name");
    let deleteName =
        name == "superAdmin" ? Lang.get("js.admin") : Lang.get("js.user");

    deleteItem(route("users.destroy", recordId), deleteName);
});

listenClick(".admin-delete-btn", function (event) {
    let recordId = $(event.currentTarget).data("id");
    let name = $(event.currentTarget).data("name");
    let deleteName =
        name == "superAdmin" ? Lang.get("js.admin") : Lang.get("js.user");

    deleteItem(route("admins.destroy", recordId), deleteName);
});

listenClick(".organisation-delete-btn", function (event) {
    let recordId = $(event.currentTarget).data("id");

    deleteItem(route("organisation.destroy", recordId), "organisation");
});
listenClick(".organisation-user-delete-btn", function (event) {
    let recordId = $(event.currentTarget).data("id");

    deleteItem(route("organisation.users.destroy", recordId), "user");
});
listen("contextmenu", ".user-impersonate", function (e) {
    e.preventDefault(); // Stop right click on link
    return false;
});

listen("contextmenu", ".user-admin-impersonate", function (e) {
    e.preventDefault(); // Stop right click on link
    return false;
});

var control = false;
listen("keyup keydown", function (e) {
    control = e.ctrlKey;
});

listenClick(".user-impersonate", function () {
    if (control) {
        return false; // Stop ctrl + click on link
    }
    let id = $(this).data("id");
    let element = document.createElement("a");
    element.setAttribute("href", route("impersonate", id));
    element.setAttribute("data-turbo", false);
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
    $(".user-impersonate").prop("disabled", true);
});

listenClick(".user-admin-impersonate", function () {
    if (control) {
        return false; // Stop ctrl + click on link
    }
    let id = $(this).data("id");
    let element = document.createElement("a");
    element.setAttribute("href", route("admin.impersonate", id));
    element.setAttribute("data-turbo", false);
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
    $(".user-admin-impersonate").prop("disabled", true);
});

function isEmailUser(email) {
    let regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}

listenSubmit("#userCreateForm", function () {
    if ($.trim($("#userFirstName").val()) == "") {
        displayErrorMessage(Lang.get("js.first_name_required"));
        return false;
    }

    if ($.trim($("#userLastName").val()) == "") {
        displayErrorMessage(Lang.get("js.last_name_required"));
        return false;
    }
    if (!isEmailUser($("#email").val())) {
        displayErrorMessage(Lang.get("js.enter_valid_email"));
        return false;
    }

    let passwordVal = $("#password").val();
    if ($.trim(passwordVal) == "") {
        displayErrorMessage(Lang.get("js.passwords"));
        return false;
    }
    if (passwordVal.length < 8) {
        displayErrorMessage(Lang.get("js.password_character"));
        return false;
    }

    let confirmPassWord = $("#cPassword").val();
    if (passwordVal !== confirmPassWord) {
        displayErrorMessage(Lang.get("js.password_must_match"));
        return false;
    }
});

listenSubmit("#userEditForm", function () {
    if ($.trim($("#userFirstName").val()) == "") {
        displayErrorMessage(Lang.get("js.first_name_required"));
        return false;
    }
    if (!isEmailUser($("#email").val())) {
        displayErrorMessage(Lang.get("js.enter_valid_email"));
        return false;
    }

    if ($.trim($("#userLastName").val()) == "") {
        displayErrorMessage(Lang.get("js.last_name_required"));
        return false;
    }
});
listenSubmit("#organisationUserCreateForm", function () {
    if ($.trim($("#userFirstName").val()) == "") {
        displayErrorMessage(Lang.get("js.first_name_required"));
        return false;
    }

    if ($.trim($("#userLastName").val()) == "") {
        displayErrorMessage(Lang.get("js.last_name_required"));
        return false;
    }
    if (!isEmailUser($("#email").val())) {
        displayErrorMessage(Lang.get("js.enter_valid_email"));
        return false;
    }

    let passwordVal = $("#password").val();
    if ($.trim(passwordVal) == "") {
        displayErrorMessage(Lang.get("js.passwords"));
        return false;
    }
    if (passwordVal.length < 8) {
        displayErrorMessage(Lang.get("js.password_character"));
        return false;
    }

    let confirmPassWord = $("#cPassword").val();
    if (passwordVal !== confirmPassWord) {
        displayErrorMessage(Lang.get("js.password_must_match"));
        return false;
    }
});

listenSubmit("#organisationUserEditForm", function () {
    if ($.trim($("#userFirstName").val()) == "") {
        displayErrorMessage(Lang.get("js.first_name_required"));
        return false;
    }
    if (!isEmailUser($("#email").val())) {
        displayErrorMessage(Lang.get("js.enter_valid_email"));
        return false;
    }

    if ($.trim($("#userLastName").val()) == "") {
        displayErrorMessage(Lang.get("js.last_name_required"));
        return false;
    }
});

listenClick(".user-change-password", function () {
    let userId = $(this).attr("data-id");
    $("#changePasswordUserId").val(userId);
    $("#changeUserPasswordModal").modal("show").appendTo("body");
});
listenClick(".organisation-user-change-password", function () {
    let userId = $(this).attr("data-id");
    $("#changeOrganisationUserPasswordId").val(userId);
    $("#changeOrganisationUserPasswordModal").modal("show").appendTo("body");
});

listenClick("#UserPasswordChangeBtn", function () {
    let userId = $("#changePasswordUserId").val();
    $(this).attr("disabled", true);

    $.ajax({
        url: route("changePassword", userId),
        type: "PUT",
        data: $("#changeUserPasswordForm").serialize(),
        success: function (result) {
            $("#changeUserPasswordModal").modal("hide");
            displaySuccessMessage(result.message);
            $("#UserPasswordChangeBtn").attr("disabled", false);
        },
        error: function error(result) {
            $("#UserPasswordChangeBtn").attr("disabled", false);
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenHiddenBsModal("#changeUserPasswordModal", function () {
    $("#changeUserPasswordForm")[0].reset();
});
listenClick("#organisationUserPasswordChangeBtn", function () {
    let userId = $("#changeOrganisationUserPasswordId").val();
    $(this).attr("disabled", true);

    $.ajax({
        url: route("organisation.users.change-password", userId),
        type: "PUT",
        data: $("#changeOrganisationUserPasswordForm").serialize(),
        success: function (result) {
            $("#changeOrganisationUserPasswordModal").modal("hide");
            displaySuccessMessage(result.message);
            $("#organisationUserPasswordChangeBtn").attr("disabled", false);
        },
        error: function error(result) {
            $("#organisationUserPasswordChangeBtn").attr("disabled", false);
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenHiddenBsModal("#changeOrganisationUserPasswordModal", function () {
    $("#changeOrganisationUserPasswordForm")[0].reset();
});
function loadUserfilter() {
    $("#userStatus").select2();
    $("#organisationUserStatus").select2();
}
listen("change", "#userStatus", function () {
    Livewire.dispatch("statusFilter", { status: $(this).val() });
    window.hideDropdownManually(
        $("#dropdownMenuUserStatus"),
        $(".dropdown-menu")
    );
});
listen("change", "#organisationUserStatus", function () {
    Livewire.dispatch("statusFilter", { status: $(this).val() });
    window.hideDropdownManually(
        $("#dropdownMenuOrganisationUserStatus"),
        $(".dropdown-menu")
    );
});
function hideDropdownManually(button, menu) {
    button.attr("aria-expanded", "false"); // Set aria-expanded attribute to false on the dropdown button
    menu.removeClass("show"); // Remove 'show' class from the dropdown menu
}
listen("click", "#userResetFilter", function () {
    $("#userStatus").val(2).change();
    Livewire.dispatch("statusFilter", { status: "" });
    window.hideDropdownManually(
        $("#dropdownMenuUserStatus"),
        $(".dropdown-menu")
    );
});
listen("click", "#organisationUserResetFilter", function () {
    $("#organisationUserStatus").val(2).change();
    Livewire.dispatch("statusFilter", { status: "" });
    window.hideDropdownManually(
        $("#dropdownMenuOrganisationUserStatus"),
        $(".dropdown-menu")
    );
});
listenKeyup(".check-email", function () {
         let originalEmail = document.getElementById('originalEmail').value;
         let email = $(this).val();

         if(email == originalEmail || email == ""){
             $("#email-error-msg").text("");
             return false;
         }
         $.ajax({
             url: route('check.email', email),
             type: "GET",
             success: function (result) {
                 if(result.success){
                     $("#email-error-msg").text("");
                 }else{
                     $("#email-error-msg").text(Lang.get("js.check_email"));
                 }
             },
         });
});

document.addEventListener("bulk-delete-user", function (data) {
    let userIds = data.detail;

    if (userIds.length > 0) {
        swal({
            title: "Delete" + " !",
            text: Lang.get("js.are_you_sure_want_to_delete_selected_Users"),
            buttons: {
                confirm: "Yes",
                cancel: "No",
            },
            reverseButtons: true,
            icon: sweetAlertIcon,
        }).then(function (willDelete) {
            if (willDelete) {
                Livewire.dispatch("deleteUser", userIds);
            }
        });
    } else {
        displayErrorMessage(
            "js.please_select_one_or_more_records_for_multiples_delete"
        );
        return false;
    }
});

document.addEventListener("delete-user-success", function () {
    Livewire.dispatch("refresh");
    Livewire.dispatch("resetPageTable");
    swal({
        icon: "success",
        title: Lang.get("js.deleted") + " !",
        text: "User " + Lang.get("js.has_been_deleted"),
        timer: 3000,
        buttons: {
            confirm: Lang.get("js.ok"),
        },
    });
});

document.addEventListener("bulk-delete-organisation", function (data) {
    let userIds = data.detail;

    if (userIds.length > 0) {
        swal({
            title: "Delete" + " !",
            text: Lang.get("js.are_you_sure_want_to_delete_selected_Organization"),
            buttons: {
                confirm: "Yes",
                cancel: "No",
            },
            reverseButtons: true,
            icon: sweetAlertIcon,
        }).then(function (willDelete) {
            if (willDelete) {
                Livewire.dispatch("deleteUser", userIds);
            }
        });
    } else {
        displayErrorMessage(
            "js.please_select_one_or_more_records_for_multiples_delete"
        );
        return false;
    }
});

document.addEventListener("delete-organisation-success", function () {
    Livewire.dispatch("refresh");
    Livewire.dispatch("resetPageTable");
    swal({
        icon: "success",
        title: Lang.get("js.deleted") + " !",
        text: "Organization " + Lang.get("js.has_been_deleted"),
        timer: 3000,
        buttons: {
            confirm: Lang.get("js.ok"),
        },
    });
});

document.addEventListener("bulk-delete-organisation-user", function (data) {
    let userIds = data.detail;

    if (userIds.length > 0) {
        swal({
            title: "Delete" + " !",
            text: Lang.get("js.are_you_sure_want_to_delete_selected_Users"),
            buttons: {
                confirm: "Yes",
                cancel: "No",
            },
            reverseButtons: true,
            icon: sweetAlertIcon,
        }).then(function (willDelete) {
            if (willDelete) {
                Livewire.dispatch("deleteOrganisationUser", userIds);
            }
        });
    } else {
        displayErrorMessage(
            "js.please_select_one_or_more_records_for_multiples_delete"
        );
        return false;
    }
});

document.addEventListener("delete-organisation-user-success", function () {
    Livewire.dispatch("refresh");
    Livewire.dispatch("resetPageTable");
    swal({
        icon: "success",
        title: Lang.get("js.deleted") + " !",
        text: "User " + Lang.get("js.has_been_deleted"),
        timer: 3000,
        buttons: {
            confirm: Lang.get("js.ok"),
        },
    });
});

document.addEventListener("bulk-delete-error", function () {
    displayErrorMessage(
        "js.please_select_one_or_more_records_for_multiples_delete"
    );
});

listenChange('#canCreateVcard', function () {
    if ($(this).is(':checked')) {
        $('#noOfVcardInput').removeClass('d-none');
    } else {
        $('#noOfVcardInput').addClass('d-none');
        $('#noOfVcardInput').find('input').val('');
    }
});

listenChange('#canCreateWhatsappStore', function () {
    if ($(this).is(':checked')) {
        $('#noOfWhatsappStoreInput').removeClass('d-none');
    } else {
        $('#noOfWhatsappStoreInput').addClass('d-none');
        $('#noOfWhatsappStoreInput').find('input').val('');
    }
});
