<?php
require_once "./includes/connection.php";

// variables
$txnDate = $partyName = $brandName = $productName = $productRate = $productQty = $productAmount = $discountPercent = $discountValue = $grandTotal = "";
$editBrands = [];
$editProducts = [];
$editProductRates = [];
$editProductQuantities = [];
$brandNameArr = [];
$productNameArr = [];
$productRateArr = [];
$productQtyArr = [];
$productAmountArr = [];

// Update variables
$update_id = isset($_REQUEST['update_id']) ? $_REQUEST['update_id'] : "";
$update_txn_date = $update_party_name = $update_brand_name = $update_product_name = $update_product_rate = $update_product_qty = $update_product_amount = "";

// error handling
$txnDateErr = $partyNameErr = $brandNameErr = $productNameErr = $productRateErr = $productQtyErr = "";

// Create the purchaseorder table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS purchaseorder (
    id INT AUTO_INCREMENT PRIMARY KEY,
    txn_date DATE NOT NULL,
    party_name VARCHAR(255) NOT NULL,
    brand_name VARCHAR(255) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_rate VARCHAR(255) NOT NULL,
    product_qty VARCHAR(255) NOT NULL,
    product_amount VARCHAR(255) NOT NULL,
    discount_value VARCHAR(255) NOT NULL,
    grand_total VARCHAR(255) NOT NULL

)";
if (!mysqli_query($conn, $createTableSQL)) {
    echo "Error creating table: " . mysqli_error($conn);
}

// Function to ensure variable is a string and trim it
function ensure_string($var)
{
    if (is_array($var)) {
        return implode(",", $var);
    }
    return trim($var);
}

$txnDateStr = ensure_string($txnDate);
$partyNameStr = ensure_string($partyName);
$brandNameStr = ensure_string($brandName);
$productNameStr = ensure_string($productName);
$productRateStr = ensure_string($productRate);
$productQtyStr = ensure_string($productQty);

