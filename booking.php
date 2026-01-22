<?php
include "db/connect.php";

if(!isset($_GET['ticket_id'])){
    header("Location: index.php");
    exit();
}

$ticket_id = (int)$_GET['ticket_id'];
$selectedDate = $_GET['date'] ?? "";

// get ticket info
$getTicket = mysqli_query($conn, "SELECT * FROM tickets WHERE id='$ticket_id' LIMIT 1");
if(mysqli_num_rows($getTicket) == 0){
    header("Location: index.php");
    exit();
}

$ticket = mysqli_fetch_assoc($getTicket);
$unitPrice = (float)$ticket['price'];

$error = "";

if(isset($_POST['btnBook'])){

    $name  = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $email = mysqli_real_escape_string($conn, $_POST['customer_email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $visit_date = $_POST['visit_date'];
    $quantity = (int)$_POST['quantity'];

    if($quantity <= 0){
        $error = "❌ Quantity must be at least 1.";
    } else if($visit_date < date("Y-m-d")){
        $error = "❌ Visit date cannot be in the past.";
    } else {

        $total = $unitPrice * $quantity;

        // ✅ INSERT booking first as Pending + Unpaid
        $insert = mysqli_query($conn, "
            INSERT INTO bookings(ticket_id, customer_name, customer_email, phone, visit_date, quantity, total_price, status, payment_status)
            VALUES('$ticket_id', '$name', '$email', '$phone', '$visit_date', '$quantity', '$total', 'Pending', 'Unpaid')
        ");

        if($insert){
            $booking_id = mysqli_insert_id($conn);

            // ✅ redirect to payment first
            header("Location: online_payment.php?id=$booking_id");
            exit();
        } else {
            $error = "❌ Booking failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Booking - Waterpark</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div style="max-width:1200px; margin:0 auto; padding:22px;">

  <a href="index.php?date=<?= urlencode($selectedDate); ?>" style="color:#00c6ff; font-weight:900; text-decoration:none;">
    ← Back to Ticket Page
  </a>

  <div class="form-box">
    <h2>🎟️ Booking Ticket</h2>

    <p style="opacity:0.9; margin-top:10px; line-height:1.7;">
      Ticket: <b><?= htmlspecialchars($ticket['ticket_name']); ?></b><br>
      Unit Price: <b>RM <?= number_format($unitPrice,2); ?></b>
    </p>

    <?php if($error != ""): ?>
      <div class="alert"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">

      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="customer_name" placeholder="Enter your full name" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="customer_email" placeholder="example@gmail.com" required>
      </div>

      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone" placeholder="01XXXXXXXX" required>
      </div>

      <div class="form-group">
        <label>Visit Date</label>
        <input type="date" name="visit_date"
               min="<?= date('Y-m-d'); ?>"
               value="<?= htmlspecialchars($selectedDate); ?>"
               required>
      </div>

      <div class="form-group">
        <label>Quantity</label>
        <div class="qty-box">
          <button type="button" class="qty-btn" onclick="minusQty()">−</button>

          <input type="number" id="qty" class="qty-input"
                 name="quantity" min="1" value="1" required>

          <button type="button" class="qty-btn" onclick="plusQty()">+</button>
        </div>
      </div>

      <div class="total-box">
        <b>Total Price:</b> RM <span id="totalPrice"><?= number_format($unitPrice, 2); ?></span>
      </div>

      <button class="btn" type="submit" name="btnBook">
        Continue to Payment 💳
      </button>
    </form>
  </div>

</div>

<script>
let unitPrice = <?= $unitPrice; ?>;

function updateTotal(){
    let qty = parseInt(document.getElementById("qty").value) || 1;
    if(qty < 1) qty = 1;
    document.getElementById("qty").value = qty;

    let total = unitPrice * qty;
    document.getElementById("totalPrice").innerText = total.toFixed(2);
}

function plusQty(){
    let qty = parseInt(document.getElementById("qty").value) || 1;
    document.getElementById("qty").value = qty + 1;
    updateTotal();
}

function minusQty(){
    let qty = parseInt(document.getElementById("qty").value) || 1;
    if(qty > 1) document.getElementById("qty").value = qty - 1;
    updateTotal();
}

document.getElementById("qty").addEventListener("input", updateTotal);
updateTotal();
</script>

</body>
</html>
