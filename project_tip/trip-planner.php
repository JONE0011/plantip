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

// บันทึกลำดับทริปในไฟล์นี้เลย ไม่ต้องใช้ save-trip.php
// รับ JSON: {"places":["place_key1","place_key2",...]}
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'save_trip'){
    header('Content-Type: application/json; charset=utf-8');
    $input = json_decode(file_get_contents('php://input'), true);
    $places = isset($input['places']) && is_array($input['places']) ? $input['places'] : [];

    // ทำความสะอาด id และตัดค่าซ้ำ โดยคงลำดับเดิม
    $clean = [];
    foreach($places as $placeKey){
        $placeKey = trim((string)$placeKey);
        if($placeKey !== '' && !in_array($placeKey, $clean, true)) $clean[] = $placeKey;
    }

    mysqli_begin_transaction($connect);
    try{
        $del = mysqli_prepare($connect, "DELETE FROM trip_place WHERE id_account = ?");
        mysqli_stmt_bind_param($del, 'i', $id_account);
        if(!mysqli_stmt_execute($del)) throw new Exception(mysqli_stmt_error($del));
        mysqli_stmt_close($del);

        $find = mysqli_prepare($connect, "SELECT id_place FROM place WHERE place_key = ? LIMIT 1");
        $ins  = mysqli_prepare($connect, "INSERT INTO trip_place (id_account, id_place, order_no) VALUES (?, ?, ?)");
        if(!$find || !$ins) throw new Exception(mysqli_error($connect));

        $saved = 0;
        foreach($clean as $index => $placeKey){
            mysqli_stmt_bind_param($find, 's', $placeKey);
            if(!mysqli_stmt_execute($find)) throw new Exception(mysqli_stmt_error($find));
            $result = mysqli_stmt_get_result($find);
            $row = $result ? mysqli_fetch_assoc($result) : null;
            if(!$row) continue;

            $id_place = (int)$row['id_place'];
            $order_no = $index + 1;
            mysqli_stmt_bind_param($ins, 'iii', $id_account, $id_place, $order_no);
            if(!mysqli_stmt_execute($ins)) throw new Exception(mysqli_stmt_error($ins));
            $saved++;
        }

        mysqli_stmt_close($find);
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
$query_places = "SELECT place_key, name_place, location_place, lat_place, lng_place, image_place FROM place ORDER BY id_place";
$result_places = mysqli_query($connect, $query_places);
while($row = mysqli_fetch_assoc($result_places)){
    $places_data[] = [
        'id'   => $row['place_key'],
        'name' => $row['name_place'],
        'loc'  => $row['location_place'],
        'lat'  => (float) $row['lat_place'],
        'lng'  => (float) $row['lng_place'],
        'img'  => $row['image_place'],
    ];
}

// ดึงทริปที่บันทึกไว้ของบัญชีนี้ เรียงตามลำดับที่จัดไว้
$trip_data = [];
$query_trip = "SELECT p.place_key
                FROM trip_place tp
                JOIN place p ON p.id_place = tp.id_place
                WHERE tp.id_account = $id_account
                ORDER BY tp.order_no";
$result_trip = mysqli_query($connect, $query_trip);
while($row = mysqli_fetch_assoc($result_trip)){
    $trip_data[] = $row['place_key'];
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
</head>
<body>

    <!-- Map at the top, matching the reference layout -->
    <section class="map-hero" id="map-hero">
        <div id="map"></div>
        <div
            class="map-resize-handle"
            id="map-resize-handle"
            role="separator"
            aria-label="ลากเพื่อปรับความสูงแผนที่"
            title="ลากขึ้นลงเพื่อปรับความสูงแผนที่">
        </div>
    </section>

    <nav class="nav">
        <div class="wrap">
            <ul class="nav-links">
                <li><a href="home.php">หน้าแรก</a></li>
                <li><a href="home.php#destinations">สถานที่เที่ยว</a></li>
                <li><a href="shops.php">ร้านอาหาร &amp; คาเฟ่</a></li>
                <li><a href="trip-planner.php" class="active">วางแผนทริป</a></li>
            </ul>

            <div class="nav-actions">
                <span class="who">สวัสดี <?php echo htmlspecialchars($username_account); ?></span>
                <a href="trip-planner.php?logout=1" class="logout-link">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <main class="wrap">
        <div class="dashboard">

            <section class="panel-card places-panel">
                <h2>สถานที่เที่ยว</h2>
                <p class="sub">เลือกสถานที่ที่อยากไป แล้วเพิ่มเข้าทริปของคุณ</p>
                <div class="place-grid" id="place-grid"></div>
            </section>

            <aside class="panel-card trip-panel">
                <h2>ลำดับทริปของคุณ</h2>
                <p class="sub">ลากรายการเพื่อจัดลำดับใหม่</p>
                <ul id="trip-list"></ul>

                <div class="trip-actions">
                    <button class="share-trip-btn" id="share-trip-btn" type="button">
                        ▣ แชร์ทริปด้วย QR Code
                    </button>
                    <button class="clear-btn" id="clear-trip">ล้างทริปทั้งหมด</button>
                </div>
            </aside>

        </div>
    </main>

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

    // ถ้ายังไม่เคยมีทริปในฐานข้อมูลเลย แต่เคยเลือกไว้ตอนยังไม่ login (เก็บใน localStorage
    // จากหน้า home.php) ให้ดึงมาใช้ครั้งแรก แล้วเซฟเข้าฐานข้อมูลทันทีเพื่อไม่ให้ข้อมูลหาย
    const STORAGE_KEY = 'takTripPlaces';
    if(trip.length === 0){
        try{
            const local = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
            trip = local.filter(id => PLACES_BY_ID[id]);
            if(trip.length > 0) saveTrip();
        }catch(e){ /* ไม่มีข้อมูลเก่า ไม่ต้องทำอะไร */ }
    }

    let saveTimer = null;
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

    // ---------- Render: all-places grid ----------
    const grid = document.getElementById('place-grid');
    PLACES.forEach(place => {
        const card = document.createElement('div');
        card.className = 'place-card';
        card.innerHTML = `
            <div class="photo" style="background-image:url('${place.img}')"></div>
            <div class="info">
                <h3>${place.name}</h3>
                <p class="meta">${place.loc}</p>
                <button type="button" data-id="${place.id}"></button>
            </div>`;
        grid.appendChild(card);
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

    function renderTripList(){
        tripListEl.innerHTML = '';
        if(trip.length === 0){
            tripListEl.innerHTML = '<li class="empty-hint">ยังไม่ได้เลือกสถานที่ — เพิ่มจากรายการทางซ้ายได้เลย</li>';
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
        refreshGridButtons();
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

</body>
</html>