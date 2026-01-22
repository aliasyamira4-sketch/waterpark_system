<?php
include "db/connect.php";

$id = $_GET['id'] ?? "";
if($id==""){ header("Location:index.php"); exit(); }
$id = (int)$id;

$get = mysqli_query($conn, "
    SELECT b.*, t.ticket_name, t.ticket_type
    FROM bookings b
    LEFT JOIN tickets t ON b.ticket_id=t.id
    WHERE b.id='$id' LIMIT 1
");

if(!$get) die("SQL Error: ".mysqli_error($conn));
if(mysqli_num_rows($get)==0) die("Booking not found");

$b = mysqli_fetch_assoc($get);

$payment_method = $b['payment_method'] ?? '-';
$payment_status = $b['payment_status'] ?? 'Unpaid';

// ✅ QR content (for gate scan)
$qrText = "WATERPARK|BOOKING_ID=".$b['id'].
          "|NAME=".$b['customer_name'].
          "|DATE=".$b['visit_date'].
          "|QTY=".$b['quantity'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Receipt</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.receipt-card{
  max-width:720px;margin:40px auto;padding:22px;border-radius:18px;
  background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);
}
.row{display:flex;justify-content:space-between;gap:20px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.10);flex-wrap:wrap;}
.badge{
  display:inline-block;padding:6px 12px;border-radius:999px;
  background:rgba(0,198,255,0.15);
  border:1px solid rgba(0,198,255,0.25);
  font-weight:800;font-size:12px;
}
.qrbox{
  margin-top:18px;text-align:center;padding:16px;border-radius:16px;
  background:rgba(0,198,255,0.10);
  border:1px solid rgba(0,198,255,0.22);
}
@media print{
  body{background:white !important;color:black !important;}
  .receipt-card{background:white !important;border:1px solid #ddd !important;}
  .btn,.print-hide{display:none !important;}
}
</style>
</head>
<body>

<div style="max-width:1200px;margin:0 auto;padding:22px;">

  <div class="receipt-card">

    <h2 style="text-align:center;margin:0;">🎟️ Waterpark Receipt</h2>
    <p style="text-align:center;opacity:.85;margin-top:8px;">
      Show QR Code at the entrance.
    </p>

    <div style="text-align:center;margin:12px 0;" class="print-hide">
      <span class="badge">
        Payment: <?= strtoupper(htmlspecialchars($payment_method)); ?> |
        Status: <?= strtoupper(htmlspecialchars($payment_status)); ?>
      </span>
    </div>

    <?php if($payment_status != "Paid"): ?>
      <div class="alert" style="margin-top:14px;">
        ❌ Payment not completed yet. Please pay first before printing receipt.
      </div>

      <a href="online_payment.php?id=<?= $b['id']; ?>" class="btn"
         style="margin-top:14px;text-decoration:none;display:inline-block;text-align:center;">
        Pay Now 💳
      </a>

    <?php else: ?>

      <div style="margin-top:18px;">
        <div class="row"><b>Booking ID</b><span>#<?= $b['id']; ?></span></div>
        <div class="row"><b>Ticket</b><span><?= htmlspecialchars($b['ticket_name']); ?></span></div>
        <div class="row"><b>Type</b><span><?= htmlspecialchars($b['ticket_type']); ?></span></div>
        <div class="row"><b>Name</b><span><?= htmlspecialchars($b['customer_name']); ?></span></div>
        <div class="row"><b>Email</b><span><?= htmlspecialchars($b['customer_email']); ?></span></div>
        <div class="row"><b>Phone</b><span><?= htmlspecialchars($b['phone']); ?></span></div>
        <div class="row"><b>Visit Date</b><span><?= htmlspecialchars($b['visit_date']); ?></span></div>
        <div class="row"><b>Quantity</b><span><?= htmlspecialchars($b['quantity']); ?></span></div>
        <div class="row"><b>Total</b><span>RM <?= number_format($b['total_price'],2); ?></span></div>
      </div>

      <!-- ✅ QR Code -->
      <div class="qrbox">
        <h3 style="margin:0 0 10px;">✅ Entry QR Code</h3>

        <!-- ✅ QR generated locally -->
        <img src="qrcode.php?text=<?= urlencode($qrText); ?>" 
             alt="QR Code" style="width:220px; height:220px;">

        <p style="margin-top:10px; opacity:0.85; font-size:13px;">
          Please scan this QR code at the gate.
        </p>
      </div>

      <!-- ✅ Uploaded Receipt Preview -->
      <?php if(!empty($b['receipt_file'])): ?>
        <div class="qrbox" style="margin-top:14px;">
          <h3 style="margin:0 0 10px;">📄 Uploaded Receipt</h3>

          <?php
            $file = "uploads/receipts/" . $b['receipt_file'];
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
          ?>

          <?php if(in_array($ext, ['jpg','jpeg','png'])): ?>
            <img src="<?= $file; ?>" alt="Receipt" style="width:100%; max-width:420px; border-radius:14px;">
          <?php else: ?>
            <a class="wp-select-btn" href="<?= $file; ?>" target="_blank">
              View Receipt PDF
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:18px;" class="print-hide">
        <a href="index.php" class="btn" style="flex:1;text-decoration:none;text-align:center;">
          Back to Home
        </a>

        <button onclick="window.print()" class="btn"
                style="flex:1;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.18);color:white;">
          Print Receipt 🖨️
        </button>
      </div>

    <?php endif; ?>

  </div>

</div>

</body>
</html>
