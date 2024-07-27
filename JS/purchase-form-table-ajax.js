$(document).ready(function () {
  // Event listener for the Add button
  $("#addrow-btn").click(function () {
    let selectedBrand = "";
    if ($("#brand-select").length > 0) {
      selectedBrand = $("#brand-select").val();
    }
    let selectedProduct = "";
    if ($("#products-select").length > 0) {
      selectedProduct = $("#products-select").val();
    }
    let productRate = "";
    productRate = $("#product-rate").val();
    let productQuantity = "";
    productQuantity = $("#product-quantity").val();
    let productAmount = "";
    productAmount = $("#product-amount").val();
    let row_count = 0;
    if ($("#row_count").length > 0) {
      row_count = $("#row_count").val();
      row_index = parseInt(row_count) + 1;
      $("#row_count").val(row_index);
    }
    var post_url =
      "purchase_form_changes.php?selected_products=" +
      selectedProduct +
      "&selected_brand=" +
      selectedBrand +
      "&product_rate=" +
      productRate +
      "&product_quantity=" +
      productQuantity +
      "&product_amount=" +
      productAmount +
      "&row_index=" +
      row_index;
    jQuery.ajax({
      url: post_url,
      success: function (result) {
        // alert(result);
        if (result != "") {
          if ($("#table-body").find("tr").length > 0) {
            $("#table-body").find("tr:last").after(result);
            calculateSubtotal();
          } else {
            $("#table-body").append(result);
            calculateSubtotal();
          }
        }
      },
    });
  });
});
