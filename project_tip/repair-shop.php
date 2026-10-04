<?php
$open_connect = 1;
require('connect.php');
if(($_GET['key'] ?? '') !== 'takfix-shop-2026') { http_response_code(404); exit; }
mysqli_set_charset($connect,'utf8mb4');
mysqli_query($connect, "UPDATE shop SET name_shop='มีกินมีดอง', category_shop='ร้านอาหาร', description_shop='เปิด 8:00 - 16:00', address_shop='มทร.ตาก' WHERE id_shop=1");
echo 'OK';
?>