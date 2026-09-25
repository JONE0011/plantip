-- ============================================================
-- เพิ่มเข้าไปในฐานข้อมูล test_world (ฐานเดียวกับตาราง account)
-- รันไฟล์นี้ผ่าน phpMyAdmin > Import หรือแท็บ SQL
-- ============================================================

-- ตารางสถานที่เที่ยว (master data — ใช้แทน PLACES ที่เคย hardcode ไว้ใน JS)
CREATE TABLE IF NOT EXISTS place (
    id_place INT AUTO_INCREMENT PRIMARY KEY,
    place_key VARCHAR(50) NOT NULL UNIQUE,      -- ใช้เชื่อมกับ data-place="..." ฝั่งหน้าเว็บ
    name_place VARCHAR(150) NOT NULL,
    location_place VARCHAR(150) NOT NULL,        -- เช่น "อ.อุ้มผาง"
    category_place VARCHAR(50) DEFAULT NULL,     -- เช่น "น้ำตก", "ชายแดน"
    lat_place DECIMAL(10,7) NOT NULL,
    lng_place DECIMAL(10,7) NOT NULL,
    image_place VARCHAR(255) DEFAULT NULL,       -- path รูป เช่น images/dest-thilosu.jpg
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO place (place_key, name_place, location_place, category_place, lat_place, lng_place, image_place) VALUES
('thi-lo-su',         'น้ำตกทีลอซู',                   'อ.อุ้มผาง',   'น้ำตก',      15.7003000, 98.6294000, 'images/dest-thilosu.jpg'),
('doi-musoe',         'ดอยมูเซอ',                       'อ.เมืองตาก',  'ทะเลหมอก',   16.8300000, 98.7500000, 'images/dest-doimusoe.jpg'),
('bhumibol-dam',      'เขื่อนภูมิพล',                   'อ.สามเงา',    'ธรรมชาติ',   17.2400000, 98.9700000, 'images/dest-bhumibol.jpg'),
('mae-sot-market',    'ตลาดริมเมย',                     'อ.แม่สอด',    'ชายแดน',     16.6989000, 98.5347000, 'images/dest-maesot.jpg'),
('lan-sang',          'อุทยานแห่งชาติลานสาง',           'อ.เมืองตาก',  'ธรรมชาติ',   16.8500000, 98.8500000, 'images/dest-lansang.jpg'),
('taksin-maharat',    'อุทยานแห่งชาติตากสินมหาราช',     'อ.บ้านตาก',   'ธรรมชาติ',   17.0000000, 98.8300000, 'images/dest-taksinmaharat.jpg'),
('wat-borommathat',   'วัดพระบรมธาตุ บ้านตาก',          'อ.บ้านตาก',   'วัฒนธรรม',   17.0200000, 99.0700000, 'images/dest-watborommathat.jpg'),
('friendship-bridge', 'สะพานมิตรภาพไทย–เมียนมา',        'อ.แม่สอด',    'ชายแดน',     16.7000000, 98.5300000, 'images/dest-friendshipbridge.jpg')
ON DUPLICATE KEY UPDATE name_place = VALUES(name_place);

-- ทริปของแต่ละสมาชิก: 1 แถว = 1 สถานที่ที่อยู่ในทริปปัจจุบันของ account นั้น
-- order_no คือลำดับ 1, 2, 3, ... ที่ผู้ใช้จัดเอง
CREATE TABLE IF NOT EXISTS trip_place (
    id_trip_place INT AUTO_INCREMENT PRIMARY KEY,
    id_account INT NOT NULL,
    id_place INT NOT NULL,
    order_no INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_account_place (id_account, id_place),
    FOREIGN KEY (id_account) REFERENCES account(id_account) ON DELETE CASCADE,
    FOREIGN KEY (id_place) REFERENCES place(id_place) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
