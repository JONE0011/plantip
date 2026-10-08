<?php
session_start();$open_connect=1;require('connect.php');
if(!isset($_SESSION['id_account'])){header('Location:form-login.php');exit;}
$id=(int)($_POST['id_place']??0);$uid=(int)$_SESSION['id_account'];
$q=mysqli_query($connect,"SELECT * FROM place WHERE id_place=$id AND id_account=$uid LIMIT 1");
$old=$q?mysqli_fetch_assoc($q):null;if(!$old){http_response_code(403);die('คุณไม่มีสิทธิ์แก้ไขสถานที่นี้');}
$name=trim($_POST['name_place']??'');$location=trim($_POST['location_place']??'');$category=trim($_POST['category_place']??'');$desc=trim($_POST['description_place']??'');$lat=$_POST['lat_place']??'';$lng=$_POST['lng_place']??'';
if($name===''||$location===''||!is_numeric($lat)||!is_numeric($lng)){header("Location:edit-place.php?id=$id&error=missing");exit;}
$image=$old['image_place']??'';
if(isset($_FILES['image_place'])&&$_FILES['image_place']['error']===UPLOAD_ERR_OK){$ext=strtolower(pathinfo($_FILES['image_place']['name'],PATHINFO_EXTENSION));if(!in_array($ext,['jpg','jpeg','png'],true)){header("Location:edit-place.php?id=$id&error=upload");exit;}$dir='images/places/';if(!is_dir($dir))mkdir($dir,0755,true);$file=$dir.'user-'.uniqid().'.'.$ext;if(move_uploaded_file($_FILES['image_place']['tmp_name'],$file)){$image=$file;if($old['image_place']&&strpos($old['image_place'],'images/places/user-')===0&&is_file($old['image_place']))@unlink($old['image_place']);}}
$e1=mysqli_real_escape_string($connect,$name);$e2=mysqli_real_escape_string($connect,$location);$e3=mysqli_real_escape_string($connect,$category);$e4=mysqli_real_escape_string($connect,$desc);$e5=mysqli_real_escape_string($connect,$image);
$sql="UPDATE place SET name_place='$e1',location_place='$e2',category_place='$e3',description_place='$e4',lat_place=".(float)$lat.",lng_place=".(float)$lng.",image_place='$e5' WHERE id_place=$id AND id_account=$uid";
if(mysqli_query($connect,$sql)){header('Location:places.php?updated=1');exit;}header("Location:edit-place.php?id=$id&error=db");