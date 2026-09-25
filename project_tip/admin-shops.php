<?php
session_start();
$open_connect = 1;
require('connect.php');

if(!isset($_SESSION['id_account']) || $_SESSION['role_account'] != 'admin'){
    die(header('Location: form-login.php'));
}

// ---------- บันทึกการแก้ไข ----------
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save'){
    $id_shop           = (int) ($_POST['id_shop'] ?? 0);
    $name_shop         = trim($_POST['name_shop'] ?? '');
    $category_shop     = trim($_POST['category_shop'] ?? '');
    $address_shop      = trim($_POST['address_shop'] ?? '');
    $description_shop  = trim($_POST['description_shop'] ?? '');
    $lat_shop          = $_POST['lat_shop'] ?? '';
    $lng_shop          = $_POST['lng_shop'] ?? '';
    $image_shop        = trim($_POST['image_shop'] ?? '');

    $allowed_categories = ['ร้านอาหาร', 'คาเฟ่', 'ร้านค้า', 'อื่นๆ'];

    if($id_shop > 0 && $name_shop !== '' && in_array($category_shop, $allowed_categories, true) && is_numeric($lat_shop) && is_numeric($lng_shop)){
        $name_shop_esc        = mysqli_real_escape_string($connect, $name_shop);
        $category_shop_esc    = mysqli_real_escape_string($connect, $category_shop);
        $address_shop_esc     = mysqli_real_escape_string($connect, $address_shop);
        $description_shop_esc = mysqli_real_escape_string($connect, $description_shop);
        $image_shop_esc       = mysqli_real_escape_string($connect, $image_shop);
        $lat_shop_f = (float) $lat_shop;
        $lng_shop_f = (float) $lng_shop;

        mysqli_query($connect, "UPDATE shop SET
            name_shop = '$name_shop_esc',
            category_shop = '$category_shop_esc',
            address_shop = '$address_shop_esc',
            description_shop = '$description_shop_esc',
            lat_shop = '$lat_shop_f',
            lng_shop = '$lng_shop_f',
            image_shop = '$image_shop_esc'
            WHERE id_shop = $id_shop");

        die(header('Location: admin-shops.php?saved=1'));
    }
}

// ---------- สลับสถานะ แสดง/ซ่อน ----------
if(isset($_GET['toggle'])){
    $id_toggle = (int) $_GET['toggle'];
    mysqli_query($connect, "UPDATE shop SET status_shop = 1 - status_shop WHERE id_shop = $id_toggle");
    die(header('Location: admin-shops.php?updated=1'));
}

// ---------- ลบ ----------
if(isset($_GET['delete'])){
    $id_delete = (int) $_GET['delete'];
    mysqli_query($connect, "DELETE FROM shop WHERE id_shop = $id_delete");
    die(header('Location: admin-shops.php?deleted=1'));
}

// ---------- โหลดร้านที่กำลังแก้ไข ----------
$editing = null;
if(isset($_GET['edit'])){
    $id_edit = (int) $_GET['edit'];
    $result_edit = mysqli_query($connect, "SELECT * FROM shop WHERE id_shop = $id_edit");
    $editing = mysqli_fetch_assoc($result_edit);
}

