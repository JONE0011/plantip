<?php
session_start();$open_connect=1;require('connect.php');
if(!isset($_SESSION['id_account'])){header('Location:form-login.php');exit;}
$id=(int)($_POST['id_shop']??0);$uid=(int)$_SESSION['id_account'];
$q=mysqli_query($connect,"SELECT * FROM shop WHERE id_shop=$id AND id_account=$uid LIMIT 1");
$old=$q?mysqli_fetch_assoc($q):null;
if(!$old){http_response_code(403);die('คุณไม่มีสิทธิ์แก้ไขร้านนี้');}

$name=trim($_POST['name_shop']??'');$cat=trim($_POST['category_shop']??'');$addr=trim($_POST['address_shop']??'');$desc=trim($_POST['description_shop']??'');
$lat=$_POST['lat_shop']??'';$lng=$_POST['lng_shop']??'';$allowed=['ร้านอาหาร','คาเฟ่','ร้านค้า','อื่นๆ'];
if($name===''||!in_array($cat,$allowed,true)||!is_numeric($lat)||!is_numeric($lng)){header("Location:edit-shop.php?id=$id&error=missing");exit;}

$old_images=array_values(array_filter(explode('|',(string)$old['image_shop'])));
$remove=array_map('strval',$_POST['remove_images']??[]);
$keep=[];
foreach($old_images as $img){
    if(in_array($img,$remove,true)){
        $path=__DIR__.'/'.ltrim($img,'/');
        if(is_file($path)) @unlink($path);
    }else $keep[]=$img;
}

$dir='images_shop/';
if(!is_dir($dir))mkdir($dir,0755,true);
$files=$_FILES['image_shop']??null;
if($files && is_array($files['name'])){
    $count=count($files['name']);
    for($i=0;$i<$count;$i++){
        if(($files['error'][$i]??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)continue;
        if(($files['error'][$i]??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK){header("Location:edit-shop.php?id=$id&error=upload");exit;}
        $ext=strtolower(pathinfo($files['name'][$i],PATHINFO_EXTENSION));
        if(!in_array($ext,['jpg','jpeg','png'],true)){header("Location:edit-shop.php?id=$id&error=upload");exit;}
        $file=$dir.'shop_'.uniqid().'.'.$ext;
        if(!move_uploaded_file($files['tmp_name'][$i],$file)){header("Location:edit-shop.php?id=$id&error=upload");exit;}
        $keep[]=$file;
    }
}

$image=implode('|',$keep);
$e1=mysqli_real_escape_string($connect,$name);$e2=mysqli_real_escape_string($connect,$cat);$e3=mysqli_real_escape_string($connect,$addr);$e4=mysqli_real_escape_string($connect,$desc);$e5=mysqli_real_escape_string($connect,$image);
$sql="UPDATE shop SET name_shop='$e1',category_shop='$e2',description_shop='$e4',address_shop='$e3',lat_shop=".(float)$lat.",lng_shop=".(float)$lng.",image_shop='$e5' WHERE id_shop=$id AND id_account=$uid";
if(mysqli_query($connect,$sql)){header('Location:shops.php?updated=1');exit;}
header("Location:edit-shop.php?id=$id&error=db");exit;
?>