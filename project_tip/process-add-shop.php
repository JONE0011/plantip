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

// ---------- อัปโหลดรูปหลายรูป (ถ้ามีแนบมา) ----------
$image_paths = [];
if(isset($_FILES['image_shop'])){
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $upload_dir = 'images_shop/';
    if(!is_dir($upload_dir)){
        mkdir($upload_dir, 0755, true);
    }

    $files = $_FILES['image_shop'];
    $count = is_array($files['name']) ? count($files['name']) : 0;
    for($i = 0; $i < $count; $i++){
        if(($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
        if(($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK){
            die(header('Location: add-shop.php?error=upload'));
        }

        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        if(!in_array($ext, $allowed_ext, true)){
            die(header('Location: add-shop.php?error=upload'));
        }

        $filename = uniqid('shop_', true) . '.' . $ext;
        $target_path = $upload_dir . $filename;
        if(!move_uploaded_file($files['tmp_name'][$i], $target_path)){
            die(header('Location: add-shop.php?error=upload'));
        }
        $image_paths[] = $target_path;
    }
}

// เก็บหลายรูปไว้ในช่องเดิม โดยคั่นด้วย | เพื่อไม่ต้องแก้โครงสร้างฐานข้อมูล
$image_path = !empty($image_paths) ? implode('|', $image_paths) : null;

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