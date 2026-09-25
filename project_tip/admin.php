<?php
session_start();
$open_connect = 1;
require('connect.php');

if(!isset($_SESSION['id_account']) || $_SESSION['role_account'] != 'admin'){
    die(header('Location: form-login.php'));
}elseif(isset($_GET['logout'])){
    session_destroy();
    die(header('Location: form-login.php'));
}

$id_account = $_SESSION['id_account'];
$query_show = "SELECT * FROM account WHERE id_account = '$id_account'";
$call_back_show = mysqli_query($connect, $query_show);
$result_show = mysqli_fetch_assoc($call_back_show);

$directory = 'images_account/';
$image_name = $directory . $result_show['images_account'];
$image_account = is_file($image_name) ? ($image_name . '?' . filemtime($image_name)) : $image_name;

// ตัวเลขสรุปไว้โชว์บนแดชบอร์ด
$count_places = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS n FROM place"))['n'] ?? 0;
$count_shops_pending = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS n FROM shop WHERE status_shop = 1"))['n'] ?? 0;
$count_accounts = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS n FROM account"))['n'] ?? 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | เที่ยวตาก</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root{
        --ink: #1d231d; --ink-soft: #4c554b; --cream: #faf8f3; --panel: #eef1e9;
        --green: #24593f; --green-deep: #163a28; --line: rgba(29,35,29,0.12); --radius: 14px;
    }
    *{ box-sizing: border-box; }
    body{ margin: 0; font-family: 'Prompt', sans-serif; color: var(--ink); background: var(--cream); }
    a{ color: inherit; }
    .wrap{ max-width: 1180px; margin: 0 auto; padding: 0 1.5rem; }

    .nav{ background: var(--green-deep); color: #fdfbf5; }
    .nav .wrap{ display: flex; align-items: center; justify-content: space-between; height: 4.2rem; }
    .logo{ font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem; }
    .logo small{ font-weight: 400; font-size: 0.68rem; letter-spacing: 0.1em; color: rgba(253,251,245,0.65); }
    .who{ display: flex; align-items: center; gap: 0.7rem; font-size: 0.85rem; }
    .who img{ width: 2rem; height: 2rem; border-radius: 50%; object-fit: cover; }
    .logout-link{ color: rgba(253,251,245,0.75); text-decoration: none; font-size: 0.85rem; border-bottom: 1px solid rgba(253,251,245,0.4); }
    .logout-link:hover{ color: #fff; }

    .page-head{ padding: 2.4rem 0 1.5rem; }
    .page-head h1{ margin: 0 0 0.3rem; font-size: 1.7rem; font-weight: 700; }
    .page-head p{ margin: 0; color: var(--ink-soft); font-size: 0.92rem; }

    .stats{ display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2rem; }
    @media (max-width: 700px){ .stats{ grid-template-columns: 1fr; } }
    .stat-card{ background: #fff; border-radius: var(--radius); padding: 1.2rem 1.4rem; box-shadow: 0 1px 2px rgba(29,35,29,0.06); }
    .stat-card .num{ font-size: 1.8rem; font-weight: 700; color: var(--green-deep); }
    .stat-card .label{ font-size: 0.82rem; color: var(--ink-soft); }

    .actions{ display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; padding-bottom: 3rem; }
    @media (max-width: 900px){ .actions{ grid-template-columns: 1fr; } }
    .action-card{
        background: var(--panel);
        border-radius: 20px;
        padding: 1.6rem;
        text-decoration: none;
        color: var(--ink);
        display: block;
    }
    .action-card h2{ margin: 0 0 0.4rem; font-size: 1.15rem; font-weight: 700; }
    .action-card p{ margin: 0; font-size: 0.86rem; color: var(--ink-soft); line-height: 1.6; }
    .action-card .go{ display: inline-block; margin-top: 0.9rem; font-size: 0.85rem; color: var(--green-deep); font-weight: 600; }
    .action-card:hover{ outline: 1px solid var(--green); }
</style>
</head>
<body>

<nav class="nav">
    <div class="wrap">
        <div class="logo">เที่ยวตาก<small>&nbsp;ADMIN</small></div>
        <div class="who">
            <img src="<?php echo htmlspecialchars($image_account); ?>" alt="">
            <span>คุณ<?php echo htmlspecialchars($result_show['username_account']); ?> · <?php echo htmlspecialchars($result_show['role_account']); ?></span>
            <a href="admin.php?logout=1" class="logout-link">ออกจากระบบ</a>
        </div>
    </div>
</nav>

<div class="wrap">
    <div class="page-head">
        <h1>แดชบอร์ดผู้ดูแลระบบ</h1>
        <p>ยินดีต้อนรับคุณ <?php echo htmlspecialchars($result_show['username_account']); ?> — จัดการสถานที่เที่ยวและร้านค้าที่สมาชิกเพิ่มเข้ามาได้ที่นี่</p>
    </div>

    <div class="stats">
        <div class="stat-card">
            <div class="num"><?php echo $count_places; ?></div>
            <div class="label">สถานที่เที่ยวทั้งหมด</div>
        </div>
        <div class="stat-card">
            <div class="num"><?php echo $count_shops_pending; ?></div>
            <div class="label">ร้านค้าที่แสดงผลอยู่</div>
        </div>
        <div class="stat-card">
            <div class="num"><?php echo $count_accounts; ?></div>
            <div class="label">สมาชิกทั้งหมด</div>
        </div>
    </div>

    <div class="actions">
        <a href="admin-places.php" class="action-card">
            <h2>จัดการสถานที่เที่ยว</h2>
            <p>เพิ่ม แก้ไข หรือลบสถานที่เที่ยวที่แสดงในหน้าแรกและหน้าวางแผนทริป</p>
            <span class="go">ไปที่หน้าจัดการ →</span>
        </a>
        <a href="admin-shops.php" class="action-card">
            <h2>จัดการร้านค้า</h2>
            <p>ตรวจสอบร้านอาหาร/คาเฟ่ที่สมาชิกเพิ่มเข้ามา แก้ไข ซ่อน หรือลบร้านที่ไม่เหมาะสมได้</p>
            <span class="go">ไปที่หน้าจัดการ →</span>
        </a>
        <a href="admin-members.php" class="action-card">
            <h2>จัดการสมาชิก</h2>
            <p>แก้ไขข้อมูลบัญชี ปลดล็อกบัญชีที่ถูกระงับ และกำหนดสิทธิ์ admin/member ได้ที่นี่</p>
            <span class="go">ไปที่หน้าจัดการ →</span>
        </a>
    </div>
</div>

</body>
</html>