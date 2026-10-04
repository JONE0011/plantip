<?php
session_start(); $open_connect=1; require('connect.php');
if(!isset($_SESSION['id_account'])){header('Location: form-login.php');exit;}
$id=(int)($_GET['id']??0); $uid=(int)$_SESSION['id_account'];
$q=mysqli_query($connect,"SELECT * FROM place WHERE id_place=$id AND id_account=$uid LIMIT 1");
$place=$q?mysqli_fetch_assoc($q):null;
if(!$place){http_response_code(403);die('คุณไม่มีสิทธิ์แก้ไขสถานที่นี้');}
$image=$place['image_place']??'';
?>
<!doctype html><html lang="th"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>แก้ไขสถานที่ | TAK EXPLORE</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="modern.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<style>
body{margin:0;background:#faf9f5;color:#18231e;font-family:Prompt,sans-serif}.wrap{width:min(1180px,calc(100% - 30px));margin:auto}.nav{height:78px;border-bottom:1px solid #ddd;background:rgba(250,249,245,.92);backdrop-filter:blur(18px);display:flex;align-items:center}.logo{font:600 24px "Playfair Display";color:#183d31;text-decoration:none}.hero{padding:75px 0 35px;background:linear-gradient(180deg,#edf1e9,#faf9f5)}.ey{font-size:10px;letter-spacing:.25em;color:#7d8780}.hero h1{font:600 58px "Playfair Display";margin:12px 0}.hero p{color:#68736c}.form{display:grid;grid-template-columns:1fr 1fr;gap:20px;padding:30px 0 80px}.panel{background:#fff;border:1px solid rgba(24,35,30,.12);border-radius:24px;padding:28px}.field{margin-bottom:16px}.field label{display:block;font-size:12px;color:#68736c;margin-bottom:6px}.field input,.field textarea{width:100%;box-sizing:border-box;padding:11px 13px;border:1px solid #d8ded8;border-radius:11px;font:14px Prompt}.field textarea{min-height:100px;resize:vertical}.current{font-size:11px;color:#68736c;margin-top:7px}.current img{display:block;width:150px;height:100px;object-fit:cover;border-radius:12px;margin-top:8px}#map{height:390px;border-radius:18px;overflow:hidden}.row{display:grid;grid-template-columns:1fr 1fr;gap:10px}.btn{width:100%;border:0;background:#183d31;color:#fff;padding:14px;border-radius:999px;font:600 14px Prompt;cursor:pointer}.back{display:inline-block;margin-top:12px;color:#68736c;font-size:12px}@media(max-width:800px){.form{grid-template-columns:1fr}.hero h1{font-size:45px}}
</style></head><body>
<nav class="nav"><div class="wrap"><a class="logo" href="places.php">เที่ยว ตาก · TAK EXPLORE</a></div></nav>
<header class="hero"><div class="wrap"><div class="ey">MY CONTRIBUTION · EDIT</div><h1>แก้ไขสถานที่</h1><p>แก้ไขได้เฉพาะสถานที่ที่คุณเป็นคนเพิ่มเข้าระบบ</p></div></header>
<main class="wrap"><form class="form" method="POST" action="process-edit-place.php" enctype="multipart/form-data">
<input type="hidden" name="id_place" value="<?php echo $id; ?>">
<section class="panel"><h2>ข้อมูลสถานที่</h2>
<div class="field"><label>ชื่อสถานที่</label><input name="name_place" value="<?php echo htmlspecialchars($place['name_place']); ?>" required></div>
<div class="field"><label>ที่ตั้ง</label><input name="location_place" value="<?php echo htmlspecialchars($place['location_place']); ?>" required></div>
<div class="field"><label>หมวดหมู่</label><input name="category_place" value="<?php echo htmlspecialchars($place['category_place']??''); ?>"></div>
<div class="field"><label>รายละเอียด</label><textarea name="description_place"><?php echo htmlspecialchars($place['description_place']??''); ?></textarea></div>
<div class="field"><label>เปลี่ยนรูปสถานที่</label><input type="file" name="image_place" accept="image/jpeg,image/png"><div class="current">รูปปัจจุบัน:<?php if($image): ?><img src="<?php echo htmlspecialchars($image); ?>" alt=""><?php else: ?> ไม่มีรูป<?php endif; ?></div></div>
</section>
<section class="panel"><h2>ตำแหน่ง</h2><p style="font-size:11px;color:#68736c">คลิกแผนที่หรือเลื่อนหมุดเพื่อเปลี่ยนตำแหน่ง</p><div id="map"></div>
<div class="row"><div class="field"><label>ละติจูด</label><input id="lat" name="lat_place" value="<?php echo htmlspecialchars($place['lat_place']); ?>" readonly required></div><div class="field"><label>ลองจิจูด</label><input id="lng" name="lng_place" value="<?php echo htmlspecialchars($place['lng_place']); ?>" readonly required></div></div>
<button class="btn">บันทึกการแก้ไข</button><a class="back" href="places.php">← กลับหน้าสถานที่เที่ยว</a>
</section></form></main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script><script>
const lat=parseFloat(document.getElementById('lat').value),lng=parseFloat(document.getElementById('lng').value);
const map=L.map('map').setView([lat,lng],13);L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap contributors'}).addTo(map);
let marker=L.marker([lat,lng],{draggable:true}).addTo(map);
function set(p){document.getElementById('lat').value=p.lat.toFixed(7);document.getElementById('lng').value=p.lng.toFixed(7);marker.setLatLng(p)}
map.on('click',e=>set(e.latlng));marker.on('dragend',()=>set(marker.getLatLng()));
</script></body></html>