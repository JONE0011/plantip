<?php
session_start();
$open_connect = 1;
require('connect.php');

if(!isset($_SESSION['id_account'])){
    header('Location: form-login.php');
    exit;
}

$id_account = (int)$_SESSION['id_account'];
$id_shop = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($connect, "SELECT id_shop, name_shop, category_shop, description_shop, address_shop, lat_shop, lng_shop, image_shop FROM shop WHERE id_shop = ? AND id_account = ? AND status_shop = 1 LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ii', $id_shop, $id_account);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$shop = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($stmt);

if(!$shop){
    header('Location: shops.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>แก้ไขร้าน | เที่ยวตาก</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<link rel="stylesheet" href="modern.css">
<style>
.page-head{padding:2.4rem 0 1.5rem}.page-head h1{margin:0 0 .3rem;font-size:1.8rem}.page-head p{margin:0;color:#4c554b}
.form-layout{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;padding-bottom:3rem}.panel-card{background:#eef1e9;border-radius:20px;padding:1.5rem}.panel-card h2{margin:0 0 .2rem;font-size:1.05rem}.panel-card .sub{margin:0 0 1.1rem;font-size:.82rem;color:#4c554b}
.field{margin-bottom:1.1rem}.field label{display:block;font-size:.82rem;color:#4c554b;margin-bottom:.35rem}
.field input,.field select,.field textarea{width:100%;box-sizing:border-box;border:1px solid rgba(29,35,29,.12);border-radius:10px;padding:.65rem .75rem;font-family:'Prompt',sans-serif;font-size:.9rem;background:#fff;color:#1d231d}.field textarea{resize:vertical;min-height:5rem}.field input:focus,.field select:focus,.field textarea:focus{outline:none;border-color:#24593f}
#map{height:360px;border-radius:14px;overflow:hidden;margin-bottom:1rem}.coords{display:flex;gap:.6rem}.coords .field{flex:1}.coords input{background:#f3f1ea;color:#4c554b}
.btn-save{width:100%;border:0;border-radius:999px;padding:.85rem;background:#24593f;color:#fff;font:600 .95rem 'Prompt',sans-serif;cursor:pointer}.btn-save:hover{background:#163a28}
.current-image{display:flex;align-items:center;gap:.8rem;margin-top:.5rem}.current-image img{width:72px;height:72px;border-radius:12px;object-fit:cover;background:#d0d6ca}.image-missing{width:72px;height:72px;border-radius:12px;background:#e5e9e1;color:#778078;display:flex;align-items:center;justify-content:center;text-align:center;font-size:.65rem;padding:.4rem}.back-link{display:inline-block;margin-bottom:1rem;color:#24593f;text-decoration:none}
.drop-pin{width:1.9rem;height:1.9rem;border-radius:50% 50% 50% 0;background:#24593f;transform:rotate(-45deg);border:2px solid #fff;box-shadow:0 2px 5px rgba(0,0,0,.3)}
@media(max-width:900px){.form-layout{grid-template-columns:1fr}}

/* Base layout for standalone edit page */
*{box-sizing:border-box}html{font-size:16px}body{margin:0;min-height:100vh;font-family:'Prompt',sans-serif;color:#17221b;background:#f5f7f2;line-height:1.6}a{color:inherit}button,input,select,textarea{font-family:inherit}
.wrap{width:min(1100px,calc(100% - 40px));margin:0 auto}.nav{position:sticky;top:0;z-index:2000;background:rgba(250,252,248,.94);border-bottom:1px solid rgba(23,34,27,.07);box-shadow:0 8px 30px rgba(20,45,31,.05);backdrop-filter:blur(18px)}
.nav .wrap{height:76px;display:flex;align-items:center;gap:28px}.logo{text-decoration:none;font-weight:800;font-size:1.35rem;white-space:nowrap;color:#17221b}.logo b{color:#1f6044}.logo small{font-size:.62rem;letter-spacing:.08em;color:#6b756d;font-weight:600}.nav-links{list-style:none;margin:0;padding:0;display:flex;align-items:center;gap:25px;flex:1}.nav-links li{margin:0;padding:0}.nav-links a{text-decoration:none;color:#4b554e;font-size:.82rem;font-weight:500;white-space:nowrap}.nav-links a:hover,.nav-links a.active{color:#1f6044}.nav-actions{display:flex;align-items:center;gap:.65rem;margin-left:auto}.btn-plan,.btn-add{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border:0;border-radius:999px;padding:.65rem 1rem;background:#1f6044;color:#fff;font:600 .8rem 'Prompt',sans-serif;white-space:nowrap}.btn-plan:hover{background:#123b2a;transform:translateY(-1px)}
.page-head{padding:2.6rem 0 1.4rem}.form-layout{padding-bottom:1.4rem}.panel-card{box-shadow:0 12px 35px rgba(24,49,35,.07)!important}.btn-save{margin-bottom:2rem;box-shadow:0 10px 24px rgba(31,96,68,.18)}footer{padding:2rem 0 3rem;color:#788178;font-size:.75rem;text-align:center}@media(max-width:900px){.nav .wrap{height:auto;min-height:72px;padding:12px 0;flex-wrap:wrap}.nav-links{order:3;width:100%;flex-basis:100%;justify-content:center;gap:16px;overflow:auto}.nav-actions{margin-left:auto}.logo small{display:none}.wrap{width:min(100% - 24px,1100px)}}
</style>
</head>
<body>
<nav class="nav">
 <div class="wrap">
  <a href="home.php" class="logo">เที่ยว<b>ตาก</b><small>&nbsp;TAK EXPLORE</small></a>
  <ul class="nav-links">
   <li><a href="home.php">หน้าแรก</a></li><li><a href="home.php#destinations">สถานที่เที่ยว</a></li>
   <li><a href="shops.php" class="active">ร้านอาหาร &amp; คาเฟ่</a></li><li><a href="trip-planner.php">วางแผนทริป</a></li>
  </ul>
  <div class="nav-actions"><?php include("profile-widget.php"); ?><a href="trip-planner.php" class="btn-plan">วางแผนทริป</a></div>
 </div>
</nav>
<div class="wrap">
 <div class="page-head">
  <a class="back-link" href="shops.php">← กลับไปร้านอาหาร &amp; คาเฟ่</a>
  <h1>แก้ไขข้อมูลร้าน</h1>
  <p>แก้ไขได้เฉพาะร้านที่คุณเป็นคนเพิ่มเท่านั้น</p>
 </div>
 <form action="process-edit-shop.php" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="id_shop" value="<?php echo (int)$shop['id_shop']; ?>">
  <div class="form-layout">
   <div class="panel-card">
    <h2>ข้อมูลร้าน</h2><p class="sub">อัปเดตข้อมูลร้านของคุณได้เลย</p>
    <div class="field"><label>ชื่อร้าน</label><input type="text" name="name_shop" value="<?php echo htmlspecialchars($shop['name_shop']); ?>" required></div>
    <div class="field"><label>หมวดหมู่</label><select name="category_shop" required>
      <?php foreach(['ร้านอาหาร','คาเฟ่','ร้านค้า','อื่นๆ'] as $cat): ?><option value="<?php echo $cat; ?>" <?php echo $shop['category_shop']===$cat?'selected':''; ?>><?php echo $cat; ?></option><?php endforeach; ?>
    </select></div>
    <div class="field"><label>ที่อยู่ / จุดสังเกต</label><input type="text" name="address_shop" value="<?php echo htmlspecialchars($shop['address_shop']); ?>"></div>
    <div class="field"><label>รายละเอียดร้าน</label><textarea name="description_shop"><?php echo htmlspecialchars($shop['description_shop']); ?></textarea></div>
    <div class="field"><label>เปลี่ยนรูปร้าน</label><input type="file" name="image_shop" accept="image/jpeg,image/png"></div>
    <?php if($shop['image_shop'] && is_file(__DIR__ . '/' . $shop['image_shop'])): ?><div class="current-image"><img src="<?php echo htmlspecialchars($shop['image_shop']); ?>" alt=""><span>รูปปัจจุบัน</span></div><?php elseif($shop['image_shop']): ?><div class="current-image"><div class="image-missing">ไม่มีรูปที่ใช้งานได้</div><span>เลือกรูปใหม่ได้ด้านบน</span></div><?php endif; ?>
   </div>
   <div class="panel-card">
    <h2>ตำแหน่งร้าน</h2><p class="sub">คลิกแผนที่หรือลากหมุดเพื่อแก้ตำแหน่ง</p>
    <div id="map"></div>
    <div class="coords">
     <div class="field"><label>ละติจูด</label><input id="lat_shop" name="lat_shop" value="<?php echo htmlspecialchars($shop['lat_shop']); ?>" readonly required></div>
     <div class="field"><label>ลองจิจูด</label><input id="lng_shop" name="lng_shop" value="<?php echo htmlspecialchars($shop['lng_shop']); ?>" readonly required></div>
    </div>
   </div>
  </div>
  <button class="btn-save" type="submit">บันทึกการแก้ไข</button>
 </form>
</div>
<footer><div class="wrap">© <?php echo date('Y'); ?> เที่ยวตาก · แพลตฟอร์มวางแผนท่องเที่ยวจังหวัดตาก</div></footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
const initial=[<?php echo (float)$shop['lat_shop']; ?>,<?php echo (float)$shop['lng_shop']; ?>];
const map=L.map('map').setView(initial,15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'&copy; OpenStreetMap contributors',maxZoom:18}).addTo(map);
const latInput=document.getElementById('lat_shop'),lngInput=document.getElementById('lng_shop');
let marker=L.marker(initial,{draggable:true,icon:L.divIcon({className:'',html:'<div class="drop-pin"></div>',iconSize:[30,30],iconAnchor:[15,28]})}).addTo(map);
function setCoords(p){latInput.value=p.lat.toFixed(7);lngInput.value=p.lng.toFixed(7)}
map.on('click',e=>{marker.setLatLng(e.latlng);setCoords(e.latlng)});
marker.on('dragend',()=>setCoords(marker.getLatLng()));
</script>
</body>
</html>