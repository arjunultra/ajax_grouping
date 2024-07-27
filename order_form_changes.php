<?php
if (isset($_REQUEST['brand_name']) && isset($_REQUEST['product_name']) && isset($_REQUEST['product_rate']) && isset($_REQUEST['product_qty']) && isset($_REQUEST['product_amount']) && isset($_REQUEST['row_index'])) {
    $brand_name = $_REQUEST['brand_name'];
    $product_name = $_REQUEST['product_name'];
    $product_rate = $_REQUEST['product_rate'];
    $product_qty = $_REQUEST['product_qty'];
    $product_amount = $_REQUEST['product_amount'];
    $row_index = $_REQUEST['row_index'];

    if (!empty($brand_name) && !empty($product_name)) { ?>
        <tr class="data-row data-row<?php echo $row_index ?>" data-row-index="<?php echo $row_index ?>">
            <td><?php $brand_name ?>
                <input type="hidden" name="brand_name[]" value="<?php echo $brand_name ?>">
            </td>
            <td><?php echo $product_name ?>
                <input type="hidden" name="product_name[]" value="<?php echo $product_name ?>">
            </td>
            <td>
                <input class="w-75 product-rate" type="text" name="product_rate[]" value="<?php echo $product_rate ?>">
            </td>
            <td>
                <input class="w-75 product-qty" type="text" name="product_qty[]" value="<?php echo $product_qty ?>">
            </td>
            <td class="product-amount"><?php echo $product_amount ?>
                <input type="hidden" name="product_amount[]" value="<?php echo $product_amount ?>">
            </td>
            <td class="function">
                <input type="hidden" name="function">
                <button type="button" class="btn btn-outline-danger delete-btn"
                    onclick="DeleteRow(<?php echo $row_index; ?>)">Delete</button>
            </td>
        </tr>
    <?php }
}
?>