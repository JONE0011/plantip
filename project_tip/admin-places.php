<?php
session_start();
$open_connect = 1;
require('connect.php');

if(!isset($_SESSION['id_account']) || $_SESSION['role_account'] != 'admin'){
    die(header('Location: form-login.php'));
}

$errors = [];

// ---------- บันทึก (เพิ่มใหม่ หรือแก้ไขของเดิม) ----------
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save'){
    $id_place       = (int) ($_POST['id_place'] ?? 0);
    $place_key      = trim($_POST['place_key'] ?? '');
    $name_place     = trim($_POST['name_place'] ?? '');
    $location_place = trim($_POST['location_place'] ?? '');
    $category_place = trim($_POST['category_place'] ?? '');
    $lat_place      = $_POST['lat_place'] ?? '';
    $lng_place      = $_POST['lng_place'] ?? '';
    $image_place    = trim($_POST['image_place'] ?? '');

    if($place_key === '' || $name_place === '' || $location_place === '' || !is_numeric($lat_place) || !is_numeric($lng_place)){
        $errors[] = 'กรุณากรอก place_key, ชื่อสถานที่, ที่ตั้ง และพิกัด (ตัวเลข) ให้ครบ';
    }else{
        $place_key_esc      = mysqli_real_escape_string($connect, $place_key);
        $name_place_esc     = mysqli_real_escape_string($connect, $name_place);
        $location_place_esc = mysqli_real_escape_string($connect, $location_place);
        $category_place_esc = mysqli_real_escape_string($connect, $category_place);
        $image_place_esc    = mysqli_real_escape_string($connect, $image_place);
        $lat_place_f = (float) $lat_place;
        $lng_place_f = (float) $lng_place;

        if($id_place > 0){
            $query = "UPDATE place SET
                        place_key = '$place_key_esc',
                        name_place = '$name_place_esc',
                        location_place = '$location_place_esc',
                        category_place = '$category_place_esc',
                        lat_place = '$lat_place_f',
                        lng_place = '$lng_place_f',
                        image_place = '$image_place_esc'
                      WHERE id_place = $id_place";
        }else{
            $query = "INSERT INTO place (place_key, name_place, location_place, category_place, lat_place, lng_place, image_place)
                      VALUES ('$place_key_esc', '$name_place_esc', '$location_place_esc', '$category_place_esc', '$lat_place_f', '$lng_place_f', '$image_place_esc')";
        }

        if(mysqli_query($connect, $query)){
            die(header('Location: admin-places.php?saved=1'));
        }else{
            $errors[] = 'บันทึกไม่สำเร็จ: place_key นี้อาจซ้ำกับที่มีอยู่แล้ว';
        }
    }
}

// ---------- ลบ ----------
if(isset($_GET['delete'])){
    $id_delete = (int) $_GET['delete'];
    mysqli_query($connect, "DELETE FROM place WHERE id_place = $id_delete");
    die(header('Location: admin-places.php?deleted=1'));
}

// ---------- โหลดข้อมูลสำหรับฟอร์มแก้ไข (ถ้ามี ?edit=) ----------
$editing = null;
if(isset($_GET['edit'])){
    $id_edit = (int) $_GET['edit'];
    $result_edit = mysqli_query($connect, "SELECT * FROM place WHERE id_place = $id_edit");
    $editing = mysqli_fetch_assoc($result_edit);
}

