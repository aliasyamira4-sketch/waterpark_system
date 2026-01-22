<?php
include "db/connect.php";
$date = $_GET['date'] ?? "";
$tickets = mysqli_query($conn, "SELECT * FROM tickets ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Waterpark Booking</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div style="max-width:1200px; margin:0 auto; padding:22px;">

  <div style="font-weight:900; font-size:18px;">🌊 Adventure Waterpark Booking</div>

  <div style="margin-top:18px; font-size:14px; opacity:0.9;">Home  >  One Day Ticket</div>
  <div style="margin-top:8px; opacity:0.85;">Adventure Waterpark Desaru Coast</div>
  <div style="font-size:44px; font-weight:900; margin-top:6px;">One Day Ticket</div>

  <!-- Gallery -->
  <div class="wp-gallery" style="margin-top:18px;">
    <div class="wp-gallery-main">
      <button class="wp-arrow left" onclick="prevImg()">‹</button>
      <button class="wp-arrow right" onclick="nextImg()">›</button>

      <img class="wp-main-img active" src="assets/img/g1.jpg">
      <img class="wp-main-img" src="assets/img/g2.jpg">
      <img class="wp-main-img" src="assets/img/g3.jpg">
      <img class="wp-main-img" src="assets/img/g4.jpg">
      <img class="wp-main-img" src="assets/img/g5.jpg">
    </div>

    <div class="wp-thumbs">
      <div class="wp-thumb active" onclick="setImg(0)"><img src="assets/img/g1.jpg"></div>
      <div class="wp-thumb" onclick="setImg(1)"><img src="assets/img/g2.jpg"></div>
      <div class="wp-thumb" onclick="setImg(2)"><img src="assets/img/g3.jpg"></div>
      <div class="wp-thumb" onclick="setImg(3)"><img src="assets/img/g4.jpg"></div>
      <div class="wp-thumb" onclick="setImg(4)"><img src="assets/img/g5.jpg"></div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="wp-tabs">
    <button class="wp-tab-btn active" onclick="openWpTab(event,'desc')">Description</button>
    <button class="wp-tab-btn" onclick="openWpTab(event,'hours')">Operating Hours</button>
    <button class="wp-tab-btn" onclick="openWpTab(event,'loc')">Location</button>
  </div>

  <div class="wp-tab-panel show" id="desc">
    <h3 style="font-size:26px; font-weight:900; margin-bottom:10px;">Highlights</h3>
    <ul style="padding-left:22px; line-height:1.9;">
      <li>Ride the waves in our massive wave pool.</li>
      <li>Over 20 rides and slides for all ages.</li>
      <li>Family-friendly areas and kids zone.</li>
    </ul>
  </div>

  <div class="wp-tab-panel" id="hours">
    <h3 style="font-size:26px; font-weight:900; margin-bottom:10px;">Operating Hours</h3>
    <p>Daily: <b>10:00 AM - 6:00 PM</b></p>
  </div>

  <div class="wp-tab-panel" id="loc">
  <h3 style="font-size:26px; font-weight:900; margin-bottom:10px;">Location</h3>
  <p style="opacity:0.92; line-height:1.8;">
    <b>Adventure Waterpark, Desaru Coast</b><br>
    Bandar Penawar, Johor, Malaysia
  </p>

  <!-- ✅ Google Map -->
  <div class="map-box">
    <iframe
      src="https://www.google.com/maps?q=Adventure%20Waterpark%20Desaru%20Coast&output=embed"
      width="100%" height="300"
      style="border:0; border-radius:16px;"
      allowfullscreen=""
      loading="lazy">
    </iframe>
  </div>
</div>


  <!-- Check Availability -->
  <div class="wp-section-title">📅 Check Availability</div>
  <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:14px;">
    <a class="wp-tab-btn" style="min-width:160px; text-align:center;"
       href="index.php?date=<?= date('Y-m-d'); ?>">Today</a>

    <a class="wp-tab-btn" style="min-width:160px; text-align:center;"
       href="index.php?date=<?= date('Y-m-d', strtotime('+1 day')); ?>">Tomorrow</a>

    <button class="wp-tab-btn" style="min-width:190px;"
      onclick="document.getElementById('pickDate').showPicker();">📆 Select Date</button>

    <form id="dateForm" method="GET">
      <input type="date" id="pickDate" name="date" min="<?= date('Y-m-d'); ?>"
             style="opacity:0; width:0; height:0; position:absolute;"
             onchange="document.getElementById('dateForm').submit();">
    </form>
  </div>

  <?php if($date!=""): ?>
    <div style="margin-top:10px; opacity:0.92;">✅ Selected Date: <b><?= htmlspecialchars($date); ?></b></div>
  <?php endif; ?>

  <!-- Ticket Options -->
  <div class="wp-section-title">🎟️ Ticket Options</div>

  <div class="wp-ticket-list">
    <?php while($t = mysqli_fetch_assoc($tickets)): ?>
      <div class="wp-ticket-card">
        <div style="flex:1;">
          <h3><?= htmlspecialchars($t['ticket_name']); ?></h3>
          <p><?= htmlspecialchars($t['ticket_type']); ?></p>
          <details class="wp-details">
  <summary>View Details & Inclusions</summary>
  <div class="wp-details-body">
    <?= nl2br(htmlspecialchars($t['description'])); ?><br><br>
    ✅ Access to rides & slides<br>
    ✅ Wave pool and kids zone included<br>
    ✅ Safety equipment provided (selected rides)<br>
    ✅ Family-friendly environment
  </div>
</details>

        </div>

        <div class="wp-ticket-right">
          <div class="wp-ticket-price">From <b>RM <?= number_format($t['price'],2); ?></b></div>
          <a class="wp-select-btn"
             href="booking.php?ticket_id=<?= $t['id']; ?>&date=<?= urlencode($date); ?>">
             Select
          </a>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

</div>

<script>
let currentIndex=0;
function showImg(index){
  const imgs=document.querySelectorAll(".wp-main-img");
  const thumbs=document.querySelectorAll(".wp-thumb");
  imgs.forEach(i=>i.classList.remove("active"));
  thumbs.forEach(t=>t.classList.remove("active"));
  imgs[index].classList.add("active");
  thumbs[index].classList.add("active");
  currentIndex=index;
}
function prevImg(){ const imgs=document.querySelectorAll(".wp-main-img"); currentIndex=(currentIndex-1+imgs.length)%imgs.length; showImg(currentIndex); }
function nextImg(){ const imgs=document.querySelectorAll(".wp-main-img"); currentIndex=(currentIndex+1)%imgs.length; showImg(currentIndex); }
function setImg(i){ showImg(i); }

function openWpTab(evt, tabId){
  document.querySelectorAll(".wp-tab-panel").forEach(p=>p.classList.remove("show"));
  document.querySelectorAll(".wp-tab-btn").forEach(b=>b.classList.remove("active"));
  document.getElementById(tabId).classList.add("show");
  evt.currentTarget.classList.add("active");
}
</script>

</body>
</html>
