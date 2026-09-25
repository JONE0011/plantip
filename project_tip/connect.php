<?php

if($open_connect != 1){
    die(header('Location: form-login.php'));
}

// รองรับทั้ง XAMPP และ Render
$hostname = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'programming_world';
$port = getenv('DB_PORT') ?: '3306';

$connect = mysqli_connect(
    $hostname,
    $username,
    $password,
    $database,
    (int)$port
);

if(!$connect){
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว : " . mysqli_connect_error());
}else{
    mysqli_set_charset($connect, 'utf8mb4');

    $limit_login_account = 3; // จำนวนครั้งที่กรอกรหัสผ่านผิดได้
    $time_ban_account = 1; // จำนวนนาทีที่ระงับบัญชี

    $query_reset_ban_account = "
        UPDATE account 
        SET lock_account = 0, login_count_account = 0 
        WHERE ban_account <= NOW() 
        AND login_count_account >= '$limit_login_account'
    ";

    $call_back_reset_ban_account = mysqli_query(
        $connect,
        $query_reset_ban_account
    );
}

?>
