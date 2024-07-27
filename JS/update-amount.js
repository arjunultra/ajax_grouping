document.addEventListener("DOMContentLoaded", function () {
  let tBody = document.getElementById("table-body");

  // Assuming rows are added dynamically, listen for clicks within the table body.
  // This example uses 'click' event for demonstration; adjust according to your actual event.
  tBody.addEventListener("keyup", function (event) {
    // Check if the clicked element is within a product-row
    let selectedRow = event.target.closest(".product-row");
    let rowTotal = selectedRow.querySelector(".amount");
    if (selectedRow) {
      // Fetch the product rate from the selected row
      let productRateElement = selectedRow.querySelector(".product-rate");
      let productQuantityElement =
        selectedRow.querySelector(".product-quantity");
      if (productRateElement) {
        var productRate = parseInt(productRateElement.value);
      }
      if (productQuantityElement) {
        var productQuantity = parseInt(productQuantityElement.value);
      }
      console.log(productRate);
      console.log(productQuantity);
      if (productRateElement && productQuantityElement) {
        rowTotal.innerHTML = productRate * productQuantity;
        console.log(rowTotal.innerHTML);
        calculateSubtotal();
      }
    }
  });
});
