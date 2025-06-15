<?php
$servername = "localhost";
$username = "mirol";
$password = "1234";
$dbname = "utemhubdb";

//use pdo if database it no mysql
$conn = new mysqli($servername, $username, $password, $dbname);

if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully 1";

$db2 = "student_staff_list";

$conn2 = new mysqli($servername, $username, $password, $db2);
if ($conn2->connect_error) {
    die("Connection to DB2 failed: " . $conn2->connect_error);
}
// echo "Connected successfully 2";
?>