// ---------- รายการทั้งหมด พร้อมชื่อคนเพิ่ม ----------
$shops = [];
$result_all = mysqli_query($connect, "
    SELECT s.*, a.username_account
    FROM shop s
    JOIN account a ON a.id_account = s.id_account
    ORDER BY s.created_at DESC
");
while($row = mysqli_fetch_assoc($result_all)){
    $shops[] = $row;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>จัดการร้านค้า | Admin</title>
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
    .logo{ font-weight: 700; font-size: 1.1rem; }
    .nav-links{ display: flex; gap: 1.5rem; list-style: none; margin: 0; padding: 0; }
    .nav-links a{ color: rgba(253,251,245,0.75); text-decoration: none; font-size: 0.85rem; }
    .nav-links a.active, .nav-links a:hover{ color: #fff; }

    .page-head{ padding: 2.2rem 0 1.3rem; }
    .page-head h1{ margin: 0 0 0.3rem; font-size: 1.6rem; font-weight: 700; }
    .page-head p{ margin: 0; color: var(--ink-soft); font-size: 0.88rem; }

    .notice{ border-radius: 10px; padding: 0.7rem 1rem; font-size: 0.85rem; margin-bottom: 1.2rem; background: #e8f3ec; border: 1px solid #a9d3b8; color: var(--green-deep); }

    .shop-grid{ display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; padding-bottom: 3rem; }
    @media (max-width: 980px){ .shop-grid{ grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px){ .shop-grid{ grid-template-columns: 1fr; } }

    .shop-card{ background: #fff; border-radius: var(--radius); overflow: hidden; box-shadow: 0 1px 2px rgba(29,35,29,0.06); }
    .shop-card.hidden{ opacity: 0.55; }
    .shop-card .thumb{ aspect-ratio: 16/10; background: #cfd6c8 center / cover no-repeat; }
    .shop-card .body{ padding: 0.9rem 1rem 1rem; }
    .shop-card .tag{ display: inline-block; font-size: 0.7rem; color: var(--green-deep); background: var(--panel); border-radius: 999px; padding: 0.1rem 0.55rem; margin-bottom: 0.35rem; }
    .shop-card h3{ margin: 0 0 0.2rem; font-size: 0.95rem; font-weight: 600; }
    .shop-card .addr{ margin: 0 0 0.3rem; font-size: 0.78rem; color: var(--ink-soft); }
    .shop-card .by{ margin: 0 0 0.7rem; font-size: 0.72rem; color: var(--ink-soft); opacity: 0.8; }
    .shop-card .status{ font-size: 0.72rem; font-weight: 600; margin-bottom: 0.6rem; }
    .shop-card .status.show{ color: var(--green-deep); }
    .shop-card .status.hide{ color: #b3413a; }

    .btn-row{ display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .btn{ border: none; border-radius: 999px; padding: 0.4rem 0.9rem; font-family: 'Prompt', sans-serif; font-size: 0.78rem; cursor: pointer; text-decoration: none; }
    .btn-toggle{ background: var(--panel); color: var(--ink); }
    .btn-toggle:hover{ background: #e0e5d9; }
    .btn-delete{ background: #fdeceb; color: #8a2c25; }
    .btn-delete:hover{ background: #f9d8d6; }

    .empty-hint{ font-size: 0.9rem; color: var(--ink-soft); padding: 3rem 1rem; text-align: center; border: 1px dashed var(--line); border-radius: var(--radius); grid-column: 1 / -1; }

    .edit-panel{ background: var(--panel); border-radius: 20px; padding: 1.5rem; margin-bottom: 1.5rem; }
    .edit-panel h2{ margin: 0 0 1rem; font-size: 1.05rem; font-weight: 700; }
    .edit-panel label{ display: block; font-size: 0.8rem; color: var(--ink-soft); margin: 0 0 0.3rem; }
    .edit-panel .field{ margin-bottom: 0.9rem; }
    .edit-panel input[type="text"], .edit-panel select, .edit-panel textarea{
        width: 100%; border: 1px solid var(--line); border-radius: 10px;
        padding: 0.55rem 0.7rem; font-family: 'Prompt', sans-serif; font-size: 0.88rem;
        background: #fff; color: var(--ink);
    }
    .edit-panel textarea{ resize: vertical; min-height: 4rem; }
    .edit-panel input:focus, .edit-panel select:focus, .edit-panel textarea:focus{ outline: none; border-color: var(--green); }
    .edit-panel .row-2, .edit-panel .row-3{ display: grid; gap: 0.7rem; }
    .edit-panel .row-2{ grid-template-columns: 1fr 1fr; }
    .edit-panel .row-3{ grid-template-columns: 1fr 1fr 1fr; }
    .btn-primary{ background: var(--green); color: #fff; }
    .btn-primary:hover{ background: var(--green-deep); }
    .btn-ghost-link{ background: transparent; border: 1px solid var(--line); color: var(--ink-soft); }
</style>
</head>
<body>

<nav class="nav">
    <div class="wrap">
        <div class="logo">เที่ยวตาก ADMIN</div>
        <ul class="nav-links">
            <li><a href="admin.php">แดชบอร์ด</a></li>
            <li><a href="admin-places.php">สถานที่เที่ยว</a></li>
            <li><a href="admin-shops.php" class="active">ร้านค้า</a></li>
            <li><a href="admin.php?logout=1">ออกจากระบบ</a></li>
        </ul>
    </div>
</nav>

<div class="wrap">
    <div class="page-head">
        <h1>จัดการร้านค้า</h1>
        <p>ร้านที่สมาชิกเพิ่มเข้ามาทั้งหมด — กด "ซ่อน" เพื่อไม่ให้แสดงในหน้า shops.php โดยไม่ลบข้อมูล หรือกด "ลบ" เพื่อลบถาวร</p>
    </div>

    <?php if(isset($_GET['saved'])): ?><div class="notice">บันทึกการแก้ไขเรียบร้อยแล้ว</div><?php endif; ?>
    <?php if(isset($_GET['updated'])): ?><div class="notice">อัปเดตสถานะเรียบร้อยแล้ว</div><?php endif; ?>
    <?php if(isset($_GET['deleted'])): ?><div class="notice">ลบร้านเรียบร้อยแล้ว</div><?php endif; ?>

    <?php if($editing): ?>
    <div class="edit-panel">
        <h2>แก้ไขร้าน: <?php echo htmlspecialchars($editing['name_shop']); ?></h2>
        <form method="POST" action="admin-shops.php">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id_shop" value="<?php echo $editing['id_shop']; ?>">

            <div class="row-2">
                <div class="field">
                    <label>ชื่อร้าน</label>
                    <input type="text" name="name_shop" value="<?php echo htmlspecialchars($editing['name_shop']); ?>" required>
                </div>
                <div class="field">
                    <label>หมวดหมู่</label>
                    <select name="category_shop" required>
                        <?php foreach(['ร้านอาหาร','คาเฟ่','ร้านค้า','อื่นๆ'] as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo $editing['category_shop'] === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="field">
                <label>ที่อยู่ / จุดสังเกต</label>
                <input type="text" name="address_shop" value="<?php echo htmlspecialchars($editing['address_shop'] ?? ''); ?>">
            </div>

            <div class="field">
                <label>รายละเอียด</label>
                <textarea name="description_shop"><?php echo htmlspecialchars($editing['description_shop'] ?? ''); ?></textarea>
            </div>

            <div class="row-3">
                <div class="field">
                    <label>ละติจูด</label>
                    <input type="text" name="lat_shop" value="<?php echo htmlspecialchars($editing['lat_shop']); ?>" required>
                </div>
                <div class="field">
                    <label>ลองจิจูด</label>
                    <input type="text" name="lng_shop" value="<?php echo htmlspecialchars($editing['lng_shop']); ?>" required>
                </div>
                <div class="field">
                    <label>path รูป</label>
                    <input type="text" name="image_shop" value="<?php echo htmlspecialchars($editing['image_shop'] ?? ''); ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
            <a href="admin-shops.php" class="btn btn-ghost-link">ยกเลิก</a>
        </form>
    </div>
    <?php endif; ?>

    <div class="shop-grid">
        <?php if(count($shops) === 0): ?>
            <div class="empty-hint">ยังไม่มีร้านที่สมาชิกเพิ่มเข้ามา</div>
        <?php endif; ?>

        <?php foreach($shops as $s): ?>
            <?php $is_visible = (int) $s['status_shop'] === 1; ?>
            <div class="shop-card <?php echo $is_visible ? '' : 'hidden'; ?>">
                <div class="thumb" style="<?php echo $s['image_shop'] ? "background-image:url('" . htmlspecialchars($s['image_shop']) . "')" : ''; ?>"></div>
                <div class="body">
                    <span class="tag"><?php echo htmlspecialchars($s['category_shop']); ?></span>
                    <h3><?php echo htmlspecialchars($s['name_shop']); ?></h3>
                    <p class="addr"><?php echo htmlspecialchars($s['address_shop'] ?: 'ไม่ระบุที่อยู่'); ?></p>
                    <p class="by">เพิ่มโดย <?php echo htmlspecialchars($s['username_account']); ?></p>
                    <div class="status <?php echo $is_visible ? 'show' : 'hide'; ?>">
                        <?php echo $is_visible ? '● กำลังแสดงผล' : '● ถูกซ่อนอยู่'; ?>
                    </div>
                    <div class="btn-row">
                        <a href="admin-shops.php?edit=<?php echo $s['id_shop']; ?>" class="btn btn-toggle">แก้ไข</a>
                        <a href="admin-shops.php?toggle=<?php echo $s['id_shop']; ?>" class="btn btn-toggle">
                            <?php echo $is_visible ? 'ซ่อน' : 'แสดงอีกครั้ง'; ?>
                        </a>
                        <a href="admin-shops.php?delete=<?php echo $s['id_shop']; ?>" class="btn btn-delete"
                           onclick="return confirm('ลบร้าน &quot;<?php echo htmlspecialchars($s['name_shop'], ENT_QUOTES); ?>&quot; ถาวรใช่ไหม?');">ลบ</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>