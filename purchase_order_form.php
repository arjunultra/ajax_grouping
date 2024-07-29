<?php
require_once "./includes/connection.php";

// variables
$txnDate = $partyName = $brandName = $productName = $productRate = $productQty = $productAmount = "";

// Update variables
$update_id = isset($_REQUEST['update_id']) ? $_REQUEST['update_id'] : "";
$update_txn_date = $update_party_name = $update_brand_name = $update_product_name = $update_product_rate = $update_product_qty = "";

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
    product_amount VARCHAR(255) NOT NULL
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
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $isValid = true;

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
            $productAmountArr[] = "0";
        } else {
            $productAmountArr[] = $rate * $productQtyArr[$key];
        }
    }

    // Ensure all variables are strings
    $txnDateStr = ensure_string($txnDate);
    $partyNameStr = ensure_string($partyName);
    $brandNameStr = ensure_string($brandNameArr);
    $productNameStr = ensure_string($productNameArr);
    $productRateStr = ensure_string($productRateArr);
    $productQtyStr = ensure_string($productQtyArr);
    $productAmountStr = ensure_string($productAmountArr);

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
                product_amount = '$productAmountStr' 
                WHERE id = '$update_id'";
            if (mysqli_query($conn, $update_query)) {
                echo "<p class='text-bg-primary p-2 mt-4'>Existing record updated successfully</p><br>
                      <script>setTimeout(function() { window.location.href = 'purchase_order_form.php'; }, 4000);</script>";
            } else {
                echo "Error inserting record: " . mysqli_error($conn);
            }
        } else {
            $insert_query = "INSERT INTO purchaseorder 
                (txn_date, party_name, brand_name, product_name, product_rate, product_qty, product_amount) 
                VALUES ('$txnDateStr', '$partyNameStr', '$brandNameStr', '$productNameStr', '$productRateStr', '$productQtyStr', '$productAmountStr')";
            if (mysqli_query($conn, $insert_query)) {
                echo "<p class='text-bg-success p-2 mt-4'>New record created successfully</p><br>
                      <script>setTimeout(function() { window.location.href = 'purchase_order_form.php'; }, 4000);</script>";
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
        <form method="POST" action="">
            <div class="form-group">
                <label for="txn-date">Transaction Date</label>
                <input type="date" class="form-control <?php echo $txnDateErr ? 'is-invalid' : ''; ?>" id="txn-date"
                    name="txn_date" value="<?php echo $update_txn_date; ?>" required>
                <div class="invalid-feedback"><?php echo $txnDateErr; ?></div>
            </div>
            <div class="form-group">
                <label for="party-name">Party Name</label>
                <input type="text" class="form-control <?php echo $partyNameErr ? 'is-invalid' : ''; ?>" id="party-name"
                    name="party_name" value="<?php echo $update_party_name; ?>" required>
                <div class="invalid-feedback"><?php echo $partyNameErr; ?></div>
            </div>
            <div class="form-group">
                <label for="brand-name">Brand Name</label>
                <input type="text" class="form-control <?php echo $brandNameErr ? 'is-invalid' : ''; ?>" id="brand-name"
                    name="brand_name" value="<?php echo $update_brand_name; ?>" required>
                <div class="invalid-feedback"><?php echo $brandNameErr; ?></div>
            </div>
            <div class="form-group">
                <label for="product-name">Product Name</label>
                <input type="text" class="form-control <?php echo $productNameErr ? 'is-invalid' : ''; ?>"
                    id="product-name" name="product_name" value="<?php echo $update_product_name; ?>" required>
                <div class="invalid-feedback"><?php echo $productNameErr; ?></div>
            </div>
            <div class="form-group">
                <label for="product-rate">Product Rate</label>
                <input type="number" class="form-control <?php echo $productRateErr ? 'is-invalid' : ''; ?>"
                    id="product-rate" name="product_rate" value="<?php echo $update_product_rate; ?>" required>
                <div class="invalid-feedback"><?php echo $productRateErr; ?></div>
            </div>
            <div class="form-group">
                <label for="product-qty">Product Quantity</label>
                <input type="number" class="form-control <?php echo $productQtyErr ? 'is-invalid' : ''; ?>"
                    id="product-qty" name="product_qty" value="<?php echo $update_product_qty; ?>" required>
                <div class="invalid-feedback"><?php echo $productQtyErr; ?></div>
            </div>
            <div class="form-group">
                <label for="product-amount">Product Amount</label>
                <input type="text" class="form-control" id="product-amount" name="product_amount" readonly>
            </div>
            <div class="form-group d-flex">
                <button id="add-btn" class="btn btn-success" type="button">Add</button>
            </div>
            <div class="container-fluid mt-5">
                <h2>Party Order Details</h2>
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
                        </tbody>
                        <tfoot>
                            <td colspan="4" class="text-center">Subtotal</td>
                            <td colspan="2" class="fw-bold display-6" id="sub-total">
                                <input type="hidden" name="sub_total" id="sub-total-hidden">
                            </td>
                        </tfoot>
                    </table>
                </div>
            </div>
            <button type="submit" class="d-block mx-auto text-center btn btn-primary mt-4">Submit</button>
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
            $("#add-btn").click(function () {
                let brandName = $("#brand-name").val();
                let productName = $("#product-name").val();
                let productRate = $("#product-rate").val();
                let productQty = $("#product-qty").val();
                let productAmount = $("#product-amount").val();

                if (!brandName || !productName || !productRate || !productQty) {
                    alert("Please fill in all fields.");
                    return;
                }

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
        }
    </script>
</body>

</html>