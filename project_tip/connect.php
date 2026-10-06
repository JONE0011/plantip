<?php

if ($open_connect != 1) {
    die(header('Location: form-login.php'));
}

$hostname = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'programming_world';
$port = (int)(getenv('DB_PORT') ?: 3306);

$connect = mysqli_init();
$caFile = getenv('DB_SSL_CA') ?: '';
$flags = 0;

if ($caFile && is_readable($caFile)) {
    mysqli_ssl_set($connect, null, null, $caFile, null, null);
    $flags = MYSQLI_CLIENT_SSL;
}

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
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว : " . mysqli_connect_error());
}

mysqli_set_charset($connect, 'utf8mb4');
// Community reviews and 1-5 star ratings
mysqli_query($connect, "CREATE TABLE IF NOT EXISTS reviews (
    id_review BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_account INT(11) NOT NULL,
    item_type ENUM('place','shop') NOT NULL,
    id_place INT(11) NULL,
    id_shop INT(11) NULL,
    rating TINYINT UNSIGNED NOT NULL,
    review_text TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_review),
    UNIQUE KEY uniq_account_item (id_account, item_type, id_place, id_shop),
    KEY idx_review_place (id_place),
    KEY idx_review_shop (id_shop),
    KEY idx_review_type_rating (item_type, rating),
    CONSTRAINT fk_review_account FOREIGN KEY (id_account) REFERENCES account(id_account) ON DELETE CASCADE,
    CONSTRAINT fk_review_place FOREIGN KEY (id_place) REFERENCES place(id_place) ON DELETE CASCADE,
    CONSTRAINT fk_review_shop FOREIGN KEY (id_shop) REFERENCES shop(id_shop) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

// เพิ่มรายละเอียดสถานที่และเจ้าของสถานที่สำหรับระบบสมาชิก
$place_desc = mysqli_query($connect, "SHOW COLUMNS FROM place LIKE 'description_place'");
if (!$place_desc || mysqli_num_rows($place_desc) === 0) {
    mysqli_query($connect, "ALTER TABLE place ADD COLUMN description_place TEXT NULL AFTER category_place");
}

// รองรับเจ้าของสถานที่: สมาชิกสามารถแก้ไขได้เฉพาะสถานที่ที่ตัวเองเพิ่ม
$place_col = mysqli_query($connect, "SHOW COLUMNS FROM place LIKE 'id_account'");
if (!$place_col || mysqli_num_rows($place_col) === 0) {
    mysqli_query($connect, "ALTER TABLE place ADD COLUMN id_account INT(11) NULL AFTER id_place");
}
$place_idx = mysqli_query($connect, "SHOW INDEX FROM place WHERE Key_name = 'idx_place_account'");
if (!$place_idx || mysqli_num_rows($place_idx) === 0) {
    mysqli_query($connect, "ALTER TABLE place ADD KEY idx_place_account (id_account)");
}
$place_fk = mysqli_query($connect, "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'place' AND CONSTRAINT_NAME = 'fk_place_account'");
if (!$place_fk || mysqli_num_rows($place_fk) === 0) {
    mysqli_query($connect, "ALTER TABLE place ADD CONSTRAINT fk_place_account FOREIGN KEY (id_account) REFERENCES account(id_account) ON DELETE SET NULL");
}

// รองรับทริปที่มีทั้งสถานที่เที่ยวและร้านอาหาร/คาเฟ่/ร้านค้า
// ตรวจสอบก่อนปรับ schema เพื่อไม่ให้ ALTER TABLE เพิ่มคอลัมน์/ดัชนีซ้ำทุกครั้งที่เปิดหน้าเว็บ
$trip_columns = [];
$column_result = mysqli_query($connect, "SHOW COLUMNS FROM trip_place");
if ($column_result) {
    while ($column = mysqli_fetch_assoc($column_result)) {
        $trip_columns[$column['Field']] = true;
    }
}
if (isset($trip_columns['id_place'])) {
    mysqli_query($connect, "ALTER TABLE trip_place MODIFY id_place INT(11) NULL");
}
if (!isset($trip_columns['item_type'])) {
    mysqli_query($connect, "ALTER TABLE trip_place ADD COLUMN item_type VARCHAR(20) NOT NULL DEFAULT 'place' AFTER id_account");
}
if (!isset($trip_columns['id_shop'])) {
    mysqli_query($connect, "ALTER TABLE trip_place ADD COLUMN id_shop INT(11) NULL AFTER id_place");
}

$index_result = mysqli_query($connect, "SHOW INDEX FROM trip_place WHERE Key_name = 'idx_trip_shop'");
if ($index_result && mysqli_num_rows($index_result) === 0) {
    mysqli_query($connect, "ALTER TABLE trip_place ADD KEY idx_trip_shop (id_shop)");
}

$fk_result = mysqli_query($connect, "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'trip_place'
    AND CONSTRAINT_NAME = 'trip_place_shop_fk' LIMIT 1");
if ($fk_result && mysqli_num_rows($fk_result) === 0) {
    mysqli_query($connect, "ALTER TABLE trip_place ADD CONSTRAINT trip_place_shop_fk
        FOREIGN KEY (id_shop) REFERENCES shop(id_shop) ON DELETE CASCADE");
}

// เก็บประวัติสถานที่ที่สมาชิกเคยเพิ่มเข้าทริป แยกจากทริปปัจจุบัน
mysqli_query($connect, "CREATE TABLE IF NOT EXISTS place_history (
    id_history BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_account INT(11) NOT NULL,
    id_place INT(11) NOT NULL,
    added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_history),
    UNIQUE KEY uniq_account_place_history (id_account, id_place),
    KEY idx_history_place (id_place),
    CONSTRAINT fk_history_account FOREIGN KEY (id_account) REFERENCES account(id_account) ON DELETE CASCADE,
    CONSTRAINT fk_history_place FOREIGN KEY (id_place) REFERENCES place(id_place) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

$limit_login_account = 3;
$time_ban_account = 1;

$query_reset_ban_account = "
    UPDATE account
    SET lock_account = 0, login_count_account = 0
    WHERE ban_account <= NOW()
    AND login_count_account >= '$limit_login_account'
";

$call_back_reset_ban_account = mysqli_query($connect, $query_reset_ban_account);
?>