// ---------- รายการทั้งหมด ----------
$places = [];
$result_all = mysqli_query($connect, "SELECT * FROM place ORDER BY id_place DESC");
while($row = mysqli_fetch_assoc($result_all)){
    $places[] = $row;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>จัดการสถานที่เที่ยว | Admin</title>
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

    .notice{ border-radius: 10px; padding: 0.7rem 1rem; font-size: 0.85rem; margin-bottom: 1.2rem; }
    .notice.ok{ background: #e8f3ec; border: 1px solid #a9d3b8; color: var(--green-deep); }
    .notice.err{ background: #fdeceb; border: 1px solid #f2b8b5; color: #8a2c25; }

    .layout{ display: grid; grid-template-columns: 0.9fr 1.3fr; gap: 1.5rem; padding-bottom: 3rem; }
    @media (max-width: 950px){ .layout{ grid-template-columns: 1fr; } }

    .panel-card{ background: var(--panel); border-radius: 20px; padding: 1.5rem; }
    .panel-card h2{ margin: 0 0 1rem; font-size: 1.05rem; font-weight: 700; }

    label{ display: block; font-size: 0.8rem; color: var(--ink-soft); margin: 0 0 0.3rem; }
    .field{ margin-bottom: 0.9rem; }
    input[type="text"], input[type="number"]{
        width: 100%; border: 1px solid var(--line); border-radius: 10px;
        padding: 0.55rem 0.7rem; font-family: 'Prompt', sans-serif; font-size: 0.88rem;
        background: #fff; color: var(--ink);
    }
    input:focus{ outline: none; border-color: var(--green); }
    .row-2{ display: grid; grid-template-columns: 1fr 1fr; gap: 0.7rem; }

    .btn{
        border: none; border-radius: 999px; padding: 0.65rem 1.3rem;
        font-family: 'Prompt', sans-serif; font-size: 0.85rem; font-weight: 500; cursor: pointer;
    }
    .btn-primary{ background: var(--green); color: #fff; }
    .btn-primary:hover{ background: var(--green-deep); }
    .btn-ghost{ background: transparent; border: 1px solid var(--line); color: var(--ink-soft); text-decoration: none; display: inline-block; }

    table{ width: 100%; border-collapse: collapse; background: #fff; border-radius: var(--radius); overflow: hidden; }
    th, td{ text-align: left; padding: 0.65rem 0.8rem; font-size: 0.82rem; border-bottom: 1px solid var(--line); }
    th{ background: var(--panel); font-weight: 600; }
    tr:last-child td{ border-bottom: none; }
    .actions-cell{ display: flex; gap: 0.6rem; white-space: nowrap; }
    .actions-cell a{ font-size: 0.8rem; text-decoration: none; }
    .actions-cell .edit{ color: var(--green-deep); }
    .actions-cell .del{ color: #b3413a; }
    .table-wrap{ overflow-x: auto; }
</style>
</head>
<body>

<nav class="nav">
    <div class="wrap">
        <div class="logo">เที่ยวตาก ADMIN</div>
        <ul class="nav-links">
            <li><a href="admin.php">แดชบอร์ด</a></li>
            <li><a href="admin-places.php" class="active">สถานที่เที่ยว</a></li>
            <li><a href="admin-shops.php">ร้านค้า</a></li>
            <li><a href="admin.php?logout=1">ออกจากระบบ</a></li>
        </ul>
    </div>
</nav>

<div class="wrap">
    <div class="page-head">
        <h1>จัดการสถานที่เที่ยว</h1>
        <p>ข้อมูลชุดนี้ใช้แสดงบนหน้าแรกและหน้าวางแผนทริป — place_key ต้องไม่ซ้ำกัน เพราะเป็นตัวเชื่อมกับทริปที่สมาชิกบันทึกไว้</p>
    </div>

    <?php if(isset($_GET['saved'])): ?><div class="notice ok">บันทึกข้อมูลเรียบร้อยแล้ว</div><?php endif; ?>
    <?php if(isset($_GET['deleted'])): ?><div class="notice ok">ลบสถานที่เรียบร้อยแล้ว</div><?php endif; ?>
    <?php foreach($errors as $e): ?><div class="notice err"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>

    <div class="layout">

        <div class="panel-card">
            <h2><?php echo $editing ? 'แก้ไขสถานที่' : 'เพิ่มสถานที่ใหม่'; ?></h2>
            <form method="POST" action="admin-places.php">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id_place" value="<?php echo $editing['id_place'] ?? 0; ?>">

                <div class="field">
                    <label>place_key (ภาษาอังกฤษ, ไม่เว้นวรรค เช่น thi-lo-su)</label>
                    <input type="text" name="place_key" value="<?php echo htmlspecialchars($editing['place_key'] ?? ''); ?>" required>
                </div>
                <div class="field">
                    <label>ชื่อสถานที่</label>
                    <input type="text" name="name_place" value="<?php echo htmlspecialchars($editing['name_place'] ?? ''); ?>" required>
                </div>
                <div class="field">
                    <label>ที่ตั้ง (เช่น อ.อุ้มผาง)</label>
                    <input type="text" name="location_place" value="<?php echo htmlspecialchars($editing['location_place'] ?? ''); ?>" required>
                </div>
                <div class="field">
                    <label>หมวดหมู่ (เช่น น้ำตก, ทะเลหมอก, ชายแดน)</label>
                    <input type="text" name="category_place" value="<?php echo htmlspecialchars($editing['category_place'] ?? ''); ?>">
                </div>
                <div class="row-2">
                    <div class="field">
                        <label>ละติจูด</label>
                        <input type="text" name="lat_place" value="<?php echo htmlspecialchars($editing['lat_place'] ?? ''); ?>" required>
                    </div>
                    <div class="field">
                        <label>ลองจิจูด</label>
                        <input type="text" name="lng_place" value="<?php echo htmlspecialchars($editing['lng_place'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="field">
                    <label>path รูป (เช่น images/dest-thilosu.jpg)</label>
                    <input type="text" name="image_place" value="<?php echo htmlspecialchars($editing['image_place'] ?? ''); ?>">
                </div>

                <button type="submit" class="btn btn-primary"><?php echo $editing ? 'บันทึกการแก้ไข' : 'เพิ่มสถานที่'; ?></button>
                <?php if($editing): ?>
                    <a href="admin-places.php" class="btn btn-ghost">ยกเลิก</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="panel-card">
            <h2>สถานที่ทั้งหมด (<?php echo count($places); ?>)</h2>
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>ชื่อ</th>
                        <th>place_key</th>
                        <th>ที่ตั้ง</th>
                        <th>หมวดหมู่</th>
                        <th></th>
                    </tr>
                    <?php foreach($places as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['name_place']); ?></td>
                        <td><?php echo htmlspecialchars($p['place_key']); ?></td>
                        <td><?php echo htmlspecialchars($p['location_place']); ?></td>
                        <td><?php echo htmlspecialchars($p['category_place']); ?></td>
                        <td class="actions-cell">
                            <a href="admin-places.php?edit=<?php echo $p['id_place']; ?>" class="edit">แก้ไข</a>
                            <a href="admin-places.php?delete=<?php echo $p['id_place']; ?>" class="del"
                               onclick="return confirm('ลบ &quot;<?php echo htmlspecialchars($p['name_place'], ENT_QUOTES); ?>&quot; ใช่ไหม? ทริปที่สมาชิกบันทึกจุดนี้ไว้จะถูกลบไปด้วย');">ลบ</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

    </div>
</div>

</body>
</html>