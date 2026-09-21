
Livewire.hook("element.init", () => {
    loadProductFilter();
});

function loadProductFilter() {
    $(".wp-product-order-status").select2({
        minimumResultsForSearch: -1,
    });
}

listenChange(".wp-product-order-status", function () {
    let orderId = $(this).data("id");
    let status = $(this).val();
    if (status == 0) return;

    let url = route("wp.product.update.order.status", orderId);
    $.ajax({
        url: url,
        type: "POST",
        data: { status: status },
        success: function (response) {
            Livewire.dispatch("refresh");
            prepareAndSendWpMessage(response.data[0], response.data[1]);
            displaySuccessMessage(response.message);
        },
        error: function (response) {
            displayErrorMessage(response.responseJSON.message);
        },
    });
});

function prepareAndSendWpMessage(order, base_url) {
    let baseUrl = base_url;
    let storeAlias = order.wp_store.url_alias;
    let regionCode = order.region_code;
    let whatsappNumber = order.phone;
    let templateId = order.wp_store.template_id;
    let message = "";

    if (order.status == 1) {
        message = Lang.get("js.order_dispatched") + `:\n\n`;
    } else if (order.status == 2) {
        message = Lang.get("js.order_delivered") + `:\n\n`;
    } else if (order.status == 3) {
        message = Lang.get("js.order_cancelled") + `:\n\n`;
    } else {
        return;
    }

    message = Lang.get("js.customer_details") + `:\n`;
    message += `------------------------------\n`;
    message += Lang.get("js.name") + `: ${order.name}\n`;
    message +=
        Lang.get("js.phone") + `: +${order.region_code} ${order.phone}\n`;
    message += Lang.get("js.address") + `: ${order.address}\n\n`;
    message += Lang.get("js.order_id") + `: ${order.order_id}\n`;

    if (order.order_type > 0) {
        message += Lang.get('js.order_type') + `: ${order.order_type == 1 ? Lang.get('js.take_away') : Lang.get('js.delivery')}\n`;
    }
    message += `------------------------------\n`;
    message += Lang.get("js.product_details") + `:\n`;
    message += `------------------------------\n`;

    let subTotal = 0;
    if (order.products && Array.isArray(order.products)) {
        order.products.forEach((product, index) => {
            let productUrl = `${baseUrl}/whatsapp-store/${storeAlias}/${product.product_id}/product-details`;
            let currencyIcon = (product.product && product.product.currency) ? product.product.currency.currency_icon : "";
            let productName = product.product ? product.product.name : "Unknown";

            subTotal += Number(product.total_price || 0);
            message += `${index + 1}.\n`;
            message += Lang.get("js.product_name") + `: ${productName}\n`;
            message += Lang.get("js.product_url") + ` : ${productUrl}\n`;
            message += Lang.get("js.price") + ` : ${currencyIcon} ${product.price}\n`;
            message += Lang.get("js.quantity") + ` : ${product.qty}\n`;
            message += Lang.get("js.total_price") + ` : ${currencyIcon} ${product.total_price}\n`;
            message += `------------------------------\n`;
        });
    }

    message += `\n${Lang.get("js.total")}: ${subTotal.toFixed(2)}`;
    if (order.discount_amount && parseFloat(order.discount_amount) > 0) {
        message += `\n${Lang.get("js.discount")}: ${order.discount_amount}`;
    }
    if (order.order_type > 1 && order.delivery_charge && parseFloat(order.delivery_charge) > 0) {
        message += `\n${Lang.get('js.delivery_charge')}: ${order.delivery_charge}`;
    }
    message += `\n${Lang.get("js.grand_total")}: ${order.grand_total}\n`;

    let encodedMessage = encodeURIComponent(message);
    let recipientPhone = `+${regionCode}${whatsappNumber}`;

    let whatsappUrl = `https://wa.me/${recipientPhone}?text=${encodedMessage}`;

    window.open(whatsappUrl);
}

