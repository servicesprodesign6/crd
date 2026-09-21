
Livewire.hook("element.init", () => {
    loadProductStatusFilter();
});

function loadProductStatusFilter() {
    $(".product-transaction-order-status").select2({
        minimumResultsForSearch: -1,
    });
}

listenChange(".product-transaction-order-status", function () {
    let id = $(this).data("id");
    let status = $(this).val();
    let $this = $(this);
    $this.prop("disabled", true);

    $.ajax({
        url: route("product.update.order.status", id),
        type: "POST",
        data: { status: status },
        success: function (response) {
            Livewire.dispatch("refresh");
            displaySuccessMessage(response.message);
        },
        error: function (response) {
            $this.prop("disabled", false);
            displayErrorMessage(response.responseJSON.message);
        },
    });
});
