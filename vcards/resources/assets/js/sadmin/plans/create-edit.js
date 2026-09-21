window.featureChecked = function (featureLength) {
    let totalFeature = $(".feature:checkbox").length;
    if (featureLength === totalFeature) {
        $("#featureAll").prop("checked", true);
    } else {
        $("#featureAll").prop("checked", false);
    }
    setMultiTemplateAll();
};

// document.addEventListener("turbo:load", loadPlanCreateEditData);
document.addEventListener("DOMContentLoaded", loadPlanCreateEditData);
function setMultiTemplateAll(){
    if ($(".templateIds").length === $(".templateIds:checked").length) {
        $("#multiTemplatesAll").prop("checked", true);
    }else{
        $("#multiTemplatesAll").prop("checked", false);
    }
}

function loadPlanCreateEditData() {
    let featureLength = $(".feature:checkbox:checked").length;
    featureChecked(featureLength);
}

listenClick("#featureAll", function () {
    if ($("#featureAll").is(":checked")) {
        $(".feature").each(function () {
            $(this).prop("checked", true);
            $('.ribbon').addClass("template-border");
            $('.ribbon').prev().prop("checked", true);
        });
        $("#whatsappStoreCheck").prop("checked", true).trigger("change");
        $("#noOfVisitorsCheck").prop("checked", true).trigger("change");
        $("#organizationUsersCheck").prop("checked", true).trigger("change");
    } else {
        $(".feature").each(function () {
            $(this).prop("checked", false);
            $('.ribbon').removeClass("template-border");
            $('.ribbon').prev().prop("checked", false);
        });
        $("#whatsappStoreCheck").prop("checked", false).trigger("change");
        $("#noOfVisitorsCheck").prop("checked", false).trigger("change");
        $("#organizationUsersCheck").prop("checked", false).trigger("change");
    }
    setMultiTemplateAll();
});
listenClick(".feature", function () {
    let featureLength = $(".feature:checkbox:checked").length;
    featureChecked(featureLength);
    if($(this).attr("name") == "dynamic_vcard") {
        if($(this).is(":checked")){
            $('.ribbon').addClass("template-border");
            $('.ribbon').prev().prop("checked", true);
        }else{
            $('.ribbon').removeClass("template-border");
            $('.ribbon').prev().prop("checked", false);
        }
        setMultiTemplateAll();
    }
});

listenClick(".screen.image", function () {
    let template = $(this).prev();
    let defaultValue = template[0].defaultValue;

    if (template.is(":checked")) {
        if (defaultValue == 22) {
            template.prop("checked", false);
            $(this).removeClass("template-border");
            $("input[name='dynamic_vcard']").prop("checked", false);
        } else {
            template.prop("checked", false);
            $(this).removeClass("template-border");
        }
    } else {
        template.prop("checked", true);
        $(this).addClass("template-border");

        if (defaultValue == 22) {
            $("input[name='dynamic_vcard']").prop("checked", true);
        }
    }
    setMultiTemplateAll();
    let featureLength = $(".feature:checkbox:checked").length;
    featureChecked(featureLength);
});

listenClick("#isTrial", function () {
    if ($(this).is(":checked")) {
        $("#duration_type").val(1).trigger("change");
        $("#price").val(0);
        $("#duration_type, #price").prop("disabled", true);
    } else {
        $("#price").val("");
        $("#duration_type, #price").prop("disabled", false);
    }
});

listenClick("#planFormSubmit", function (e) {
    if (!$(".templateIds").is(":checked")) {
        displayErrorMessage(Lang.get("js.multi_templates"));
        return false;
    } else if (!$(".feature").is(":checked")) {
        displayErrorMessage(
            Lang.get("js.select_one_or_more")
        );
        return false;
    }
});

listenClick("#multiTemplatesAll", function () {
    if ($("#multiTemplatesAll").is(":checked")) {
        $(".templateIds").each(function () {
            $(this).prop("checked", true);
            $(this).next().addClass("template-border");
            $("input[name='dynamic_vcard']").prop("checked", true);
        });
    } else {
        $(".templateIds").each(function () {
            $(this).prop("checked", false);
            $(this).next().removeClass("template-border");
            $("input[name='dynamic_vcard']").prop("checked", false);
        });
    }
});

listenChange("#whatsappStoreCheck", function () {
    const container = document.getElementById("whatsappStore");
    const input = container.querySelector('input[name="no_of_whatsapp_store"]');
    if ($(this).is(":checked")) {
        $("#whatsappStore").removeClass("d-none");
        input.setAttribute("required", true);
    } else {
        input.removeAttribute("required");
        $("#whatsappStore").addClass("d-none");
    }
});

listenChange("#noOfVisitorsCheck", function () {
    const container = document.getElementById("noOfVisitors");
    const input = container.querySelector('input[name="no_of_visitors"]');
    if ($(this).is(":checked")) {
        $("#noOfVisitors").removeClass("d-none");
        input.setAttribute("required", true);
    } else {
        input.removeAttribute("required");
        $("#noOfVisitors").addClass("d-none");
    }
});

listenChange("#organizationUsersCheck", function () {
    const container = document.getElementById("organizationUsers");
    const input = container.querySelector('input[name="no_of_organization_users"]');
    if ($(this).is(":checked")) {
        $("#organizationUsers").removeClass("d-none");
        input.setAttribute("required", true);
    } else {
        input.removeAttribute("required");
        $("#organizationUsers").addClass("d-none");
    }
});
