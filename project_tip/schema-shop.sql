-- ============================================================
-- เพิ่มเข้าไปในฐานข้อมูล test_world (ฐานเดียวกับตาราง account, place, trip_place)
-- รันผ่าน phpMyAdmin > SQL หรือ Import
-- ============================================================

-- ร้านอาหาร/คาเฟ่/ร้านค้าที่สมาชิกช่วยกันเพิ่มเข้ามา (แบบ Google Maps)
CREATE TABLE IF NOT EXISTS shop (
    id_shop INT AUTO_INCREMENT PRIMARY KEY,
    id_account INT NOT NULL,                      -- สมาชิกที่เพิ่มร้านนี้
    name_shop VARCHAR(150) NOT NULL,
    category_shop VARCHAR(30) NOT NULL,            -- 'ร้านอาหาร', 'คาเฟ่', 'ร้านค้า', 'อื่นๆ'
    description_shop TEXT DEFAULT NULL,
    address_shop VARCHAR(255) DEFAULT NULL,
    lat_shop DECIMAL(10,7) NOT NULL,
    lng_shop DECIMAL(10,7) NOT NULL,
    image_shop VARCHAR(255) DEFAULT NULL,          -- path รูป เช่น images_shop/xxxx.jpg
    status_shop TINYINT(1) NOT NULL DEFAULT 1,     -- 1 = แสดงผล, 0 = ซ่อน (เผื่อระบบ admin ตรวจสอบภายหลัง)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_account) REFERENCES account(id_account) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
