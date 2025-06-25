<?php
session_start();
require('../inc/connect.php');

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$item_id = intval($_GET['id']);

$delete = $conn->prepare("DELETE FROM item WHERE Item_ID = ?");
$delete->bind_param("i", $item_id);

if ($delete->execute()) {
    echo "Item deleted successfully.";
    header("Refresh:2; url=../profile/account.php");
} else {
    echo "Error deleting item: " . $conn->error;
}
?>
