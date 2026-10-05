<?php
$open_connect=1; require '/var/www/html/connect.php';
$owner=1;
$places=[
['doi-hua-mot','ดอยหัวหมด','อ.อุ้มผาง','ทะเลหมอก',16.0000000,98.8600000,'images/places/doi-hua-mot.jpg'],
['mae-moei','อุทยานแห่งชาติแม่เมย','อ.ท่าสองยาง','ธรรมชาติ',17.5500000,98.0500000,'images/places/mae-moei.jpg'],
['doi-thule','ดอยทูเล','อ.ท่าสองยาง','ภูเขา',17.0800000,98.4200000,'images/places/doi-thule.jpg'],
['pha-charoen','น้ำตกพาเจริญ','อ.พบพระ','น้ำตก',16.3900000,98.7000000,'images/places/pha-charoen.jpg'],
['pa-wai','น้ำตกป่าไหว','อ.พบพระ','น้ำตก',16.2500000,98.6500000,'images/places/pa-wai.jpg'],
['thararak','น้ำตกธารารักษ์','อ.แม่สอด','น้ำตก',16.5800000,98.6100000,'images/places/thararak.jpg'],
['doi-phawo','ดอยพะวอ','อ.แม่สอด','ทะเลหมอก',16.7696300,98.6832200,'images/places/doi-phawo.jpg'],
['mae-kasa','บ่อน้ำพุร้อนแม่กาษา','อ.แม่สอด','ธรรมชาติ',16.8400000,98.6000000,'images/places/mae-kasa.jpg'],
['lan-rao-ma','น้ำตกลานเลี้ยงม้า','อ.เมืองตาก','น้ำตก',16.8500000,98.8500000,'images/places/lan-rao-ma.jpg'],
['wat-borom-new','วัดพระบรมธาตุ บ้านตาก','อ.บ้านตาก','วัฒนธรรม',17.0200000,99.0700000,'images/places/wat-borom-new.jpg'],
['king-taksin-shrine','ศาลสมเด็จพระเจ้าตากสินมหาราช','อ.เมืองตาก','วัฒนธรรม',16.8750000,99.1250000,'images/places/king-taksin-shrine.jpg'],
['bridge-200','สะพานแขวนสมโภชกรุงรัตนโกสินทร์ 200 ปี','อ.เมืองตาก','แลนด์มาร์ก',16.8750000,99.1260000,'images/places/bridge-200.jpg'],
['wat-thai-wattanaram','วัดไทยวัฒนาราม','อ.แม่สอด','วัฒนธรรม',16.7160000,98.5750000,'images/places/wat-thai-wattanaram.jpg'],
['trok-ban-chin','ตรอกบ้านจีน','อ.เมืองตาก','ชุมชนเก่า',16.8840000,99.1260000,'images/places/trok-ban-chin.jpg'],
['chao-pho-phawo','ศาลเจ้าพ่อพะวอ','อ.แม่สอด','วัฒนธรรม',16.7700000,98.6800000,'images/places/doi-phawo.jpg'],
['khun-phawo','อุทยานแห่งชาติขุนพะวอ','อ.แม่ระมาด','ธรรมชาติ',17.0385000,98.6277000,'images/places/mae-moei.jpg'],
['doi-luang-tak','ดอยหลวงตาก','อ.บ้านตาก','ภูเขา',17.0800000,98.8200000,'images/places/doi-hua-mot.jpg'],
['doi-muser-market','ตลาดมูเซอ','อ.แม่สอด','ตลาดชุมชน',16.8300000,98.7500000,'images/places/doi-musoe.jpg'],
['phra-that-rattana-chedi','พระธาตุรัตนเจดีย์','อ.ท่าสองยาง','วัฒนธรรม',17.5200000,98.0600000,'images/places/wat-borom-new.jpg'],
['thi-lo-cho','น้ำตกทีลอจ่อ','อ.อุ้มผาง','น้ำตก',16.0500000,98.8700000,'images/places/thi-lo-su-2.jpg']
];
$shops=[
['Borderline Tea Garden','ร้านอาหาร','อาหารพม่าและอาหารมังสวิรัติ บรรยากาศสวนและงานศิลปะ','674/14 ถนนอินทรคีรี อ.แม่สอด',16.7149102,98.5609835,'images_shop/borderline.jpg'],
['Krua Canadian Restaurant','ร้านอาหาร','อาหารไทย เม็กซิกัน และอาหารตะวันตกในแม่สอด','ถนนศรีพานิช อ.แม่สอด',16.7155000,98.5700000,'images_shop/bite-me.jpg'],
['The Passport Restaurant Maesot','ร้านอาหาร','ร้านอาหารและ French Bakery ภายใน HCTC แม่สอด','อ.แม่สอด',16.6940000,98.5740000,'images_shop/sun-secrets.jpg'],
['ไฉไลติ่มซำ','ร้านอาหาร','ติ่มซำ อาหารจีน และอาหารเช้าหลากหลายเมนู','19/12-13 ถนนสายเอเชีย อ.แม่สอด',16.7140000,98.5750000,'images_shop/chailai.jpg'],
['No Two บาร์บีคิว & นูดเดิลส์','ร้านอาหาร','บาร์บีคิวและก๋วยเตี๋ยว บรรยากาศสบาย ๆ','10 ถนนประสาทวิถี อ.แม่สอด',16.7155000,98.5695000,'images_shop/khaosoi.jpg'],
['ตุ๋ยข้าวซอยแม่สอด','ร้านอาหาร','ข้าวซอยสูตรท้องถิ่นแม่สอด เมนูเรียบง่ายราคาจับต้องได้','อ.แม่สอด',16.7160000,98.5650000,'images_shop/khaosoi.jpg'],
['บ้านผักรักตะวัน','ร้านอาหาร','อาหารไทยและอาหารเพื่อสุขภาพจากผักปลอดสารของฟาร์ม','อ.แม่ท้อ จ.ตาก',16.7800000,99.0200000,'images_shop/baanphak.jpg'],
['ไอยราวดี','ร้านอาหาร','ร้านอาหารไทยริมแม่น้ำปิง บรรยากาศสวนร่มรื่น','ถนนเจดีย์ยุทธหัตถี อ.เมืองตาก',16.8700000,99.1200000,'images_shop/aiyara.jpg'],
['Rim Ping Terrace','ร้านอาหาร','ร้านอาหารบรรยากาศริมแม่น้ำปิงในเมืองตาก','236 ถนนจอมพล อ.เมืองตาก',16.8750000,99.1230000,'images_shop/hits-house.jpg'],
['Chit Chon','ร้านอาหาร','ร้านอาหารริมปิง บรรยากาศสบาย เหมาะกับครอบครัว','276/16 ถนนไทยชนะ อ.เมืองตาก',16.8755000,99.1205000,'images_shop/aiyara.jpg'],
['TARA kaffee&Patisserie','คาเฟ่','กาแฟ เบเกอรี่ และขนมสไตล์พาทิสเซอรีริมแม่น้ำปิง','18 ต.ป่ามะม่วง อ.เมืองตาก',16.8700000,99.1150000,'images_shop/tiengna.jpg'],
['Tieng Na Coffee and Bakery Farm','คาเฟ่','คาเฟ่และเบเกอรี่ในบรรยากาศสวนและฟาร์ม','27/28 ถนนพหลโยธิน ต.ไม้งาม อ.เมืองตาก',16.9000000,99.1300000,'images_shop/tiengna.jpg'],
['Weasel Coffee Maesot','คาเฟ่','คาเฟ่กาแฟชะมดและเครื่องดื่มหลากหลาย','อ.แม่สอด',16.7200000,98.5750000,'images_shop/weasel.jpg'],
['Mae Sod View 360','คาเฟ่','คาเฟ่วิวพาโนรามา กาแฟ เครื่องดื่ม และเบเกอรี่','ถนนแม่สอด-ตาก ต.แม่ปะ อ.แม่สอด',16.7400000,98.5900000,'images_shop/maesot-view360.jpg'],
['โรตีแปะโอ่ง','ร้านอาหาร','โรตีอบโอ่ง อาหารเช้า นมสด และเครื่องดื่มแบบท้องถิ่น','อ.แม่สอด',16.7150000,98.5650000,'images_shop/roti.jpg'],
['ไร่เตรยาวรรณ','คาเฟ่','ร้านกาแฟ ร้านอาหาร และพิพิธภัณฑ์บนเส้นทางแม่สอด-อุ้มผาง','อ.พบพระ',16.4700000,98.7000000,'images_shop/treyawan.jpg'],
['Hits’s House Cafe','คาเฟ่','คาเฟ่สวน บริการกาแฟ เค้ก และอาหารในบรรยากาศร่มรื่น','เส้นแม่สอด-แม่ระมาด จ.ตาก',16.9000000,98.6000000,'images_shop/hits-house.jpg'],
['Sun Secrets Cafe & Restaurant','คาเฟ่','คาเฟ่และร้านอาหารสไตล์สวนอังกฤษ จุดถ่ายรูปสวยในแม่สอด','อ.แม่สอด',16.7100000,98.5750000,'images_shop/sun-secrets.jpg'],
['Bite Me Bakery & Cafe','คาเฟ่','เบเกอรี่ เค้ก และกาแฟสไตล์มินิมอลในแม่สอด','อ.แม่สอด',16.7150000,98.5700000,'images_shop/bite-me.jpg'],
['TheSun Cafe','คาเฟ่','คาเฟ่บรรยากาศสงบ กาแฟและเบเกอรี่','อ.แม่สอด',16.7150000,98.5700000,'images_shop/sun-secrets.jpg'],
['A Coffee บ้านตาก','คาเฟ่','คาเฟ่สไตล์ลอฟต์กลางสวนในบ้านตาก','ต.ตากออก อ.บ้านตาก',17.0400000,99.0700000,'images_shop/hits-house.jpg']
];
$pc=0;$sc=0;
foreach($places as $p){[$key,$name,$loc,$cat,$lat,$lng,$img]=$p;$ek=mysqli_real_escape_string($connect,$key);$en=mysqli_real_escape_string($connect,$name);$el=mysqli_real_escape_string($connect,$loc);$ec=mysqli_real_escape_string($connect,$cat);$ei=mysqli_real_escape_string($connect,$img);$sql="INSERT INTO place (id_account,place_key,name_place,location_place,category_place,lat_place,lng_place,image_place) SELECT $owner,'$ek','$en','$el','$ec',$lat,$lng,'$ei' WHERE NOT EXISTS (SELECT 1 FROM place WHERE place_key='$ek')";if(mysqli_query($connect,$sql))$pc+=mysqli_affected_rows($connect);}
foreach($shops as $s){[$name,$cat,$desc,$addr,$lat,$lng,$img]=$s;$en=mysqli_real_escape_string($connect,$name);$ec=mysqli_real_escape_string($connect,$cat);$ed=mysqli_real_escape_string($connect,$desc);$ea=mysqli_real_escape_string($connect,$addr);$ei=mysqli_real_escape_string($connect,$img);$sql="INSERT INTO shop (id_account,name_shop,category_shop,description_shop,address_shop,lat_shop,lng_shop,image_shop,status_shop) SELECT $owner,'$en','$ec','$ed','$ea',$lat,$lng,'$ei',1 WHERE NOT EXISTS (SELECT 1 FROM shop WHERE name_shop='$en')";if(mysqli_query($connect,$sql))$sc+=mysqli_affected_rows($connect);}
echo "SEEDED_PLACES=$pc SEEDED_SHOPS=$sc\n";
?>