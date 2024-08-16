<?php require_once('header.php'); ?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php

if (!isset($_SESSION['customer'])) {
    header('location: login.php');
    exit;
}

if (isset($_POST['submit_payment'])) {
    $payment_method = $_POST['payment_method'];
    $final_total = $_POST['final_total'];
    $payment_date = $_POST['payment_date'];

    // Generate a unique payment ID
    $payment_id = uniqid();

    // Fetch product details from session
    $product_ids = $_SESSION['cart_p_id'];
    $product_names = $_SESSION['cart_p_name'];
    $quantities = $_SESSION['cart_p_qty'];
    $unit_prices = $_SESSION['cart_p_current_price'];
    
    // Insert payment details into tbl_payment
    $statement = $pdo->prepare("INSERT INTO tbl_payment (
        customer_id,
        customer_name,
        customer_email,
        payment_date,
        paid_amount,
        payment_method,
        payment_status,
        shipping_status,
        payment_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $payment_status = 'Success'; // Assuming payment is successful
    $shipping_status = 'Pending'; // Provide a default value for shipping_status

    // Execute the payment statement
    $statement->execute(array(
        $_SESSION['customer']['cust_id'],
        $_SESSION['customer']['cust_name'],
        $_SESSION['customer']['cust_email'],
        $payment_date,
        $final_total,
        $payment_method,
        $payment_status,
        $shipping_status,
        $payment_id
    ));

    // Loop through the products and insert each into tbl_order
    foreach ($product_ids as $index => $product_id) {
        $product_name = $product_names[$index];
        $quantity = $quantities[$index];
        $unit_price = $unit_prices[$index];
        
        // Insert the order into tbl_order
        $statement = $pdo->prepare("INSERT INTO tbl_order (
            product_id,
            product_name,
            size,
            color,
            quantity,
            unit_price,
            payment_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        // Assuming size and color are set; using placeholders if not
        $size = $_SESSION['cart_size_name'][$index] ?? 'N/A';
        $color = $_SESSION['cart_color_name'][$index] ?? 'N/A';
  
        $statement->execute(array(
            $product_id,
            $product_name,
            $size,
            $color,
            $quantity,
            $unit_price,
            $payment_id
        ));
    }

    // Clear cart session data
    unset($_SESSION['cart_p_id']);
    unset($_SESSION['cart_size_id']);
    unset($_SESSION['cart_size_name']);
    unset($_SESSION['cart_color_id']);
    unset($_SESSION['cart_color_name']);
    unset($_SESSION['cart_p_qty']);
    unset($_SESSION['cart_p_current_price']);
    unset($_SESSION['cart_p_name']);
    unset($_SESSION['cart_p_featured_photo']);

    // Redirect to payment success page
    header('location: payment_success.php');
    exit;
}
?>
