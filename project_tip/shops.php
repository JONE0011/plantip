<?php
session_start();
$open_connect = 1;
require('connect.php');

$is_logged_in = isset($_SESSION['id_account']);

// ดึงร้านทั้งหมดที่แสดงผลอยู่ (status_shop = 1) พร้อมชื่อคนเพิ่ม
$shops_data = [];
$query_shops = "SELECT s.id_shop, s.name_shop, s.category_shop, s.description_shop,
                        s.address_shop, s.lat_shop, s.lng_shop, s.image_shop,
                        a.username_account
                 FROM shop s
                 JOIN account a ON a.id_account = s.id_account
                 WHERE s.status_shop = 1
                 ORDER BY s.created_at DESC";
$result_shops = mysqli_query($connect, $query_shops);
if($result_shops){
    while($row = mysqli_fetch_assoc($result_shops)){
        $shops_data[] = [
            'id'       => (int) $row['id_shop'],
            'name'     => $row['name_shop'],
            'category' => $row['category_shop'],
            'desc'     => $row['description_shop'],
            'address'  => $row['address_shop'],
            'lat'      => (float) $row['lat_shop'],
            'lng'      => (float) $row['lng_shop'],
            'img'      => $row['image_shop'],
            'by'       => $row['username_account'],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ร้านอาหาร &amp; คาเฟ่ | เที่ยวตาก</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<style>
    :root{
        --ink: #1d231d;
        --ink-soft: #4c554b;
        --cream: #faf8f3;
        --panel: #eef1e9;
        --green: #24593f;
        --green-deep: #163a28;
        --green-bright: #3f8f5f;
        --line: rgba(29,35,29,0.12);
        --radius: 14px;
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

    .page-head{
        padding: 2.4rem 0 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    .page-head h1{ margin: 0 0 0.3rem; font-size: 1.8rem; font-weight: 700; }
    .page-head p{ margin: 0; color: var(--ink-soft); font-size: 0.92rem; max-width: 34rem; line-height: 1.6; }
    .btn-add{
        background: var(--green);
        color: #fff;
        border: none;
        border-radius: 999px;
        padding: 0.7rem 1.4rem;
        font-family: 'Prompt', sans-serif;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-add:hover{ background: var(--green-deep); }

    .success-box{
        background: #e8f3ec;
        border: 1px solid #a9d3b8;
        color: var(--green-deep);
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.88rem;
        margin-bottom: 1.2rem;
    }

    .filters{ display: flex; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .filter-btn{
        border: 1px solid var(--line);
        background: #fff;
        border-radius: 999px;
        padding: 0.5rem 1.1rem;
        font-family: 'Prompt', sans-serif;
        font-size: 0.85rem;
        cursor: pointer;
        color: var(--ink-soft);
    }
    .filter-btn.active{ background: var(--green); border-color: var(--green); color: #fff; }

    .layout{ display: grid; grid-template-columns: 1fr 1.3fr; gap: 1.5rem; padding-bottom: 3rem; }
    @media (max-width: 980px){ .layout{ grid-template-columns: 1fr; } }

    #shop-list{
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
        max-height: 620px;
        overflow-y: auto;
        padding-right: 0.3rem;
    }
    .empty-hint{
        font-size: 0.9rem;
        color: var(--ink-soft);
        padding: 2rem 1rem;
        text-align: center;
        border: 1px dashed var(--line);
        border-radius: var(--radius);
    }
    .shop-card{
        display: flex;
        gap: 0.9rem;
        background: #fff;
        border-radius: var(--radius);
        padding: 0.8rem;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(29,35,29,0.06);
        border: 1px solid transparent;
    }
    .shop-card:hover, .shop-card.selected{ border-color: var(--green); }
    .shop-card .thumb{
        flex: none;
        width: 4.5rem; height: 4.5rem;
        border-radius: 10px;
        background: #cfd6c8 center / cover no-repeat;
    }
    .shop-card .body{ flex: 1; min-width: 0; }
    .shop-card h3{ margin: 0 0 0.15rem; font-size: 0.95rem; font-weight: 600; }
    .shop-card .tag{
        display: inline-block;
        font-size: 0.7rem;
        color: var(--green-deep);
        background: var(--panel);
        border-radius: 999px;
        padding: 0.1rem 0.55rem;
        margin-bottom: 0.3rem;
    }
    .shop-card .addr{ margin: 0; font-size: 0.78rem; color: var(--ink-soft); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .shop-card .by{ margin: 0.2rem 0 0; font-size: 0.72rem; color: var(--ink-soft); opacity: 0.8; }

    #map{ height: 620px; border-radius: var(--radius); overflow: hidden; }
    .leaflet-div-icon{ background: transparent; border: none; }
    .shop-pin{
        width: 1.9rem; height: 1.9rem;
        border-radius: 50% 50% 50% 0;
        background: var(--green);
        transform: rotate(-45deg);
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        border: 2px solid #fff;
        font-size: 0.9rem;
    }
    .shop-pin span{ transform: rotate(45deg); }

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
        <div>
            <h1>ร้านอาหาร &amp; คาเฟ่ในตาก</h1>
            <p>รวมร้านที่สมาชิกช่วยกันเพิ่มเข้ามา เหมือนแผนที่ของเราเอง — ถ้าเจอร้านเด็ดที่ยังไม่มีในนี้ กดเพิ่มได้เลย</p>
        </div>
        <a href="add-shop.php" class="btn-add">+ เพิ่มร้านใหม่</a>
    </div>

    <?php if(isset($_GET['added'])): ?>
        <div class="success-box">เพิ่มร้านสำเร็จแล้ว ขอบคุณที่ช่วยเติมข้อมูลให้เพื่อน ๆ นักเดินทางคนอื่นครับ 🙌</div>
    <?php endif; ?>

    <div class="filters" id="filters">
        <button class="filter-btn active" data-cat="ทั้งหมด">ทั้งหมด</button>
        <button class="filter-btn" data-cat="ร้านอาหาร">ร้านอาหาร</button>
        <button class="filter-btn" data-cat="คาเฟ่">คาเฟ่</button>
        <button class="filter-btn" data-cat="ร้านค้า">ร้านค้า</button>
        <button class="filter-btn" data-cat="อื่นๆ">อื่น ๆ</button>
    </div>

    <div class="layout">
        <div id="shop-list"></div>
        <div id="map"></div>
    </div>
</div>

<footer>
    <div class="wrap">© <?php echo date('Y'); ?> เที่ยวตาก · แพลตฟอร์มวางแผนท่องเที่ยวจังหวัดตาก</div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
    const SHOPS = <?php echo json_encode($shops_data, JSON_UNESCAPED_UNICODE); ?>;

    const map = L.map('map', { scrollWheelZoom: false }).setView([16.95, 98.85], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(map);

    function shopIcon(){
        return L.divIcon({
            className: '',
            html: `<div class="shop-pin"><span>📍</span></div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 28]
        });
    }

    const listEl = document.getElementById('shop-list');
    const markerById = {};
    let activeCat = 'ทั้งหมด';

    function render(){
        listEl.innerHTML = '';
        const filtered = SHOPS.filter(s => activeCat === 'ทั้งหมด' || s.category === activeCat);

        if(filtered.length === 0){
            listEl.innerHTML = '<div class="empty-hint">ยังไม่มีร้านในหมวดนี้ — เป็นคนแรกที่เพิ่มร้านสิ!</div>';
        }

        filtered.forEach(shop => {
            const card = document.createElement('div');
            card.className = 'shop-card';
            card.dataset.id = shop.id;
            const thumbStyle = shop.img ? `background-image:url('${shop.img}')` : '';
            card.innerHTML = `
                <div class="thumb" style="${thumbStyle}"></div>
                <div class="body">
                    <span class="tag">${shop.category}</span>
                    <h3>${shop.name}</h3>
                    <p class="addr">${shop.address || 'ไม่ระบุที่อยู่'}</p>
                    <p class="by">เพิ่มโดย ${shop.by}</p>
                </div>`;
            card.addEventListener('click', () => {
                map.setView([shop.lat, shop.lng], 15);
                markerById[shop.id].openPopup();
            });
            listEl.appendChild(card);
        });

        // markers: clear and redraw for current filter
        Object.values(markerById).forEach(m => map.removeLayer(m));
        for(const key in markerById) delete markerById[key];

        filtered.forEach(shop => {
            const marker = L.marker([shop.lat, shop.lng], { icon: shopIcon() })
                .addTo(map)
                .bindPopup(`<b>${shop.name}</b><br>${shop.category}<br>${shop.address || ''}`);
            markerById[shop.id] = marker;
        });
    }

    document.getElementById('filters').addEventListener('click', e => {
        const btn = e.target.closest('.filter-btn');
        if(!btn) return;
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        activeCat = btn.dataset.cat;
        render();
    });

    render();
</script>

</body>
</html>