<?php
session_start();
include "db/connect.php";

// pastikan user login
if (!isset($_SESSION['name'])) {
    header("Location: index.php");
    exit();
}

// booking_id dari URL
$booking_id = $_GET['id'] ?? "";

if ($booking_id == "") {
    header("Location: dashboard.php");
    exit();
}

// ambil detail booking
$stmt = $conn->prepare("
    SELECT b.*, v.vehicle_name
    FROM bookings b
    LEFT JOIN vehicles v ON b.vehicle_id = v.id
    WHERE b.id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$b = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Confirm Booking</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <style>
        body{ background:#f5f6fa; }
        .box{
            width: 450px;
            max-width: 95%;
            margin: 70px auto;
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .box h2{ text-align:center; margin-bottom: 10px; }
        .details{
            margin-top: 15px;
            font-size: 14px;
        }
        .details div{
            display:flex;
            justify-content:space-between;
            padding: 8px 0;
            border-bottom:1px solid #eee;
        }
        .paybox{
            margin-top: 20px;
            padding: 15px;
            background:#f8f9ff;
            border-radius: 12px;
        }
        .paybox label{
            display:block;
            padding: 10px;
            border-radius: 10px;
            background:white;
            margin-bottom:10px;
            cursor:pointer;
            border:1px solid #eee;
        }
        .btn{
            width:100%;
            padding: 12px;
            border:none;
            border-radius: 12px;
            background:#2d36ff;
            color:white;
            font-weight:bold;
            cursor:pointer;
            margin-top: 15px;
        }
        .back{
            display:block;
            text-align:center;
            margin-top: 12px;
            text-decoration:none;
            color:#555;
            font-size:14px;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Confirm Booking</h2>
    <p style="text-align:center; color:#666;">Please confirm your booking & select payment method</p>

    <div class="details">
        <div><b>Booking ID</b><span>#<?= $b['id'] ?></span></div>
        <div><b>Vehicle</b><span><?= htmlspecialchars($b['vehicle_name'] ?? '-') ?></span></div>
        <div><b>Pickup Date</b><span><?= $b['pickup_date'] ?? '-' ?></span></div>
        <div><b>Return Date</b><span><?= $b['return_date'] ?? '-' ?></span></div>
        <div><b>Total Price</b><span>RM <?= $b['total_price'] ?? '0' ?></span></div>
    </div>

    <form method="POST" action="confirm_booking_process.php">
        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">

        <div class="paybox">
            <h3 style="margin:0 0 10px;">Choose Payment Method</h3>

            <label>
                <input type="radio" name="payment_method" value="f2f" required>
                Face to Face (Pay when pickup)
            </label>

            <label>
                <input type="radio" name="payment_method" value="online" required>
                Online Payment (FPX / QR / Transfer)
            </label>
        </div>

        <button type="submit" class="btn">Confirm Booking</button>
    </form>

    <a href="dashboard.php" class="back">← Back to Dashboard</a>
</div>

</body>
</html>
