<?php
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
$profile_user=null;$profile_history=[];$profile_places=[];$profile_shops=[];
if(isset($_SESSION['id_account'])){
 $profile_id=(int)$_SESSION['id_account'];
 $q=mysqli_query($connect,"SELECT id_account,username_account,email_account,images_account FROM account WHERE id_account=$profile_id LIMIT 1");
 $profile_user=$q?mysqli_fetch_assoc($q):null;
 if($profile_user){
  $hq=mysqli_query($connect,"SELECT p.place_key,p.name_place,p.location_place,h.added_at FROM place_history h JOIN place p ON p.id_place=h.id_place WHERE h.id_account=$profile_id ORDER BY h.added_at DESC LIMIT 12");
  if($hq)while($h=mysqli_fetch_assoc($hq))$profile_history[]=$h;
  $pq=mysqli_query($connect,"SELECT id_place,name_place,location_place,image_place FROM place WHERE id_account=$profile_id ORDER BY created_at DESC");
  if($pq)while($p=mysqli_fetch_assoc($pq))$profile_places[]=$p;
  $sq=mysqli_query($connect,"SELECT id_shop,name_shop,category_shop,address_shop,image_shop FROM shop WHERE id_account=$profile_id ORDER BY created_at DESC");
  if($sq)while($s=mysqli_fetch_assoc($sq))$profile_shops[]=$s;
 }
}
if($profile_user){
 $profile_image='images_account/'.basename($profile_user['images_account']?:'default_images_account.jpg');
 if(!is_file(__DIR__.'/'.$profile_image))$profile_image='images_account/default_images_account.jpg';
?>
<div class="profile-widget">
<button type="button" class="profile-trigger" id="profile-trigger"><img src="<?php echo htmlspecialchars($profile_image);?>" alt=""><span><?php echo htmlspecialchars($profile_user['username_account']);?></span><b>⌄</b></button>
<div class="profile-overlay" id="profile-overlay" aria-hidden="true"><aside class="profile-drawer" role="dialog" aria-modal="true">
<button type="button" class="profile-close" id="profile-close">×</button><div class="profile-cover"><div class="profile-avatar-large"><img src="<?php echo htmlspecialchars($profile_image);?>" alt=""></div></div>
<div class="profile-body"><span class="profile-kicker">TAK EXPLORE · MY PROFILE</span><h2 id="profile-title"><?php echo htmlspecialchars($profile_user['username_account']);?></h2><p class="profile-email"><?php echo htmlspecialchars($profile_user['email_account']);?></p>
<form action="profile-update.php" method="POST" enctype="multipart/form-data" class="profile-form"><label>ชื่อที่แสดง<input type="text" name="username_account" value="<?php echo htmlspecialchars($profile_user['username_account']);?>" maxlength="40" required></label><label>รูปโปรไฟล์<input type="file" name="images_account" accept="image/jpeg,image/png,image/webp"></label><button type="submit">บันทึกโปรไฟล์</button></form>

<div class="profile-history-head"><div><strong>สถานที่ที่ฉันเพิ่ม</strong><small>แก้ไขได้เฉพาะข้อมูลของคุณ</small></div><span><?php echo count($profile_places);?> รายการ</span></div>
<div class="profile-history"><?php if(!$profile_places):?><div class="profile-empty">คุณยังไม่ได้เพิ่มสถานที่</div><?php else:foreach($profile_places as $p):?><div class="profile-history-item"><div class="profile-history-dot">⌖</div><div><strong><?php echo htmlspecialchars($p['name_place']);?></strong><small><?php echo htmlspecialchars($p['location_place']);?></small></div><a href="edit-place.php?id=<?php echo (int)$p['id_place'];?>" style="margin-left:auto">แก้ไข</a></div><?php endforeach;endif;?></div>

<div class="profile-history-head"><div><strong>ร้านที่ฉันเพิ่ม</strong><small>ร้านอาหาร คาเฟ่ และร้านค้า</small></div><span><?php echo count($profile_shops);?> รายการ</span></div>
<div class="profile-history"><?php if(!$profile_shops):?><div class="profile-empty">คุณยังไม่ได้เพิ่มร้าน</div><?php else:foreach($profile_shops as $s):?><div class="profile-history-item"><div class="profile-history-dot">⌂</div><div><strong><?php echo htmlspecialchars($s['name_shop']);?></strong><small><?php echo htmlspecialchars($s['category_shop'].' · '.($s['address_shop']?:'จังหวัดตาก'));?></small></div><a href="edit-shop.php?id=<?php echo (int)$s['id_shop'];?>" style="margin-left:auto">แก้ไข</a></div><?php endforeach;endif;?></div>

<div class="profile-history-head"><div><strong>ประวัติสถานที่ในทริป</strong><small>สถานที่ที่คุณเคยเพิ่มเข้าทริป</small></div><span><?php echo count($profile_history);?> รายการ</span></div>
<div class="profile-history"><?php if(!$profile_history):?><div class="profile-empty">ยังไม่มีประวัติ</div><?php else:foreach($profile_history as $history):?><div class="profile-history-item"><div class="profile-history-dot">✓</div><div><strong><?php echo htmlspecialchars($history['name_place']);?></strong><small><?php echo htmlspecialchars($history['location_place']);?></small></div></div><?php endforeach;endif;?></div>
<a href="profile-update.php?logout=1" class="profile-logout">ออกจากระบบ</a></div></aside></div></div>
<?php } else { ?><a href="form-login.php" class="btn-login-open">เข้าสู่ระบบ</a><?php } ?>
<?php if($profile_user):?><script>(function(){const o=document.getElementById('profile-overlay'),b=document.getElementById('profile-trigger');if(!o||!b)return;if(o.parentElement!==document.body)document.body.appendChild(o);const c=document.getElementById('profile-close');function s(){o.classList.add('is-open');o.setAttribute('aria-hidden','false');document.body.classList.add('profile-open')}function h(){o.classList.remove('is-open');o.setAttribute('aria-hidden','true');document.body.classList.remove('profile-open')}b.addEventListener('click',s);c?.addEventListener('click',h);o.addEventListener('click',e=>{if(e.target===o)h()});document.addEventListener('keydown',e=>{if(e.key==='Escape'&&o.classList.contains('is-open'))h()})})();</script><?php endif;?>