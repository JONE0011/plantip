<?php
session_start();
$open_connect = 1;
require('connect.php');

if(!isset($_SESSION['id_account'])){
    die(header('Location: form-login.php'));
}

$id_account = (int) $_SESSION['id_account'];

$name_shop        = trim($_POST['name_shop'] ?? '');
$category_shop    = trim($_POST['category_shop'] ?? '');
$address_shop     = trim($_POST['address_shop'] ?? '');
$description_shop = trim($_POST['description_shop'] ?? '');
$lat_shop         = $_POST['lat_shop'] ?? '';
$lng_shop         = $_POST['lng_shop'] ?? '';

$allowed_categories = ['ร้านอาหาร', 'คาเฟ่', 'ร้านค้า', 'อื่นๆ'];

// ตรวจข้อมูลจำเป็น: ชื่อร้าน, หมวดหมู่ที่อยู่ในลิสต์ที่อนุญาต, และพิกัดต้องเป็นตัวเลข
if(
    $name_shop === '' ||
    !in_array($category_shop, $allowed_categories, true) ||
    !is_numeric($lat_shop) || !is_numeric($lng_shop)
){
    die(header('Location: add-shop.php?error=missing'));
}

$lat_shop = (float) $lat_shop;
$lng_shop = (float) $lng_shop;

// ---------- อัปโหลดรูป (ถ้ามีแนบมา) ----------
$image_path = null;
if(isset($_FILES['image_shop']) && $_FILES['image_shop']['error'] === UPLOAD_ERR_OK){
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($_FILES['image_shop']['name'], PATHINFO_EXTENSION));

    if(!in_array($ext, $allowed_ext, true)){
        die(header('Location: add-shop.php?error=upload'));
    }

    $upload_dir = 'images_shop/';
    if(!is_dir($upload_dir)){
        mkdir($upload_dir, 0755, true);
    }

    $filename = uniqid('shop_', true) . '.' . $ext;
    $target_path = $upload_dir . $filename;

    if(move_uploaded_file($_FILES['image_shop']['tmp_name'], $target_path)){
        $image_path = $target_path;
    }else{
        die(header('Location: add-shop.php?error=upload'));
    }
}elseif(isset($_FILES['image_shop']) && $_FILES['image_shop']['error'] !== UPLOAD_ERR_NO_FILE){
    // มีการแนบไฟล์แต่เกิดข้อผิดพลาดระหว่างอัปโหลด (เช่นไฟล์ใหญ่เกินกำหนด)
    die(header('Location: add-shop.php?error=upload'));
}

// ---------- บันทึกลงฐานข้อมูล ----------
$name_shop_esc        = mysqli_real_escape_string($connect, $name_shop);
$category_shop_esc    = mysqli_real_escape_string($connect, $category_shop);
$address_shop_esc     = mysqli_real_escape_string($connect, $address_shop);
$description_shop_esc = mysqli_real_escape_string($connect, $description_shop);
$image_path_sql        = $image_path !== null ? "'" . mysqli_real_escape_string($connect, $image_path) . "'" : "NULL";

$query_insert = "INSERT INTO shop
    (id_account, name_shop, category_shop, description_shop, address_shop, lat_shop, lng_shop, image_shop)
    VALUES
    ('$id_account', '$name_shop_esc', '$category_shop_esc', '$description_shop_esc', '$address_shop_esc', '$lat_shop', '$lng_shop', $image_path_sql)";

$result = mysqli_query($connect, $query_insert);

if($result){
    die(header('Location: shops.php?added=1'));
}else{
    die(header('Location: add-shop.php?error=db'));
}