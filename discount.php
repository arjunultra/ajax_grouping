<?php
require_once "./includes/connection.php";

// Variables
$discountPercent = "";
$update_id = isset($_REQUEST['update_id']) ? $_REQUEST['update_id'] : "";
$update_discount_percent = "";

// Error handling
$discountPercentError = "";

// Table creation
$sqlCreateTable = "CREATE TABLE IF NOT EXISTS discount(
    id INT AUTO_INCREMENT PRIMARY KEY,
    discount_percent VARCHAR(255))";
mysqli_query($conn, $sqlCreateTable);
// Fetch admin data for update if update_id is set
if ($update_id) {
    $query = "SELECT * FROM discount WHERE id='$update_id'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $update_discount_percent = $row['discount_percent'];
    }
}

// Fetching data from POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $update_id = isset($_POST['update_id']) ? $_POST['update_id'] : "";
    $discountPercent = isset($_POST['discount_percent']) ? $_POST['discount_percent'] : "";

    // Validation
    $isValid = true;
    if (empty($discountPercent) || !preg_match("/^[0-9]{1,2}$/", $discountPercent)) {
        $discountPercentError = "Invalid discount format";
        $isValid = false;
    }

    if ($isValid) {
        if ($update_id) {
            // Update existing record
            $updateSQL = "UPDATE discount SET discount_percent='$discountPercent' WHERE id='$update_id'";
            if (mysqli_query($conn, $updateSQL)) {
                echo "<p class='text-bg-primary p-2 mt-4'>Record updated successfully.</p><br> <script>setTimeout(function() {
                    window.location.href = 'discount_table.php';
                }, 4000);</script>";
            } else {
                echo "Error updating record: " . mysqli_error($conn);
            }
        } else {
            // Insert new record
            $insertSQL = "INSERT INTO discount (discount_percent) VALUES('$discountPercent')";
            if (mysqli_query($conn, $insertSQL)) {
                echo "<p class='text-bg-success p-2 mt-4'>New record created successfully</p><br> <script>setTimeout(function() {
                    window.location.href = 'discount.php';
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
    <title>Discount Form</title>
    <link rel="stylesheet" href="./CSS/bootstrap.min.css">
    <link rel="stylesheet" href="./CSS/style.css">
</head>

<body>
    <h1 class="main-title text-center">Discount Entry Form</h1>
    <div class="container-sm">
        <form method="POST" class="form w-100 text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
            <input type="hidden" name="update_id" value="<?= $update_id ?>">
            <div class="form-group">
                <label for="discount-percent">Discount %</label>
                <input
                    value="<?php echo isset($_POST['discount_percent']) ? $_POST['discount_percent'] : $update_discount_percent; ?>"
                    type="text" id="discount-percent" name="discount_percent" class="form-control">
                <span class="error"><?php echo $discountPercentError; ?></span>
            </div>
            <button class="btn btn-primary mt-5" type="submit">Submit</button>
            <a target="_blank" class="btn btn-success mt-5" href="discount_table.php">Go to Table</a>
        </form>
    </div>
    <?php mysqli_close($conn); ?>
</body>

</html>