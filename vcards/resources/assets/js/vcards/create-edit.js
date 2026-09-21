"use strict";
// document.addEventListener("turbo:load", loadAppoimentCopy);
document.addEventListener("DOMContentLoaded", function () {
    loadAppoimentCopy();
    initBusinessHourToggles();
    initAiDescriptionGenerator();
    initAppointmentServices();
    initWhatsappStoreSelect();
});

listenChange("#profileImg", function () {
    let validFile = isValidFile($(this), "#profileImageValidationErrors");
    if (validFile) {
        displayPhoto(this, "#profilePreview");
    } else {
        $("#profilePreview").attr("src", defaultProfileUrl);
    }
});

listenClick(".cancel-profile", function () {
    $("#profilePreview").attr("src", defaultProfileUrl);
});

listenClick(".cancel-cover", function () {
    $("#coverPreview").attr("src", defaultCoverUrl);
});

listenClick("#coverImg", function () {
    let coverImg = document.getElementById("coverImg");
    if (coverImg) {
        coverImg.addEventListener("change", function () {
            let file = this.files[0];
            let fileType = file["type"];
            let validVideoTypes = [
                "video/mp4",
                "video/mpeg",
                "video/quicktime",
                "video/x-msvideo",
            ];
            let validImageTypes = ["image/jpeg", "image/png", "image/jpg"];

            if ($.inArray(fileType, validVideoTypes) !== -1) {
                $("#coverPreview").css(
                    "background-image",
                    "url(" + defaultVideoCoverImg + ")"
                );
            } else if ($.inArray(fileType, validImageTypes) !== -1) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    $("#coverPreview").css(
                        "background-image",
                        "url(" + e.target.result + ")"
                    );
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

listenClick(".theme-img-radio ", function () {
    $(".theme-img-radio").removeClass("img-border");
    $(this).addClass("img-border");
    $("#themeInput").val($(this).attr("data-id"));
});

listenClick(".img-radio ", function () {
    $(".img-radio").removeClass("img-border");
    $(this).addClass("img-border");
    $("#templateId").val($(this).attr("data-id"));
});

listenClick(".template-save", function () {
    let template = $("#templateId").val();
    if (isEmpty(template)) {
        displayErrorMessage(Lang.get("js.choose_one_template"));
        return false;
    }
});

listenClick(".auth-theme-img-radio ", function () {
    $(".auth-theme-img-radio").removeClass("img-border");
    $(this).addClass("img-border");
    $("#authThemeInput").val($(this).attr("data-id"));
});

listenClick(".img-radio ", function () {
    $(".img-radio").removeClass("img-border");
    $(this).addClass("img-border");
    $("#authTemplateId").val($(this).attr("data-id"));
});

listenChange('select[name^="startTime"]', function (e) {
    let selectedIndex = $(this)[0].selectedIndex;
    let endTimeOptions = $(this)
        .closest(".buisness_end")
        .find('select[name^="endTime"] option');
    endTimeOptions
        .eq(selectedIndex + 1)
        .prop("selected", true)
        .trigger("change");
    endTimeOptions.each(function (index) {
        if (index <= selectedIndex) {
            $(this).attr("disabled", true);
        } else {
            $(this).attr("disabled", false);
        }
    });
});

// document.addEventListener("turbo:load", loadVcardCreateEdit);
document.addEventListener("DOMContentLoaded", loadVcardCreateEdit);

function loadVcardCreateEdit() {
    if (!$("#vcardCreateEditIsTrue").length) {
        return;
    }
    if (
        $("#vcardCreateEditIsTrue").length &&
        $("#vcardCreateEditIsTrue").val()
    ) {
        $('select[name^="startTime"]').each(function () {
            let selectedIndex = $(this)[0].selectedIndex;
            let endSelectedIndex = $(this)
                .closest(".buisness_end")
                .find('select[name^="endTime"] option:selected')[0].index;
            let endTimeOptions = $(this)
                .closest(".buisness_end")
                .find('select[name^="endTime"] option');
            if (selectedIndex >= endSelectedIndex) {
                endTimeOptions
                    .eq(selectedIndex + 1)
                    .prop("selected", true)
                    .trigger("change");
            }
            endTimeOptions.each(function (index) {
                if (index <= selectedIndex) {
                    $(this).attr("disabled", true);
                } else {
                    $(this).attr("disabled", false);
                }
            });
        });
    }

    if ($("#privacyPolicyQuill").length) {
        window.quillPrivacyPolicy = new Quill("#privacyPolicyQuill", {
            modules: {
                toolbar: [
                    [
                        {
                            header: [1, 2, false],
                        },
                    ],
                    ["bold", "italic", "underline"],
                ],
            },
            theme: "snow", // or 'bubble'
            placeholder: Lang.get("js.privacy_policy"),
        });

        quillPrivacyPolicy.on(
            "text-change",
            function (delta, oldDelta, source) {
                if (quillPrivacyPolicy.getText().trim().length === 0) {
                    quillPrivacyPolicy.setContents([{ insert: "" }]);
                }
            }
        );
        let element = document.createElement("textarea");
        element.innerHTML = $("#privacyData").val();
        quillPrivacyPolicy.root.innerHTML = element.value;
    }

    if ($("#termConditionQuill").length) {
        window.termConditionQuill = new Quill("#termConditionQuill", {
            modules: {
                toolbar: [
                    [
                        {
                            header: [1, 2, false],
                        },
                    ],
                    ["bold", "italic", "underline"],
                ],
            },
            placeholder: Lang.get("js.term_condition").replace(/&amp;/g, "&"),
            theme: "snow", // or 'bubble'
        });

        termConditionQuill.on(
            "text-change",
            function (delta, oldDelta, source) {
                if (termConditionQuill.getText().trim().length === 0) {
                    termConditionQuill.setContents([{ insert: "" }]);
                }
            }
        );
        let element = document.createElement("textarea");
        element.innerHTML = $("#conditionData").val();
        termConditionQuill.root.innerHTML = element.value;
    }

    if ($("#vcardDescriptionQuill").length) {
        window.quillVcardDescription = new Quill("#vcardDescriptionQuill", {
            modules: {
                toolbar: [
                    ["bold", "italic", "underline", "strike"], // toggled buttons
                    ["blockquote", "code-block"],
                    [{ header: [1, 2, 3, 4, 5, 6, false] }],
                    [{ color: [] }, { background: [] }],
                ],
            },
            theme: "snow",
            placeholder: Lang.get("js.description"),
        });

        quillVcardDescription.on(
            "text-change",
            function (delta, oldDelta, source) {
                if (quillVcardDescription.getText().trim().length === 0) {
                    quillVcardDescription.setContents([{ insert: "" }]);
                }
            }
        );

        let element = document.createElement("textarea");
        element.innerHTML = $("#vcardDescriptionData").val();
        quillVcardDescription.root.innerHTML = element.value;
    }

    listenClick("#vcardSaveBtn", function () {
        let editor_content_1 = quillVcardDescription.root.innerHTML;
        $("#vcardDescriptionData").val(editor_content_1);
    });

    $('select[name^="endTimes"]').each(function () {
        let selectedIndex = $(this)[0].selectedIndex;
        let startTimeOptions = $(this)
            .closest(".timeSlot")
            .next()
            .find('select[name^="startTimes"] option');
        startTimeOptions.each(function (index) {
            if (index < selectedIndex) {
                $(this).attr("disabled", true);
            } else {
                $(this).attr("disabled", false);
            }
        });
    });

    $('select[name^="startTimes"]').each(function () {
        let selectedIndex = $(this)[0].selectedIndex;
        let endSelectedIndex = $(this)
            .closest(".add-slot")
            .find('select[name^="endTimes"] option:selected')[0].index;
        let endTimeOptions = $(this)
            .closest(".add-slot")
            .find('select[name^="endTimes"] option');
        if (selectedIndex >= endSelectedIndex) {
            endTimeOptions
                .eq(selectedIndex + 1)
                .prop("selected", true)
                .trigger("change");
        }
        endTimeOptions.each(function (index) {
            if (index <= selectedIndex) {
                $(this).attr("disabled", true);
            } else {
                $(this).attr("disabled", false);
            }
        });
    });
}

listenClick("#privacyPolicySave", function () {
    let element = document.createElement("textarea");
    let editor_content_1 = quillPrivacyPolicy.root.innerHTML;
    element.innerHTML = editor_content_1;
    let partName = $("#privacyPolicyPartName").val();
    if (partName == "privacy-policy") {
        if (quillPrivacyPolicy.getText().trim().length === 0) {
            displayErrorMessage(Lang.get("js.privacy_policy"));
            return false;
        }
        let input = JSON.stringify(editor_content_1);
        $("#privacyData").val(input.replace(/"/g, ""));
    }
    return true;
});

listenClick("#termConditionSave", function () {
    let element = document.createElement("textarea");
    let editor_content_1 = termConditionQuill.root.innerHTML;
    element.innerHTML = editor_content_1;
    let partName = $("#termConditionPartName").val();
    if (partName == "term-condition") {
        let input = JSON.stringify(editor_content_1);
        $("#conditionData").val(input.replace(/"/g, ""));

        if (termConditionQuill.getText().trim().length === 0) {
            displayErrorMessage(Lang.get("js.the_term_conditions"));
            return false;
        }
        return true;
    }
});

listenClick(".add-session-time", function () {
    let selectedIndex = 0;
    let dayId = $(this).data("day");
    if (
        $(this)
            .parent()
            .prev()
            .children(".session-times")
            .find(".timeSlot:last-child").length > 0
    ) {
        selectedIndex = $(this)
            .parent()
            .prev()
            .children(".session-times")
            .find(".timeSlot:last-child")
            .children(".add-slot")
            .find('select[name^="endTimes"] option:selected')[0].index;
    }

    let day = $(this).closest(".weekly-content").attr("data-day");
    let $ele = $(this);
    let weeklyEle = $(this).closest(".weekly-content");
    $.ajax({
        url: route("get.slot"),
        data: { day: day },
        success: function (data) {
            weeklyEle.find(".unavailable-time").remove();
            weeklyEle
                .find('input[name="checked_week_days[]"')
                .prop("checked", true)
                .prop("disabled", false);
            $ele.closest(".weekly-content")
                .find(".session-times")
                .append(data.data);
            weeklyEle.find('select[data-control="select2"]').select2();

            let startTimeOptions = $("#add-session-" + dayId)
                .parent()
                .prev()
                .children(".session-times")
                .find(".timeSlot:last-child")
                .children(".add-slot")
                .find('select[name^="startTimes"] option');
            startTimeOptions.each(function (index) {
                if (index < selectedIndex) {
                    $(this).attr("disabled", true);
                } else {
                    $(this).attr("disabled", false);
                }
            });
        },
    });
});
listenClick(".deleteBtn", function () {
    let selectedIndex = 0;
    if ($(this).closest(".timeSlot").prev().length > 0) {
        selectedIndex = $(this)
            .closest(".timeSlot")
            .prev()
            .children(".add-slot")
            .find('select[name^="endTimes"] option:selected')[0].index;
    }

    if (
        $(this).closest(".weekly-row").find(".session-times").find("select")
            .length === 2
    ) {
        let dayChk = $(this)
            .closest(".weekly-row")
            .find('input[name="checked_week_days[]"');
        dayChk.prop("checked", false).prop("disabled", true);
        $(this)
            .closest(".weekly-row")
            .children()
            .next()
            .append(
                '<div class="unavailable-time">' +
                Lang.get("js.unavailable") +
                "</div>"
            );
    }

    let startTimeOptions = $(this)
        .closest(".timeSlot")
        .next()
        .children(".add-slot")
        .find('select[name^="startTimes"] option');
    startTimeOptions.each(function (index) {
        if (index <= selectedIndex) {
            $(this).attr("disabled", true);
        } else {
            $(this).attr("disabled", false);
        }
    });

    $(this).parent().siblings(".error-msg").remove();
    $(this).parent().closest(".timeSlot").remove();
    $(this).parent().remove();
});

listenChange('select[name^="startTimes"]', function (e) {
    let selectedIndex = $(this)[0].selectedIndex;
    let endTimeOptions = $(this)
        .closest(".add-slot")
        .find('select[name^="endTimes"] option');
    let endSelectedIndex = $(this)
        .closest(".add-slot")
        .find('select[name^="endTimes"] option:selected')[0].index;
    if (selectedIndex >= endSelectedIndex) {
        endTimeOptions
            .eq(selectedIndex + 1)
            .prop("selected", true)
            .trigger("change");
    }
    endTimeOptions.each(function (index) {
        if (index <= selectedIndex) {
            $(this).attr("disabled", true);
        } else {
            $(this).attr("disabled", false);
        }
    });
});

listenChange('select[name^="endTimes"]', function (e) {
    let selectedIndex = $(this)[0].selectedIndex;
    let startTimeOptions = $(this)
        .closest(".timeSlot")
        .next()
        .find('select[name^="startTimes"] option');
    startTimeOptions.each(function (index) {
        if (index <= selectedIndex) {
            $(this).attr("disabled", true);
        } else {
            $(this).attr("disabled", false);
        }
    });
});

$(document).ready(function () {
    function togglePaidInput() {
        if ($('#paidRadio').is(':checked')) {
            $('#userPaidInputDiv').removeClass('d-none');
            $('#userPaymentAmount').attr('required', true);
            $('#isUserPaidId').val(1);
        } else {
            $('#userPaidInputDiv').addClass('d-none');
            $('#userPaymentAmount').removeAttr('required').val('');
            $('#isUserPaidId').val(0);
        }
    }

    $('#freeRadio, #paidRadio').on('change', togglePaidInput);
    togglePaidInput();
});

listenClick("#generate-url-alias", function () {
    $.ajax({
        url: route("vcards.get-unique-url-alias"),
        type: "GET",
        success: function (result) {
            $("#vcard-url-alias").val(result);
        },
    });
});

listen("blur", "#vcard-url-alias", function () {
    let vcardId = $("#vcardId").length ? $("#vcardId").val() : "";
    if ($(this).val().trim().length) {
        $.ajax({
            url: route("vcards.check-unique-url-alias", $(this).val()),
            type: "GET",
            success: function (result) {
                let data = result.data;
                if (!data.isUnique && data.usedInVcard != vcardId) {
                    $("#error-url-alias-msg").removeClass("d-none");
                }

                setTimeout(() => {
                    $("#error-url-alias-msg").addClass("d-none");
                }, 3000);
            },
        });
    }
});

function loadAppoimentCopy() {
    $('.copy-btn').on('click', function () {
        $(this).closest('.copy-card').removeClass('show');

        let $currentSessionTimes = $(this).closest('.weekly-content').find('.session-times');

        if ($currentSessionTimes.find('select').length == 0) {
            $(this).closest('.menu-content').find('.copy-label .form-check-input:checked').each(function () {
                let $weekEle = $(`.weekly-content[data-day="${$(this).val()}"]`);
                $weekEle.find('.session-times').html('');
                $weekEle.find('.weekly-row .unavailable-time').remove();
                $weekEle.find('.weekly-row').append('<div class="unavailable-time">Unavailable</div>');
                $weekEle.find('input[name="checked_week_days[]"]').prop('checked', false).prop('disabled', true);
            });
        } else {
            // Store ALL select elements with their selected values
            let slotsData = [];
            $currentSessionTimes.find('select[name^="startTimes"]').each(function(index) {
                let $parentRow = $(this).closest('.row, .d-flex, div[class*="slot"]').first();
                let $startSelect = $(this);
                let $endSelect = $parentRow.find('select[name^="endTimes"]');

                slotsData.push({
                    startValue: $startSelect.val(),
                    endValue: $endSelect.val(),
                    startHtml: $startSelect.html(), // Store options HTML
                    endHtml: $endSelect.html()
                });
            });

            // console.log('Stored slots data:', slotsData);

            // Destroy select2 on source
            $currentSessionTimes.find('select').select2('destroy');

            // Get the raw HTML
            let sourceHtml = $currentSessionTimes.html();

            // Copy to selected days
            $(this).closest('.menu-content').find('.copy-label .form-check-input:checked').each(function () {
                let currentDay = $(this).val();
                let $weekEle = $(`.weekly-content[data-day="${currentDay}"]`);

                // Clear existing content
                $weekEle.find('.unavailable-time').remove();
                $weekEle.find('.error-msg').html('');
                $weekEle.find('.session-times').html(sourceHtml);

                // Update select names and set correct values
                let slotIndex = 0;
                $weekEle.find('select[name^="startTimes"]').each(function() {
                    let $startSelect = $(this);
                    let $parentRow = $startSelect.closest('.row, .d-flex, div[class*="slot"]').first();
                    let $endSelect = $parentRow.find('select[name^="endTimes"]');

                    // Update names
                    $startSelect.attr('name', `startTimes[${currentDay}][]`);
                    $endSelect.attr('name', `endTimes[${currentDay}][]`);

                    // Set the correct values from stored data
                    if (slotsData[slotIndex]) {
                        // Make sure options exist, then set value
                        $startSelect.html(slotsData[slotIndex].startHtml);
                        $endSelect.html(slotsData[slotIndex].endHtml);

                        $startSelect.val(slotsData[slotIndex].startValue);
                        $endSelect.val(slotsData[slotIndex].endValue);

                        // console.log(`Day ${currentDay}, Slot ${slotIndex}: Start=${slotsData[slotIndex].startValue}, End=${slotsData[slotIndex].endValue}`);
                    }

                    slotIndex++;
                });

                // Re-initialize select2
                $weekEle.find('.session-times select').select2();

                // Enable checkbox
                $weekEle.find('input[name="checked_week_days[]"]').prop('disabled', false).prop('checked', true);
            });

            // Re-initialize select2 on source day
            $currentSessionTimes.find('select').select2();

            // Uncheck all copy checkboxes
            $('.copy-check-input').prop('checked', false);
        }

        $('.copy-menu, .copy-days-btn').removeClass('show');
    });

    $('#location_type').on('change', function () {
        var selectedType = $(this).val();
        if (selectedType == 0) {
            $('#linkInputGroup').show();
            $('#iframeInputGroup').hide();
        } else if (selectedType == 1) {
            $('#linkInputGroup').hide();
            $('#iframeInputGroup').show();
        }
    });

    $('#location_type').trigger('change');

    var selectedCoverType = $('.cover-type').val();
    toggleCoverSections(selectedCoverType);
}

listenChange('.cover-type', function () {
    var selectedCoverType = $(this).val();
    toggleCoverSections(selectedCoverType);
});
function toggleCoverSections(type) {
    if (type == 0) {
        $('.cover-imgs').removeClass('d-none');
        $('.cover_youtube_link').addClass('d-none');
        $('.cover-video').addClass('d-none');
    } else if (type == 1) {
        $('.cover-imgs').addClass('d-none');
        $('.cover-video').removeClass('d-none');
        $('.cover_youtube_link').addClass('d-none');
    } else if (type == 2) {
        $('.cover-imgs').addClass('d-none');
        $('.cover_youtube_link').removeClass('d-none');
        $('.cover-video').addClass('d-none');
    }
}
function initBusinessHourToggles() {
    document.querySelectorAll('.day-toggle').forEach(function (checkbox) {
        const dayKey = checkbox.value;

        toggleDayTime(dayKey);

        checkbox.addEventListener('change', function () {
            toggleDayTime(dayKey);
        });
    });
}

function toggleDayTime(dayKey) {
    const checkbox = document.getElementById('dayToggle' + dayKey);
    const timeFields = document.getElementById('timeFields' + dayKey);
    const closedState = document.getElementById('closedState' + dayKey);

    if (checkbox.checked) {
        // Show time fields, hide closed state
        timeFields.style.display = 'flex';
        closedState.style.display = 'none';
    } else {
        // Hide time fields, show closed state
        timeFields.style.display = 'none';
        closedState.style.display = 'flex';
    }
}

function initAiDescriptionGenerator() {
    if (!$("#generateAiDescriptionBtn").length) {
        return;
    }
}

listenClick("#generateAiDescriptionBtn", function () {
    const prompt = $("#aiDescriptionTextarea").val().trim();
    if (!prompt) {
        displayErrorMessage(Lang.get("js.please_enter_prompt"));
        return false;
    }

    $(this).prop('disabled', true);
    $("#aiDescriptionCloseBtn").prop('disabled', true);
    $("#aiDescriptionDiscardBtn").prop('disabled', true);

    const modalEl = document.getElementById("aiDescriptionModal");
    let modalInstance;
    if (modalEl) {
        modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (!modalInstance) {
            modalInstance = new bootstrap.Modal(modalEl);
        }
        modalInstance._config.backdrop = "static";
        modalInstance._config.keyboard = false;
    }

    $.ajax({
        url: route('vcards.generate.ai.description'),
        type: 'POST',
        data: {
            prompt: prompt,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        timeout: 35000,
        success: function (result) {
            $("#generateAiDescriptionBtn").prop('disabled', false);
            $("#aiDescriptionCloseBtn").prop('disabled', false);
            $("#aiDescriptionDiscardBtn").prop('disabled', false);

            if (modalInstance) {
                modalInstance._config.backdrop = true;
                modalInstance._config.keyboard = true;
            }

            if (result.success) {
                if (typeof quillVcardDescription !== 'undefined') {
                    quillVcardDescription.root.innerHTML = result.description;
                    $("#vcardDescriptionData").val(result.description);
                } else {
                    $("#vcardDescriptionData").val(result.description);
                }

                $("#aiDescriptionModal").modal('hide');
                $("#aiDescriptionTextarea").val('');
                displaySuccessMessage(Lang.get("js.description_generated_successfully"));
            } else {
                displayErrorMessage(result.message);
            }
        },
        error: function (xhr, status, error) {
            $("#generateAiDescriptionBtn").prop('disabled', false);
            $("#aiDescriptionCloseBtn").prop('disabled', false);
            $("#aiDescriptionDiscardBtn").prop('disabled', false);

            if (modalInstance) {
                modalInstance._config.backdrop = true;
                modalInstance._config.keyboard = true;
            }

            if (xhr.responseJSON && xhr.responseJSON.message) {
                displayErrorMessage(xhr.responseJSON.message);
            }
        }
    });
});

listenClick('[data-bs-dismiss="modal"]', function () {
    if ($(this).closest('#aiDescriptionModal').length) {
        $("#aiDescriptionTextarea").val('');
        $("#generateAiDescriptionBtn").prop('disabled', false);
        $("#aiDescriptionCloseBtn").prop('disabled', false);
        $("#aiDescriptionDiscardBtn").prop('disabled', false);
    }
});

$('#aiDescriptionModal').on('hidden.bs.modal', function () {
    $("#aiDescriptionTextarea").val('');
    $("#generateAiDescriptionBtn").prop('disabled', false);
    $("#aiDescriptionCloseBtn").prop('disabled', false);
    $("#aiDescriptionDiscardBtn").prop('disabled', false);

    const modalEl = document.getElementById("aiDescriptionModal");
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) {
            modalInstance._config.backdrop = true;
            modalInstance._config.keyboard = true;
        }
    }
});

function initAppointmentServices() {
    if (!$("#services-container").length) return;

    let serviceIndex = $('.service-row').length;

    // Track ALL currently selected service IDs (not just initial ones)
    const selectedServices = new Set();

    // Initialize - populate selectedServices from ALL rows
    $('.service-row').each(function() {
        const serviceId = $(this).find('.service-select').val();
        if (serviceId) {
            selectedServices.add(serviceId);
            $(this).attr('data-service-id', serviceId);
        }
    });

    // Service change handler
    $(document).on('change', '.service-select', function() {
        const $select = $(this);
        const serviceId = $select.val();
        const $row = $select.closest('tr');
        const $priceInput = $row.find('.service-price');
        const oldServiceId = $row.attr('data-service-id');

        if (serviceId) {
            // FIXED: Check if already selected ANYWHERE else
            if (selectedServices.has(serviceId) && oldServiceId != serviceId) {
                displayErrorMessage(Lang.get('js.this_service_is_already_selected'));
                $select.val('').trigger('change');
                return false;
            }

            const service = window.servicesData.find(s => s.id == serviceId);
            $priceInput.val(service ? service.amount : 0);

            // Update tracking
            $row.attr('data-service-id', serviceId);
            selectedServices.add(serviceId);

            if (oldServiceId && oldServiceId != serviceId) {
                selectedServices.delete(oldServiceId);
            }
        } else {
            // Clear selection
            if (oldServiceId) {
                selectedServices.delete(oldServiceId);
            }
            $row.removeAttr('data-service-id');
            $priceInput.val('');
        }
    });

    listenClick("#add-service", function () {
        addEmptyServiceRow();
        toggleDeleteButtons();
    });

    listenClick(".service-delete-button, .remove-service", function (e) {
        e.preventDefault();
        const $row = $(this).closest('tr');
        const serviceId = $row.attr('data-service-id');

        if (serviceId) {
            selectedServices.delete(serviceId);
        }

        $row.remove();

        if ($('.service-row').length === 0) {
            addEmptyServiceRow();
        }

        toggleDeleteButtons();
    });

    function toggleDeleteButtons() {
        const rowCount = $('.service-row').length;

        $('.service-row .service-delete-button, .service-row .remove-service').removeClass('d-none');
    }


    // Initialize Select2 for existing selects
    $('#services-container .service-select').select2();

    toggleDeleteButtons();

    // Number validation
    $(document).on('input', '.service-price', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(value);
    });

    function addEmptyServiceRow() {
        let optionsHtml = `
            <option value="">
                ${Lang.get('js.select_service')}
            </option>
        `;
        window.servicesData.forEach(function(service) {
            optionsHtml += `<option value="${service.id}" data-amount="${service.amount}">
                ${service.name}
            </option>`;
        });

        const newRow = `
            <tr class="service-row" data-index="${serviceIndex}">
                <td>
                    <label class="form-label">${Lang.get('js.service_name')}:</label>
                    <select class="form-select service-select" name="services[${serviceIndex}][service_id]" data-control="select2">
                        ${optionsHtml}
                    </select>
                </td>
                <td>
                    <label class="form-label">${Lang.get('js.amount')}:</label>
                    <input type="number" class="form-control service-price"
                        name="services[${serviceIndex}][amount]" placeholder="${Lang.get('js.amount')}"
                        min="0" value="">
                </td>
                <td>
                    <a href="javascript:void(0)" class="btn px-1 text-danger fs-3 service-delete-button remove-service">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>
            </tr>
        `;

        $('#services-container').append(newRow);
        $('#services-container tr:last .service-select').select2();
        serviceIndex++;
    }

}

function initWhatsappStoreSelect() {
    if (!$('.whatsapp-store-select').length) {
        return;
    }

    function formatWhatsappStore(store) {
        if (!store.id) {
            return store.text;
        }

        const logo = $(store.element).data('logo');

        return $(`
            <div class="d-flex align-items-center gap-2">
                <img src="${logo}" class="whatsapp-store-select-logo" />
                <span>${store.text}</span>
            </div>
        `);
    }

    function formatSelectedStore(store) {
        if (!store.id) {
            return store.text;
        }

        const logo = $(store.element).data('logo');

        return $(`
            <div class="d-flex align-items-center gap-2 select2-selected-store">
                <img src="${logo}" class="whatsapp-store-select-logo" />
                <span class="store-name">${store.text}</span>
                <span class="store-remove" data-id="${store.id}">×</span>
            </div>
        `);
    }

    const $select = $('.whatsapp-store-select').select2({
        templateResult: formatWhatsappStore,
        templateSelection: formatSelectedStore,
        closeOnSelect: false,
        width: '100%'
    });

    // Custom remove handler
    $(document).on('click', '.store-remove', function (e) {
        e.stopPropagation();

        const storeId = $(this).data('id');
        $select.find(`option[value="${storeId}"]`)
            .prop('selected', false);

        $select.trigger('change');
    });
}

listenClick("#aiVcardBtn", function () {
    var modalElement = document.getElementById("aiVcardModal");

    var aiModal = new bootstrap.Modal(modalElement, {
        backdrop: "static",
        keyboard: false
    });

    aiModal.show();
});

$('#aiVcardModal').on('hidden.bs.modal', function () {
    $('#aiCardForm')[0].reset();

    $('#aiCardForm input[type="file"]').val('');

    $("#aiGenerateBtn").prop("disabled", false);
    $("#aiLoader").addClass("d-none");

    $("#aiVcardModal .btn-close").prop("disabled", false);
    $("#aiDiscardBtn").prop("disabled", false);
});

$(document).on("submit", "#aiCardForm", function(e) {
    e.preventDefault();

    let formData = new FormData(this);

    $("#aiGenerateBtn").prop("disabled", true);
    $("#aiLoader").removeClass("d-none");
    $("#aiVcardModal .btn-close").prop("disabled", true);
    $("#aiDiscardBtn").prop("disabled", true);

    $.ajax({
        url: route("vcards.ai.generate"),
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            window.location.href = response.data.redirect_url;
        },
        error: function (xhr) {

            $("#aiGenerateBtn").prop("disabled", false);
            $("#aiLoader").addClass("d-none");
            $("#aiVcardModal .btn-close").prop("disabled", false);
            $("#aiDiscardBtn").prop("disabled", false);

            if (xhr.responseJSON && xhr.responseJSON.message) {
                displayErrorMessage(xhr.responseJSON.message);
            }

        }
    });

});

document.addEventListener('DOMContentLoaded', function () {
    $('#iconPickerV2').iconpicker({
        cols: 10,
        iconset: 'fontawesome5',
        labelHeader: '{0} of {1} pages',
        search: true,
        searchText: Lang.get('js.search_icon'),
        placement: 'bottom',
    }).on('change', function (e) {
        $('#dob_icon').val(e.icon);
        $('#dobIconPreview').html('<i class="' + e.icon + '"></i>');
        bootstrap.Modal.getInstance(document.getElementById('iconPickerModalV2')).hide();
    });
});
