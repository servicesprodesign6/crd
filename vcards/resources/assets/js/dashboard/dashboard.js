import moment from 'moment';
import 'moment/min/locales';
// document.addEventListener("turbo:load", loadDashboardData);
document.addEventListener("DOMContentLoaded", loadDashboardData);

function loadDashboardData() {
    clickDayData();
    appointmentsDataAjax();
    datePickerInitialise();
    vcardDatePickerInitialise();
    loadAdminDashboardData();
    loadDateRangeWithChartData();
}
// let dashboardChartType = "line";
// let dashboardStacked = false;
// let dashboardWeeklyBarChartResult = "";
let dashboardPlanIncomeChartData = "";
listenClick("#dayData", function (e) {
    e.preventDefault();
    $.ajax({
        url: route("usersData.dashboard"),
        type: "GET",
        data: { day: "day" },
        success: function (result) {
            if (result.success) {
                $("#dailyReport").empty();
                $(document).find("#month").removeClass("show active");
                $(document).find("#week").removeClass("show active");
                $(document).find("#day").addClass("show active");
                if (result.data.users.data != "") {
                    $.each(result.data.users.data, function (index, value) {
                        let data = [
                            {
                                name: value.first_name + " " + value.last_name,
                                email: value.email,
                                contact: !isEmpty(value.contact)
                                    ? "+" +
                                    value.region_code +
                                    " " +
                                    value.contact
                                    : "N/A",
                                registered: moment
                                    .parseZone(value.created_at)
                                    .locale(lang)
                                    .format('LLL')
                            },
                        ];
                        $(document)
                            .find("#dailyReport")
                            .append(
                                prepareTemplateRender(
                                    "#sadminDashboardTemplate",
                                    data
                                )
                            );
                    });
                } else {
                    $(document).find("#dailyReport").append(`
                    <tr class="text-center">
                        <td colspan="5" class="text-muted fw-bold">${noData}</td>
                    </tr>`);
                }
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

function clickDayData() {
    if (!$("#dayData").length) {
        return;
    }
    $("#dayData").click();
}

listenClick("#weekData", function (e) {
    e.preventDefault();
    $.ajax({
        url: route("usersData.dashboard"),
        type: "GET",
        data: { week: "week" },
        success: function (result) {
            if (result.success) {
                $("#weeklyReport").empty();
                $(document).find("#month").removeClass("show active");
                $(document).find("#week").addClass("show active");
                $(document).find("#day").removeClass("show active");
                if (result.data.users.data != "") {
                    $.each(result.data.users.data, function (index, value) {
                        let data = [
                            {
                                name: value.first_name + " " + value.last_name,
                                email: value.email,
                                contact: !isEmpty(value.contact)
                                    ? "+" +
                                    value.region_code +
                                    " " +
                                    value.contact
                                    : "N/A",
                                registered: moment
                                    .parseZone(value.created_at)
                                    .locale(lang)
                                    .format('LLL')
                            },
                        ];
                        $(document)
                            .find("#weeklyReport")
                            .append(
                                prepareTemplateRender(
                                    "#sadminDashboardTemplate",
                                    data
                                )
                            );
                    });
                } else {
                    $(document).find("#weeklyReport").append(`
                    <tr class="text-center">
                        <td colspan="5" class="text-muted fw-bold">${noData}</td>
                    </tr>`);
                }
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});
listenClick("#monthData", function (e) {
    e.preventDefault();
    $.ajax({
        url: route("usersData.dashboard"),
        type: "GET",
        data: { month: "month" },
        success: function (result) {
            if (result.success) {
                $("#monthlyReport").empty();
                $(document).find("#month").addClass("show active");
                $(document).find("#week").removeClass("show active");
                $(document).find("#day").removeClass("show active");
                if (result.data.users.data != "") {
                    $.each(result.data.users.data, function (index, value) {
                        let data = [
                            {
                                name: value.first_name + " " + value.last_name,
                                email: value.email,
                                contact: !isEmpty(value.contact)
                                    ? "+" +
                                    value.region_code +
                                    " " +
                                    value.contact
                                    : "N/A",
                                registered: moment
                                    .parseZone(value.created_at)
                                    .locale(lang)
                                    .format('LLL')
                            },
                        ];
                        $(document)
                            .find("#monthlyReport")
                            .append(
                                prepareTemplateRender(
                                    "#sadminDashboardTemplate",
                                    data
                                )
                            );
                    });
                } else {
                    $(document).find("#monthlyReport").append(`
                    <tr class="text-center">
                        <td colspan="5" class="text-muted fw-bold">${noData}</td>
                    </tr>`);
                }
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

function appointmentsDataAjax() {
    if (!$("#appointmentReport").length) {
        return;
    }
    $.ajax({
        url: route("appointmentsData.data"),
        type: "GET",
        success: function (result) {
            $(document).find("#appointmentReport").children().remove();
            if (result.data.data != "") {
                $.each(result.data.data, function (index, value) {
                    let data = [
                        {
                            vcardname: value.vcard.name,
                            name: value.name,
                            phone: !isEmpty(value.phone)
                                ? "+" + value.phone
                                : "N/A",
                            email: value.email,
                        },
                    ];
                    $(document)
                        .find("#appointmentReport")
                        .append(
                            prepareTemplateRender("#appointmentTemplate", data)
                        );
                });
            } else {
                $(document).find("#appointmentReport").append(`
                    <tr class="text-center">
                        <td colspan="5" class="text-muted fw-bold">${noData}</td>
                    </tr>`);
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
}
// let start = "";
// let end = "";
// const datePickerInitialise = () => {
//     if (!$("#dashboardTimeRange").length) {
//         return;
//     }
//     let timeRange = $("#dashboardTimeRange");
//     let isPickerApply = true;
//     const today = moment();
//     start = moment().subtract("7", "days");
//     end = today.clone().endOf("days");
//     timeRange.on("apply.daterangepicker", function (ev, picker) {
//         isPickerApply = true;
//         start = picker.startDate;
//         end = picker.endDate;
//         loadDashboardChart(
//             start.format("YYYY-MM-D  H:mm:ss"),
//             end.format("YYYY-MM-D  H:mm:ss")
//         );
//     });

//     window.cb = function (start, end) {
//         timeRange
//             .find("span")
//             .html(
//                 start.format("MMM D, YYYY") + " - " + end.format("MMM D, YYYY")
//             );
//     };

//     timeRange.daterangepicker(
//         {
//             startDate: start,
//             endDate: end,
//             opens: "left",
//             showDropdowns: true,
//             autoUpdateInput: false,
//             locale: {
//                 customRangeLabel: Lang.get("js.custom"),
//                 applyLabel: Lang.get("js.apply"),
//                 cancelLabel: Lang.get("js.cancel"),
//                 fromLabel: Lang.get("js.from"),
//                 toLabel: Lang.get("js.to"),
//                 monthNames: [
//                     Lang.get("js.jan"),
//                     Lang.get("js.feb"),
//                     Lang.get("js.mar"),
//                     Lang.get("js.apr"),
//                     Lang.get("js.may"),
//                     Lang.get("js.jun"),
//                     Lang.get("js.jul"),
//                     Lang.get("js.aug"),
//                     Lang.get("js.sep"),
//                     Lang.get("js.oct"),
//                     Lang.get("js.nov"),
//                     Lang.get("js.dec"),
//                 ],

//                 daysOfWeek: [
//                     Lang.get("js.sun"),
//                     Lang.get("js.mon"),
//                     Lang.get("js.tue"),
//                     Lang.get("js.wed"),
//                     Lang.get("js.thu"),
//                     Lang.get("js.fri"),
//                     Lang.get("js.sat"),
//                 ],
//             },
//             ranges: {
//                 [Lang.get("js.this_week")]: [
//                     moment().startOf("week"),
//                     moment().endOf("week"),
//                 ],
//                 [Lang.get("js.last_week")]: [
//                     moment().startOf("week").subtract(7, "days"),
//                     moment().startOf("week").subtract(1, "days"),
//                 ],
//             },
//         },
//         cb
//     );
//     cb(start, end);

//     loadDashboardChart(
//         start.format("YYYY-MM-D H:mm:ss"),
//         end.format("YYYY-MM-D H:mm:ss")
//     );
// };

// const loadDashboardChart = (startDate, endDate) => {
//     $.ajax({
//         type: "GET",
//         url: route("dashboard.vcard.chart"),
//         dataType: "json",
//         data: {
//             start_date: startDate,
//             end_date: endDate,
//         },
//         success: function (result) {
//             dashboardWeeklyBarChartResult = result;
//             dashboardWeeklyBarChart(result);
//         },
//         cache: false,
//     });
// };

// const dashboardWeeklyBarChart = (result) => {
//     const dashboardWeeklyUserBarChartContainer = $(
//         "#dashboardWeeklyUserBarChartContainer"
//     );
//     if (!dashboardWeeklyUserBarChartContainer.length) {
//         return;
//     }

//     dashboardWeeklyUserBarChartContainer.html("");
//     $("canvas#dashboardWeeklyUserBarChart").remove();
//     dashboardWeeklyUserBarChartContainer.append(
//         '<canvas id="dashboardWeeklyUserBarChart" height="275" width="905" style="display: block; width: 905px; height: 500px;"></canvas>'
//     );

//     let data = result.data;
//     let barChartData = {
//         labels: data.weeklyLabels,
//         datasets: data.data,
//     };
//     let ctx = $("#dashboardWeeklyUserBarChart");
//     let config = new Chart(ctx, {
//         type: dashboardChartType,
//         data: barChartData,
//         options: {
//             plugins: {
//                 legend: {
//                     display: false,
//                 },
//             },
//             scales: {
//                 y: {
//                     stacked: dashboardStacked,
//                     ticks: {
//                         min: 0,
//                         precision: 0,
//                     },
//                     min: 0,
//                 },
//                 x: {
//                     stacked: dashboardStacked,
//                 },
//             },
//         },
//     });
// };

// listenClick("#dashboardChangeChart", function () {
//     if (dashboardChartType === "bar") {
//         dashboardChartType = "line";
//         dashboardStacked = false;
//         $(".chart").removeClass("fa-chart-line");
//         $(".chart").addClass("fa-chart-column");
//         dashboardWeeklyBarChart(dashboardWeeklyBarChartResult);
//     } else {
//         dashboardChartType = "bar";
//         dashboardStacked = true;
//         $(".chart").addClass("fa-chart-line");
//         $(".chart").removeClass("fa-chart-column");
//         dashboardWeeklyBarChart(dashboardWeeklyBarChartResult);
//     }
// });

window.statiscticsColors = [
    "rgb(245, 158, 11)",
    "rgb(77, 124, 15)",
    "rgb(254, 199, 2)",
    "rgb(80, 205, 137)",
    "rgb(16, 158, 247)",
    "rgb(241, 65, 108)",
    "rgb(80, 205, 137)",
    "rgb(245, 152, 280)",
    "rgb(13, 148, 136)",
    "rgb(59, 130, 246)",
];

let incomeChartCanvasAttr = "";
let dashboardIncomeChartType = "line";
let incomeStartDate = "";
let incomeEndDate = "";

function loadAdminDashboardData() {
    if (!$("#incomeChartCanvas").length) {
        return;
    }

    incomeChartCanvasAttr = $("#incomeChartCanvas");
    dashboardPlanChart();
    dashboardIncomeChart();
}

const dashboardPlanChart = () => {
    $.ajax({
        type: "post",
        url: route("dashboard.plan-chart"),
        dataType: "json",
        success: function (result) {
            dashboardPlanPieChart(result.data.breakDown, result.data.labels);
        },
        cache: false,
    });
};

const dashboardPlanPieChart = (data, labels) => {
    if (!$("#dashboardPlanPieChart").length) {
        return;
    }

    let ctx = document.getElementById("dashboardPlanPieChart").getContext("2d");

    const colors = ['#5B63E0', '#C09300', '#0A1C5C', '#A94242', '#1BBED1', '#565A96'];

    new Chart(ctx, {
        type: "pie",
        options: {
            responsive: true,
            maintainAspectRatio: false,
            responsiveAnimationDuration: 500,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return (
                                labels[context.dataIndex] +
                                " " +
                                context.formattedValue +
                                "%"
                            );
                        },
                    },
                },
            },
        },
        data: {
            datasets: [
                {
                    data: data,
                    backgroundColor: colors,
                },
            ],
        },
    });
};

const dashboardIncomeChart = () => {
    // If income container not present, do nothing
    if (!$("#incomeChartCanvas").length) {
        return;
    }

    $.ajax({
        type: "post",
        url: route("dashboard.income-chart"),
        dataType: "json",
        success: function (result) {
            // Always reselect as jQuery object
            incomeChartCanvasAttr = $("#incomeChartCanvas");
            incomeChartCanvasAttr.empty();
            dashboardPlanIncomeChartData = result.data;
            dashboardPlanIncomeChart(dashboardPlanIncomeChartData);
        },
        cache: false,
    });
};

listenClick("#dashboardChangeIncomeChart", function () {
    if (dashboardIncomeChartType === "bar") {
        dashboardIncomeChartType = "line";
        $(".income-chart").removeClass("fa-chart-line");
        $(".income-chart").addClass("fa-chart-bar");
    } else {
        dashboardIncomeChartType = "bar";
        $(".income-chart").addClass("fa-chart-line");
        $(".income-chart").removeClass("fa-chart-bar");
    }

    // Re-render only if container exists
    if ($("#incomeChartCanvas").length && dashboardPlanIncomeChartData) {
        dashboardPlanIncomeChart(dashboardPlanIncomeChartData);
    }
});

listenClick("#dashboardRefreshIncomeChart", function () {
    // Reset to default date range and refresh chart
    incomeStartDate = moment().startOf("year");
    incomeEndDate = moment().endOf("year");
    loadDateRangeWithChartData();
    initIncomeDatePicker();
});

listenClick("#dashboardRefreshVcardChart", function () {

    const refreshChart = (selector, callback) => {
        if (!$(selector).length) return;

        let start = moment().subtract(7, "days"),
            end = moment(),
            range = $(selector),
            picker = range.data('daterangepicker');

        range.find("span").html(
            `${start.format("MMM D, YYYY")} - ${end.format("MMM D, YYYY")}`
        );

        picker?.setStartDate(start);
        picker?.setEndDate(end);

        callback(
            start.format("YYYY-MM-D H:mm:ss"),
            end.format("YYYY-MM-D H:mm:ss")
        );
    };

    refreshChart("#dashboardTimeRange", loadDashboardChart);
    refreshChart("#vcardDashboardTimeRange", loadVcardDashboardChart);
});

// ................ dashboard income chart js .........
function loadDateRangeWithChartData() {
    if (!incomeStartDate || !incomeEndDate) {
        incomeStartDate = moment().startOf("year");
        incomeEndDate = moment().endOf("year");
    }

    fetchIncomeChartData(
        incomeStartDate.format("YYYY-MM-DD H:mm:ss"),
        incomeEndDate.format("YYYY-MM-DD H:mm:ss")
    );
}

function initIncomeDatePicker() {
    if (!$("#dashboardIncomeTimeRange").length) {
        return;
    }

    let timeRange = $("#dashboardIncomeTimeRange");
    let today = moment();

    // Use existing dates or set defaults
    if (!incomeStartDate || !incomeEndDate) {
        incomeStartDate = moment().startOf("year");
        incomeEndDate = moment().endOf("year");
    }

    // Destroy existing datepicker if it exists
    if (timeRange.data('daterangepicker')) {
        timeRange.data('daterangepicker').remove();
    }

    timeRange.daterangepicker(
        {
            startDate: incomeStartDate,
            endDate: incomeEndDate,
            opens: "left",
            showDropdowns: true,
            autoUpdateInput: false,
            locale: {
                customRangeLabel: Lang.get("js.custom"),
                applyLabel: Lang.get("js.apply"),
                cancelLabel: Lang.get("js.cancel"),
                fromLabel: Lang.get("js.from"),
                toLabel: Lang.get("js.to"),
                monthNames: [
                    Lang.get("js.jan"),
                    Lang.get("js.feb"),
                    Lang.get("js.mar"),
                    Lang.get("js.apr"),
                    Lang.get("js.may"),
                    Lang.get("js.jun"),
                    Lang.get("js.jul"),
                    Lang.get("js.aug"),
                    Lang.get("js.sep"),
                    Lang.get("js.oct"),
                    Lang.get("js.nov"),
                    Lang.get("js.dec"),
                ],
                daysOfWeek: [
                    Lang.get("js.sun"),
                    Lang.get("js.mon"),
                    Lang.get("js.tue"),
                    Lang.get("js.wed"),
                    Lang.get("js.thu"),
                    Lang.get("js.fri"),
                    Lang.get("js.sat"),
                ],
            },
            ranges: {
                [Lang.get("js.today")]: [moment(), moment()],
                [Lang.get("js.yesterday")]: [
                    moment().subtract(1, "days"),
                    moment().subtract(1, "days"),
                ],
                [Lang.get("js.last_7_days")]: [
                    moment().subtract(6, "days"),
                    moment(),
                ],
                [Lang.get("js.last_30_days")]: [
                    moment().subtract(29, "days"),
                    moment(),
                ],
                [Lang.get("js.this_month")]: [
                    moment().startOf("month"),
                    moment().endOf("month"),
                ],
            },
        },
        function (start, end) {
            timeRange.find("span").html(
                start.format("MMM D, YYYY") +
                " - " +
                end.format("MMM D, YYYY")
            );
        }
    );

    timeRange.find("span").html(
        incomeStartDate.format("MMM D, YYYY") +
        " - " +
        incomeEndDate.format("MMM D, YYYY")
    );

    timeRange.on("apply.daterangepicker", function (ev, picker) {
        incomeStartDate = picker.startDate;
        incomeEndDate = picker.endDate;

        timeRange.find("span").html(
            incomeStartDate.format("MMM D, YYYY") +
            " - " +
            incomeEndDate.format("MMM D, YYYY")
        );

        fetchIncomeChartData(
            incomeStartDate.format("YYYY-MM-DD H:mm:ss"),
            incomeEndDate.format("YYYY-MM-DD H:mm:ss")
        );
    });
}

const fetchIncomeChartData = (startDate, endDate) => {
    // If income container not present, skip
    if (!$("#incomeChartCanvas").length) {
        return;
    }

    $.ajax({
        type: "POST",
        url: route("dashboard.income-chart"),
        dataType: "json",
        data: {
            start_date: startDate,
            end_date: endDate,
        },
        success: function (result) {
            dashboardPlanIncomeChartData = result.data;
            dashboardPlanIncomeChart(result.data);
        },
        cache: false,
    });
};

const dashboardPlanIncomeChart = (data) => {
    if (data == null) {
        return false;
    }

    // Ensure container exists before manipulating it
    if (!$("#incomeChartCanvas").length) {
        return false;
    }

    incomeChartCanvasAttr = $("#incomeChartCanvas");
    incomeChartCanvasAttr.empty();
    incomeChartCanvasAttr.append(
        '<canvas id="dashboardIncomeChart" class="mh-350px pt-0"></canvas>'
    );
    let ctx = document.getElementById("dashboardIncomeChart").getContext("2d");

    // Gradient background fill
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, "rgba(30, 58, 138, 0.4)");
    gradient.addColorStop(1, "rgba(30, 58, 138, 0.05)");

    if (data.breakDown && data.breakDown.length > 0) {
        data.breakDown[0].backgroundColor = gradient;
        data.breakDown[0].borderColor = "rgba(30, 58, 138, 1)";
        data.breakDown[0].fill = true;
    }

    let incomeChartObj = new Chart(ctx, {
        type: dashboardIncomeChartType, // Keep dynamic chart type switching
        data: {
            labels: data.labels,
            datasets: data.breakDown,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || "";
                            if (label) {
                                label += ": ";
                            }
                            if (context.parsed.y !== null) {
                                label += getCurrencyAmount(
                                    context.parsed.y.toFixed(2),
                                    getCurrencyCode
                                );
                            }
                            return label;
                        },
                    },
                },
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: Lang.get("js.amount"),
                    },
                    min: 0,
                },
                x: {
                    title: {
                        display: true,
                        text: '',
                    },
                },
            },
        },
    });

    incomeChartObj.canvas.parentNode.style.height = "334px";
};

// Initialize the income date picker on load
document.addEventListener("DOMContentLoaded", function () {
    initIncomeDatePicker();
});

let start = "";
let end = "";
let dashboardWeeklyChartInstance;

const datePickerInitialise = () => {
    if (!$("#dashboardTimeRange").length) {
        return;
    }

    let timeRange = $("#dashboardTimeRange");
    start = moment().subtract(7, "days");
    end = moment();

    timeRange.on("apply.daterangepicker", function (ev, picker) {
        start = picker.startDate;
        end = picker.endDate;
        loadDashboardChart(
            start.format("YYYY-MM-D H:mm:ss"),
            end.format("YYYY-MM-D H:mm:ss")
        );
    });

    function updateLabel(start, end) {
        timeRange.find("span").html(start.format("MMM D, YYYY") + " - " + end.format("MMM D, YYYY"));
    }

    timeRange.daterangepicker({
        startDate: start,
        endDate: end,
        opens: "left",
        showDropdowns: true,
        autoUpdateInput: false,
        locale: {
            customRangeLabel: Lang.get("js.custom"),
            applyLabel: Lang.get("js.apply"),
            cancelLabel: Lang.get("js.cancel"),
            fromLabel: Lang.get("js.from"),
            toLabel: Lang.get("js.to"),
            daysOfWeek: [
                Lang.get("js.sun"),
                Lang.get("js.mon"),
                Lang.get("js.tue"),
                Lang.get("js.wed"),
                Lang.get("js.thu"),
                Lang.get("js.fri"),
                Lang.get("js.sat"),
            ],
            monthNames: [
                Lang.get("js.jan"),
                Lang.get("js.feb"),
                Lang.get("js.mar"),
                Lang.get("js.apr"),
                Lang.get("js.may"),
                Lang.get("js.jun"),
                Lang.get("js.jul"),
                Lang.get("js.aug"),
                Lang.get("js.sep"),
                Lang.get("js.oct"),
                Lang.get("js.nov"),
                Lang.get("js.dec"),
            ],
        },
        ranges: {
            [Lang.get("js.this_week")]: [
                moment().startOf("week"), moment().endOf("week")
            ],
            [Lang.get("js.last_week")]: [
                moment().startOf("week").subtract(7, "days"),
                moment().startOf("week").subtract(1, "days")
            ],
        },
    }, updateLabel
    );

    updateLabel(start, end);

    loadDashboardChart(
        start.format("YYYY-MM-D H:mm:ss"),
        end.format("YYYY-MM-D H:mm:ss")
    );
};

const loadDashboardChart = (startDate, endDate) => {
    $.ajax({
        type: "GET",
        url: route("dashboard.chart"),
        dataType: "json",
        data: {
            start_date: startDate,
            end_date: endDate,
        },
        success: function (result) {
            dashboardWeeklyBarChart(result);
        },
        cache: false,
    });
};

const dashboardWeeklyBarChart = (result) => {
    const container = $("#dashboardWeeklyUserBarChartContainer");

    if (!container.length) return;

    container.html("");
    container.css('height', '320px');
    $("canvas#dashboardWeeklyUserBarChart").remove();

    container.append(
        '<canvas id="dashboardWeeklyUserBarChart"></canvas>'
    );

    const ctx = document.getElementById("dashboardWeeklyUserBarChart").getContext("2d");

    const chartLabels = result.data.weeklyLabels;
    const chartDatasets = result.data.data;

    if (chartDatasets.length === 2) {
        chartDatasets[0].backgroundColor = '#4F58B9'; // vCard
        chartDatasets[1].backgroundColor = '#1e3a8a';  // WhatsApp Store
    }

    if (dashboardWeeklyChartInstance) {
        dashboardWeeklyChartInstance.destroy();
    }

    const allValues = chartDatasets.flatMap(ds => ds.data);
    const maxValue = Math.max(...allValues.map(Math.abs));
    const suggestedMax = Math.ceil(maxValue) + 1;

    chartDatasets.forEach((dataset) => {
        dataset.borderRadius = 10;
    });


    dashboardWeeklyChartInstance = new Chart(ctx, {
        type: "bar",
        data: {
            labels: chartLabels,
            datasets: chartDatasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    stacked: true,
                    suggestedMax: suggestedMax,
                    suggestedMin: -suggestedMax,
                    ticks: {
                        callback: function (value) {
                            return Math.abs(value);
                        }
                    },
                    grid: {
                        drawBorder: false,
                        drawOnChartArea: true,
                        drawTicks: false,
                        color: function (context) {
                            // Only show line at y = 0
                            return context.tick.value === 0 ? '#E5E7EB' : 'transparent';
                        }
                    }
                },
                x: {
                    stacked: true,
                    grid: {
                        display: false
                    },
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `${context.dataset.label}: ${Math.abs(context.raw)}`;
                        }
                    }
                },
                legend: {
                    display: true,
                }
            }
        }
    });
};

let vcardStart = "";
let vcardEnd = "";
let vcardDashboardWeeklyChartInstance;

const vcardDatePickerInitialise = () => {
    if (!$("#vcardDashboardTimeRange").length) {
        return;
    }

    let vcardTimeRange = $("#vcardDashboardTimeRange");
    vcardStart = moment().subtract(7, "days");
    vcardEnd = moment();

    vcardTimeRange.on("apply.daterangepicker", function (ev, picker) {
        vcardStart = picker.startDate;
        vcardEnd = picker.endDate;
        loadVcardDashboardChart(
            vcardStart.format("YYYY-MM-D H:mm:ss"),
            vcardEnd.format("YYYY-MM-D H:mm:ss")
        );
    });

    function updateLabel(start, end) {
        vcardTimeRange.find("span").html(start.format("MMM D, YYYY") + " - " + end.format("MMM D, YYYY"));
    }

    vcardTimeRange.daterangepicker({
        startDate: vcardStart,
        endDate: vcardEnd,
        opens: "left",
        showDropdowns: true,
        autoUpdateInput: false,
        locale: {
            customRangeLabel: Lang.get("js.custom"),
            applyLabel: Lang.get("js.apply"),
            cancelLabel: Lang.get("js.cancel"),
            fromLabel: Lang.get("js.from"),
            toLabel: Lang.get("js.to"),
            daysOfWeek: [
                Lang.get("js.sun"),
                Lang.get("js.mon"),
                Lang.get("js.tue"),
                Lang.get("js.wed"),
                Lang.get("js.thu"),
                Lang.get("js.fri"),
                Lang.get("js.sat"),
            ],
            monthNames: [
                Lang.get("js.jan"),
                Lang.get("js.feb"),
                Lang.get("js.mar"),
                Lang.get("js.apr"),
                Lang.get("js.may"),
                Lang.get("js.jun"),
                Lang.get("js.jul"),
                Lang.get("js.aug"),
                Lang.get("js.sep"),
                Lang.get("js.oct"),
                Lang.get("js.nov"),
                Lang.get("js.dec"),
            ],
        },
        ranges: {
            [Lang.get("js.this_week")]: [
                moment().startOf("week"), moment().endOf("week")
            ],
            [Lang.get("js.last_week")]: [
                moment().startOf("week").subtract(7, "days"),
                moment().startOf("week").subtract(1, "days")
            ],
        },
    }, updateLabel
    );

    updateLabel(vcardStart, vcardEnd);

    loadVcardDashboardChart(
        vcardStart.format("YYYY-MM-D H:mm:ss"),
        vcardEnd.format("YYYY-MM-D H:mm:ss")
    );
};

const loadVcardDashboardChart = (startDate, endDate) => {
    $.ajax({
        type: "GET",
        url: route("vcard.dashboard.chart"),
        dataType: "json",
        data: {
            start_date: startDate,
            end_date: endDate,
        },
        success: function (result) {
            dashboardVcardWeeklyBarChart(result);
        },
        cache: false,
    });
};

const dashboardVcardWeeklyBarChart = (result) => {
    const container = $("#vcardDashboardWeeklyUserBarChartContainer");

    if (!container.length) return;

    container.html("");
    container.css('height', '320px');
    $("canvas#vcardDashboardWeeklyUserBarChart").remove();

    container.append(
        '<canvas id="vcardDashboardWeeklyUserBarChart"></canvas>'
    );

    const ctx = document.getElementById("vcardDashboardWeeklyUserBarChart").getContext("2d");

    const chartLabels = result.data.weeklyLabels;
    const chartDatasets = result.data.data;

    if (chartDatasets.length === 2) {
        chartDatasets[0].backgroundColor = '#4F58B9'; // vCard
        chartDatasets[0].hoverBackgroundColor = '#4F58B9';
        chartDatasets[1].backgroundColor = '#4F58B9'; // vCard
        chartDatasets[1].hoverBackgroundColor = '#4F58B9';
    }

    if (vcardDashboardWeeklyChartInstance) {
        vcardDashboardWeeklyChartInstance.destroy();
    }

    const allValues = chartDatasets.flatMap(ds => ds.data);
    const maxValue = Math.max(...allValues.map(Math.abs));
    const suggestedMax = Math.ceil(maxValue) + 1;

    chartDatasets.forEach((dataset) => {
        dataset.borderRadius = 10;
    });


    vcardDashboardWeeklyChartInstance = new Chart(ctx, {
        type: "bar",
        data: {
            labels: chartLabels,
            datasets: chartDatasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    stacked: true,
                    suggestedMax: suggestedMax,
                    suggestedMin: -suggestedMax,
                    ticks: {
                        callback: function (value) {
                            return Math.abs(value);
                        }
                    },
                    grid: {
                        display: false,
                        drawBorder: false
                    }
                },
                x: {
                    stacked: true,
                    grid: {
                        display: false
                    },
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `${context.dataset.label}: ${Math.abs(context.raw)}`;
                        }
                    }
                },
                legend: {
                    labels: {
                        filter: item => item.datasetIndex === 0
                    }
                }
            }
        }
    });
};