// Fetch order form data for update if update_id is set
if ($update_id) {
    $query = "SELECT * FROM purchaseorder WHERE id='$update_id'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $update_txn_date = $row['txn_date'];
        $update_party_name = $row['party_name'];
        $update_brand_name = $row['brand_name'];
        $update_product_name = $row['product_name'];
        $update_product_rate = $row['product_rate'];
        $update_product_qty = $row['product_qty'];
        $update_product_amount = $row['product_amount'];
    }
}
// discount %
$discountSQL = "SELECT * FROM discount";
$resultDiscount = mysqli_query($conn, $discountSQL);
if (mysqli_num_rows($resultDiscount) > 0) {
    $rows = mysqli_fetch_all($resultDiscount, MYSQLI_ASSOC);
    foreach ($rows as $row) {
        $discountID = $row['id'];
        $discountPercent = $row['discount_percent'];
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $isValid = true;
    $brandName = $_POST['brandform_name'];

    // Validate txn date
    $txnDate = $_POST["txn_date"];
    if (empty($txnDate) || !preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $txnDate)) {
        $txnDateErr = "Invalid date format";
        $isValid = false;
    }

    // Validate party name
    $partyName = $_POST["party_name"];
    if (empty($partyName)) {
        $partyNameErr = "Party name is required";
        $isValid = false;
    } else if (!preg_match("/^[a-zA-Z]+$/", $partyName)) {
        $partyNameErr = "Party name should contain only letters";
        $isValid = false;
    }

    // Validate brand name
    if (is_array($_POST["brand_name"])) {
        $brandNameArr = $_POST['brand_name'];
    } else {
        $brandNameArr = array($_POST["brand_name"]);
    }
    if (empty($_POST['brand_name'])) {
        $brandNameErr = "Brand name cannot be empty!";
        $isValid = false;
    }
    // Validate Product Name
    if (is_array($_POST["product_name"])) {
        $productNameArr = $_POST['product_name'];
    } else {
        $productNameArr = array($_POST["product_name"]);
    }

    // Validate product rate
    if (is_array($_POST["product_rate"])) {
        $productRateArr = $_POST['product_rate'];
    } else {
        $productRateArr = array($_POST["product_rate"]);
    }

    // Validate product quantity
    if (is_array($_POST["product_qty"])) {
        $productQtyArr = $_POST['product_qty'];
    } else {
        $productQtyArr = array($_POST["product_qty"]);
    }

    // Calculate product amount for each item
    $productAmountArr = array();
    foreach ($productRateArr as $key => $rate) {
        if (empty($rate) || empty($productQtyArr[$key])) {
            $productAmountArr[] = 0;
        } else {
            $productAmountArr[] = (int) $rate * (int) $productQtyArr[$key];
        }
    }
    // Calculate subtotal
    $subTotal = array_sum($productAmountArr);
    // Discount Value
    $discountPercent = (int) $discountPercent;
    $discountValue = ($subTotal * $discountPercent) / 100;
    // Grand Total
    $grandTotal = $subTotal - $discountValue;
    // echo $discountValue . "  discount value <br>";
    // echo $subTotal . "  Subtotal <br>";
    // echo $grandTotal . "  Grand Total <br>";
    // echo $discountPercent . "  discount Percent <br>";

    // Ensure all variables are strings
    $txnDateStr = ensure_string($txnDate);
    $partyNameStr = ensure_string($partyName);
    $brandNameStr = ensure_string($brandNameArr);
    $productNameStr = ensure_string($productNameArr);
    $productRateStr = ensure_string($productRateArr);
    $productQtyStr = ensure_string($productQtyArr);
    $productAmountStr = ensure_string($productAmountArr);
    $discountValueStr = ensure_string($discountValue);
    $grandTotalStr = ensure_string($grandTotal);

    // Update or insert logic
    if ($isValid) {
        if ($update_id) {
            $update_query = "UPDATE purchaseorder SET 
            txn_date = '$txnDateStr', 
            party_name = '$partyNameStr', 
            brand_name = '$brandNameStr', 
            product_name = '$productNameStr', 
            product_rate = '$productRateStr', 
            product_qty = '$productQtyStr', 
            product_amount = '$productAmountStr',
            discount_value = '$discountValueStr',
            grand_total = '$grandTotalStr' 
            WHERE id = '$update_id'";
            if (mysqli_query($conn, $update_query)) {
                echo "<p class='text-bg-primary p-2 mt-4'>Existing record updated successfully</p><br>
                <script>setTimeout(function() {
                    window.location.href = 'purchase_order_table.php';
                }, 4000);</script>
                  ";
            } else {
                echo "Error inserting record: " . mysqli_error($conn);
            }
        } else {
            $insert_query = "INSERT INTO purchaseorder 
            (txn_date, party_name, brand_name, product_name, product_rate, product_qty, product_amount, discount_value, grand_total) 
            VALUES ('$txnDateStr', '$partyNameStr', '$brandNameStr', '$productNameStr', '$productRateStr', '$productQtyStr', '$productAmountStr','$discountValueStr','$grandTotalStr')";
            if (mysqli_query($conn, $insert_query)) {
                echo "<p class='text-bg-success p-2 mt-4'>New record created successfully</p><br>
                <script>setTimeout(function() {
                window.location.href = 'purchase_order_form.php';
                }, 4000);</script>
                  ";
            } else {
                echo "Error inserting record: " . mysqli_error($conn);
            }
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Form</title>
    <link rel="stylesheet" href="./CSS/bootstrap.min.css">
    <link rel="stylesheet" href="./CSS/style.css">
    <style>
        #productTable {
            width: 100vw;
            position: relative;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
            padding: 0 20px;
            /* Optional: to avoid the table edges touching the screen borders */
        }
    </style>
</head>

<body>
    <div class="container-xs">
        <h1 class="main-title text-center">Purchase Order Form</h1>
        <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
            <input type="hidden" name="update_id" value="<?php echo $update_id; ?>">
            <div class="form-group">
                <label for="txn-date">Transaction Date</label>
                <input type="date" class="form-control <?php echo $txnDateErr ? 'is-invalid' : ''; ?>" id="txn-date"
                    name="txn_date" value="<?php echo $update_txn_date; ?>">
                <div class="invalid-feedback"><?php echo $txnDateErr; ?></div>
            </div>
            <div class="form-group">
                <label for="party-name">Party Name</label>
                <input type="text" class="form-control <?php echo $partyNameErr ? 'is-invalid' : ''; ?>" id="party-name"
                    name="party_name" value="<?php echo $update_party_name; ?>">
                <div class="invalid-feedback"><?php echo $partyNameErr; ?></div>
            </div>
            <div class="form-group">
                <label for="brand-name">Brand Name</label>
                <input type="text" class="form-control <?php echo $brandNameErr ? 'is-invalid' : ''; ?>" id="brand-name"
                    name="brandform_name" value="<?php $update_brand_name; ?>">
                <div class="invalid-feedback"><?php echo $brandNameErr; ?></div>
            </div>
            <div class="form-group">
                <label for="product-name">Product Name</label>
                <input type="text" class="form-control <?php echo $productNameErr ? 'is-invalid' : ''; ?>"
                    id="product-name" name="product_name" value="<?php $update_product_name; ?>">
                <div class="invalid-feedback"><?php echo $productNameErr; ?></div>
            </div>
            <div class="form-group">
                <label for="product-rate">Product Rate</label>
                <input type="number" class="form-control <?php echo $productRateErr ? 'is-invalid' : ''; ?>"
                    id="product-rate" name="product_rate" value="<?php echo $update_product_rate; ?>">
                <div class="invalid-feedback"><?php echo $productRateErr; ?></div>
            </div>
            <div class="form-group">
                <label for="product-qty">Product Quantity</label>
                <input type="number" class="form-control <?php echo $productQtyErr ? 'is-invalid' : ''; ?>"
                    id="product-qty" name="product_qty" value="<?php echo $update_product_qty; ?>">
                <div class="invalid-feedback"><?php echo $productQtyErr; ?></div>
            </div>
            <label for="product-amount">Product Amount</label>
            <div class="form-group d-flex">
                <input type="text" class="form-control" id="product-amount" name="product_amount" readonly>
                <button id="add-btn" class="btn btn-success" type="button">Add</button>
            </div>
            <div class="form-group d-flex">

            </div>
            <div class="container-fluid mt-5">
                <h2>Purchase Order Details</h2>
                <div id="productTable" class="table-responsive">
                    <table id="purchase-table" class="table table-striped table-hover table-bordered">
                        <input type="hidden" id="row_count" name="row_count" value="0">
                        <thead class="table-dark bg-primary">
                            <tr>
                                <th>Brand Name</th>
                                <th>Product Name</th>
                                <th>Product Rate</th>
                                <th>Product Quantity</th>
                                <th>Row Total</th>
                                <th>Function</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <!-- cc -->
                            <?php
                            $index = 0;

                            if (!empty($update_brand_name)) {
                                $rowCounter = "1";
                                $previousBrand = "";
                                $editBrands = explode(',', $update_brand_name);
                                $editProducts = explode(',', $update_product_name);
                                $editProductRates = explode(',', $update_product_rate);
                                $editProductQuantities = explode(',', $update_product_qty);
                                print_r($editProductRates);
                                print_r($editProductQuantities);
                                for ($i = 0; $i < count($editBrands); $i++) {
                                    $index = $i + 1;
                                    $amount[$i] = $editProductRates[$i] * $editProductQuantities[$i];
                                    $row_index = $i + 1;
                                    ?>
                                    <tr class="data-row data-row<?php echo $row_index ?>"
                                        data-row-index="<?php echo $row_index ?>">
                                        <td>

                                            <?php if (!empty($previousBrand) && $previousBrand == $editBrands[$i]) {
                                                echo "";
                                            } else {
                                                echo $editBrands[$i];
                                            } ?>
                                            <input type="hidden" name="brand_name[]" value="<?php echo $editBrands[$i] ?>">
                                        </td>
                                        <td><?php echo $editProducts[$i] ?>
                                            <input type="hidden" name="product_name[]" value="<?php echo $editProducts[$i] ?>">
                                        </td>
                                        <td>
                                            <input id="table-rate" class="w-75 product-rate" type="text" name="product_rate[]"
                                                value="<?php echo $editProductRates[$i] ?>">
                                        </td>
                                        <td>
                                            <input id="table-qty" class="w-75 product-quantity" type="text" name="product_qty[]"
                                                value="<?php echo $editProductQuantities[$i] ?>">
                                        </td>
                                        <td class="product-amount"><?php echo $amount[$i]; ?>
                                            <input class="w-75 products-amt" type="hidden" name="product_amount[]">
                                        </td>
                                        <td class="function">
                                            <input type="hidden" name="function">
                                            <button type="button" class="btn btn-outline-danger delete-btn"
                                                onclick="DeleteRow('<?php echo $row_index; ?>')">Delete</button>
                                        </td>

                                        <?php $previousBrand = $editBrands[$i];
                                        $rowCounter++;
                                }
                            } ?>
                            </tr>
                        </tbody>
                        <tfoot>
                            <td colspan="4" class="">Subtotal</td>
                            <td colspan="2" class="fw-bold display-6" id="sub-total">
                                <input type="hidden" name="sub_total" id="sub-total-hidden">
                            </td>
                            <tr class="sundries">
                                <td colspan="4">Discount <span id="discount-percent">
                                        <?php echo $discountPercent ?>
                                    </span>%</td>
                                <td id="discount-value" class="text-bg-success display-6"><span></span>
                                    <input name="discount_value" type="hidden">
                                </td>
                                <td colspan="2" class="fw-bold display-6" id="discount"></td>
                            </tr>
                            <tr>
                                <td>Grand Total :</td>
                                <td colspan="3"></td>
                                <td class="text-bg-dark display-4" id="grand-total"><span></span></td>
                                <td></td>

                            </tr>

                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="button-container d-flex gap-2 align-items-center justify-content-center">
                <div><a class="btn btn-success" href="purchase_order_table.php">Go to Table</a></div>
                <div><button type="submit" class="d-block mx-auto text-center btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>
    <script src="./JS/jquery-3.7.1.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let productQty = document.getElementById('product-qty');
            let productRate = document.getElementById('product-rate');
            let productAmountDisplay = document.getElementById('product-amount');

            function updateAmount() {
                let qtyValue = parseInt(productQty.value) || 0;
                let rateValue = parseInt(productRate.value) || 0;
                let amountValue = qtyValue * rateValue;
                productAmountDisplay.value = amountValue;
            }

            productQty.addEventListener('keyup', updateAmount);
            productRate.addEventListener('keyup', updateAmount);
        });

        // AJAX
        $(document).ready(function () {
            var number_regex = /^[0-9]+$/;
            var text_regex = /^[a-zA-Z ]+$/;

            $("#add-btn").click(function () {
                if ($("span.error").length > 0) {
                    $("span.error").remove();
                }

                let brandName = $("#brand-name").val().trim();
                let productName = $("#product-name").val().trim();
                let productRate = $("#product-rate").val().trim();
                let productQty = $("#product-qty").val().trim();
                let productAmount = $("#product-amount").val().trim();

                let valid = true;

                if (!text_regex.test(brandName)) {
                    $("#brand-name").addClass('is-invalid').after('<span class="invalid-feedback error">Invalid Brand Name</span>');
                    valid = false;
                } else {
                    $("#brand-name").removeClass('is-invalid').addClass('is-valid');
                }

                if (!text_regex.test(productName)) {
                    $("#product-name").addClass('is-invalid').after('<span class="invalid-feedback error">Invalid Product Name</span>');
                    valid = false;
                } else {
                    $("#product-name").removeClass('is-invalid').addClass('is-valid');
                }

                if (!number_regex.test(productRate) || productRate <= 0) {
                    $("#product-rate").addClass('is-invalid').after('<span class="invalid-feedback error">Invalid Product Rate</span>');
                    valid = false;
                } else {
                    $("#product-rate").removeClass('is-invalid').addClass('is-valid');
                }

                if (!number_regex.test(productQty) || productQty <= 0) {
                    $("#product-qty").addClass('is-invalid').after('<span class="invalid-feedback error">Invalid Product Quantity</span>');
                    valid = false;
                } else {
                    $("#product-qty").removeClass('is-invalid').addClass('is-valid');
                }

                if (!valid) return;

                // Reset form fields and remove validation states
                $("#brand-name, #product-name, #product-rate, #product-qty").removeClass('is-valid').val('');
                $("#product-amount").val('');

                // Rest of your AJAX logic to add the row to the table
                let row_count = $("#row_count").val();
                let row_index = parseInt(row_count) + 1;
                $("#row_count").val(row_index);

                let post_url = "order_form_changes.php?brand_name=" + brandName + "&product_name=" + productName + "&product_rate=" + productRate + "&product_qty=" + productQty + "&product_amount=" + productAmount + "&row_index=" + row_index;

                $.ajax({
                    url: post_url,
                    method: 'GET',
                    success: function (result) {
                        if (result.trim() !== "") {
                            let brandHeaderExists = false;
                            $("#table-body tr.brand-header").each(function () {
                                if ($(this).find('td').text().trim() === brandName) {
                                    $(this).after(result);
                                    calculateSubtotal();
                                    brandHeaderExists = true;
                                    $(document).on('click', '.delete-btn', function () {
                                        $(this).closest('tr').remove();
                                        calculateSubtotal();
                                    });
                                    return false;
                                }
                            });
                            if (!brandHeaderExists) {
                                $("#table-body").append(`
                        <tr class="brand-header">
                            <td colspan="6"><strong>${brandName}</strong></td>
                        </tr>
                        ${result}
                    `);
                                $(document).on('click', '.delete-btn', function () {
                                    $(this).closest('tr').remove();
                                    calculateSubtotal();
                                });
                                calculateSubtotal();
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX request failed:", status, error);
                        alert("An error occurred while adding the row. Please try again.");
                    }
                });
            });
        });

        function calculateSubtotal() {
            let totalAmount = 0;
            $('.data-row').find('.product-amount').each(function () {
                let amount = parseInt($(this).text()) || 0;
                totalAmount += amount;
            });
            $('#sub-total').html(totalAmount);
            $('#sub-total-hidden').val(totalAmount);
            calculateDiscount();
        }
        <?php if (!empty($update_id)) { ?>
            calculateSubtotal();
        <?php } ?>
        // delete row on update
        function DeleteRow(row_index) {
            console.log(row_index);
            if (row_index != "") {
                if ($('.data-row' + row_index).length > 0) {
                    $('.data-row' + row_index).remove();
                }
                calculateSubtotal();
            }

        }
        // Calculate Discount
        function calculateDiscount() {
            let discountValue;
            let subTotal = parseInt(document.getElementById('sub-total').textContent);
            let discountPercent = parseInt(document.getElementById('discount-percent').textContent);
            let discountValueDisplay = document.getElementById('discount-value');
            let grandTotalDisplay = document.getElementById('grand-total');
            discountValue = (subTotal * discountPercent) / 100;
            discountValueDisplay.textContent = discountValue;
            let grandTotal = subTotal - discountValue;
            grandTotalDisplay.textContent = grandTotal;
        }

    </script>
</body>

</html>