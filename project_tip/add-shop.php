<?php
session_start();
$open_connect = 1;
require('connect.php');

// ต้องเข้าสู่ระบบก่อนถึงจะเพิ่มร้านได้ (ทั้ง member และ admin ทำได้)
if(!isset($_SESSION['id_account'])){
    die(header('Location: form-login.php'));
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เพิ่มร้านใหม่ | เที่ยวตาก</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<style>
    :root{
        --ink: #1d231d; --ink-soft: #4c554b; --cream: #faf8f3; --panel: #eef1e9;
        --green: #24593f; --green-deep: #163a28; --line: rgba(29,35,29,0.12); --radius: 14px;
    }
    *{ box-sizing: border-box; }
    body{ margin: 0; font-family: 'Prompt', sans-serif; color: var(--ink); background: var(--cream); }
    a{ color: inherit; }
    .wrap{ max-width: 1440px; margin: 0 auto; padding: 0 1.5rem; }

    .nav{ position: sticky; top: 0; z-index: 20; background: rgba(250,248,243,0.92); backdrop-filter: blur(6px); border-bottom: 1px solid var(--line); }
    .nav .wrap{ display: flex; align-items: center; justify-content: space-between; height: 4.5rem; }
    .logo{ display: flex; align-items: baseline; gap: 0.35rem; font-weight: 700; font-size: 1.25rem; text-decoration: none; }
    .logo b{ color: var(--green); }
    .logo small{ font-weight: 400; font-size: 0.7rem; color: var(--ink-soft); letter-spacing: 0.08em; }
    .nav-links{ display: flex; gap: 2rem; list-style: none; margin: 0; padding: 0; }
    .nav-links a{ text-decoration: none; font-size: 0.92rem; font-weight: 500; color: var(--ink-soft); }
    .nav-links a.active, .nav-links a:hover{ color: var(--green); }
    @media (max-width: 900px){ .nav-links{ display: none; } }

    .page-head{ padding: 2.4rem 0 1.5rem; }
    .page-head h1{ margin: 0 0 0.3rem; font-size: 1.8rem; font-weight: 700; }
    .page-head p{ margin: 0; color: var(--ink-soft); font-size: 0.92rem; max-width: 34rem; line-height: 1.6; }

    .form-layout{ display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; padding-bottom: 3rem; }
    @media (max-width: 900px){ .form-layout{ grid-template-columns: 1fr; } }

    .panel-card{ background: var(--panel); border-radius: 20px; padding: 1.5rem; }
    .panel-card h2{ margin: 0 0 0.2rem; font-size: 1.05rem; font-weight: 700; }
    .panel-card .sub{ margin: 0 0 1.1rem; font-size: 0.82rem; color: var(--ink-soft); }

    label{ display: block; font-size: 0.82rem; color: var(--ink-soft); margin: 0 0 0.35rem; }
    .field{ margin-bottom: 1.1rem; }
    input[type="text"], select, textarea, input[type="file"]{
        width: 100%;
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 0.6rem 0.75rem;
        font-family: 'Prompt', sans-serif;
        font-size: 0.9rem;
        background: #fff;
        color: var(--ink);
    }
    textarea{ resize: vertical; min-height: 5rem; }
    input:focus, select:focus, textarea:focus{ outline: none; border-color: var(--green); }

    .coords{ display: flex; gap: 0.6rem; }
    .coords .field{ flex: 1; margin-bottom: 0; }
    .coords input{ background: #f3f1ea; color: var(--ink-soft); }

    .map-hint{
        font-size: 0.8rem;
        color: var(--ink-soft);
        margin: 0 0 0.7rem;
        padding: 0.6rem 0.8rem;
        background: #fff;
        border-radius: 10px;
        border: 1px dashed var(--line);
    }
    #map{ height: 320px; border-radius: var(--radius); overflow: hidden; margin-bottom: 1rem; }
    .leaflet-div-icon{ background: transparent; border: none; }
    .drop-pin{
        width: 1.9rem; height: 1.9rem;
        border-radius: 50% 50% 50% 0;
        background: var(--green);
        transform: rotate(-45deg);
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        border: 2px solid #fff;
    }

    .btn-submit{
        width: 100%;
        background: var(--green);
        color: #fff;
        border: none;
        border-radius: 999px;
        padding: 0.85rem 0;
        font-family: 'Prompt', sans-serif;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-submit:hover{ background: var(--green-deep); }
    .btn-submit:disabled{ background: #9aa79a; cursor: not-allowed; }

    .error-box{
        background: #fdeceb;
        border: 1px solid #f2b8b5;
        color: #8a2c25;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        margin-bottom: 1.2rem;
    }

    footer{ border-top: 1px solid var(--line); padding: 2rem 0 2.5rem; color: var(--ink-soft); font-size: 0.85rem; }
</style>
</head>
<body>

<nav class="nav">
    <div class="wrap">
        <a href="home.php" class="logo">เที่ยว<b>ตาก</b><small>&nbsp;TAK EXPLORE</small></a>
        <ul class="nav-links">
            <li><a href="home.php">หน้าแรก</a></li>
            <li><a href="home.php#destinations">สถานที่เที่ยว</a></li>
            <li><a href="shops.php" class="active">ร้านอาหาร &amp; คาเฟ่</a></li>
            <li><a href="trip-planner.php">วางแผนทริป</a></li>
        </ul>
    </div>
</nav>

<div class="wrap">
    <div class="page-head">
        <h1>เพิ่มร้านใหม่</h1>
        <p>ช่วยเพื่อน ๆ นักเดินทางคนอื่นด้วยการเพิ่มร้านอาหาร คาเฟ่ หรือร้านค้าที่คุณรู้จักในจังหวัดตาก</p>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <div class="error-box">
            <?php
            $errors = [
                'missing'  => 'กรุณากรอกชื่อร้าน หมวดหมู่ และปักหมุดตำแหน่งบนแผนที่ให้ครบก่อนบันทึก',
                'upload'   => 'อัปโหลดรูปไม่สำเร็จ กรุณาลองใหม่ หรือเลือกไฟล์รูปอื่น',
                'db'       => 'บันทึกข้อมูลไม่สำเร็จ กรุณาลองใหม่อีกครั้ง',
            ];
            echo $errors[$_GET['error']] ?? 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
            ?>
        </div>
    <?php endif; ?>

    <form action="process-add-shop.php" method="POST" enctype="multipart/form-data" id="shop-form">
        <div class="form-layout">

            <div class="panel-card">
                <h2>ข้อมูลร้าน</h2>
                <p class="sub">กรอกให้ครบเพื่อให้คนอื่นหาร้านคุณเจอง่าย ๆ</p>

                <div class="field">
                    <label for="name_shop">ชื่อร้าน</label>
                    <input type="text" id="name_shop" name="name_shop" placeholder="เช่น ร้านกาแฟบ้านสวน" required>
                </div>

                <div class="field">
                    <label for="category_shop">หมวดหมู่</label>
                    <select id="category_shop" name="category_shop" required>
                        <option value="" disabled selected>เลือกหมวดหมู่</option>
                        <option value="ร้านอาหาร">ร้านอาหาร</option>
                        <option value="คาเฟ่">คาเฟ่</option>
                        <option value="ร้านค้า">ร้านค้า</option>
                        <option value="อื่นๆ">อื่น ๆ</option>
                    </select>
                </div>

                <div class="field">
                    <label for="address_shop">ที่อยู่ / จุดสังเกต (ถ้ามี)</label>
                    <input type="text" id="address_shop" name="address_shop" placeholder="เช่น ถนนมหาดไทยบำรุง ต.หนองหลวง อ.เมืองตาก">
                </div>

                <div class="field">
                    <label for="description_shop">รายละเอียดร้าน</label>
                    <textarea id="description_shop" name="description_shop" placeholder="เมนูเด็ด บรรยากาศ เวลาเปิด-ปิด ฯลฯ"></textarea>
                </div>

                <div class="field">
                    <label for="image_shop">รูปร้าน (ถ้ามี)</label>
                    <input type="file" id="image_shop" name="image_shop" accept="image/jpeg,image/png">
                </div>
            </div>

            <div class="panel-card">
                <h2>ปักหมุดตำแหน่งร้าน</h2>
                <p class="map-hint">คลิกบนแผนที่ตรงตำแหน่งร้าน หมุดจะย้ายไปตามที่คลิก ลากหมุดเพื่อขยับตำแหน่งให้แม่นขึ้นได้</p>
                <div id="map"></div>

                <div class="coords">
                    <div class="field">
                        <label for="lat_shop">ละติจูด</label>
                        <input type="text" id="lat_shop" name="lat_shop" readonly required>
                    </div>
                    <div class="field">
                        <label for="lng_shop">ลองจิจูด</label>
                        <input type="text" id="lng_shop" name="lng_shop" readonly required>
                    </div>
                </div>
            </div>

        </div>

        <button type="submit" class="btn-submit" id="submit-btn" disabled>ปักหมุดก่อนถึงจะบันทึกได้</button>
    </form>
</div>

<footer>
    <div class="wrap">© <?php echo date('Y'); ?> เที่ยวตาก · แพลตฟอร์มวางแผนท่องเที่ยวจังหวัดตาก</div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
    // ศูนย์กลางจังหวัดตากโดยประมาณ ใช้เป็นจุดเริ่มต้นของแผนที่
    const map = L.map('map').setView([16.95, 98.85], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(map);

    const latInput = document.getElementById('lat_shop');
    const lngInput = document.getElementById('lng_shop');
    const submitBtn = document.getElementById('submit-btn');

    let marker = null;
    function dropPin(latlng){
        if(marker){
            marker.setLatLng(latlng);
        }else{
            marker = L.marker(latlng, {
                draggable: true,
                icon: L.divIcon({ className: '', html: '<div class="drop-pin"></div>', iconSize: [30,30], iconAnchor: [15,28] })
            }).addTo(map);
            marker.on('dragend', () => setCoords(marker.getLatLng()));
        }
        setCoords(latlng);
    }

    function setCoords(latlng){
        latInput.value = latlng.lat.toFixed(7);
        lngInput.value = latlng.lng.toFixed(7);
        submitBtn.disabled = false;
        submitBtn.textContent = 'บันทึกร้านนี้';
    }

    map.on('click', e => dropPin(e.latlng));
</script>

</body>
</html>