<?php
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
$profile_user = null;
$profile_history = [];
if(isset($_SESSION['id_account'])){
    $profile_id = (int)$_SESSION['id_account'];
    $profile_q = mysqli_query($connect, "SELECT id_account, username_account, email_account, images_account FROM account WHERE id_account = $profile_id LIMIT 1");
    $profile_user = $profile_q ? mysqli_fetch_assoc($profile_q) : null;
    if($profile_user){
        $profile_hq = mysqli_query($connect, "SELECT p.place_key, p.name_place, p.location_place, h.added_at
            FROM place_history h JOIN place p ON p.id_place = h.id_place
            WHERE h.id_account = $profile_id ORDER BY h.added_at DESC LIMIT 12");
        if($profile_hq){
            while($h = mysqli_fetch_assoc($profile_hq)) $profile_history[] = $h;
        }
    }
}
if($profile_user){
    $profile_image = 'images_account/' . basename($profile_user['images_account'] ?: 'default_images_account.jpg');
    if(!is_file(__DIR__ . '/' . $profile_image)) $profile_image = 'images_account/default_images_account.jpg';
?>
<div class="profile-widget">
    <button type="button" class="profile-trigger" id="profile-trigger" aria-label="เปิดโปรไฟล์">
        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="">
        <span><?php echo htmlspecialchars($profile_user['username_account']); ?></span>
        <b>⌄</b>
    </button>
    <div class="profile-overlay" id="profile-overlay" aria-hidden="true">
        <aside class="profile-drawer" role="dialog" aria-modal="true" aria-labelledby="profile-title">
            <button type="button" class="profile-close" id="profile-close" aria-label="ปิด">×</button>
            <div class="profile-cover">
                <div class="profile-avatar-large"><img src="<?php echo htmlspecialchars($profile_image); ?>" alt=""></div>
            </div>
            <div class="profile-body">
                <span class="profile-kicker">TAK EXPLORE · MY PROFILE</span>
                <h2 id="profile-title"><?php echo htmlspecialchars($profile_user['username_account']); ?></h2>
                <p class="profile-email"><?php echo htmlspecialchars($profile_user['email_account']); ?></p>
                <form action="profile-update.php" method="POST" enctype="multipart/form-data" class="profile-form">
                    <label>ชื่อที่แสดง<input type="text" name="username_account" value="<?php echo htmlspecialchars($profile_user['username_account']); ?>" maxlength="40" required></label>
                    <label>รูปโปรไฟล์<input type="file" name="images_account" accept="image/jpeg,image/png,image/webp"></label>
                    <button type="submit">บันทึกโปรไฟล์</button>
                </form>
                <div class="profile-history-head">
                    <div><strong>ประวัติสถานที่</strong><small>สถานที่ที่คุณเคยเพิ่มเข้าทริป</small></div>
                    <span><?php echo count($profile_history); ?> รายการ</span>
                </div>
                <div class="profile-history">
                    <?php if(!$profile_history): ?>
                        <div class="profile-empty">ยังไม่มีสถานที่ที่เคยเพิ่ม ลองเลือกสถานที่จากหน้าแรกได้เลย</div>
                    <?php else: foreach($profile_history as $history): ?>
                        <div class="profile-history-item">
                            <div class="profile-history-dot">✓</div>
                            <div><strong><?php echo htmlspecialchars($history['name_place']); ?></strong><small><?php echo htmlspecialchars($history['location_place']); ?></small></div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
                <a href="profile-update.php?logout=1" class="profile-logout">ออกจากระบบ</a>
            </div>
        </aside>
    </div>
</div>
<?php } else { ?>
<a href="form-login.php" class="btn-login-open">เข้าสู่ระบบ</a>
<?php } ?>
<?php if($profile_user): ?>
<script>
(function(){
 const overlay=document.getElementById('profile-overlay'), open=document.getElementById('profile-trigger');
 if(!overlay || !open) return;

 // Navbar มี backdrop-filter จึงสร้าง containing block ให้ position:fixed
 // ย้าย Drawer ไปไว้ใต้ body โดยตรง เพื่อให้เต็มจอจริง
 if(overlay.parentElement !== document.body){
   document.body.appendChild(overlay);
 }

 const close=document.getElementById('profile-close');
 function show(){
   overlay.classList.add('is-open');
   overlay.setAttribute('aria-hidden','false');
   document.body.classList.add('profile-open');
   setTimeout(()=>document.querySelector('#profile-title')?.focus(),100);
 }
 function hide(){
   overlay.classList.remove('is-open');
   overlay.setAttribute('aria-hidden','true');
   document.body.classList.remove('profile-open');
 }
 open.addEventListener('click',show);
 close?.addEventListener('click',hide);
 overlay.addEventListener('click',e=>{if(e.target===overlay)hide()});
 document.addEventListener('keydown',e=>{if(e.key==='Escape' && overlay.classList.contains('is-open'))hide()});
})();
</script>
<?php endif; ?>
