<?php
session_start();
$open_connect=1; require('connect.php');
if(!isset($_SESSION['id_account']) || ($_SESSION['role_account']??'')!=='admin'){header('Location:form-login.php');exit;}

$errors=[];
function esc($v){global $connect;return mysqli_real_escape_string($connect,(string)$v);}
function images_list($v){return array_values(array_filter(array_map('trim',explode('|',(string)$v)));}
function delete_uploaded_images($value,$prefixes){
    foreach(images_list($value) as $img){
        $path=__DIR__.'/'.ltrim($img,'/');
        $ok=false; foreach($prefixes as $prefix){if(strpos($img,$prefix)===0){$ok=true;break;}}
        if($ok && is_file($path)) @unlink($path);
    }
}
function upload_images($field,$dir,$prefix){
    $out=[]; if(!isset($_FILES[$field]) || !is_array($_FILES[$field]['name'])) return $out;
    if(!is_dir(__DIR__.'/'.$dir)) mkdir(__DIR__.'/'.$dir,0755,true);
    $f=$_FILES[$field]; $n=count($f['name']);
    for($i=0;$i<$n;$i++){
        $err=$f['error'][$i]??UPLOAD_ERR_NO_FILE; if($err===UPLOAD_ERR_NO_FILE) continue;
        if($err!==UPLOAD_ERR_OK) throw new Exception('อัปโหลดรูปไม่สำเร็จ');
        $ext=strtolower(pathinfo($f['name'][$i],PATHINFO_EXTENSION));
        if(!in_array($ext,['jpg','jpeg','png','webp'],true)) throw new Exception('รองรับเฉพาะ JPG, PNG และ WEBP');
        $file=$dir.$prefix.uniqid().'.'.$ext;
        if(!move_uploaded_file($f['tmp_name'][$i],__DIR__.'/'.$file)) throw new Exception('บันทึกไฟล์รูปไม่สำเร็จ');
        $out[]=$file;
    } return $out;
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action']??'';
    if($action==='delete'){
        $id=(int)($_POST['id_place']??0);
        $r=mysqli_query($connect,"SELECT image_place FROM place WHERE id_place=$id LIMIT 1"); $old=$r?mysqli_fetch_assoc($r):null;
        if($old && mysqli_query($connect,"DELETE FROM place WHERE id_place=$id")){delete_uploaded_images($old['image_place'],['images/places/user-','images/places/admin-']);header('Location:admin-places.php?deleted=1');exit;}
        $errors[]='ลบสถานที่ไม่สำเร็จ';
    }
    if($action==='save'){
        $id=(int)($_POST['id_place']??0); $key=trim($_POST['place_key']??''); $name=trim($_POST['name_place']??'');
        $loc=trim($_POST['location_place']??''); $cat=trim($_POST['category_place']??''); $desc=trim($_POST['description_place']??'');
        $lat=$_POST['lat_place']??''; $lng=$_POST['lng_place']??'';
        if($name===''||$loc===''||!is_numeric($lat)||!is_numeric($lng)){ $errors[]='กรุณากรอกชื่อ ที่ตั้ง และพิกัดให้ครบ'; }
        else{
            try{
                if($key===''){ $key=preg_replace('/[^a-z0-9]+/i','-',strtolower($name)); $key=trim($key,'-'); if($key==='')$key='place-'.time(); }
                $base=$key;$n=2; $keyEsc=esc($key);
                while($r=mysqli_query($connect,"SELECT id_place FROM place WHERE place_key='$keyEsc'".($id?" AND id_place<>$id":'')) && mysqli_num_rows($r)){ $key=$base.'-'.$n++; $keyEsc=esc($key); }
                $new=upload_images('image_place','images/places/','admin-');
                $oldImages=[]; if($id){$r=mysqli_query($connect,"SELECT image_place FROM place WHERE id_place=$id LIMIT 1");$old=$r?mysqli_fetch_assoc($r):null;$oldImages=images_list($old['image_place']??'');
                    $remove=array_map('strval',$_POST['remove_images']??[]); $keep=[];
                    foreach($oldImages as $img){if(in_array($img,$remove,true)){delete_uploaded_images($img,['images/places/user-','images/places/admin-']);}else $keep[]=$img;}
                    $all=array_merge($keep,$new);
                    $sql="UPDATE place SET place_key='".esc($key)."',name_place='".esc($name)."',location_place='".esc($loc)."',category_place='".esc($cat)."',description_place='".esc($desc)."',lat_place=".(float)$lat.",lng_place=".(float)$lng.",image_place='".esc(implode('|',$all))."' WHERE id_place=$id";
                }else{
                    $all=$new; $sql="INSERT INTO place (id_account,place_key,name_place,location_place,category_place,description_place,lat_place,lng_place,image_place) VALUES (".(int)$_SESSION['id_account'].",'".esc($key)."','".esc($name)."','".esc($loc)."','".esc($cat)."','".esc($desc)."',".(float)$lat.",".(float)$lng.",'".esc(implode('|',$all))."')";
                }
                if(mysqli_query($connect,$sql)){header('Location:admin-places.php?saved=1');exit;}
                $errors[]='บันทึกไม่สำเร็จ: '.mysqli_error($connect);
            }catch(Exception $e){$errors[]=$e->getMessage();}
        }
    }
}
$editing=null;
if(isset($_GET['edit'])){$id=(int)$_GET['edit'];$r=mysqli_query($connect,"SELECT * FROM place WHERE id_place=$id LIMIT 1");$editing=$r?mysqli_fetch_assoc($r):null;}
$q=trim($_GET['q']??''); $places=[];
$sql="SELECT * FROM place"; if($q!==''){ $l='%'.esc($q).'%';$sql.=" WHERE name_place LIKE '$l' OR location_place LIKE '$l' OR category_place LIKE '$l' OR description_place LIKE '$l' OR place_key LIKE '$l'"; } $sql.=" ORDER BY id_place DESC";
$r=mysqli_query($connect,$sql);while($r&&($row=mysqli_fetch_assoc($r)))$places[]=$row;
?>
<!doctype html><html lang="th"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>จัดการสถานที่ | Admin</title>
<link rel="stylesheet" href="gallery.css"><link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}body{margin:0;background:#faf9f5;color:#1d231d;font-family:Prompt,sans-serif}.wrap{width:min(1280px,calc(100% - 32px));margin:auto}.nav{background:#163a28;color:#fff}.nav .wrap{height:68px;display:flex;align-items:center;justify-content:space-between}.logo{font-weight:700}.navlinks{display:flex;gap:22px}.navlinks a{color:#dfe9e1;text-decoration:none;font-size:13px}.navlinks a.active{color:#fff;font-weight:600}.head{padding:34px 0 20px}.head h1{margin:0 0 5px;font-size:30px}.head p{margin:0;color:#68736c;font-size:13px}.notice{padding:11px 14px;border-radius:12px;margin-bottom:14px;background:#e8f3ec;color:#163a28}.err{background:#fdeceb;color:#8a2c25}.layout{display:grid;grid-template-columns:420px 1fr;gap:18px;padding-bottom:50px}.panel{background:#eef1e9;border-radius:22px;padding:20px}.panel h2{margin:0 0 15px;font-size:18px}.field{margin-bottom:12px}label{display:block;color:#68736c;font-size:12px;margin-bottom:5px}input,textarea{width:100%;border:1px solid #d8ded8;border-radius:11px;background:#fff;padding:10px;font:13px Prompt;color:#1d231d}textarea{min-height:75px;resize:vertical}.row{display:grid;grid-template-columns:1fr 1fr;gap:9px}.btn{border:0;border-radius:999px;padding:9px 15px;font:600 12px Prompt;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}.primary{background:#24593f;color:#fff}.ghost{background:#fff;color:#526058;border:1px solid #d8ded8}.danger{background:#fdeceb;color:#8a2c25}.search{display:flex;gap:8px;margin-bottom:12px}.search input{flex:1}.image-box{background:#fff;border:1px solid #d8ded8;border-radius:14px;padding:10px}.image-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}.image-list{display:grid;gap:7px}.image-row{display:grid;grid-template-columns:1fr 32px;gap:6px}.existing{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:8px}.existing label{position:relative;margin:0}.existing img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:10px;display:block}.existing input{position:absolute;right:5px;top:5px;width:auto}.table{background:#fff;border-radius:18px;overflow:hidden}.item{display:grid;grid-template-columns:82px 1fr auto;gap:13px;padding:12px;border-bottom:1px solid #e3e6e1;align-items:center}.item:last-child{border-bottom:0}.thumb{width:82px;height:62px;border-radius:10px;background:#e5e9e3 center/cover no-repeat}.item h3{font-size:14px;margin:0 0 3px}.meta{font-size:11px;color:#68736c}.actions{display:flex;gap:6px;align-items:center}.gallery-card{cursor:pointer}.empty{padding:30px;text-align:center;color:#68736c}.global-search-btn{display:none}
@media(max-width:900px){.layout{grid-template-columns:1fr}.item{grid-template-columns:65px 1fr}.item .actions{grid-column:2}.navlinks{gap:10px}}@media(max-width:600px){.navlinks{display:none}.row{grid-template-columns:1fr}}
</style></head><body>
<nav class="nav"><div class="wrap"><div class="logo">เที่ยวตาก · ADMIN</div><div class="navlinks"><a href="admin.php">แดชบอร์ด</a><a href="admin-places.php" class="active">สถานที่</a><a href="admin-shops.php">ร้านอาหาร & คาเฟ่</a><a href="admin-members.php">สมาชิก</a><a href="admin.php?logout=1">ออกจากระบบ</a></div></div></nav>
<div class="wrap"><div class="head"><h1>จัดการสถานที่เที่ยว</h1><p>เพิ่ม แก้ไข ลบ ค้นหา และจัดการรูปภาพหลายรูปของสถานที่</p></div>
<?php if(isset($_GET['saved'])):?><div class="notice">บันทึกข้อมูลเรียบร้อยแล้ว</div><?php endif;?><?php if(isset($_GET['deleted'])):?><div class="notice">ลบสถานที่เรียบร้อยแล้ว</div><?php endif;?><?php foreach($errors as $e):?><div class="notice err"><?php echo htmlspecialchars($e);?></div><?php endforeach;?>
<div class="layout"><section class="panel"><h2><?php echo $editing?'แก้ไขสถานที่':'เพิ่มสถานที่ใหม่';?></h2>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="save"><input type="hidden" name="id_place" value="<?php echo (int)($editing['id_place']??0);?>">
<div class="field"><label>ชื่อสถานที่</label><input name="name_place" value="<?php echo htmlspecialchars($editing['name_place']??'');?>" required></div>
<div class="field"><label>place_key</label><input name="place_key" value="<?php echo htmlspecialchars($editing['place_key']??'');?>" placeholder="ปล่อยว่างให้ระบบสร้าง"></div>
<div class="field"><label>ที่ตั้ง</label><input name="location_place" value="<?php echo htmlspecialchars($editing['location_place']??'');?>" required></div>
<div class="field"><label>หมวดหมู่</label><input name="category_place" value="<?php echo htmlspecialchars($editing['category_place']??'');?>"></div>
<div class="field"><label>รายละเอียด</label><textarea name="description_place"><?php echo htmlspecialchars($editing['description_place']??'');?></textarea></div>
<div class="row"><div class="field"><label>ละติจูด</label><input name="lat_place" value="<?php echo htmlspecialchars($editing['lat_place']??'');?>" required></div><div class="field"><label>ลองจิจูด</label><input name="lng_place" value="<?php echo htmlspecialchars($editing['lng_place']??'');?>" required></div></div>
<div class="image-box"><div class="image-head"><strong style="font-size:12px">รูปภาพ</strong><button type="button" class="btn primary" id="add-image">＋ เพิ่มรูป</button></div>
<?php if($editing && images_list($editing['image_place']??'')):?><div class="existing"><?php foreach(images_list($editing['image_place']) as $img):?><label><img src="<?php echo htmlspecialchars($img);?>" alt=""><input type="checkbox" name="remove_images[]" value="<?php echo htmlspecialchars($img);?>" title="ลบรูปนี้"></label><?php endforeach;?></div><div style="font-size:10px;color:#68736c;margin-bottom:8px">ติ๊กที่รูปเพื่อลบตอนบันทึก</div><?php endif;?>
<div class="image-list" id="image-list"><div class="image-row"><input type="file" name="image_place[]" accept="image/jpeg,image/png,image/webp"></div></div>
</div><div style="display:flex;gap:8px;margin-top:14px"><button class="btn primary" type="submit"><?php echo $editing?'บันทึกการแก้ไข':'เพิ่มสถานที่';?></button><?php if($editing):?><a class="btn ghost" href="admin-places.php">ยกเลิก</a><?php endif;?></div>
</form></section>
<section class="panel"><h2>สถานที่ทั้งหมด · <?php echo count($places);?> รายการ</h2><form class="search" method="get"><input name="q" value="<?php echo htmlspecialchars($q);?>" placeholder="ค้นหาชื่อ ที่ตั้ง หมวดหมู่..."><button class="btn primary">ค้นหา</button></form><div class="table">
<?php if(!$places):?><div class="empty">ไม่พบสถานที่</div><?php endif;?>
<?php foreach($places as $p):$imgs=images_list($p['image_place']);$img=$imgs[0]??'';?>
<div class="item"><div class="thumb gallery-card" data-gallery="<?php echo htmlspecialchars(json_encode($imgs,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),ENT_QUOTES);?>"></div><div><h3><?php echo htmlspecialchars($p['name_place']);?></h3><div class="meta">📍 <?php echo htmlspecialchars($p['location_place']);?> · <?php echo htmlspecialchars($p['category_place']);?></div><div class="meta"><?php echo count($imgs);?> รูป · <?php echo htmlspecialchars($p['place_key']);?></div></div><div class="actions"><a class="btn ghost" href="admin-places.php?edit=<?php echo (int)$p['id_place'];?>">แก้ไข</a><form method="post" onsubmit="return confirm('ลบ <?php echo htmlspecialchars($p['name_place'],ENT_QUOTES);?> ใช่ไหม?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id_place" value="<?php echo (int)$p['id_place'];?>"><button class="btn danger">ลบ</button></form></div></div>
<script>document.currentScript.previousElementSibling?.querySelector('.thumb')?.style.setProperty('background-image',"url('<?php echo htmlspecialchars($img,ENT_QUOTES);?>')");</script>
<?php endforeach;?></div></section></div></div>
<script src="gallery.js?v=20261007-admin-place"></script><script>
const list=document.getElementById('image-list'),add=document.getElementById('add-image');add.onclick=()=>{const row=document.createElement('div');row.className='image-row';row.innerHTML='<input type="file" name="image_place[]" accept="image/jpeg,image/png,image/webp"><button type="button" class="btn danger remove-image">×</button>';list.appendChild(row);row.querySelector('.remove-image').onclick=()=>row.remove();};

</script></body></html>