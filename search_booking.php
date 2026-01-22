<?php
include "db/connect.php";

$error = "";
$resultData = null;

if(isset($_POST['btnSearch'])){
    $booking_id = (int)$_POST['booking_id'];
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $get = mysqli_query($conn, "
        SELECT bookings.*, tickets.ticket_name, tickets.ticket_type
        FROM bookings
        INNER JOIN tickets ON bookings.ticket_id = tickets.id
        WHERE bookings.id='$booking_id'
        AND bookings.customer_email='$email'
        LIMIT 1
    ");

    if(mysqli_num_rows($get) > 0){
        $resultData = mysqli_fetch_assoc($get);
    } else {
        $error = "❌ Booking not found. Please check Booking ID and Email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Search Booking</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div style="max-width:1200px; margin:0 auto; padding:22px;">
  <a href="index.php" style="color:#00c6ff; font-weight:900; text-decoration:none;">
    ← Back to Home
  </a>

  <div class="form-box" style="max-width:750px;">
    <h2>🔎 Search Booking</h2>
    <p style="opacity:0.9; margin-top:8px;">Enter your Booking ID and Email to view your booking details.</p>

    <?php if($error != ""): ?>
      <div class="alert"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Booking ID</label>
        <input type="number" name="booking_id" placeholder="Example: 12" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="example@gmail.com" required>
      </div>

      <button class="btn" type="submit" name="btnSearch">Search</button>
    </form>

    <?php if($resultData != null): ?>
      <div class="success" style="margin-top:18px;">
        ✅ Booking Found! Status: <b><?= htmlspecialchars($resultData['status']); ?></b>
      </div>

      <div style="margin-top:14px; line-height:2; opacity:0.95;">
        <div><b>Booking ID:</b> #<?= $resultData['id']; ?></div>
        <div><b>Ticket:</b> <?= htmlspecialchars($resultData['ticket_name']); ?> (<?= htmlspecialchars($resultData['ticket_type']); ?>)</div>
        <div><b>Name:</b> <?= htmlspecialchars($resultData['customer_name']); ?></div>
        <div><b>Email:</b> <?= htmlspecialchars($resultData['customer_email']); ?></div>
        <div><b>Phone:</b> <?= htmlspecialchars($resultData['phone']); ?></div>
        <div><b>Visit Date:</b> <?= htmlspecialchars($resultData['visit_date']); ?></div>
        <div><b>Quantity:</b> <?= htmlspecialchars($resultData['quantity']); ?></div>
        <div><b>Total:</b> RM <?= number_format($resultData['total_price'],2); ?></div>
        <div><b>Created At:</b> <?= htmlspecialchars($resultData['created_at']); ?></div>
      </div>
    <?php endif; ?>

  </div>
</div>

</body>
</html>
