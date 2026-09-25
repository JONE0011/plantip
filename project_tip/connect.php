<?php

if ($open_connect != 1) {
    die(header('Location: form-login.php'));
}

/*
 * Database connection
 * รองรับ XAMPP + Render + Aiven MySQL
 */

$hostname = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'programming_world';
$port = (int)(getenv('DB_PORT') ?: 3306);

$connect = mysqli_init();

/*
 * Aiven MySQL ใช้ SSL
 */
$caFile = getenv('DB_SSL_CA') ?: '';

if ($caFile && is_readable($caFile)) {

    mysqli_ssl_set(
        $connect,
        null,
        null,
        $caFile,
        null,
        null
    );

    $flags = MYSQLI_CLIENT_SSL;

} else {

    $flags = 0;
}

/*
 * เชื่อมต่อฐานข้อมูล
 */
if (!mysqli_real_connect(
    $connect,
    $hostname,
    $username,
    $password,
    $database,
    $port,
    null,
    $flags
)) {

    die(
        "การเชื่อมต่อฐานข้อมูลล้มเหลว : "
        . mysqli_connect_error()
    );
}

/*
 * ตั้งค่าภาษาไทย
 */
mysqli_set_charset($connect, 'utf8mb4');


/*
 * ระบบล็อกอิน
 */
$limit_login_account = 3;
$time_ban_account = 1;


/*
 * ปลดล็อกบัญชีที่หมดเวลาแบน
 */
$query_reset_ban_account = "
    UPDATE account
    SET
        lock_account = 0,
        login_count_account = 0
    WHERE ban_account <= NOW()
    AND login_count_account >= '$limit_login_account'
";

$call_back_reset_ban_account = mysqli_query(
    $connect,
    $query_reset_ban_account
);

?>
