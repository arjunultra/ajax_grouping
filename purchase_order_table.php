<?php
require_once "./includes/connection.php";

// Fetching data from purchasetable
$sql = "SELECT * FROM purchaseorder";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Purchase Order Form Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>
    <div class="container mt-5">
        <h1>Purchase Order Form Data</h1>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark bg-primary">
                    <tr>
                        <th>ID</th>
                        <th>Transaction Date</th>
                        <th>Party Name</th>
                        <th>Brand Name</th>
                        <th>Product Name</th>
                        <th>Product Rate</th>
                        <th>Product Quantity</th>
                        <th>Grand Total</th>
                        <th class="text-center">Function</th>
                    </tr>
                </thead>
                <tbody id="tBody">
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        // Fetch all data at once and store it in an associative array
                        $allRows = mysqli_fetch_all($result, MYSQLI_ASSOC);

                        // Iterate through each row using a foreach loop
                        foreach ($allRows as $row) { ?>
                            <tr data-party="<?php echo $row["party_name"] ?>">
                                <td><?php echo $row["id"] ?></td>
                                <td><?php echo $row["txn_date"] ?></td>
                                <td><?php echo $row["party_name"] ?></td>
                                <td><?php echo $row["brand_name"] ?></td>
                                <td><?php echo $row["product_name"] ?></td>
                                <td><?php echo $row["product_rate"] ?></td>
                                <td><?php echo $row["product_qty"] ?></td>
                                <td><?php echo $row["product_amount"] ?></td>
                                <td class='d-flex gap-2 p-4 pe-4'>
                                    <a target="_blank" class="btn col-6 btn-outline-primary"
                                        href="purchase_order_form.php?update_id=<?php echo $row['id']; ?>">UPDATE</a>
                                    <a class="col-6 btn btn-danger"
                                        href="purchase_order_table.php?delete_id=<?php echo $row['id']; ?>">DELETE</a>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td class='bg-danger text-light text-center fw-bold h1' colspan='9'>No results found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <?php
            // Delete functionality
            if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
                $delete_id = $_GET['delete_id'];
                $sql = "DELETE FROM purchaseorder WHERE id=$delete_id";
                if (mysqli_query($conn, $sql)) {
                    echo "<h5 class='d-inline-block p-2 text-center text-danger fw-bold border border-danger'>Record Deleted Successfully</h5>";
                    echo "<script>setTimeout(function() { window.location.href = 'purchase_order_table.php'; }, 3000);</script>"; // Refresh the page
                } else {
                    echo "Error deleting record: " . mysqli_error($conn);
                }
            }
            ?>
            <a class="btn btn-primary d-block" href="purchase_order_form.php">Go to Form</a>
        </div>
    </div>
    <script>

    </script>
    <?php mysqli_close($conn); ?>
</body>

</html>