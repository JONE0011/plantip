<?php
session_start();
$open_connect=1; require('connect.php');
if(!isset($_SESSION['id_account'])){http_response_code(401);echo json_encode(['ok'=>false,'message'=>'กรุณาเข้าสู่ระบบก่อนรีวิว'],JSON_UNESCAPED_UNICODE);exit;}
header('Content-Type: application/json; charset=UTF-8');
$type=$_POST['item_type']??'';
$id=(int)($_POST['item_id']??0);
$rating=(int)($_POST['rating']??0);
$text=trim($_POST['review_text']??'');
if(!in_array($type,['place','shop'],true)||$id<1||$rating<1||$rating>5){http_response_code(400);echo json_encode(['ok'=>false,'message'=>'ข้อมูลรีวิวไม่ถูกต้อง'],JSON_UNESCAPED_UNICODE);exit;}
$uid=(int)$_SESSION['id_account'];
$esc=mysqli_real_escape_string($connect,$text);
if($type==='place'){
  $exists=mysqli_query($connect,"SELECT id_place FROM place WHERE id_place=$id LIMIT 1");
  if(!$exists||!mysqli_num_rows($exists)){http_response_code(404);echo json_encode(['ok'=>false,'message'=>'ไม่พบสถานที่'],JSON_UNESCAPED_UNICODE);exit;}
  $sql="INSERT INTO reviews (id_account,item_type,id_place,id_shop,rating,review_text) VALUES ($uid,'place',$id,NULL,$rating,'$esc')
        ON DUPLICATE KEY UPDATE rating=VALUES(rating),review_text=VALUES(review_text),updated_at=CURRENT_TIMESTAMP";
}else{
  $exists=mysqli_query($connect,"SELECT id_shop FROM shop WHERE id_shop=$id AND status_shop=1 LIMIT 1");
  if(!$exists||!mysqli_num_rows($exists)){http_response_code(404);echo json_encode(['ok'=>false,'message'=>'ไม่พบร้าน'],JSON_UNESCAPED_UNICODE);exit;}
  $sql="INSERT INTO reviews (id_account,item_type,id_place,id_shop,rating,review_text) VALUES ($uid,'shop',NULL,$id,$rating,'$esc')
        ON DUPLICATE KEY UPDATE rating=VALUES(rating),review_text=VALUES(review_text),updated_at=CURRENT_TIMESTAMP";
}
if(!mysqli_query($connect,$sql)){http_response_code(500);echo json_encode(['ok'=>false,'message'=>'บันทึกรีวิวไม่สำเร็จ'],JSON_UNESCAPED_UNICODE);exit;}

// ส่งข้อมูลรีวิวล่าสุดกลับไปให้หน้า Gallery อัปเดตทันที โดยไม่ต้องรีเฟรชหน้า
$review_where = $type==='place' ? "r.id_place=$id AND r.item_type='place'" : "r.id_shop=$id AND r.item_type='shop'";
$stats_where = $type==='place' ? "id_place=$id AND item_type='place'" : "id_shop=$id AND item_type='shop'";
$review_filter = $type==='place' ? "r.id_place=$id AND r.item_type='place' AND r.id_account=$uid" : "r.id_shop=$id AND r.item_type='shop' AND r.id_account=$uid";
$review_q = mysqli_query($connect,"SELECT r.id_review,r.rating,r.review_text,r.created_at,a.username_account FROM reviews r JOIN account a ON a.id_account=r.id_account WHERE $review_filter ORDER BY r.id_review DESC LIMIT 1");
$review = $review_q ? (mysqli_fetch_assoc($review_q) ?: null) : null;
$stats_q = mysqli_query($connect,"SELECT ROUND(AVG(rating),1) AS average, COUNT(*) AS count FROM reviews WHERE $stats_where");
$stats = $stats_q ? (mysqli_fetch_assoc($stats_q) ?: ['average'=>0,'count'=>0]) : ['average'=>0,'count'=>0];

echo json_encode(['ok'=>true,'message'=>'บันทึกรีวิวแล้ว','review'=>$review,'average'=>(float)$stats['average'],'count'=>(int)$stats['count']],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);