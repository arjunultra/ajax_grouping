<?php
require_once "./includes/connection.php";

// Initialize variables
$txnDate = $partyName = "";
$txnDateErr = $partyNameErr = $brandNameErr = $productNameErr = $productRateErr = $productQtyErr = "";

// Update variables
$update_id = isset($_REQUEST['update_id']) ? $_REQUEST['update_id'] : "";
$update_txn_date = $update_party_name = $update_brand_name = $update_product_name = $update_product_rate = $update_product_qty = "";

// Create the purchaseorder table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS purchaseorder (
    id INT AUTO_INCREMENT PRIMARY KEY,
    txn_date DATE NOT NULL,
    party_name VARCHAR(255) NOT NULL,
    brand_name TEXT NOT NULL,
    product_name TEXT NOT NULL,
    product_rate TEXT NOT NULL,
    product_qty TEXT NOT NULL,
    product_amount TEXT NOT NULL
)";
if (!mysqli_query($conn, $createTableSQL)) {
    echo "Error creating table: " . mysqli_error($conn);
}

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
    $txnDate = trim($_POST["txn_date"]);
    if (empty($txnDate) || !preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $txnDate)) {
        $txnDateErr = "Invalid date format";
        $isValid = false;
    }

    // Validate party name
    $partyName = trim($_POST["party_name"]);
    if (empty($partyName)) {
        $partyNameErr = "Party name is required";
        $isValid = false;
    } else if (!preg_match("/^[a-zA-Z]+$/", $partyName)) {
        $partyNameErr = "Party name should contain only letters";
        $isValid = false;
    }

    // Initialize arrays to hold the product data
    $brandNames = [];
    $productNames = [];
    $productRates = [];
    $productQtys = [];
    $productAmounts = [];

    // Validate each product row
    $rows = isset($_POST["product_rows"]) ? $_POST["product_rows"] : [];
    foreach ($rows as $row) {
        $brandName = trim($row["brand_name"]);
        $productName = trim($row["product_name"]);
        $productRate = trim($row["product_rate"]);
        $productQty = trim($row["product_qty"]);
        $productAmount = trim($row["product_amount"]);

        if (empty($brandName) || !preg_match("/^[a-zA-Z\s]+$/", $brandName)) {
            $brandNameErr = "Brand name should contain only letters and spaces";
            $isValid = false;
        }

        if (empty($productName) || !preg_match("/^[a-zA-Z\s]+$/", $productName)) {
            $productNameErr = "Product name should contain only letters and spaces";
            $isValid = false;
        }

        if (empty($productRate) || !is_numeric($productRate)) {
            $productRateErr = "Product rate must be a number";
            $isValid = false;
        }

        if (empty($productQty) || !is_numeric($productQty)) {
            $productQtyErr = "Product quantity must be a number";
            $isValid = false;
        }

        if ($isValid) {
            $brandNames[] = $brandName;
            $productNames[] = $productName;
            $productRates[] = $productRate;
            $productQtys[] = $productQty;
            $productAmounts[] = $productAmount;
        }
    }

    if ($isValid) {
        $brandNamesStr = implode(",", $brandNames);
        $productNamesStr = implode(",", $productNames);
        $productRatesStr = implode(",", $productRates);
        $productQtysStr = implode(",", $productQtys);
        $productAmountsStr = implode(",", $productAmounts);

        if ($update_id) {
            $update_query = "UPDATE purchaseorder SET 
                txn_date = '$txnDate', 
                party_name = '$partyName', 
                brand_name = '$brandNamesStr', 
                product_name = '$productNamesStr', 
                product_rate = '$productRatesStr', 
                product_qty = '$productQtysStr', 
                product_amount = '$productAmountsStr' 
                WHERE id = '$update_id'";
            mysqli_query($conn, $update_query);
        } else {
            $insert_query = "INSERT INTO purchaseorder 
                (txn_date, party_name, brand_name, product_name, product_rate, product_qty, product_amount) 
                VALUES ('$txnDate', '$partyName', '$brandNamesStr', '$productNamesStr', '$productRatesStr', '$productQtysStr', '$productAmountsStr')";
            if (mysqli_query($conn, $insert_query)) {
                echo "<p class='text-bg-success p-2 mt-4'>New record created successfully</p><br> <script>setTimeout(function() {
                    window.location.href = 'purchase_order_form.php';
                }, 4000);</script>";
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
                <input type="text" class="form-control" id="brand-name" name="brand_name" value="">
                <div class="invalid-feedback" id="brandNameErr"></div>
            </div>
            <div class="form-group">
                <label for="product-name">Product Name</label>
                <input type="text" class="form-control" id="product-name" name="product_name" value="">
                <div class="invalid-feedback" id="productNameErr"></div>
            </div>
            <div class="form-group">
                <label for="product-rate">Product Rate</label>
                <input type="number" class="form-control" id="product-rate" name="product_rate" value="">
                <div class="invalid-feedback" id="productRateErr"></div>
            </div>
            <div class="form-group">
                <label for="product-qty">Product Quantity</label>
                <input type="number" class="form-control" id="product-qty" name="product_qty" value="">
                <div class="invalid-feedback" id="productQtyErr"></div>
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
                    <table id="purchase-table" class="table table-bordered text-center">
                        <thead class="table-dark bg-primary">
                            <tr>
                                <th>Brand Name</th>
                                <th>Product Name</th>
                                <th>Product Rate</th>
                                <th>Product Quantity</th>
                                <th>Product Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <!-- Add dynamically generated rows here -->
                        </tbody>
                        <tfoot>
                            <td colspan="4">Total:</td>
                            <td class="display-5" colspan="6" id="sub-total">
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

        $(document).ready(function () {
            function validateFields() {
                let isValid = true;

                let brandName = $("#brand-name").val();
                let productName = $("#product-name").val();
                let productRate = $("#product-rate").val();
                let productQty = $("#product-qty").val();

                $("#brandNameErr").text("");
                $("#productNameErr").text("");
                $("#productRateErr").text("");
                $("#productQtyErr").text("");

                if (!brandName.match(/^[a-zA-Z\s]+$/)) {
                    $("#brandNameErr").text("Brand name should contain only letters and spaces");
                    isValid = false;
                }

                if (!productName.match(/^[a-zA-Z\s]+$/)) {
                    $("#productNameErr").text("Product name should contain only letters and spaces");
                    isValid = false;
                }

                if (!$.isNumeric(productRate)) {
                    $("#productRateErr").text("Product rate must be a number");
                    isValid = false;
                }

                if (!$.isNumeric(productQty)) {
                    $("#productQtyErr").text("Product quantity must be a number");
                    isValid = false;
                }

                return isValid;
            }

            $("#add-btn").click(function () {
                if (!validateFields()) {
                    return;
                }

                let brandName = $("#brand-name").val();
                let productName = $("#product-name").val();
                let productRate = $("#product-rate").val();
                let productQty = $("#product-qty").val();
                let productAmount = $("#product-amount").val();

                let row_count = $("#row_count").val();
                let row_index = parseInt(row_count) + 1;
                $("#row_count").val(row_index);

                let newRow = `
            <tr class="data-row">
                <td><span class="brand-name">${brandName}</span><input type="hidden" name="product_rows[${row_index}][brand_name]" value="${brandName}"></td>
                <td><span class="product-name">${productName}</span><input type="hidden" name="product_rows[${row_index}][product_name]" value="${productName}"></td>
                <td><span class="product-rate">${productRate}</span><input type="hidden" name="product_rows[${row_index}][product_rate]" value="${productRate}"></td>
                <td><span class="product-qty">${productQty}</span><input type="hidden" name="product_rows[${row_index}][product_qty]" value="${productQty}"></td>
                <td><span class="product-amount">${productAmount}</span><input type="hidden" name="product_rows[${row_index}][product_amount]" value="${productAmount}"></td>
                <td><button class="btn btn-danger btn-sm delete-btn" type="button">Delete</button></td>
            </tr>
        `;

                $("#table-body").append(newRow);
                calculateSubtotal();

                // Reset input fields
                $("#brand-name").val('');
                $("#product-name").val('');
                $("#product-rate").val('');
                $("#product-qty").val('');
                $("#product-amount").val('');
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

            $(document).on('click', '.delete-btn', function () {
                $(this).closest('tr').remove();
                calculateSubtotal();
            });
        });
    </script>
</body>

</html>