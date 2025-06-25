<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_now'])) {
    require('../inc/connect.php');
    session_start();

    $user_id = $_SESSION['User_ID'];
    $item_id = $_POST['item_id'];
    $quantity = (int)$_POST['quantity'];

    // Get item price
    $stmt = $conn->prepare("SELECT Price FROM item WHERE Item_ID = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $priceResult = $stmt->get_result()->fetch_assoc();
    $price = $priceResult['Price'];
    $total_price = $price * $quantity;

    // Check for existing pending order
    $order_check = $conn->prepare("SELECT Order_ID FROM ordertable WHERE User_ID = ? AND Status = 'Pending' LIMIT 1");
    $order_check->bind_param("i", $user_id);
    $order_check->execute();
    $order_result = $order_check->get_result()->fetch_assoc();

    if ($order_result) {
        $order_id = $order_result['Order_ID'];
    } else {
        // Create new order
        $new_order = $conn->prepare("INSERT INTO ordertable (User_ID, Status) VALUES (?, 'Pending')");
        $new_order->bind_param("i", $user_id);
        $new_order->execute();
        $order_id = $conn->insert_id;
    }

    // Insert into item_order
    $insert = $conn->prepare("INSERT INTO item_order (Item_ID, Order_ID, Quantity, Total_Price, Status) VALUES (?, ?, ?, ?, 'Pending')");
    $insert->bind_param("iiid", $item_id, $order_id, $quantity, $total_price);
    $insert->execute();

    // Redirect to WhatsApp
    header("Location: https://wa.me/$phone?text=" . urlencode("I'm interested in this item: http://yourdomain/product/page5.php?id=$item_id"));
    exit();
}
?>