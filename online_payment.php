<?php
include "db/connect.php";

if(!isset($_GET['id'])){
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];

$get = mysqli_query($conn, "
    SELECT b.*, t.ticket_name, t.ticket_type
    FROM bookings b
    LEFT JOIN tickets t ON b.ticket_id = t.id
    WHERE b.id='$id'
    LIMIT 1
");

if(!$get) die("SQL Error: " . mysqli_error($conn));
if(mysqli_num_rows($get)==0) die("Booking not found!");
$b = mysqli_fetch_assoc($get);

$error = "";

if(isset($_POST['btnPay'])){
    $method = $_POST['payment_method'] ?? "";

    if($method == ""){
        $error = "❌ Please choose a payment method.";
    } else {

        // ✅ Receipt upload
        $receiptFileName = null;

        if(isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0){

            $allowed = ['jpg','jpeg','png','pdf'];
            $fileExt = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));

            if(!in_array($fileExt, $allowed)){
                $error = "❌ Receipt must be JPG, PNG or PDF only.";
            } else {

                // ✅ new file name
                $receiptFileName = "receipt_" . $id . "_" . time() . "." . $fileExt;
                $uploadPath = "uploads/receipts/" . $receiptFileName;

                if(!move_uploaded_file($_FILES['receipt']['tmp_name'], $uploadPath)){
                    $error = "❌ Failed to upload receipt. Try again.";
                }
            }
        } else {
            $error = "❌ Please upload your payment receipt.";
        }

        // ✅ if upload ok, update booking
        if($error == ""){
            $safeMethod = mysqli_real_escape_string($conn, $method);
            $safeFile   = mysqli_real_escape_string($conn, $receiptFileName);

            mysqli_query($conn, "
                UPDATE bookings SET
                  payment_method='$safeMethod',
                  payment_status='Paid',
                  status='Approved',
                  receipt_file='$safeFile'
                WHERE id='$id'
            ");

            header("Location: booking_success.php?id=$id");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Payment</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.pay-grid{
  display:grid;
  grid-template-columns:repeat(3,1fr);
  gap:14px;
  margin-top:14px;
}
.pay-card{
  display:flex;
  gap:12px;
  align-items:center;
  padding:14px;
  border-radius:16px;
  cursor:pointer;
  background:rgba(255,255,255,0.08);
  border:1px solid rgba(255,255,255,0.14);
  transition:.2s;
}
.pay-card:hover{ background:rgba(255,255,255,0.12); }
.pay-card input{ transform:scale(1.25); }
.pay-icon{ font-size:28px; }
.pay-title{ font-weight:900; font-size:14px; }
.pay-sub{ opacity:.85; font-size:12px; margin-top:2px; }

@media(max-width:900px){
  .pay-grid{ grid-template-columns:1fr; }
}

/* ✅ coming soon design */
.coming-soon{
  opacity:0.55;
  position:relative;
}
.coming-soon::after{
  content:"COMING SOON";
  position:absolute;
  top:10px;
  right:12px;
  font-size:11px;
  font-weight:900;
  padding:4px 10px;
  border-radius:999px;
  background:rgba(255,255,255,0.14);
  border:1px solid rgba(255,255,255,0.25);
}
</style>
</head>
<body>

<div style="max-width:1200px;margin:0 auto;padding:22px;">
  <a href="index.php" style="color:#00c6ff;font-weight:900;text-decoration:none;">← Back to Home</a>

  <div class="form-box" style="max-width:850px;">
    <h2>💳 Payment</h2>
    <p style="opacity:.9;margin-top:10px;">
      Upload your payment receipt. After payment you will receive QR Code.
    </p>

    <?php if($error!=""): ?>
      <div class="alert"><?= $error; ?></div>
    <?php endif; ?>

    <div style="margin-top:18px;padding:16px;border-radius:16px;background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.14);">
      <b>Booking #<?= $b['id']; ?></b><br>
      Ticket: <?= htmlspecialchars($b['ticket_name']); ?> (<?= htmlspecialchars($b['ticket_type']); ?>)<br>
      Total: <b>RM <?= number_format($b['total_price'],2); ?></b>
    </div>

    <!-- ✅ enctype for upload -->
    <form method="POST" enctype="multipart/form-data" style="margin-top:18px;">
      <h3 style="font-size:18px;font-weight:900;">Choose Payment Method</h3>

      <!-- ✅ ONLY 1 pay-grid -->
      <div class="pay-grid">

        <!-- FPX Coming Soon -->
        <label class="pay-card coming-soon" onclick="comingSoon(event)">
          <input type="radio" name="payment_method" value="FPX Online Banking" disabled>
          <div class="pay-icon">🏦</div>
          <div>
            <div class="pay-title">FPX Online Banking</div>
            <div class="pay-sub">Coming Soon</div>
          </div>
        </label>

        <!-- Card Coming Soon -->
        <label class="pay-card coming-soon" onclick="comingSoon(event)">
          <input type="radio" name="payment_method" value="Debit/Credit Card" disabled>
          <div class="pay-icon">💳</div>
          <div>
            <div class="pay-title">Debit / Credit Card</div>
            <div class="pay-sub">Coming Soon</div>
          </div>
        </label>

        <!-- ✅ DuitNow Available -->
        <label class="pay-card">
          <input type="radio" name="payment_method" value="DuitNow QR" required>
          <div class="pay-icon">📱</div>
          <div>
            <div class="pay-title">DuitNow QR</div>
            <div class="pay-sub">Available</div>
          </div>
        </label>

      </div>

      <!-- ✅ Upload receipt -->
      <div class="form-group" style="margin-top:18px;">
        <label>Upload Payment Receipt (JPG/PNG/PDF)</label>
        <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" required>
      </div>

      <button class="btn" type="submit" name="btnPay" style="margin-top:12px;">
        Confirm Payment ✅
      </button>
    </form>

  </div>
</div>

<script>
function comingSoon(e){
  e.preventDefault();
  alert("⚠️ Coming Soon! FPX and Card payment will be available in the next update.");
}
</script>

</body>
</html>
