<?php
$open_connect = 1;
require('connect.php');

// QR ส่ง place_key ตามลำดับที่ผู้ใช้จัดไว้ เช่น ?places=A,B,C
$raw_places = $_GET['places'] ?? '';
$place_keys = [];
foreach(explode(',', $raw_places) as $key){
    $key = trim($key);
    if($key !== '' && !in_array($key, $place_keys, true)) $place_keys[] = $key;
}

$places = [];
if(count($place_keys) > 0){
    $place_keys = array_slice($place_keys, 0, 50);
    $placeholders = implode(',', array_fill(0, count($place_keys), '?'));
    $types = str_repeat('s', count($place_keys));

    $stmt = mysqli_prepare($connect,
        "SELECT place_key, name_place, location_place, lat_place, lng_place, image_place
         FROM place WHERE place_key IN ($placeholders)"
    );
    mysqli_stmt_bind_param($stmt, $types, ...$place_keys);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $byKey = [];
    while($row = mysqli_fetch_assoc($result)) $byKey[$row['place_key']] = $row;
    mysqli_stmt_close($stmt);

    // เรียงกลับตามลำดับที่ติดมากับ QR
    foreach($place_keys as $key){
        if(isset($byKey[$key])) $places[] = $byKey[$key];
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>ทริปเที่ยวตาก</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--ink:#1d231d;--soft:#596258;--cream:#f8f7f2;--green:#24593f;--deep:#163a28;--line:rgba(29,35,29,.11)}
*{box-sizing:border-box} body{margin:0;background:var(--cream);color:var(--ink);font-family:'Prompt',sans-serif}
.page{width:min(680px,100%);margin:auto;padding:0 1rem 1.5rem}.hero{background:var(--green);color:#fff;border-radius:0 0 24px 24px;padding:1.35rem 1.15rem 1.2rem;margin:0 -1rem 1rem}.hero h1{margin:0;font-size:1.4rem}.hero p{margin:.25rem 0 0;font-size:.76rem;opacity:.88}.count{display:inline-flex;margin-top:.75rem;background:rgba(255,255,255,.14);border-radius:999px;padding:.3rem .7rem;font-size:.7rem}
.trip-card{display:flex;gap:.8rem;align-items:center;background:#fff;border:1px solid var(--line);border-radius:17px;padding:.75rem;margin-bottom:.7rem;box-shadow:0 2px 7px rgba(29,35,29,.05)}
.number{flex:none;width:2.2rem;height:2.2rem;border-radius:50%;background:var(--green);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700}.place-info{min-width:0;flex:1}.place-info h2{margin:0;font-size:.9rem}.place-info p{margin:.12rem 0 0;color:var(--soft);font-size:.68rem}.map-btn{flex:none;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;background:var(--green);color:#fff;border-radius:999px;padding:.52rem .72rem;font-size:.67rem;font-weight:600}.map-btn:active{background:var(--deep)}
.empty,.error{background:#fff;border-radius:18px;padding:1.4rem;text-align:center;color:var(--soft);font-size:.8rem}.footer{text-align:center;color:#7a8278;font-size:.65rem;padding:1rem 0}.hint{background:#eef1e9;border-radius:16px;padding:.8rem 1rem;margin-bottom:.8rem;font-size:.68rem;color:var(--soft)}
@media(max-width:420px){.page{padding:0 .75rem 1.5rem}.hero{margin:0 -.75rem 1rem}.trip-card{gap:.55rem}.map-btn{padding:.48rem .58rem;font-size:.62rem}}
</style>
</head>
<body><main class="page">
<section class="hero"><h1>ทริปเที่ยวตาก</h1><p>ลำดับสถานที่จาก QR Code</p><span class="count"><?php echo count($places); ?> สถานที่</span></section>
<?php if(count($places) === 0): ?>
<div class="error"><h3>ไม่พบสถานที่ในทริป</h3><p>QR นี้อาจเก่าหรือไม่มีรายการสถานที่แล้ว</p></div>
<?php else: ?>
<div class="hint">แตะปุ่ม <b>ไป Google Maps</b> ของสถานที่ที่ต้องการ เพื่อเปิดตำแหน่งบนมือถือ</div>
<?php foreach($places as $index=>$place):
    $lat=(float)$place['lat_place']; $lng=(float)$place['lng_place'];
    $google_url='https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($lat.','.$lng);
?>
<article class="trip-card"><div class="number"><?php echo $index+1; ?></div><div class="place-info"><h2><?php echo htmlspecialchars($place['name_place']); ?></h2><p><?php echo htmlspecialchars($place['location_place']); ?></p></div><a class="map-btn" href="<?php echo htmlspecialchars($google_url); ?>" target="_blank" rel="noopener noreferrer">🗺️ ไป Google Maps</a></article>
<?php endforeach; ?>
<?php endif; ?><div class="footer">เที่ยวตาก · TAK EXPLORE</div></main></body></html>
