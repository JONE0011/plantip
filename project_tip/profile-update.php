<?php
session_start();
$open_connect = 1;
require('connect.php');

if(isset($_GET['logout'])){
    $_SESSION = [];
    if(ini_get('session.use_cookies')){
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header('Location: home.php');
    exit;
}
if(!isset($_SESSION['id_account'])){ header('Location: form-login.php'); exit; }

$id = (int)$_SESSION['id_account'];
if($_SERVER['REQUEST_METHOD'] !== 'POST'){ header('Location: home.php'); exit; }

$name = trim($_POST['username_account'] ?? '');
if($name === '') $name = 'สมาชิก';

$name = mb_substr($name, 0, 40);
$stmt = mysqli_prepare($connect, "UPDATE account SET username_account = ? WHERE id_account = ?");
mysqli_stmt_bind_param($stmt, 'si', $name, $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if(isset($_FILES['images_account']) && $_FILES['images_account']['error'] !== UPLOAD_ERR_NO_FILE){
    $file = $_FILES['images_account'];
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : '';
    if($file['error'] === UPLOAD_ERR_OK && isset($allowed[$mime]) && $file['size'] <= 5*1024*1024){
        $dir = __DIR__ . '/images_account';
        if(!is_dir($dir)) mkdir($dir, 0755, true);
        $newName = 'profile_' . $id . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
        $target = $dir . '/' . $newName;
        if(move_uploaded_file($file['tmp_name'], $target)){
            $oldQ = mysqli_query($connect, "SELECT images_account FROM account WHERE id_account = $id");
            $old = $oldQ ? mysqli_fetch_assoc($oldQ) : null;
            $up = mysqli_prepare($connect, "UPDATE account SET images_account = ? WHERE id_account = ?");
            mysqli_stmt_bind_param($up, 'si', $newName, $id);
            mysqli_stmt_execute($up);
            mysqli_stmt_close($up);
            if($old && !empty($old['images_account']) && $old['images_account'] !== 'default_images_account.jpg'){
                $oldPath = $dir . '/' . basename($old['images_account']);
                if(is_file($oldPath)) @unlink($oldPath);
            }
        }
    }
}
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'home.php'));
exit;
?>