<?php
session_start();
$open_connect = 1;
require('connect.php');

if(!isset($_SESSION['id_account']) || $_SESSION['role_account'] != 'admin'){
    die(header('Location: form-login.php'));
}

$my_id = (int) $_SESSION['id_account'];
$errors = [];

// ---------- บันทึกการแก้ไข ----------
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save'){
    $id_account       = (int) ($_POST['id_account'] ?? 0);
    $username_account = trim($_POST['username_account'] ?? '');
    $email_account     = trim($_POST['email_account'] ?? '');
    $role_account       = $_POST['role_account'] ?? '';
    $unlock             = isset($_POST['unlock']);

    if($username_account === '' || $email_account === '' || !in_array($role_account, ['member', 'admin'], true)){
        $errors[] = 'กรุณากรอกชื่อผู้ใช้ อีเมล และเลือกสิทธิ์ให้ถูกต้อง';
    }elseif($id_account === $my_id && $role_account !== 'admin'){
        // กันแอดมินเผลอถอดสิทธิ์ตัวเองแล้วเข้าหน้านี้ไม่ได้อีก
        $errors[] = 'ไม่สามารถเปลี่ยนสิทธิ์ของบัญชีตัวเองออกจาก admin ได้ ให้ใช้บัญชี admin อื่นทำแทน';
    }else{
        $username_esc = mysqli_real_escape_string($connect, $username_account);
        $email_esc    = mysqli_real_escape_string($connect, $email_account);
        $role_esc     = mysqli_real_escape_string($connect, $role_account);

        $query = "UPDATE account SET
                    username_account = '$username_esc',
                    email_account = '$email_esc',
                    role_account = '$role_esc'";
        if($unlock){
            $query .= ", lock_account = 0, login_count_account = 0";
        }
        $query .= " WHERE id_account = $id_account";

        if(mysqli_query($connect, $query)){
            die(header('Location: admin-members.php?saved=1'));
        }else{
            $errors[] = 'บันทึกไม่สำเร็จ: อีเมลนี้อาจซ้ำกับบัญชีอื่น';
        }
    }
}

// ---------- โหลดบัญชีที่กำลังแก้ไข ----------
$editing = null;
if(isset($_GET['edit'])){
    $id_edit = (int) $_GET['edit'];
    $result_edit = mysqli_query($connect, "SELECT * FROM account WHERE id_account = $id_edit");
    $editing = mysqli_fetch_assoc($result_edit);
}

