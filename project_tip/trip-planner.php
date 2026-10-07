<?php
session_start();
$open_connect = 1;
require('connect.php');

// หน้านี้ต้องเข้าสู่ระบบก่อน เพราะทริปผูกกับ id_account
if(!isset($_SESSION['id_account'])){
    die(header('Location: form-login.php'));
}elseif(isset($_GET['logout'])){
    session_destroy();
    die(header('Location: form-login.php'));
}

$id_account = (int) $_SESSION['id_account'];

// API สำหรับให้ปุ่ม “ทริปของฉัน” ดึงชื่อภาษาไทยของสถานที่/ร้านได้จากทุกหน้า
// ป้องกันกรณี localStorage เก็บ place_key เช่น thararak แล้วหน้า shops.php แสดง key แทนชื่อจริง
if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'trip_meta'){
    header('Content-Type: application/json; charset=utf-8');
    $meta = [];

    $placesMeta = mysqli_query($connect, "SELECT place_key, name_place, location_place FROM place ORDER BY id_place");
    if($placesMeta){
        while($row = mysqli_fetch_assoc($placesMeta)){
            $meta[$row['place_key']] = [
                'title' => $row['name_place'],
                'loc' => $row['location_place']
            ];
        }
    }

    $shopsMeta = mysqli_query($connect, "SELECT id_shop, name_shop, address_shop FROM shop WHERE status_shop = 1 ORDER BY id_shop");
    if($shopsMeta){
        while($row = mysqli_fetch_assoc($shopsMeta)){
            $meta['shop:' . (int)$row['id_shop']] = [
                'title' => $row['name_shop'],
                'loc' => $row['address_shop'] ?: 'จังหวัดตาก'
            ];
        }
    }

    echo json_encode(['success'=>true, 'items'=>$meta], JSON_UNESCAPED_UNICODE);
    exit;
}

