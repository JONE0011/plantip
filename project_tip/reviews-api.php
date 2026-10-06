<?php
session_start();
$open_connect=1; require('connect.php');
header('Content-Type: application/json; charset=UTF-8');

$type=$_GET['type']??'';
$id=(int)($_GET['id']??0);
if(!in_array($type,['place','shop'],true)||$id<1){http_response_code(400);echo json_encode(['ok'=>false,'message'=>'Invalid item'],JSON_UNESCAPED_UNICODE);exit;}
$where=$type==='place'?"r.id_place=$id AND r.item_type='place'":"r.id_shop=$id AND r.item_type='shop'";
$q=mysqli_query($connect,"SELECT r.id_review,r.id_account,r.rating,r.review_text,r.created_at,a.username_account
FROM reviews r JOIN account a ON a.id_account=r.id_account WHERE $where ORDER BY r.created_at DESC");
$reviews=[];
if($q){while($r=mysqli_fetch_assoc($q))$reviews[]=$r;}
$avg=0;$count=count($reviews);$sum=0;
foreach($reviews as $r)$sum+=(int)$r['rating'];
if($count)$avg=round($sum/$count,1);
$mine=null;
if(isset($_SESSION['id_account'])){
  $uid=(int)$_SESSION['id_account'];
  $q2=mysqli_query($connect,"SELECT id_review,rating,review_text FROM reviews WHERE id_account=$uid AND $where LIMIT 1");
  if($q2)$mine=mysqli_fetch_assoc($q2)?:null;
}
echo json_encode(['ok'=>true,'average'=>$avg,'count'=>$count,'reviews'=>$reviews,'mine'=>$mine,'logged_in'=>isset($_SESSION['id_account'])],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);