<?php
$text = $_GET['text'] ?? '';
$text = trim($text);

if($text == ''){
    http_response_code(400);
    exit('No text');
}

// ✅ QR provider (more stable than Google)
$qrUrl = "https://quickchart.io/qr?size=220&text=" . urlencode($text);

header("Content-Type: image/png");
echo file_get_contents($qrUrl);