listenClick(".wp-product-transaction-order-view-btn", function () {
    let orderId = $(this).data("id");
    viewproductOrderRenderData(orderId);
});

function viewproductOrderRenderData(orderId) {
    let url = route("wp.product.show.order", orderId);
    const STATUS_ARR = {
        0: Lang.get('js.pending'),
        1: Lang.get('js.dispatched'),
        2: Lang.get('js.delivered'),
        3: Lang.get('js.cancelled'),
    };

    $.ajax({
        url: url,
        type: "GET",
        success: function (response) {
            if (response.success) {
                let order = response.data;

                $("#orderId").text(order.order_id);
                $("#orderName").text(order.name);
                $("#orderPhone").text(`+${order.region_code} ${order.phone}`);
                $("#orderStatus").text(STATUS_ARR[order.status]);
                let templateId = order.wp_store.template_id;
                $("#orderAddress").text(order.address);
                if (order.order_type > 0) {
                    $(".order-type-div").removeClass("d-none");
                    $("#orderType").text(order.order_type == 1 ? Lang.get('js.take_away') : Lang.get('js.delivery'));
                } else {
                    $(".order-type-div").addClass("d-none");
                }
                $("#orderDeliveryCharge").text(order.delivery_charge);

                let subTotal = 0;
                let productRows = "";
                order.products.forEach((product, index) => {
                    subTotal += Number(product.total_price);
                    productRows += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${product.product?.name}</td>
                            <td>${product.price}</td>
                            <td>${product.qty}</td>
                            <td>${product.total_price}</td>
                        </tr>
                    `;
                });

                $("#orderSubTotal").text(subTotal.toFixed(2));
                if (order.discount_amount && parseFloat(order.discount_amount) > 0) {
                    $(".discount-row").removeClass("d-none");
                    $("#orderDiscount").text(order.discount_amount);
                } else {
                    $(".discount-row").addClass("d-none");
                }

                if (order.order_type > 1 && order.delivery_charge && parseFloat(order.delivery_charge) > 0) {
                    $(".delivery-charge-row").removeClass("d-none");
                    $("#orderFooterDeliveryCharge").text(order.delivery_charge);
                } else {
                    $(".delivery-charge-row").addClass("d-none");
                }

                $("#orderGrandTotal").text(order.grand_total);

                $(".product-list").html(productRows);

                $("#wpStoreShowProductOrderModal").modal("show");
            }
        },
        error: function (response) {
            displayErrorMessage(response.responseJSON.message);
        },
    });
}

Livewire.hook("element.init", () => {
    loadwpProductOrderFilter();
});
function loadwpProductOrderFilter() {
    $("#wpProductOrderFilter").select2();
    $("#wpProductOrderTypeFilter").select2();
}
listen("change", "#wpProductOrderFilter", function () {
    Livewire.dispatch("changeFilterStore", { id: $(this).val() });
    window.hideDropdownManually(
        $("#dropdownMenuWpProductOrderFilter"),
        $(".dropdown-menu")
    );
});
listen("change", "#wpProductOrderTypeFilter", function () {
    Livewire.dispatch("changeFilterOrderType", { orderType: $(this).val() });
    window.hideDropdownManually(
        $("#dropdownMenuWpProductOrderFilter"),
        $(".dropdown-menu")
    );
});
function hideDropdownManually(button, menu) {
    button.attr("aria-expanded", "false");
    menu.removeClass("show");
}

listen("click", "#wpProductOrderResetFilter", function () {
    $("#wpProductOrderFilter").val('').trigger('change');
    $("#wpProductOrderTypeFilter").val('').trigger('change');
    Livewire.dispatch("changeFilterStore", { id: "" });
    Livewire.dispatch("changeFilterOrderType", { orderType: "" });
    window.hideDropdownManually($("#dropdownMenuWpProductOrderFilter"), $(".dropdown-menu"));
});
