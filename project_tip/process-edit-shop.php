<?php
session_start();
$open_connect = 1;
require('connect.php');

if(!isset($_SESSION['id_account'])){
    header('Location: form-login.php');
    exit;
}

$id_account=(int)$_SESSION['id_account'];
$id_shop=(int)($_POST['id_shop'] ?? 0);
$name=trim($_POST['name_shop'] ?? '');
$category=trim($_POST['category_shop'] ?? '');
$address=trim($_POST['address_shop'] ?? '');
$description=trim($_POST['description_shop'] ?? '');
$lat=$_POST['lat_shop'] ?? '';
$lng=$_POST['lng_shop'] ?? '';
$allowed=['ร้านอาหาร','คาเฟ่','ร้านค้า','อื่นๆ'];

if($id_shop<=0 || $name==='' || !in_array($category,$allowed,true) || !is_numeric($lat) || !is_numeric($lng)){
    header('Location: shops.php?error=edit');
    exit;
}

$check=mysqli_prepare($connect,"SELECT image_shop FROM shop WHERE id_shop=? AND id_account=? AND status_shop=1 LIMIT 1");
mysqli_stmt_bind_param($check,'ii',$id_shop,$id_account);
mysqli_stmt_execute($check);
$row=mysqli_fetch_assoc(mysqli_stmt_get_result($check));
mysqli_stmt_close($check);
if(!$row){
    header('Location: shops.php');
    exit;
}

$image_path=$row['image_shop'];
if(isset($_FILES['image_shop']) && $_FILES['image_shop']['error']===UPLOAD_ERR_OK){
    $ext=strtolower(pathinfo($_FILES['image_shop']['name'],PATHINFO_EXTENSION));
    if(!in_array($ext,['jpg','jpeg','png'],true)){ header('Location: edit-shop.php?id='.$id_shop.'&error=upload'); exit; }
    $dir='images_shop/';
    if(!is_dir($dir)) mkdir($dir,0755,true);
    $filename=uniqid('shop_',true).'.'.$ext;
    $target=$dir.$filename;
    if(!move_uploaded_file($_FILES['image_shop']['tmp_name'],$target)){ header('Location: edit-shop.php?id='.$id_shop.'&error=upload'); exit; }
    if($image_path && strpos($image_path,'images_shop/')===0 && is_file($image_path)) @unlink($image_path);
    $image_path=$target;
}

$stmt=mysqli_prepare($connect,"UPDATE shop SET name_shop=?, category_shop=?, description_shop=?, address_shop=?, lat_shop=?, lng_shop=?, image_shop=? WHERE id_shop=? AND id_account=? AND status_shop=1");
mysqli_stmt_bind_param($stmt,'ssssddsii',$name,$category,$description,$address,$lat,$lng,$image_path,$id_shop,$id_account);
$ok=mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header($ok ? 'Location: shops.php?edited=1' : 'Location: edit-shop.php?id='.$id_shop.'&error=db');
exit;
?>