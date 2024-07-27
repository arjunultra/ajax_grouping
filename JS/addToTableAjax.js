$("#addProduct").click(function () {
  let selectedBrand = "";
  if ($("#brand-select").length > 0) {
    selectedBrand = $("#brand-select").val();
  }
  let selectedProduct = "";
  if ($("#product-select").length > 0) {
    selectedProduct = $("#product-select").val();
  }
  // alert(selectedProduct)
  var post_url =
    "party-form-changes.php?selected_product=" +
    selectedProduct +
    "&selected_brands=" +
    selectedBrand;
  jQuery.ajax({
    url: post_url,
    success: function (result) {
      // alert(result)
      if (result != "") {
        if ($("#table-body").find("tr").length > 0) {
          $("#table-body").find("tr:first").before(result);
        } else {
          $("#table-body").append(result);
        }
      }
    },
  });
});