// ---------- รายการสมาชิกทั้งหมด ----------
$accounts = [];
$result_all = mysqli_query($connect, "SELECT * FROM account ORDER BY id_account ASC");
while($row = mysqli_fetch_assoc($result_all)){
    $accounts[] = $row;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>จัดการสมาชิก | Admin</title>
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

    .edit-panel{ background: var(--panel); border-radius: 20px; padding: 1.5rem; margin-bottom: 1.5rem; max-width: 32rem; }
    .edit-panel h2{ margin: 0 0 1rem; font-size: 1.05rem; font-weight: 700; }
    label{ display: block; font-size: 0.8rem; color: var(--ink-soft); margin: 0 0 0.3rem; }
    .field{ margin-bottom: 0.9rem; }
    input[type="text"], input[type="email"], select{
        width: 100%; border: 1px solid var(--line); border-radius: 10px;
        padding: 0.55rem 0.7rem; font-family: 'Prompt', sans-serif; font-size: 0.88rem;
        background: #fff; color: var(--ink);
    }
    input:focus, select:focus{ outline: none; border-color: var(--green); }
    .checkbox-row{ display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; font-size: 0.85rem; color: var(--ink-soft); }
    .checkbox-row input{ width: auto; }

    .btn{ border: none; border-radius: 999px; padding: 0.6rem 1.2rem; font-family: 'Prompt', sans-serif; font-size: 0.85rem; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn-primary{ background: var(--green); color: #fff; }
    .btn-primary:hover{ background: var(--green-deep); }
    .btn-ghost{ background: transparent; border: 1px solid var(--line); color: var(--ink-soft); }

    table{ width: 100%; border-collapse: collapse; background: #fff; border-radius: var(--radius); overflow: hidden; margin-bottom: 3rem; }
    th, td{ text-align: left; padding: 0.65rem 0.8rem; font-size: 0.85rem; border-bottom: 1px solid var(--line); }
    th{ background: var(--panel); font-weight: 600; }
    tr:last-child td{ border-bottom: none; }
    .role-tag{ font-size: 0.72rem; font-weight: 600; padding: 0.15rem 0.6rem; border-radius: 999px; }
    .role-tag.admin{ background: #eadcc3; color: #7a5a1e; }
    .role-tag.member{ background: var(--panel); color: var(--green-deep); }
    .lock-tag{ font-size: 0.72rem; color: #b3413a; }
    .table-wrap{ overflow-x: auto; }
    .edit-link{ font-size: 0.82rem; color: var(--green-deep); text-decoration: none; }
</style>
</head>
<body>

<nav class="nav">
    <div class="wrap">
        <div class="logo">เที่ยวตาก ADMIN</div>
        <ul class="nav-links">
            <li><a href="admin.php">แดชบอร์ด</a></li>
            <li><a href="admin-places.php">สถานที่เที่ยว</a></li>
            <li><a href="admin-shops.php">ร้านค้า</a></li>
            <li><a href="admin-members.php" class="active">สมาชิก</a></li>
            <li><a href="admin.php?logout=1">ออกจากระบบ</a></li>
        </ul>
    </div>
</nav>

<div class="wrap">
    <div class="page-head">
        <h1>จัดการสมาชิก</h1>
        <p>แก้ไขข้อมูลบัญชี ปลดล็อกบัญชีที่ถูกระงับ หรือกำหนดว่าใครเป็น admin ได้จากหน้านี้</p>
    </div>

    <?php if(isset($_GET['saved'])): ?><div class="notice ok">บันทึกข้อมูลเรียบร้อยแล้ว</div><?php endif; ?>
    <?php foreach($errors as $e): ?><div class="notice err"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>

    <?php if($editing): ?>
    <div class="edit-panel">
        <h2>แก้ไขบัญชี: <?php echo htmlspecialchars($editing['username_account']); ?></h2>
        <form method="POST" action="admin-members.php">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id_account" value="<?php echo $editing['id_account']; ?>">

            <div class="field">
                <label>ชื่อผู้ใช้</label>
                <input type="text" name="username_account" value="<?php echo htmlspecialchars($editing['username_account']); ?>" required>
            </div>
            <div class="field">
                <label>อีเมล</label>
                <input type="email" name="email_account" value="<?php echo htmlspecialchars($editing['email_account']); ?>" required>
            </div>
            <div class="field">
                <label>สิทธิ์การใช้งาน</label>
                <select name="role_account" required>
                    <option value="member" <?php echo $editing['role_account'] === 'member' ? 'selected' : ''; ?>>member (สมาชิกทั่วไป)</option>
                    <option value="admin" <?php echo $editing['role_account'] === 'admin' ? 'selected' : ''; ?>>admin (ผู้ดูแลระบบ)</option>
                </select>
            </div>

            <?php if((int) $editing['lock_account'] === 1): ?>
            <div class="checkbox-row">
                <input type="checkbox" id="unlock" name="unlock" value="1">
                <label for="unlock" style="margin:0;">บัญชีนี้ถูกระงับอยู่ — ติ๊กเพื่อปลดล็อกทันที</label>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
            <a href="admin-members.php" class="btn btn-ghost">ยกเลิก</a>
        </form>
    </div>
    <?php endif; ?>

    <div class="table-wrap">
        <table>
            <tr>
                <th>#</th>
                <th>ชื่อผู้ใช้</th>
                <th>อีเมล</th>
                <th>สิทธิ์</th>
                <th>สถานะ</th>
                <th></th>
            </tr>
            <?php foreach($accounts as $a): ?>
            <tr>
                <td><?php echo $a['id_account']; ?></td>
                <td><?php echo htmlspecialchars($a['username_account']); ?></td>
                <td><?php echo htmlspecialchars($a['email_account']); ?></td>
                <td><span class="role-tag <?php echo $a['role_account']; ?>"><?php echo $a['role_account']; ?></span></td>
                <td><?php echo (int) $a['lock_account'] === 1 ? '<span class="lock-tag">● ถูกระงับ</span>' : '● ใช้งานได้'; ?></td>
                <td><a href="admin-members.php?edit=<?php echo $a['id_account']; ?>" class="edit-link">แก้ไข</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

</body>
</html>