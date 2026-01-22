<?php
session_start();
include "db/connect.php";

$booking_id = $_POST['booking_id'];
$payment_method = $_POST['payment_method'];

if ($payment_method == "f2f") {
    $payment_status = "unpaid";
} else {
    $payment_status = "pending";
}

$sql = "UPDATE bookings 
        SET payment_method=?, payment_status=?, booking_status='confirmed'
        WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $payment_method, $payment_status, $booking_id);
$stmt->execute();

if ($payment_method == "f2f") {
    header("Location: booking_success.php?id=$booking_id");
} else {
    header("Location: online_payment.php?id=$booking_id");
}
exit();