// บันทึกลำดับทริปในไฟล์นี้เลย ไม่ต้องใช้ save-trip.php
// รับ JSON: {"places":["place_key1","place_key2",...]}
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'save_trip'){
    header('Content-Type: application/json; charset=utf-8');
    $input = json_decode(file_get_contents('php://input'), true);
    $places = isset($input['places']) && is_array($input['places']) ? $input['places'] : [];

    // ทำความสะอาด id และตัดค่าซ้ำ โดยคงลำดับเดิม
    $clean = [];
    foreach($places as $item){
        $item = trim((string)$item);
        if($item !== '' && !in_array($item, $clean, true)) $clean[] = $item;
    }

    mysqli_begin_transaction($connect);
    try{
        $del = mysqli_prepare($connect, "DELETE FROM trip_place WHERE id_account = ?");
        mysqli_stmt_bind_param($del, 'i', $id_account);
        if(!mysqli_stmt_execute($del)) throw new Exception(mysqli_stmt_error($del));
        mysqli_stmt_close($del);

        $findPlace = mysqli_prepare($connect, "SELECT id_place FROM place WHERE place_key = ? LIMIT 1");
        $findShop = mysqli_prepare($connect, "SELECT id_shop FROM shop WHERE id_shop = ? AND status_shop = 1 LIMIT 1");
        $ins = mysqli_prepare($connect, "INSERT INTO trip_place (id_account, item_type, id_place, id_shop, order_no) VALUES (?, ?, ?, ?, ?)");
        if(!$findPlace || !$findShop || !$ins) throw new Exception(mysqli_error($connect));

        $saved = 0;
        foreach($clean as $index => $item){
            $order_no = $index + 1;
            $type = 'place';
            $id_place = null;
            $id_shop = null;

            if(strpos($item, 'shop:') === 0){
                $type = 'shop';
                $shopId = (int)substr($item, 5);
                if($shopId <= 0) continue;
                mysqli_stmt_bind_param($findShop, 'i', $shopId);
                if(!mysqli_stmt_execute($findShop)) throw new Exception(mysqli_stmt_error($findShop));
                $result = mysqli_stmt_get_result($findShop);
                $row = $result ? mysqli_fetch_assoc($result) : null;
                if(!$row) continue;
                $id_shop = (int)$row['id_shop'];
            }else{
                $placeKey = str_starts_with($item, 'place:') ? substr($item, 6) : $item;
                mysqli_stmt_bind_param($findPlace, 's', $placeKey);
                if(!mysqli_stmt_execute($findPlace)) throw new Exception(mysqli_stmt_error($findPlace));
                $result = mysqli_stmt_get_result($findPlace);
                $row = $result ? mysqli_fetch_assoc($result) : null;
                if(!$row) continue;
                $id_place = (int)$row['id_place'];
            }

            mysqli_stmt_bind_param($ins, 'isiii', $id_account, $type, $id_place, $id_shop, $order_no);
            if(!mysqli_stmt_execute($ins)) throw new Exception(mysqli_stmt_error($ins));

            if($type === 'place'){
                $history = mysqli_prepare($connect, "INSERT IGNORE INTO place_history (id_account, id_place) VALUES (?, ?)");
                if($history){
                    mysqli_stmt_bind_param($history, 'ii', $id_account, $id_place);
                    mysqli_stmt_execute($history);
                    mysqli_stmt_close($history);
                }
            }
            $saved++;
        }

        mysqli_stmt_close($findPlace);
        mysqli_stmt_close($findShop);
        mysqli_stmt_close($ins);
        mysqli_commit($connect);

        echo json_encode(['success'=>true, 'count'=>$saved], JSON_UNESCAPED_UNICODE);
    }catch(Throwable $e){
        mysqli_rollback($connect);
        http_response_code(500);
        echo json_encode(['success'=>false, 'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

$query_who = "SELECT username_account FROM account WHERE id_account = '$id_account'";
$result_who = mysqli_query($connect, $query_who);
$who = mysqli_fetch_assoc($result_who);
$username_account = $who['username_account'] ?? '';

// ดึงสถานที่เที่ยวทั้งหมดจากฐานข้อมูล (แทนของเดิมที่ hardcode ไว้ในไฟล์)
$places_data = [];

// ใช้รูปจริงที่เก็บไว้ในโปรเจกต์ เพื่อให้รูปแสดงบน Railway ได้แน่นอน
$place_images = [
    'thi-lo-su' => '/images/places/thi-lo-su.jpg',
    'doi-musoe' => '/images/places/doi-musoe.jpg',
    'bhumibol-dam' => '/images/places/bhumibol-dam.jpg',
    'mae-sot-market' => '/images/places/mae-sot-market.jpg',
    'lan-sang' => '/images/places/lan-sang.jpg',
    'taksin-maharat' => '/images/places/taksin-maharat.jpg',
    'wat-borommathat' => '/images/places/wat-borommathat.jpg',
    'friendship-bridge' => '/images/places/friendship-bridge.jpg',
];

$query_places = "SELECT place_key, name_place, location_place, lat_place, lng_place, image_place FROM place ORDER BY id_place";
$result_places = mysqli_query($connect, $query_places);
while($row = mysqli_fetch_assoc($result_places)){
    $places_data[] = [
        'id'       => $row['place_key'],
        'name'     => $row['name_place'],
        'loc'      => $row['location_place'],
        'lat'      => (float) $row['lat_place'],
        'lng'      => (float) $row['lng_place'],
        'img'      => $place_images[$row['place_key']] ?? ($row['image_place'] ?? ''),
        'category' => 'สถานที่เที่ยว',
        'type'     => 'place',
    ];
}

$query_shops = "SELECT id_shop, name_shop, category_shop, description_shop, address_shop, lat_shop, lng_shop, image_shop
                FROM shop
                WHERE status_shop = 1
                ORDER BY id_shop DESC";
$result_shops = mysqli_query($connect, $query_shops);
while($row = mysqli_fetch_assoc($result_shops)){
    $places_data[] = [
        'id'       => 'shop:' . (int)$row['id_shop'],
        'name'     => $row['name_shop'],
        'loc'      => $row['address_shop'] ?: ($row['description_shop'] ?: 'ตาก'),
        'lat'      => (float) $row['lat_shop'],
        'lng'      => (float) $row['lng_shop'],
        'img'      => explode('|', $row['image_shop'] ?: '')[0],
        'category' => $row['category_shop'],
        'type'     => 'shop',
    ];
}

// ดึงทริปที่บันทึกไว้ของบัญชีนี้ เรียงตามลำดับที่จัดไว้
$trip_data = [];
$query_trip = "SELECT tp.item_type, p.place_key, s.id_shop
                FROM trip_place tp
                LEFT JOIN place p ON p.id_place = tp.id_place
                LEFT JOIN shop s ON s.id_shop = tp.id_shop
                WHERE tp.id_account = $id_account
                ORDER BY tp.order_no";
$result_trip = mysqli_query($connect, $query_trip);
while($row = mysqli_fetch_assoc($result_trip)){
    if(($row['item_type'] ?? 'place') === 'shop' && $row['id_shop']){
        $trip_data[] = 'shop:' . (int)$row['id_shop'];
    }elseif($row['place_key']){
        $trip_data[] = $row['place_key'];
    }
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>วางแผนทริป | เที่ยวตาก</title>
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
    body{
        margin: 0;
        font-family: 'Prompt', sans-serif;
        color: var(--ink);
        background: var(--cream);
    }
    a{ color: inherit; }
    .wrap{ max-width: 1180px; margin: 0 auto; padding: 0 1.5rem; }

    /* ---------- Nav (same system as home.php) ---------- */
    .nav{
        position: sticky; top: 0; z-index: 20;
        background: rgba(250,248,243,0.92);
        backdrop-filter: blur(6px);
        border-bottom: 1px solid var(--line);
    }
    .nav .wrap{ display: flex; align-items: center; justify-content: space-between; height: 4.5rem; }
    .logo{ display: flex; align-items: baseline; gap: 0.35rem; font-weight: 700; font-size: 1.25rem; text-decoration: none; }
    .logo b{ color: var(--green); }
    .logo small{ font-weight: 400; font-size: 0.7rem; color: var(--ink-soft); letter-spacing: 0.08em; }
    .nav-links{ display: flex; gap: 2rem; list-style: none; margin: 0; padding: 0; }
    .nav-links a{ text-decoration: none; font-size: 0.92rem; font-weight: 500; color: var(--ink-soft); }
    .nav-links a.active, .nav-links a:hover{ color: var(--green); }
    .nav-actions{ display: flex; align-items: center; gap: 1rem; }
    .nav-actions .who{ font-size: 0.85rem; color: var(--ink-soft); }
    .nav-actions .logout-link{
        font-size: 0.85rem;
        color: var(--ink-soft);
        text-decoration: none;
        border-bottom: 1px solid var(--line);
        padding-bottom: 2px;
    }
    .nav-actions .logout-link:hover{ color: var(--ink); border-bottom-color: var(--ink-soft); }
    @media (max-width: 900px){ .nav-links{ display: none; } }

    /* ---------- Map-first dashboard ---------- */
    body{
        margin: 0;
        font-family: 'Prompt', sans-serif;
        color: var(--ink);
        background: #f8f7f2;
    }

    .wrap{ max-width: 1280px; margin: 0 auto; padding: 0 1.25rem; }

    /* Map is the visual header, like the reference */
    .map-hero{
        width: 100%;
        height: 350px;
        min-height: 180px;
        max-height: 75vh;
        position: relative;
        background: #dfe7dc;
        overflow: hidden;
    }

    #map{
        width: 100%;
        height: 350px;
        min-height: 180px;
        border-radius: 0;
    }

    /* จุดสำหรับลากปรับความสูงของแผนที่ */
    .map-resize-handle{
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 14px;
        z-index: 1000;
        cursor: ns-resize;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(
            to bottom,
            transparent 0%,
            rgba(250,248,243,.15) 35%,
            rgba(250,248,243,.65) 100%
        );
        touch-action: none;
    }

    .map-resize-handle::after{
        content: "";
        width: 70px;
        height: 4px;
        border-radius: 999px;
        background: rgba(29,35,29,.38);
        transition: width .15s ease, background .15s ease;
    }

    .map-resize-handle:hover::after,
    .map-hero.resizing .map-resize-handle::after{
        width: 100px;
        background: rgba(36,89,63,.8);
    }

    body.map-resizing{
        cursor: ns-resize !important;
        user-select: none !important;
    }

    body.map-resizing *{
        cursor: ns-resize !important;
    }

    /* Navigation sits directly under the map */
    .nav{
        position: relative;
        z-index: 20;
        background: rgba(250,248,243,0.98);
        border-bottom: 1px solid var(--line);
        box-shadow: 0 1px 0 rgba(29,35,29,0.03);
    }

    .nav .wrap{
        display: flex;
        align-items: center;
        justify-content: flex-start;
        height: 4.15rem;
    }

    .logo{
        display: none;
    }

    .nav-links{
        display: flex;
        align-items: center;
        gap: 2.4rem;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .nav-links a{
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--ink-soft);
        transition: color .2s ease;
    }

    .nav-links a.active,
    .nav-links a:hover{
        color: var(--green);
    }

    .nav-actions{
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .nav-actions .who{
        font-size: 0.8rem;
        color: var(--ink-soft);
    }

    .nav-actions .logout-link{
        font-size: 0.8rem;
        color: var(--ink-soft);
        text-decoration: none;
        border-bottom: 1px solid var(--line);
        padding-bottom: 2px;
    }

    .nav-actions .logout-link:hover{
        color: var(--ink);
        border-bottom-color: var(--ink-soft);
    }

    /* Main dashboard */
    .dashboard{
        display: grid;
        grid-template-columns: minmax(0, 3.1fr) minmax(270px, 1.15fr);
        gap: 1.25rem;
        padding: 1rem 0 1.5rem;
        align-items: stretch;
    }

    .panel-card{
        background: #eef1e9;
        border-radius: 20px;
        padding: 1.1rem 1.2rem;
        border: 1px solid rgba(29,35,29,.025);
    }

    .places-panel{
        min-width: 0;
        overflow: hidden;
    }

    .panel-card h2{
        margin: 0 0 .15rem;
        font-size: 1.05rem;
        font-weight: 700;
    }

    .panel-card .sub{
        margin: 0 0 .85rem;
        font-size: .76rem;
        color: var(--ink-soft);
    }

    /* Horizontal cards, matching the reference composition */
    .category-tabs{display:flex;gap:.55rem;flex-wrap:wrap;margin:0 0 1rem;padding:.15rem 0}
    .category-tab{border:1px solid var(--line);background:#fff;color:var(--ink-soft);border-radius:999px;padding:.48rem .85rem;font:500 .72rem 'Prompt',sans-serif;cursor:pointer;transition:.2s ease}
    .category-tab:hover{border-color:var(--green);color:var(--green);transform:translateY(-1px)}
    .category-tab.active{background:var(--green);border-color:var(--green);color:#fff;box-shadow:0 5px 14px rgba(36,89,63,.18)}
    .place-card .category-badge{display:inline-block;margin-bottom:.28rem;padding:.18rem .45rem;border-radius:999px;background:#edf3ed;color:var(--green);font-size:.58rem;font-weight:600}
    .place-card .photo.placeholder{display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#dfe6dc,#cbd5c8);color:#718071;font-size:2rem}
    .no-results{grid-column:1/-1;padding:2rem;text-align:center;color:var(--ink-soft)}
    .place-grid{
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        overflow-x: auto;
        scrollbar-width: thin;
        padding-bottom: .15rem;
    }

    .place-card{
        min-width: 0;
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(29,35,29,.06);
    }

    .place-card .photo{
        aspect-ratio: 1.2 / 1;
        background: #cfd6c8 center / cover no-repeat;
    }

    .place-card .info{
        padding: .7rem .75rem .75rem;
    }

    .place-card h3{
        margin: 0 0 .12rem;
        font-size: .82rem;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .place-card .meta{
        margin: 0 0 .55rem;
        font-size: .68rem;
        color: var(--ink-soft);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .place-card button{
        width: 100%;
        border: none;
        border-radius: 999px;
        padding: .43rem 0;
        font-family: 'Prompt', sans-serif;
        font-size: .72rem;
        font-weight: 500;
        cursor: pointer;
        background: var(--green);
        color: #fff;
        transition: background .2s ease;
    }

    .place-card button:hover{ background: var(--green-deep); }
    .place-card button.added{ background: var(--ink); }
    .place-card button.added::after{ content: "อยู่ในทริปแล้ว · เอาออก"; }
    .place-card button:not(.added)::after{ content: "+ เพิ่มเข้าทริป"; }

    /* Right trip panel */
    .trip-panel{
        min-width: 0;
        display: flex;
        flex-direction: column;
    }

    #trip-list{
        list-style: none;
        margin: .2rem 0 .75rem;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: .45rem;
        min-height: 3rem;
        flex: 1;
    }

    #trip-list .empty-hint{
        font-size: .72rem;
        color: var(--ink-soft);
        padding: .8rem .5rem;
        text-align: center;
        border: 1px dashed var(--line);
        border-radius: 12px;
    }

    .trip-item{
        display: flex;
        align-items: center;
        gap: .55rem;
        background: #fff;
        border-radius: 11px;
        padding: .48rem .55rem;
        cursor: grab;
        box-shadow: 0 1px 2px rgba(29,35,29,.05);
    }

    .trip-item .badge{
        flex: none;
        width: 1.55rem;
        height: 1.55rem;
        border-radius: 50%;
        background: var(--green);
        color: #fff;
        font-size: .72rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .trip-item .name{
        flex: 1;
        min-width: 0;
        font-size: .74rem;
        font-weight: 500;
        line-height: 1.35;
    }

    .trip-item .loc{
        font-size: .62rem;
        color: var(--ink-soft);
    }

    .trip-item .remove{
        flex: none;
        border: none;
        background: transparent;
        color: var(--ink-soft);
        font-size: 1rem;
        line-height: 1;
        cursor: pointer;
        padding: .15rem;
    }

    .trip-item .remove:hover{ color: var(--ink); }

    .drag-handle{
        flex: none;
        color: var(--ink-soft);
        font-size: .78rem;
        line-height: 1;
        letter-spacing: 1px;
    }

    .clear-btn{
        width: 100%;
        border: 1px solid var(--line);
        background: transparent;
        border-radius: 999px;
        padding: .48rem 0;
        font-family: 'Prompt', sans-serif;
        font-size: .7rem;
        color: var(--ink-soft);
        cursor: pointer;
    }

    .clear-btn:hover{
        border-color: var(--ink-soft);
        color: var(--ink);
    }

    .leaflet-div-icon{ background: transparent; border: none; }

    .pin{
        width: 1.9rem;
        height: 1.9rem;
        border-radius: 50% 50% 50% 0;
        background: var(--green);
        transform: rotate(-45deg);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,.3);
        border: 2px solid #fff;
    }

    .pin span{
        transform: rotate(45deg);
        color: #fff;
        font-size: .78rem;
        font-weight: 700;
    }

    .trip-actions{
        display: flex;
        flex-direction: column;
        gap: .5rem;
        margin-top: auto;
    }

    .share-trip-btn{
        width: 100%;
        border: none;
        border-radius: 999px;
        padding: .55rem .8rem;
        font-family: 'Prompt', sans-serif;
        font-size: .72rem;
        font-weight: 600;
        color: #fff;
        background: var(--green);
        cursor: pointer;
        transition: background .2s ease, transform .1s ease;
    }

    .share-trip-btn:hover{
        background: var(--green-deep);
    }

    .share-trip-btn:active{
        transform: translateY(1px);
    }

    .qr-modal{
        position: fixed;
        inset: 0;
        z-index: 5000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .qr-modal.open{
        display: flex;
    }

    .qr-backdrop{
        position: absolute;
        inset: 0;
        background: rgba(15,20,16,.62);
        backdrop-filter: blur(3px);
    }

    .qr-dialog{
        position: relative;
        z-index: 1;
        width: min(440px, 100%);
        max-height: 90vh;
        overflow: auto;
        background: #fff;
        border-radius: 22px;
        padding: 1.5rem;
        box-shadow: 0 20px 60px rgba(0,0,0,.25);
        text-align: center;
    }

    .qr-close{
        position: absolute;
        top: .6rem;
        right: .75rem;
        width: 2rem;
        height: 2rem;
        border: none;
        background: #f0f1ec;
        color: var(--ink);
        border-radius: 50%;
        font-size: 1.25rem;
        line-height: 1;
        cursor: pointer;
    }

    .qr-dialog h2{
        margin: .15rem 2rem .3rem;
        font-size: 1.15rem;
    }

    .qr-subtitle{
        margin: 0 auto 1rem;
        max-width: 350px;
        color: var(--ink-soft);
        font-size: .75rem;
        line-height: 1.6;
    }

    .qr-box{
        width: 220px;
        height: 220px;
        margin: 0 auto 1rem;
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #e2e5de;
        border-radius: 16px;
    }

    .qr-box img,
    .qr-box canvas{
        max-width: 100%;
        height: auto;
    }

    .qr-order{
        text-align: left;
        background: #f1f3ed;
        border-radius: 13px;
        padding: .65rem .8rem;
        margin-bottom: .8rem;
        font-size: .72rem;
    }

    .qr-order .qr-place{
        display: flex;
        gap: .5rem;
        align-items: center;
        padding: .25rem 0;
    }

    .qr-order .qr-number{
        flex: none;
        width: 1.45rem;
        height: 1.45rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--green);
        color: #fff;
        font-weight: 600;
    }

    .qr-url-wrap{
        display: flex;
        gap: .45rem;
    }

    .qr-url-wrap input{
        min-width: 0;
        flex: 1;
        border: 1px solid #d9ddd4;
        border-radius: 10px;
        padding: .55rem .65rem;
        font-family: inherit;
        font-size: .68rem;
        color: var(--ink-soft);
        background: #fafbf8;
    }

    .qr-url-wrap button{
        flex: none;
        border: none;
        border-radius: 10px;
        padding: 0 .8rem;
        background: var(--green);
        color: #fff;
        font-family: inherit;
        font-size: .7rem;
        cursor: pointer;
    }

    .qr-note{
        margin: .75rem 0 0;
        color: #6b7369;
        font-size: .64rem;
        line-height: 1.55;
    }

    body.modal-open{
        overflow: hidden;
    }

    footer{
        border-top: 1px solid var(--line);
        padding: 1.25rem 0 1.5rem;
        color: var(--ink-soft);
        font-size: .75rem;
    }

    @media (max-width: 980px){
        .dashboard{ grid-template-columns: 1fr; }
        .trip-panel{ min-height: 280px; }
        .place-grid{ grid-template-columns: repeat(3, minmax(180px,1fr)); }
    }

    @media (max-width: 700px){
        .map-hero{ height: 280px; min-height: 160px; max-height: 70vh; }
        #map{ min-height: 160px; }
        .nav .wrap{ height: auto; min-height: 3.8rem; padding-top: .65rem; padding-bottom: .65rem; }
        .nav-links{ gap: 1.1rem; overflow-x: auto; width: 100%; }
        .nav-actions{ display: none; }
        .dashboard{ padding-top: .7rem; }
        .place-grid{ grid-template-columns: repeat(2, minmax(160px,1fr)); }
    }

    @media (max-width: 460px){
        .wrap{ padding-left: .8rem; padding-right: .8rem; }
        .place-grid{ grid-template-columns: 1fr 1fr; gap: .65rem; }
        .place-card .photo{ aspect-ratio: 1.05 / 1; }
    }

    /* ---------- Map ---------- */
    .map-card{
        margin-top: 1.5rem;
    }
    #map{
        height: 420px;
        border-radius: var(--radius);
        overflow: hidden;
    }
    .leaflet-div-icon{ background: transparent; border: none; }
    .pin{
        width: 1.9rem; height: 1.9rem;
        border-radius: 50% 50% 50% 0;
        background: var(--green);
        transform: rotate(-45deg);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        border: 2px solid #fff;
    }
    .pin span{
        transform: rotate(45deg);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .trip-actions{
        display: flex;
        flex-direction: column;
        gap: .5rem;
        margin-top: auto;
    }

    .share-trip-btn{
        width: 100%;
        border: none;
        border-radius: 999px;
        padding: .55rem .8rem;
        font-family: 'Prompt', sans-serif;
        font-size: .72rem;
        font-weight: 600;
        color: #fff;
        background: var(--green);
        cursor: pointer;
        transition: background .2s ease, transform .1s ease;
    }

    .share-trip-btn:hover{
        background: var(--green-deep);
    }

    .share-trip-btn:active{
        transform: translateY(1px);
    }

    .qr-modal{
        position: fixed;
        inset: 0;
        z-index: 5000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .qr-modal.open{
        display: flex;
    }

    .qr-backdrop{
        position: absolute;
        inset: 0;
        background: rgba(15,20,16,.62);
        backdrop-filter: blur(3px);
    }

    .qr-dialog{
        position: relative;
        z-index: 1;
        width: min(440px, 100%);
        max-height: 90vh;
        overflow: auto;
        background: #fff;
        border-radius: 22px;
        padding: 1.5rem;
        box-shadow: 0 20px 60px rgba(0,0,0,.25);
        text-align: center;
    }

    .qr-close{
        position: absolute;
        top: .6rem;
        right: .75rem;
        width: 2rem;
        height: 2rem;
        border: none;
        background: #f0f1ec;
        color: var(--ink);
        border-radius: 50%;
        font-size: 1.25rem;
        line-height: 1;
        cursor: pointer;
    }

    .qr-dialog h2{
        margin: .15rem 2rem .3rem;
        font-size: 1.15rem;
    }

    .qr-subtitle{
        margin: 0 auto 1rem;
        max-width: 350px;
        color: var(--ink-soft);
        font-size: .75rem;
        line-height: 1.6;
    }

    .qr-box{
        width: 220px;
        height: 220px;
        margin: 0 auto 1rem;
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #e2e5de;
        border-radius: 16px;
    }

    .qr-box img,
    .qr-box canvas{
        max-width: 100%;
        height: auto;
    }

    .qr-order{
        text-align: left;
        background: #f1f3ed;
        border-radius: 13px;
        padding: .65rem .8rem;
        margin-bottom: .8rem;
        font-size: .72rem;
    }

    .qr-order .qr-place{
        display: flex;
        gap: .5rem;
        align-items: center;
        padding: .25rem 0;
    }

    .qr-order .qr-number{
        flex: none;
        width: 1.45rem;
        height: 1.45rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--green);
        color: #fff;
        font-weight: 600;
    }

    .qr-url-wrap{
        display: flex;
        gap: .45rem;
    }

    .qr-url-wrap input{
        min-width: 0;
        flex: 1;
        border: 1px solid #d9ddd4;
        border-radius: 10px;
        padding: .55rem .65rem;
        font-family: inherit;
        font-size: .68rem;
        color: var(--ink-soft);
        background: #fafbf8;
    }

    .qr-url-wrap button{
        flex: none;
        border: none;
        border-radius: 10px;
        padding: 0 .8rem;
        background: var(--green);
        color: #fff;
        font-family: inherit;
        font-size: .7rem;
        cursor: pointer;
    }

    .qr-note{
        margin: .75rem 0 0;
        color: #6b7369;
        font-size: .64rem;
        line-height: 1.55;
    }

    body.modal-open{
        overflow: hidden;
    }

    footer{
        border-top: 1px solid var(--line);
        padding: 2rem 0 2.5rem;
        color: var(--ink-soft);
        font-size: 0.85rem;
    }
</style>
    <link rel="stylesheet" href="modern.css">
    <style id="planner-redesign">
        :root{
            --planner-bg:#f7f6f0;
            --planner-panel:#f1f2ec;
            --planner-green:#205840;
            --planner-green-dark:#143d2b;
            --planner-line:#dfe3da;
            --planner-text:#1f2923;
            --planner-muted:#747d75;
        }

        html,body{
            width:100%;
            height:100%;
            min-height:0;
            overflow:hidden;
        }

        body{
            margin:0;
            background:var(--planner-bg);
            color:var(--planner-text);
        }

        .planner-app{
            --side-width:340px;
            width:100vw;
            height:100vh;
            min-height:0;
            display:grid;
            grid-template-columns:minmax(420px,1fr) 8px minmax(280px,var(--side-width));
            background:var(--planner-bg);
        }

        .map-pane{
            position:relative;
            min-width:0;
            height:100vh;
            background:#dce8df;
            overflow:hidden;
        }

        .map-pane #map{
            width:100%;
            height:100%;
            border-radius:0;
        }

        .map-pane .map-resize-handle{
            display:none;
        }

        .side-pane{
            position:relative;
            z-index:10;
            min-width:0;
            height:100vh;
            min-height:0;
            overflow:hidden;
            overscroll-behavior:contain;
            background:#fbfaf6;
            border-left:1px solid #e3e5de;
            display:flex;
            flex-direction:column;
            scrollbar-width:thin;
            scrollbar-color:#b8c0b8 transparent;
        }

        .side-resize-handle{
            position:relative;
            z-index:50;
            width:8px;
            height:100%;
            cursor:ew-resize;
            background:#eceee8;
            border-left:1px solid #dfe3db;
            border-right:1px solid #dfe3db;
            display:flex;
            align-items:center;
            justify-content:center;
            touch-action:none;
        }

        .side-resize-handle::after{
            content:"";
            width:3px;
            height:54px;
            border-radius:999px;
            background:#aeb7ae;
            transition:.15s ease;
        }

        .side-resize-handle:hover::after,
        .planner-app.side-resizing .side-resize-handle::after{
            height:90px;
            background:var(--planner-green);
        }

        .planner-app.side-resizing{
            cursor:ew-resize;
            user-select:none;
        }

        .planner-app.side-resizing *{
            cursor:ew-resize !important;
        }

        .trip-summary{
            --trip-summary-height:300px;
            position:relative;
            height:var(--trip-summary-height);
            flex:0 0 auto;
            min-height:0;
            max-height:none;
            margin:14px 14px 8px;
            padding:15px;
            background:#eef0e9;
            border-radius:20px;
            border:1px solid rgba(32,88,64,.04);
            display:flex;
            flex-direction:column;
            overflow:hidden;
            box-sizing:border-box;
        }

        /* ป้องกันรายการและปุ่มทับกัน แต่ยังลากขยายกรอบได้อิสระ */
        .trip-summary.trip-resizing{
            overflow:hidden;
            transition:none !important;
            will-change:height;
        }

        .trip-summary-resize-handle{
            position:absolute;
            left:18px;
            right:18px;
            bottom:4px;
            height:14px;
            z-index:20;
            cursor:ns-resize;
            display:flex;
            align-items:center;
            justify-content:center;
            touch-action:none;
        }

        .trip-summary-resize-handle::after{
            content:"";
            width:58px;
            height:4px;
            border-radius:999px;
            background:#aeb7ae;
            transition:.15s ease;
        }

        .trip-summary-resize-handle:hover::after,
        .trip-summary.trip-resizing .trip-summary-resize-handle::after{
            width:90px;
            background:var(--planner-green);
        }

        .planner-app.trip-resizing{
            cursor:ns-resize;
            user-select:none;
        }

        .planner-app.trip-resizing *{
            cursor:ns-resize !important;
        }

        .trip-summary-head{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:10px;
            margin-bottom:8px;
        }

        .trip-summary h1{
            margin:0;
            font-size:15px;
            line-height:1.25;
            font-weight:700;
        }

        .trip-summary-count{
            flex:none;
            min-width:30px;
            padding:3px 7px;
            border-radius:999px;
            background:#dfe9e1;
            color:var(--planner-green);
            font-size:10px;
            font-weight:700;
            text-align:center;
        }

        .trip-summary .sub{
            margin:0 0 9px;
            color:var(--planner-muted);
            font-size:10px;
            line-height:1.45;
        }

        .trip-mini-list{
            min-height:36px;
            max-height:none;
            flex:0 0 auto;
            overflow:visible;
            height:auto;
            margin:0 0 8px;
            padding:0 2px 0 0;
            list-style:none;
        }

        .trip-mini-list .empty-hint{
            padding:10px;
            border:1px dashed #d7dcd4;
            border-radius:10px;
            color:#858d86;
            font-size:9px;
            text-align:center;
            line-height:1.5;
        }

        .trip-mini-list .trip-item{
            display:flex;
            align-items:center;
            gap:7px;
            min-height:31px;
            margin-bottom:5px;
            padding:5px 6px;
            background:#fff;
            border:1px solid #e7e9e3;
            border-radius:10px;
            box-shadow:none;
            cursor:grab;
        }

        .trip-mini-list .trip-item:last-child{margin-bottom:0}

        .trip-mini-list .drag-handle{
            color:#a4aaa4;
            font-size:10px;
        }

        .trip-mini-list .badge{
            width:21px;
            height:21px;
            background:var(--planner-green);
            font-size:9px;
        }

        .trip-mini-list .name{
            font-size:10px;
            line-height:1.25;
        }

        .trip-mini-list .loc{
            font-size:8px;
            color:#8a918a;
        }

        .trip-mini-list .remove{
            font-size:14px;
            color:#a1a8a1;
        }

        .trip-actions{
            flex:none;
            display:flex;
            flex-direction:column;
            gap:5px;
            margin:0;
            margin-top:0;
            padding-bottom:18px;
        }

        .share-trip-btn,
        .clear-btn{
            width:100%;
            min-height:28px;
            border-radius:999px;
            font-family:'Prompt',sans-serif;
            font-size:9px;
            font-weight:600;
        }

        .share-trip-btn{
            border:0;
            background:var(--planner-green);
            color:#fff;
        }

        .clear-btn{
            border:1px solid #dfe3db;
            background:#fff;
            color:#747d75;
        }

        .side-nav{
            order:0;
            flex:none;
            position:relative;
            z-index:30;
            display:flex;
            align-items:center;
            justify-content:space-around;
            min-height:50px;
            padding:0 8px;
            border-bottom:1px solid #e5e7e1;
            background:#fbfaf6;
        }

        .side-nav a{
            position:relative;
            padding:16px 5px 13px;
            text-decoration:none;
            color:#707870;
            font-size:10px;
            font-weight:500;
            white-space:nowrap;
        }

        .side-nav a.active,
        .side-nav a:hover{
            color:var(--planner-green);
        }

        .side-nav a.active::after{
            content:"";
            position:absolute;
            left:8px;
            right:8px;
            bottom:0;
            height:2px;
            border-radius:2px 2px 0 0;
            background:var(--planner-green);
        }

        .discover-panel{
            min-height:0;
            flex:1 1 auto;
            overflow-y:auto;
            overflow-x:hidden;
            overscroll-behavior:contain;
            padding:13px 14px 20px;
        }

        .discover-title{
            display:flex;
            align-items:end;
            justify-content:space-between;
            gap:8px;
            margin-bottom:8px;
        }

        .discover-title h2{
            margin:0;
            font-size:13px;
            font-weight:700;
        }

        .discover-title span{
            color:#8b928b;
            font-size:8px;
        }

        .category-tabs{
            display:flex;
            gap:5px;
            flex-wrap:nowrap;
            overflow-x:auto;
            margin:0 -2px 10px;
            padding:2px;
            scrollbar-width:none;
        }

        .category-tabs::-webkit-scrollbar{display:none}

        .category-tab{
            flex:none;
            border:1px solid #dfe3db;
            background:#fff;
            color:#707870;
            border-radius:999px;
            padding:6px 9px;
            font:500 8px 'Prompt',sans-serif;
            cursor:pointer;
        }

        .category-tab.active{
            background:var(--planner-green);
            border-color:var(--planner-green);
            color:#fff;
            box-shadow:0 4px 10px rgba(32,88,64,.14);
        }

        .place-grid{
            display:grid;
            grid-template-columns:repeat(2,minmax(0,1fr));
            gap:10px;
            overflow:visible;
            padding:0;
        }

        .place-card{
            min-width:0;
            background:#fff;
            border:1px solid #e7e9e2;
            border-radius:13px;
            overflow:hidden;
            box-shadow:0 2px 8px rgba(35,45,37,.035);
        }

        .place-card .photo{
            aspect-ratio:1.15/1;
            background:#d9dfd5 center/cover no-repeat;
        }

        .place-card .info{
            padding:7px 8px 8px;
        }

        .place-card .category-badge{
            display:inline-block;
            margin-bottom:3px;
            padding:2px 5px;
            border-radius:999px;
            background:#edf2eb;
            color:var(--planner-green);
            font-size:7px;
            font-weight:600;
        }

        .place-card h3{
            margin:0 0 2px;
            font-size:10px;
            line-height:1.3;
        }

        .place-card .meta{
            margin:0 0 7px;
            font-size:7px;
            line-height:1.35;
            color:#858d85;
        }

        .place-card button{
            width:100%;
            min-height:25px;
            border:0;
            border-radius:999px;
            background:var(--planner-green);
            color:#fff;
            font:600 8px 'Prompt',sans-serif;
            cursor:pointer;
        }

        .place-card button.added{
            background:#e8eee9;
            color:var(--planner-green);
        }

        .place-card button.added::after{content:"✓ อยู่ในทริปแล้ว · เอาออก"}
        .place-card button:not(.added)::after{content:"+ เพิ่มเข้าทริป"}

        .place-card button{font-size:0}
        .place-card button::after{font-size:8px}

        .no-results{
            grid-column:1/-1;
            padding:25px 8px;
            text-align:center;
            color:#858d85;
            font-size:9px;
        }

        .leaflet-control-zoom{
            margin:10px!important;
            border:0!important;
            box-shadow:0 3px 12px rgba(0,0,0,.12)!important;
        }

        .leaflet-control-zoom a{
            color:var(--planner-green)!important;
        }

        .leaflet-popup-content-wrapper{
            border-radius:12px;
        }

        footer{display:none}

        @media(max-width:900px){
            html,body{overflow:hidden}

            .planner-app{
                height:100vh;
                min-height:0;
                grid-template-columns:1fr;
            }

            .side-resize-handle{
                display:none;
            }

            .map-pane{
                height:48vh;
                min-height:300px;
            }

            .side-pane{
                height:100vh;
                min-height:0;
                overflow:hidden;
                border-left:0;
                border-top:1px solid #e3e5de;
            }

            .trip-summary{
                --trip-summary-height:300px;
                height:var(--trip-summary-height);
                flex:0 0 auto;
                margin:10px;
            }

            .discover-panel{
                min-height:0;
                flex:1 1 auto;
                overflow-y:auto;
                overflow-x:hidden;
                overscroll-behavior:contain;
            }

            .planner-app.side-resizing,
            .planner-app.trip-resizing{
                user-select:none;
            }
        }

        @media(max-width:520px){
            .map-pane{height:42vh;min-height:270px}
            .side-nav a{font-size:9px}
            .place-grid{gap:8px}
        }
    </style>
</head>
<body>

    <div class="planner-app">
        <section class="map-pane" id="map-hero">
            <div id="map"></div>
            <div class="map-resize-handle" id="map-resize-handle" aria-hidden="true"></div>
        </section>

        <div class="side-resize-handle" id="side-resize-handle"
             role="separator" aria-label="ลากเพื่อปรับความกว้างแถบด้านขวา"
             title="ลากเพื่อขยายหรือย่อแถบด้านขวา"></div>

        <aside class="side-pane">
            <nav class="side-nav" aria-label="เมนูหลัก">
                <a href="home.php">หน้าแรก</a>
                <a href="home.php#destinations">สถานที่เที่ยว</a>
                <a href="shops.php">ร้านอาหาร &amp; คาเฟ่</a>
                <a href="trip-planner.php" class="active">วางแผนทริป</a>
            </nav>

            <section class="trip-summary" id="trip-summary">
                <div class="trip-summary-resize-handle" id="trip-summary-resize-handle"
                     role="separator" aria-label="ลากเพื่อปรับความสูงลำดับทริป"
                     title="ลากเพื่อยืดหรือลดกรอบลำดับทริป"></div>
                <div class="trip-summary-head">
                    <h1>ลำดับทริปของคุณ</h1>
                    <span class="trip-summary-count" id="trip-count">0</span>
                </div>
                <p class="sub" id="trip-summary-status">ลากรายการเพื่อจัดลำดับใหม่</p>
                <ul id="trip-list" class="trip-mini-list"></ul>
                <div class="trip-actions">
                    <button class="share-trip-btn" id="share-trip-btn" type="button">▣ แชร์ทริปด้วย QR Code</button>
                    <button class="clear-btn" id="clear-trip" type="button">ล้างทริปทั้งหมด</button>
                </div>
            </section>

            <section class="discover-panel">
                <div class="discover-title">
                    <h2>สถานที่เที่ยว</h2>
                    <span id="result-count">เลือกสถานที่ที่อยากไป</span>
                </div>

                <div class="category-tabs" id="category-tabs">
                    <button type="button" class="category-tab active" data-category="all">ทั้งหมด</button>
                    <button type="button" class="category-tab" data-category="สถานที่เที่ยว">🏞️ สถานที่เที่ยว</button>
                    <button type="button" class="category-tab" data-category="ร้านอาหาร">🍜 ร้านอาหาร</button>
                    <button type="button" class="category-tab" data-category="คาเฟ่">☕ คาเฟ่</button>
                    <button type="button" class="category-tab" data-category="ร้านค้า">🛍️ ร้านค้า</button>
                    <button type="button" class="category-tab" data-category="อื่นๆ">📍 อื่น ๆ</button>
                </div>

                <div class="place-grid" id="place-grid"></div>
            </section>
        </aside>
    </div>

    <!-- QR Code modal -->
    <div class="qr-modal" id="qr-modal" aria-hidden="true">
        <div class="qr-backdrop" id="qr-backdrop"></div>
        <div class="qr-dialog" role="dialog" aria-modal="true" aria-labelledby="qr-title">
            <button class="qr-close" id="qr-close" type="button" aria-label="ปิด">×</button>
            <h2 id="qr-title">สแกนเพื่อดูทริปในมือถือ</h2>
            <p class="qr-subtitle">ลำดับสถานที่ปัจจุบันของคุณจะถูกเปิดในมือถือ และแต่ละสถานที่สามารถกดไป Google Maps ได้</p>

            <div class="qr-box" id="qrcode"></div>

            <div class="qr-order" id="qr-order"></div>

            <div class="qr-url-wrap">
                <input id="share-url" type="text" readonly>
                <button id="copy-share-url" type="button">คัดลอกลิงก์</button>
            </div>

            <p class="qr-note">
                ถ้าเปิดเว็บด้วย <b>localhost</b> โทรศัพท์จะเปิดลิงก์ไม่ได้
                ให้เปิดเว็บผ่าน IP ของคอมใน Wi‑Fi เดียวกัน เช่น
                <b>192.168.1.xxx/project_tip/trip-planner.php</b>
            </p>
        </div>
    </div>

    <footer>
        <div class="wrap">© <?php echo date('Y'); ?> เที่ยวตาก · แพลตฟอร์มวางแผนท่องเที่ยวจังหวัดตาก</div>
    </footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // ---------- Master place data ----------
    // ดึงมาจากฐานข้อมูลจริงผ่าน PHP ด้านบน (ตาราง place) แทนของเดิมที่ hardcode ไว้
    const PLACES = <?php echo json_encode($places_data, JSON_UNESCAPED_UNICODE); ?>;
    const PLACES_BY_ID = Object.fromEntries(PLACES.map(p => [p.id, p]));

    // ทริปที่บันทึกไว้ในฐานข้อมูลของบัญชีนี้ (เรียงตามลำดับที่จัดไว้แล้ว)
    let trip = <?php echo json_encode($trip_data, JSON_UNESCAPED_UNICODE); ?>;
    // ป้องกันข้อมูลซ้ำ/ข้อมูลที่ถูกลบไปแล้วหลุดเข้ามาในหน้า Planner
    trip = [...new Set(trip)].filter(id => PLACES_BY_ID[id]);

    // ถ้ายังไม่เคยมีทริปในฐานข้อมูลเลย แต่เคยเลือกไว้ตอนยังไม่ login (เก็บใน localStorage
    // จากหน้า home.php) ให้ดึงมาใช้ครั้งแรก แล้วเซฟเข้าฐานข้อมูลทันทีเพื่อไม่ให้ข้อมูลหาย
    const STORAGE_KEY = 'takTripPlaces_<?php echo (int)$id_account; ?>';
    let saveTimer = null;
    try{
        const normalizePlannerId = id => {
            id = String(id ?? '').trim();
            return id.startsWith('shop-') ? 'shop:' + id.slice(5) : id;
        };
        const local = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
        const validLocal = [...new Set(local.map(normalizePlannerId).filter(id => PLACES_BY_ID[id]))];

        // ถ้ามาจากปุ่ม “สร้างทริป” ในกล่องทริปของฉัน
        // ให้รายการใน localStorage เป็น source of truth และใช้รายการนั้นแทนทริปเก่าจาก DB
        // ป้องกันกรณี DB มี 6 รายการ แต่กล่องทริปของฉันมี 4 รายการแล้วหน้า planner กลายเป็น 6
        const params = new URLSearchParams(window.location.search);
        const fromDirectory = params.get('from') === 'directory';
        const selectedItems = params.get('items');
        const urlTrip = selectedItems ? [...new Set(decodeURIComponent(selectedItems).split(',').map(normalizePlannerId).filter(x => PLACES_BY_ID[x]))] : [];

        if(fromDirectory){
            // รายการที่กด “สร้างทริป” ส่งมาจากกล่องทริปโดยตรง ใช้รายการนี้เท่านั้น
            // ไม่รวมกับรายการเก่าที่อยู่ในฐานข้อมูล
            trip = urlTrip.length ? urlTrip : validLocal;
            saveTrip();
        }else if(validLocal.length && trip.length === 0){
            // เข้า trip-planner โดยตรง: ถ้ามี localStorage แต่ DB ยังไม่มี ให้กู้รายการขึ้นมา
            trip = validLocal;
            saveTrip();
        }
    }catch(e){ /* ไม่มีข้อมูลเก่า ไม่ต้องทำอะไร */ }

    function saveTrip(){
        // เก็บสำรองไว้ใน localStorage ด้วย เผื่อ request ไปเซิร์ฟเวอร์ล่ม
        localStorage.setItem(STORAGE_KEY, JSON.stringify(trip));

        // ดีบาวซ์การยิง request กันกดรัว ๆ ตอนลากจัดลำดับ
        clearTimeout(saveTimer);
        saveTimer = setTimeout(() => {
            fetch('trip-planner.php?action=save_trip', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ places: trip })
            })
            .then(res => res.json())
            .then(data => {
                if(!data.success) console.error('บันทึกทริปไม่สำเร็จ:', data.message);
            })
            .catch(err => console.error('เชื่อมต่อเซิร์ฟเวอร์ไม่ได้:', err));
        }, 400);
    }

    // ---------- Render: category tabs + cards ----------
    const grid = document.getElementById('place-grid');
    const categoryTabs = document.getElementById('category-tabs');
    let activeCategory = 'all';

    function renderGrid(){
        grid.innerHTML = '';
        const visible = activeCategory === 'all'
            ? PLACES
            : PLACES.filter(place => place.category === activeCategory);

        const resultCount = document.getElementById('result-count');
        if(resultCount){
            resultCount.textContent = visible.length
                ? visible.length + ' รายการ'
                : 'ยังไม่มีรายการ';
        }

        if(visible.length === 0){
            grid.innerHTML = '<div class="no-results">ยังไม่มีข้อมูลในหมวดนี้</div>';
            return;
        }

        visible.forEach(place => {
            const card = document.createElement('div');
            card.className = 'place-card';

            const photo = document.createElement('div');
            photo.className = 'photo' + (place.img ? '' : ' placeholder');
            photo.setAttribute('role','img');
            photo.setAttribute('aria-label', place.name || 'สถานที่');

            if(place.img){
                photo.style.backgroundImage = 'url("' + String(place.img).replace(/"/g, '%22') + '")';
            }else{
                photo.textContent = '📍';
            }
            const info = document.createElement('div');
            info.className = 'info';
            info.innerHTML = `
                <span class="category-badge">${escapeHtml(place.category || 'อื่นๆ')}</span>
                <h3>${escapeHtml(place.name)}</h3>
                <p class="meta">${escapeHtml(place.loc)}</p>
                <button type="button" data-id="${escapeHtml(place.id)}"></button>`;
            card.appendChild(photo);
            card.appendChild(info);
            grid.appendChild(card);
        });
        refreshGridButtons();
    }

    categoryTabs.addEventListener('click', event => {
        const btn = event.target.closest('.category-tab');
        if(!btn) return;
        activeCategory = btn.dataset.category;
        categoryTabs.querySelectorAll('.category-tab').forEach(tab => tab.classList.toggle('active', tab === btn));
        renderGrid();
    });

    function refreshGridButtons(){
        grid.querySelectorAll('button').forEach(btn => {
            btn.classList.toggle('added', trip.includes(btn.dataset.id));
        });
    }

    grid.addEventListener('click', e => {
        const btn = e.target.closest('button');
        if(!btn) return;
        const id = btn.dataset.id;
        if(trip.includes(id)){
            trip = trip.filter(x => x !== id);
        }else{
            trip = [...trip, id];
        }
        saveTrip();
        renderAll();
    });

    // ---------- Render: trip order list ----------
    const tripListEl = document.getElementById('trip-list');
    let lastRenderedTripCount = trip.length;

    function renderTripList(){
        const previousTripCount = lastRenderedTripCount;
        tripListEl.innerHTML = '';

        // ถ้าลบรายการออก ให้กรอบหดกลับมาตามเนื้อหาทันที
        // เพื่อไม่ให้เหลือพื้นที่ว่างจากความสูงเดิมที่เคยขยายไว้
        const shouldShrinkAfterDelete = trip.length < previousTripCount;

        const tripCountEl = document.getElementById('trip-count');
        const tripStatusEl = document.getElementById('trip-summary-status');

        if(tripCountEl) tripCountEl.textContent = trip.length + ' รายการ';
        if(tripStatusEl){
            tripStatusEl.textContent = trip.length
                ? 'ลากรายการเพื่อจัดลำดับใหม่ · แผนที่อัปเดตตามลำดับ'
                : 'ยังไม่ได้เลือกสถานที่ · เพิ่มจากรายการด้านล่างได้เลย';
        }

        if(trip.length === 0){
            tripListEl.innerHTML = '<li class="empty-hint">ยังไม่ได้เลือกสถานที่ — เพิ่มจากรายการด้านล่างได้เลย</li>';
            if(shouldShrinkAfterDelete){
                // ล้าง min-height ที่ค้างจากการลากก่อนคำนวณความสูงใหม่
                tripSummary.style.minHeight = '0px';
                const requiredHeight = getTripSummaryRequiredHeight();
                tripMinHeight = Math.max(150, requiredHeight);
                applyTripSummaryHeight(requiredHeight);
            }
            lastRenderedTripCount = trip.length;
            return;
        }
        trip.forEach((id, index) => {
            const place = PLACES_BY_ID[id];
            const li = document.createElement('li');
            li.className = 'trip-item';
            li.dataset.id = id;
            li.innerHTML = `
                <span class="drag-handle">⠿</span>
                <span class="badge">${index + 1}</span>
                <span class="name">${place.name}<br><span class="loc">${place.loc}</span></span>
                <button class="remove" type="button" aria-label="เอา${place.name}ออกจากทริป">×</button>`;
            tripListEl.appendChild(li);
        });

        const currentHeight = tripSummary.getBoundingClientRect().height;
        const requiredHeight = getTripSummaryRequiredHeight();

        // ไม่เก็บ min-height จากการลากไว้ถาวร
        // เพื่อให้หลังลบรายการแล้วกรอบสามารถหดกลับได้จริง
        tripSummary.style.minHeight = '0px';
        tripMinHeight = Math.max(150, requiredHeight);

        // เพิ่มรายการ: ขยายเมื่อพื้นที่ไม่พอ
        if(currentHeight < requiredHeight){
            applyTripSummaryHeight(requiredHeight);
        // ลบรายการ หรือไม่มีความสูงที่ผู้ใช้ปรับเองที่ใช้งานได้:
        // ให้กรอบหดตามเนื้อหาทันที ไม่ปล่อยค่าความสูงเก่ามาค้าง
        }else if((shouldShrinkAfterDelete || !restoredManualTripHeight) && currentHeight > requiredHeight){
            tripMinHeight = Math.max(150, requiredHeight);
            tripSummary.style.minHeight = '0px';
            applyTripSummaryHeight(requiredHeight);
        }

        lastRenderedTripCount = trip.length;

        // กันกรอบเตี้ยกว่ารายการจริง โดยเฉพาะกรณีมีความสูงเก่าค้างใน localStorage
        // ให้ browser คำนวณ layout ให้เสร็จก่อน แล้วค่อยปรับความสูงอีกครั้ง
        requestAnimationFrame(() => {
            if(resizingTripSummary) return;
            const requiredNow = getTripSummaryRequiredHeight();
            const currentNow = tripSummary.getBoundingClientRect().height;
            if(currentNow < requiredNow){
                applyTripSummaryHeight(requiredNow);
            }
        });
    }

    tripListEl.addEventListener('click', e => {
        const btn = e.target.closest('.remove');
        if(!btn) return;
        const id = btn.closest('.trip-item').dataset.id;
        trip = trip.filter(x => x !== id);
        saveTrip();
        renderAll();
    });

    document.getElementById('clear-trip').addEventListener('click', () => {
        trip = [];
        saveTrip();
        renderAll();
    });

    // Drag-to-reorder
    Sortable.create(tripListEl, {
        animation: 150,
        handle: '.drag-handle',
        onEnd(){
            trip = [...tripListEl.querySelectorAll('.trip-item')].map(li => li.dataset.id);
            saveTrip();
            renderBadgesOnly();
            updateMap();
        }
    });

    function renderBadgesOnly(){
        tripListEl.querySelectorAll('.trip-item').forEach((li, index) => {
            li.querySelector('.badge').textContent = index + 1;
        });
    }

    // ---------- Map ----------
    const map = L.map('map', { scrollWheelZoom: false, zoomControl: true }).setView([16.95, 98.85], 9);


    // ---------- Resize map vertically ----------
    // ลากแถบด้านล่างของแผนที่ขึ้น/ลง
    const mapHero = document.getElementById('map-hero');
    const mapElement = document.getElementById('map');
    const resizeHandle = document.getElementById('map-resize-handle');

    let resizingMap = false;
    let resizeStartY = 0;
    let resizeStartHeight = 0;

    function applyMapHeight(height){
        const safeHeight = Math.round(height);

        // เปลี่ยนทั้งกล่องและ #map โดยตรง เพื่อให้ Leaflet เห็นขนาดใหม่แน่นอน
        mapHero.style.height = safeHeight + 'px';
        mapElement.style.height = safeHeight + 'px';

        // รอให้ browser layout เสร็จแล้วค่อยสั่ง Leaflet คำนวณใหม่
        requestAnimationFrame(() => {
            map.invalidateSize({ pan: false, animate: false });
        });
    }

    function resizeMapTo(clientY){
        const delta = clientY - resizeStartY;
        const minHeight = 180;
        const maxHeight = Math.max(
            minHeight,
            Math.floor(window.innerHeight * 0.85)
        );

        const newHeight = Math.min(
            maxHeight,
            Math.max(minHeight, resizeStartHeight + delta)
        );

        applyMapHeight(newHeight);
    }

    resizeHandle.addEventListener('pointerdown', (event) => {
        event.preventDefault();

        resizingMap = true;
        resizeStartY = event.clientY;
        resizeStartHeight = mapHero.getBoundingClientRect().height;

        mapHero.classList.add('resizing');
        document.body.classList.add('map-resizing');

        resizeHandle.setPointerCapture(event.pointerId);
    });

    resizeHandle.addEventListener('pointermove', (event) => {
        if(resizingMap){
            resizeMapTo(event.clientY);
        }
    });

    function stopMapResize(event){
        if(!resizingMap) return;

        resizingMap = false;
        mapHero.classList.remove('resizing');
        document.body.classList.remove('map-resizing');

        if(event && resizeHandle.hasPointerCapture(event.pointerId)){
            resizeHandle.releasePointerCapture(event.pointerId);
        }

        // ให้ Leaflet วาด tile ใหม่หลังจบการลาก
        setTimeout(() => {
            map.invalidateSize({ pan: false, animate: false });
        }, 50);
    }

    resizeHandle.addEventListener('pointerup', stopMapResize);
    resizeHandle.addEventListener('pointercancel', stopMapResize);
    resizeHandle.addEventListener('lostpointercapture', () => {
        if(resizingMap){
            resizingMap = false;
            mapHero.classList.remove('resizing');
            document.body.classList.remove('map-resizing');
            map.invalidateSize({ pan: false, animate: false });
        }
    });

    // ถ้ามีการเปลี่ยนขนาดจาก CSS/หน้าต่าง ให้ Leaflet ตามด้วย
    const mapResizeObserver = new ResizeObserver(() => {
        if(!resizingMap){
            const h = mapHero.getBoundingClientRect().height;
            mapElement.style.height = Math.round(h) + 'px';
            requestAnimationFrame(() => {
                map.invalidateSize({ pan: false, animate: false });
            });
        }
    });

    mapResizeObserver.observe(mapHero);


    // ---------- Resize sidebar + trip-order box ----------
    // ลากเส้นกลางเพื่อขยาย/ย่อ Sidebar ด้านขวา
    const plannerApp = document.querySelector('.planner-app');
    const sideResizeHandle = document.getElementById('side-resize-handle');
    const tripSummary = document.getElementById('trip-summary');
    const tripSummaryResizeHandle = document.getElementById('trip-summary-resize-handle');
    const sidePane = document.querySelector('.side-pane');

    let resizingSide = false;
    let sideStartX = 0;
    let sideStartWidth = 340;

    function applySideWidth(width){
        const minWidth = 280;
        const maxWidth = Math.min(620, Math.floor(window.innerWidth * 0.48));
        const safeWidth = Math.round(Math.max(minWidth, Math.min(maxWidth, width)));

        plannerApp.style.setProperty('--side-width', safeWidth + 'px');
        localStorage.setItem('takPlannerSideWidth', String(safeWidth));

        requestAnimationFrame(() => {
            map.invalidateSize({ pan:false, animate:false });
        });
    }

    sideResizeHandle.addEventListener('pointerdown', event => {
        if(window.innerWidth <= 900) return;
        event.preventDefault();
        resizingSide = true;
        sideStartX = event.clientX;
        sideStartWidth = parseFloat(getComputedStyle(plannerApp).getPropertyValue('--side-width')) || 340;
        plannerApp.classList.add('side-resizing');
        sideResizeHandle.setPointerCapture(event.pointerId);
    });

    sideResizeHandle.addEventListener('pointermove', event => {
        if(!resizingSide) return;

        // ลากไปทางซ้าย = Sidebar กว้างขึ้น
        applySideWidth(sideStartWidth - (event.clientX - sideStartX));
    });

    function stopSideResize(event){
        if(!resizingSide) return;
        resizingSide = false;
        plannerApp.classList.remove('side-resizing');

        if(event && sideResizeHandle.hasPointerCapture(event.pointerId)){
            sideResizeHandle.releasePointerCapture(event.pointerId);
        }

        setTimeout(() => map.invalidateSize({pan:false, animate:false}), 40);
    }

    sideResizeHandle.addEventListener('pointerup', stopSideResize);
    sideResizeHandle.addEventListener('pointercancel', stopSideResize);
    sideResizeHandle.addEventListener('dblclick', () => applySideWidth(340));

    // ลากแถบด้านล่างของกรอบ “ลำดับทริปของคุณ” เพื่อเพิ่มพื้นที่รายการ
    let resizingTripSummary = false;
    let tripStartY = 0;
    let tripStartHeight = 300;
    let tripMinHeight = 0;
    let tripResizeTargetHeight = 300;
    let tripResizeFrame = 0;
    // ความสูงที่บันทึกไว้จะใช้เฉพาะกรณีผู้ใช้ลากปรับเองจริง ๆ
    // ป้องกันค่าความสูงเก่าจากเวอร์ชันก่อนทำให้กรอบค้างสูงเมื่อเหลือรายการน้อย
    let restoredManualTripHeight = false;

    // คำนวณขั้นต่ำเฉพาะตอนจำเป็น ไม่ทำซ้ำทุก pointermove
    function getTripSummaryRequiredHeight(){
        const cs = getComputedStyle(tripSummary);
        const padding = (parseFloat(cs.paddingTop) || 0) + (parseFloat(cs.paddingBottom) || 0);
        const borders = (parseFloat(cs.borderTopWidth) || 0) + (parseFloat(cs.borderBottomWidth) || 0);
        const head = tripSummary.querySelector('.trip-summary-head');
        const sub = tripSummary.querySelector('.trip-summary .sub');
        const actions = tripSummary.querySelector('.trip-actions');
        const headH = head ? head.getBoundingClientRect().height : 0;
        const subH = sub ? sub.getBoundingClientRect().height : 0;
        const listH = tripListEl ? tripListEl.scrollHeight : 0;
        const listMargin = tripListEl ? (parseFloat(getComputedStyle(tripListEl).marginBottom) || 0) : 0;
        const actionsH = actions ? actions.getBoundingClientRect().height : 0;
        return Math.ceil(padding + borders + headH + subH + listH + listMargin + actionsH + 18);
    }

    function applyTripSummaryHeight(height, save = true){
        // ไม่ผูก minHeight กับค่าที่ค้างจากการลากครั้งก่อน
        // ขั้นต่ำจริงจะถูกคุมตอน pointerdown เท่านั้น
        const safeHeight = Math.round(Math.max(60, Math.min(10000, height)));
        tripSummary.style.setProperty('--trip-summary-height', safeHeight + 'px');
        tripSummary.style.height = safeHeight + 'px';
        if(save) localStorage.setItem('takPlannerTripHeight', String(safeHeight));
    }

    tripSummaryResizeHandle.addEventListener('pointerdown', event => {
        event.preventDefault();
        resizingTripSummary = true;
        tripStartY = event.clientY;
        tripStartHeight = tripSummary.getBoundingClientRect().height;
        tripResizeTargetHeight = tripStartHeight;
        // ลากได้อิสระ แต่ไม่ให้ย่อจนเนื้อหา/ปุ่มทับกัน
        tripMinHeight = 150;
        tripSummary.style.minHeight = '0px';

        tripSummary.classList.add('trip-resizing');
        plannerApp.classList.add('trip-resizing');
        tripSummaryResizeHandle.setPointerCapture(event.pointerId);
    });

    tripSummaryResizeHandle.addEventListener('pointermove', event => {
        if(!resizingTripSummary) return;
        // เก็บตำแหน่งไว้ก่อน แล้วให้ browser วาดแค่ 1 ครั้งต่อ frame
        tripResizeTargetHeight = tripStartHeight + (event.clientY - tripStartY);
        if(tripResizeFrame) return;
        tripResizeFrame = requestAnimationFrame(() => {
            tripResizeFrame = 0;
            // ระหว่างลากก็ห้ามต่ำกว่าพื้นที่ที่เนื้อหาต้องใช้จริง
            const safeHeight = Math.round(Math.max(150, Math.min(10000, tripResizeTargetHeight)));
            tripSummary.style.setProperty('--trip-summary-height', safeHeight + 'px');
            tripSummary.style.height = safeHeight + 'px';
        });
    });

    function stopTripSummaryResize(event){
        if(!resizingTripSummary) return;

        if(tripResizeFrame){
            cancelAnimationFrame(tripResizeFrame);
            tripResizeFrame = 0;
        }
        const finalSafeHeight = Math.round(Math.max(150, Math.min(10000, tripResizeTargetHeight)));
        tripSummary.style.setProperty('--trip-summary-height', finalSafeHeight + 'px');
        tripSummary.style.height = finalSafeHeight + 'px';
        localStorage.setItem('takPlannerTripHeight', String(finalSafeHeight));
        localStorage.setItem('takPlannerTripHeightManual', '1');
        localStorage.setItem('takPlannerTripHeightCount', String(trip.length));
        restoredManualTripHeight = true;
        // min-height ใช้เฉพาะระหว่างลากเท่านั้น ไม่ให้ค่าค้างหลังปล่อยเมาส์
        tripSummary.style.minHeight = '0px';
        resizingTripSummary = false;
        tripSummary.classList.remove('trip-resizing');
        plannerApp.classList.remove('trip-resizing');

        if(event && tripSummaryResizeHandle.hasPointerCapture(event.pointerId)){
            tripSummaryResizeHandle.releasePointerCapture(event.pointerId);
        }

    }

    tripSummaryResizeHandle.addEventListener('pointerup', stopTripSummaryResize);
    tripSummaryResizeHandle.addEventListener('pointercancel', stopTripSummaryResize);
    tripSummaryResizeHandle.addEventListener('dblclick', () => {
        tripMinHeight = 60;
        tripSummary.style.minHeight = '0px';
        applyTripSummaryHeight(300);
    });

    // จำขนาดที่ผู้ใช้ปรับไว้ เมื่อกลับเข้าหน้านี้จะไม่ต้องลากใหม่
    try{
        const savedSideWidth = parseFloat(localStorage.getItem('takPlannerSideWidth'));
        const savedTripHeight = parseFloat(localStorage.getItem('takPlannerTripHeight'));
        const savedTripHeightManual = localStorage.getItem('takPlannerTripHeightManual') === '1';
        const savedTripHeightCount = parseInt(localStorage.getItem('takPlannerTripHeightCount') || '', 10);

        if(Number.isFinite(savedSideWidth) && window.innerWidth > 900){
            applySideWidth(savedSideWidth);
        }

        // ใช้ความสูงที่บันทึกไว้ต่อเมื่อเป็นค่าที่ผู้ใช้ลากตั้งเอง
        // และจำนวนรายการยังเท่าเดิมเท่านั้น ถ้ารายการเปลี่ยนให้คำนวณใหม่
        if(Number.isFinite(savedTripHeight) && savedTripHeightManual && savedTripHeightCount === trip.length){
            applyTripSummaryHeight(savedTripHeight, false);
            restoredManualTripHeight = true;
        }else{
            // ล้างค่าความสูงเก่าจากเวอร์ชันก่อน เพื่อไม่ให้กรอบค้างสูง
            localStorage.removeItem('takPlannerTripHeight');
            localStorage.removeItem('takPlannerTripHeightManual');
            localStorage.removeItem('takPlannerTripHeightCount');
        }
    }catch(e){}

    window.addEventListener('resize', () => {
        if(window.innerWidth <= 900){
            plannerApp.style.removeProperty('--side-width');
        }
        map.invalidateSize({pan:false, animate:false});
    });


    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(map);

    let markers = [];
    let routeLine = null;

    function numberedIcon(n){
        return L.divIcon({
            className: '',
            html: `<div class="pin"><span>${n}</span></div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 28]
        });
    }

    function updateMap(){
        markers.forEach(m => map.removeLayer(m));
        markers = [];
        if(routeLine){ map.removeLayer(routeLine); routeLine = null; }

        if(trip.length === 0) return;

        const latlngs = trip.map((id, index) => {
            const place = PLACES_BY_ID[id];
            const marker = L.marker([place.lat, place.lng], { icon: numberedIcon(index + 1) })
                .addTo(map)
                .bindPopup(`<b>${index + 1}. ${place.name}</b><br>${place.loc}`);
            markers.push(marker);
            return [place.lat, place.lng];
        });

        if(latlngs.length > 1){
            routeLine = L.polyline(latlngs, { color: '#3f8f5f', weight: 4, opacity: 0.85 }).addTo(map);
            map.fitBounds(routeLine.getBounds(), { padding: [30, 30] });
        }else{
            map.setView(latlngs[0], 12);
        }
    }

    function renderAll(){
        renderGrid();
        renderTripList();
        updateMap();
    }

    // ---------- Share trip by QR ----------
    const shareTripBtn = document.getElementById('share-trip-btn');
    const qrModal = document.getElementById('qr-modal');
    const qrBackdrop = document.getElementById('qr-backdrop');
    const qrClose = document.getElementById('qr-close');
    const qrCodeEl = document.getElementById('qrcode');
    const qrOrderEl = document.getElementById('qr-order');
    const shareUrlEl = document.getElementById('share-url');
    const copyShareUrlBtn = document.getElementById('copy-share-url');

    // QR จะพก place_key ตามลำดับไปด้วยโดยตรง
    // จึงไม่ต้องมี trip-share-config.php และไม่ต้องพึ่ง save-trip.php
    function getShareUrl(){
        const url = new URL('trip-view.php', window.location.href);
        url.searchParams.set('places', trip.join(','));
        return url.href;
    }

    function openQrModal(){
        if(trip.length === 0){
            alert('กรุณาเลือกสถานที่อย่างน้อย 1 แห่งก่อนสร้าง QR Code');
            return;
        }

        const shareUrl = getShareUrl();

        qrCodeEl.innerHTML = '';
        new QRCode(qrCodeEl, {
            text: shareUrl,
            width: 200,
            height: 200,
            correctLevel: QRCode.CorrectLevel.M
        });

        qrOrderEl.innerHTML = trip.map((id, index) => {
            const place = PLACES_BY_ID[id];
            return `
                <div class="qr-place">
                    <span class="qr-number">${index + 1}</span>
                    <span>${escapeHtml(place.name)}</span>
                </div>
            `;
        }).join('');

        shareUrlEl.value = shareUrl;
        qrModal.classList.add('open');
        qrModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    }

    function closeQrModal(){
        qrModal.classList.remove('open');
        qrModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    }

    function escapeHtml(value){
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&':'&amp;',
            '<':'&lt;',
            '>':'&gt;',
            '"':'&quot;',
            "'":'&#039;'
        }[char]));
    }

    shareTripBtn.addEventListener('click', openQrModal);
    qrClose.addEventListener('click', closeQrModal);
    qrBackdrop.addEventListener('click', closeQrModal);

    document.addEventListener('keydown', event => {
        if(event.key === 'Escape' && qrModal.classList.contains('open')){
            closeQrModal();
        }
    });

    copyShareUrlBtn.addEventListener('click', async () => {
        try{
            await navigator.clipboard.writeText(shareUrlEl.value);
            copyShareUrlBtn.textContent = 'คัดลอกแล้ว ✓';
            setTimeout(() => copyShareUrlBtn.textContent = 'คัดลอกลิงก์', 1500);
        }catch(e){
            shareUrlEl.select();
            document.execCommand('copy');
            copyShareUrlBtn.textContent = 'คัดลอกแล้ว ✓';
            setTimeout(() => copyShareUrlBtn.textContent = 'คัดลอกลิงก์', 1500);
        }
    });

    renderAll();
</script>

<?php include("global-search.php"); ?></body>
</html>