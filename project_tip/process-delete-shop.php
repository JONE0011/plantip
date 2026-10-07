<?php
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
$open_connect = 1;
require('connect.php');

if(!isset($_SESSION['id_account'])){
    header('Location: form-login.php');
    exit;
}

$id = (int)($_POST['id_shop'] ?? 0);
$uid = (int)$_SESSION['id_account'];

if($id <= 0){
    header('Location: home.php?error=delete');
    exit;
}

$q = mysqli_query($connect, "SELECT id_shop, image_shop FROM shop WHERE id_shop=$id AND id_account=$uid LIMIT 1");
$shop = $q ? mysqli_fetch_assoc($q) : null;

if(!$shop){
    header('Location: home.php?error=delete');
    exit;
}

foreach(array_filter(explode('|', (string)$shop['image_shop'])) as $img){
    $path = __DIR__ . '/' . ltrim($img, '/');
    if(is_file($path)) @unlink($path);
}

if(!mysqli_query($connect, "DELETE FROM shop WHERE id_shop=$id AND id_account=$uid LIMIT 1")){
    header('Location: home.php?error=delete');
    exit;
}

header('Location: home.php?deleted_shop=1');
exit;
?>