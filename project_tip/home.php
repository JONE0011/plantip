<?php
session_start();
$open_connect = 1;
require('connect.php');
$is_logged_in = isset($_SESSION['id_account']);

$home_place_images = [
    'thi-lo-su' => 'images/places/thi-lo-su.jpg',
    'doi-musoe' => 'images/places/doi-musoe.jpg',
    'bhumibol-dam' => 'images/places/bhumibol-dam.jpg',
    'mae-sot-market' => 'images/places/mae-sot-market.jpg',
    'lan-sang' => 'images/places/lan-sang.jpg',
    'taksin-maharat' => 'images/places/taksin-maharat.jpg',
    'wat-borommathat' => 'images/places/wat-borommathat.jpg',
    'friendship-bridge' => 'images/places/friendship-bridge.jpg',
];
$home_places = [];
$place_result = mysqli_query($connect, "SELECT id_place, place_key, name_place, location_place, image_place FROM place ORDER BY id_place LIMIT 3");
if ($place_result) {
    while ($row = mysqli_fetch_assoc($place_result)) {
        $row['image_url'] = $home_place_images[$row['place_key']] ?? ($row['image_place'] ?? '');
        $home_places[] = $row;
    }
}
$home_shops = [];
$shop_result = mysqli_query($connect, "SELECT id_shop, name_shop, category_shop, description_shop, address_shop, image_shop FROM shop WHERE status_shop=1 ORDER BY created_at DESC, id_shop DESC LIMIT 3");
if ($shop_result) {
    while ($row = mysqli_fetch_assoc($shop_result)) {
        $home_shops[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TAK EXPLORE | เที่ยวตากในแบบของคุณ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="modern.css">
<style>
:root{--ink:#18231e;--muted:#66716b;--cream:#f4f1e8;--paper:#fbfaf6;--green:#183d31;--green2:#285746;--line:rgba(24,35,30,.13);--gold:#d6b875}
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:var(--paper);color:var(--ink);font-family:"Prompt",sans-serif}a{text-decoration:none;color:inherit}img{max-width:100%;display:block}.home{overflow:hidden}.container{width:min(1380px,calc(100% - 48px));margin:auto}

/* NAV */
.home-nav{position:fixed;z-index:50;top:0;left:0;right:0;padding:18px 0;transition:.3s;background:linear-gradient(180deg,rgba(7,20,15,.42),transparent)}
.home-nav.scrolled{background:rgba(250,248,243,.92);backdrop-filter:blur(18px);border-bottom:1px solid var(--line)}
.home-nav .container{display:flex;align-items:center;justify-content:space-between;gap:24px}
.home-logo{color:#fff;font-family:"Playfair Display",serif;font-size:24px;letter-spacing:.06em}.home-logo span{font-family:"Prompt",sans-serif;font-size:10px;letter-spacing:.2em;margin-left:9px;opacity:.8}.scrolled .home-logo{color:var(--green)}
.home-links{display:flex;gap:28px;align-items:center}.home-links a{font-size:13px;color:rgba(255,255,255,.86);transition:.2s}.home-links a:hover{color:#fff}.scrolled .home-links a{color:var(--muted)}.scrolled .home-links a:hover{color:var(--green)}
.home-actions{display:flex;align-items:center;gap:10px}.home-plan{padding:11px 18px;border-radius:999px;background:#fff;color:var(--green);font-size:12px;font-weight:600;transition:.2s}.home-plan:hover{transform:translateY(-2px)}.scrolled .home-plan{background:var(--green);color:#fff}
@media(max-width:850px){.home-links{display:none}.home-logo{font-size:21px}.home-plan{padding:9px 14px}}

/* HERO */
.hero{min-height:100svh;position:relative;color:#fff;display:flex;align-items:flex-end;background:#17352a}
.hero-bg{position:absolute;inset:0;background-image:linear-gradient(90deg,rgba(7,22,16,.82),rgba(7,22,16,.28) 62%,rgba(7,22,16,.1)),linear-gradient(0deg,rgba(5,16,11,.78),transparent 55%),url("images/hero-tak.jpeg");background-size:cover;background-position:center;animation:heroZoom 12s ease-out both}
.hero-bg:after{content:"";position:absolute;inset:0;opacity:.1;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='n'%3E%3CfeTurbulence baseFrequency='.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}
.hero-word{position:absolute;right:-2vw;top:17vh;font:600 clamp(130px,25vw,390px)/.7 "Playfair Display",serif;letter-spacing:-.08em;color:rgba(255,255,255,.09);user-select:none}
.hero-inner{position:relative;z-index:2;width:min(1380px,calc(100% - 48px));margin:auto;padding:150px 0 72px;display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:60px;align-items:end}
.hero-copy{max-width:800px;animation:rise .9s .15s both}.eyebrow{display:flex;align-items:center;gap:10px;font-size:11px;letter-spacing:.25em;text-transform:uppercase;color:rgba(255,255,255,.72);margin-bottom:22px}.eyebrow:before{content:"";width:34px;height:1px;background:var(--gold)}
.hero h1{font:600 clamp(68px,10vw,148px)/.78 "Playfair Display",serif;letter-spacing:-.06em;margin:0 0 28px}.hero h1 small{display:block;font:500 clamp(12px,1.2vw,16px)/1 "Prompt",sans-serif;letter-spacing:.35em;margin:20px 0 0 8px;color:rgba(255,255,255,.72)}
.hero-copy p{font-size:16px;line-height:1.9;color:rgba(255,255,255,.78);max-width:610px;margin:0 0 28px}
.hero-buttons{display:flex;gap:11px;flex-wrap:wrap}.hero-btn{padding:13px 21px;border-radius:999px;font-size:13px;font-weight:500;transition:.25s}.hero-btn.primary{background:#f8f5ed;color:var(--green)}.hero-btn.secondary{border:1px solid rgba(255,255,255,.45);color:#fff;background:rgba(255,255,255,.06);backdrop-filter:blur(10px)}.hero-btn:hover{transform:translateY(-3px)}
.hero-note{justify-self:end;width:100%;max-width:330px;padding:21px;border:1px solid rgba(255,255,255,.22);background:rgba(255,255,255,.08);backdrop-filter:blur(16px);border-radius:22px;animation:rise 1s .35s both}.hero-note .num{font:600 40px/1 "Playfair Display",serif}.hero-note strong{display:block;font-size:13px;margin:8px 0 4px}.hero-note p{margin:0;color:rgba(255,255,255,.68);font-size:11px;line-height:1.7}
.scroll-mark{position:absolute;z-index:3;bottom:28px;right:32px;color:rgba(255,255,255,.6);font-size:9px;letter-spacing:.2em;writing-mode:vertical-rl}
@media(max-width:800px){.hero-inner{grid-template-columns:1fr;padding-top:130px}.hero-note{display:none}.hero h1{font-size:clamp(70px,22vw,120px)}.hero-copy p{font-size:14px}.hero-word{top:25vh}}

/* INTRO */
.intro{padding:105px 0 90px}.intro-grid{display:grid;grid-template-columns:1fr 1.25fr;gap:8vw;align-items:start}.section-label{font-size:10px;letter-spacing:.25em;text-transform:uppercase;color:#7b857f}.intro h2{font:600 clamp(38px,5vw,66px)/1 "Playfair Display",serif;letter-spacing:-.04em;margin:12px 0 0}.intro-copy p{font-size:16px;line-height:2;color:var(--muted);max-width:650px;margin:0}.intro-copy .line{width:100%;height:1px;background:var(--line);margin:27px 0}.mini-stats{display:flex;gap:45px}.mini-stat b{display:block;font:600 28px "Playfair Display",serif;color:var(--green)}.mini-stat span{font-size:11px;color:var(--muted)}
@media(max-width:800px){.intro{padding:75px 0}.intro-grid{grid-template-columns:1fr;gap:28px}.intro-copy p{font-size:14px}.mini-stats{gap:25px}}

/* DESTINATIONS */
.dest-section{padding:0 0 105px}.dest-head{display:flex;justify-content:space-between;align-items:end;gap:20px;margin-bottom:30px}.dest-head h2{font:600 clamp(34px,4vw,54px)/1 "Playfair Display",serif;margin:8px 0 0;letter-spacing:-.04em}.dest-head p{max-width:390px;color:var(--muted);font-size:12px;line-height:1.8;margin:0}.dest-grid{display:grid;grid-template-columns:1.35fr 1fr 1fr;gap:14px}.dest-card{position:relative;min-height:500px;border-radius:24px;overflow:hidden;background:#1d3329;color:#fff}.dest-card:nth-child(2),.dest-card:nth-child(3){margin-top:48px;min-height:420px}.dest-photo{position:absolute;inset:0;background-size:cover;background-position:center;transition:transform .7s}.dest-card:after{content:"";position:absolute;inset:0;background:linear-gradient(0deg,rgba(5,16,11,.84),transparent 62%)}.dest-card:hover .dest-photo{transform:scale(1.06)}.dest-tag{position:absolute;z-index:2;top:16px;left:16px;padding:6px 10px;border-radius:999px;background:rgba(250,248,243,.9);color:var(--green);font-size:10px;font-weight:600}.dest-add{position:absolute;z-index:3;right:16px;top:16px;width:38px;height:38px;border:1px solid rgba(255,255,255,.4);border-radius:50%;background:rgba(9,27,20,.28);color:#fff;font-size:21px;cursor:pointer;backdrop-filter:blur(10px);transition:.25s}.dest-add:hover{transform:scale(1.08);background:var(--green)}.dest-add[aria-pressed="true"]{background:#fff;color:var(--green)}.dest-add[aria-pressed="true"] .plus{display:none}.dest-add[aria-pressed="true"]:after{content:"✓";font-size:15px}.dest-info{position:absolute;z-index:2;left:24px;right:24px;bottom:22px}.dest-info h3{font:600 30px/1.05 "Playfair Display",serif;margin:0 0 8px}.dest-info p{font-size:11px;color:rgba(255,255,255,.72);margin:0;line-height:1.7}.dest-loc{display:inline-flex;gap:5px;align-items:center;margin-top:12px;font-size:10px;color:rgba(255,255,255,.7)}
.dest-more{display:inline-flex;margin-top:25px;padding:12px 18px;border:1px solid var(--line);border-radius:999px;font-size:12px;transition:.2s}.dest-more:hover{background:var(--green);color:#fff;border-color:var(--green)}
@media(max-width:850px){.dest-grid{grid-template-columns:1fr 1fr}.dest-card:first-child{grid-column:1/-1}.dest-card:nth-child(2),.dest-card:nth-child(3){margin-top:0;min-height:360px}.dest-card:first-child{min-height:430px}}@media(max-width:560px){.container{width:min(100% - 30px,1380px)}.dest-grid{grid-template-columns:1fr}.dest-card:first-child{grid-column:auto}.dest-card,.dest-card:nth-child(2),.dest-card:nth-child(3){min-height:420px}}

/* FOOD & CAFE */
.food-section{padding:105px 0;background:var(--cream)}.food-head{display:flex;justify-content:space-between;align-items:end;gap:20px;margin-bottom:30px}.food-head h2{font:600 clamp(34px,4vw,54px)/1 "Playfair Display",serif;margin:8px 0 0;letter-spacing:-.04em}.food-head p{max-width:390px;color:var(--muted);font-size:12px;line-height:1.8;margin:0}.food-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:15px}.food-card{background:#fff;border:1px solid var(--line);border-radius:24px;overflow:hidden;transition:.3s}.food-card:hover{transform:translateY(-6px);box-shadow:0 18px 40px rgba(20,40,30,.10)}.food-photo{aspect-ratio:1.35;background:center/cover no-repeat}.food-info{padding:20px}.food-tag{display:inline-flex;padding:5px 9px;border-radius:999px;background:#edf1e9;color:var(--green);font-size:9px;font-weight:600;margin-bottom:10px}.food-info h3{font:600 25px/1.1 "Playfair Display",serif;margin:0 0 8px}.food-info p{font-size:11px;line-height:1.7;color:var(--muted);margin:0}.food-location{display:block;font-size:10px;color:#7b857f;margin-top:10px}.food-more{display:inline-flex;margin-top:28px;padding:12px 20px;border-radius:999px;background:var(--green);color:#fff;font-size:12px;font-weight:500;transition:.2s}.food-more:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(24,61,49,.18)}
@media(max-width:800px){.food-grid{grid-template-columns:1fr 1fr}.food-card:first-child{grid-column:1/-1}}@media(max-width:560px){.food-head{display:block}.food-head p{margin-top:14px}.food-grid{grid-template-columns:1fr}.food-card:first-child{grid-column:auto}}

/* FEATURES */
.features{background:var(--cream);padding:100px 0}.features-grid{display:grid;grid-template-columns:1fr 1fr;gap:70px;align-items:start}.features-title h2{font:600 clamp(38px,4.5vw,62px)/1 "Playfair Display",serif;margin:10px 0 20px;letter-spacing:-.04em}.features-title p{color:var(--muted);line-height:1.9;font-size:14px;max-width:450px}.feature-items{border-top:1px solid var(--line)}.feature-item{display:grid;grid-template-columns:55px 1fr;gap:18px;padding:23px 0;border-bottom:1px solid var(--line)}.feature-num{font:11px;color:#8a938d;padding-top:4px}.feature-item h3{font-size:15px;margin:0 0 6px}.feature-item p{font-size:12px;line-height:1.8;color:var(--muted);margin:0}
@media(max-width:800px){.features{padding:75px 0}.features-grid{grid-template-columns:1fr;gap:40px}}

/* CTA */
.cta{padding:105px 0}.cta-box{position:relative;overflow:hidden;border-radius:30px;background:var(--green);color:#fff;padding:70px clamp(28px,7vw,90px);min-height:370px;display:flex;align-items:center}.cta-box:before{content:"TAK";position:absolute;right:-20px;bottom:-70px;font:600 280px/.7 "Playfair Display",serif;color:rgba(255,255,255,.06);letter-spacing:-.08em}.cta-content{position:relative;z-index:2;max-width:650px}.cta-content h2{font:600 clamp(40px,5vw,68px)/.98 "Playfair Display",serif;margin:12px 0 18px;letter-spacing:-.04em}.cta-content p{font-size:14px;line-height:1.9;color:rgba(255,255,255,.7);max-width:520px}.cta-btn{display:inline-flex;margin-top:15px;background:#f7f3e9;color:var(--green);padding:13px 22px;border-radius:999px;font-size:12px;font-weight:600}
footer{padding:30px 0 45px;border-top:1px solid var(--line);color:#7a847e;font-size:11px}.footer-row{display:flex;justify-content:space-between;gap:20px}.footer-brand{font:600 19px "Playfair Display",serif;color:var(--green)}
@keyframes heroZoom{from{transform:scale(1.09)}to{transform:scale(1)}}@keyframes rise{from{opacity:0;transform:translateY(25px)}to{opacity:1;transform:translateY(0)}}@media(prefers-reduced-motion:reduce){.hero-bg,.hero-copy,.hero-note{animation:none}.dest-photo{transition:none}}
</style>
</head>
<body>
<div class="home">
<nav class="home-nav" id="home-nav">
<div class="container">
<a href="home.php" class="home-logo">TAK<span>EXPLORE · เที่ยวตาก</span></a>
<div class="home-links">
<a href="places.php">สถานที่เที่ยว</a>
<a href="shops.php">ร้านอาหาร &amp; คาเฟ่</a>
<a href="#why">ทำไมต้อง TAK</a>
<a href="#about">เกี่ยวกับเรา</a>
</div>
<div class="home-actions">
<?php include("profile-widget.php"); ?>
<a class="home-plan" href="trip-planner.php">วางแผนทริป</a>
</div>
</div>
</nav>

<header class="hero">
<div class="hero-bg"></div>
<div class="hero-word">TAK</div>
<div class="hero-inner">
<div class="hero-copy">
<div class="eyebrow">TAK · NORTHERN THAILAND</div>
<h1>ตาก<small>TRAVEL IN YOUR OWN WAY</small></h1>
<p>ออกไปพบตากในแบบของคุณ — จากน้ำตกกลางป่า ทะเลหมอกบนยอดดอย ไปจนถึงเมืองชายแดน เลือกสถานที่ที่อยากไป แล้วสร้างทริปของคุณเอง</p>
<div class="hero-buttons">
<a class="hero-btn primary" href="trip-planner.php">เริ่มวางแผนทริป ↗</a>
<a class="hero-btn secondary" href="#destinations">สำรวจสถานที่</a>
</div>
</div>
<div class="hero-note"><div class="num">01</div><strong>เลือก · จัด · ออกเดินทาง</strong><p>เพิ่มสถานที่ที่สนใจ แล้วไปจัดลำดับเส้นทางต่อในหน้า Trip Planner</p></div>
</div>
<div class="scroll-mark">SCROLL TO EXPLORE</div>
</header>

<main>
<section class="intro container" id="about">
<div class="intro-grid">
<div><div class="section-label">Discover Tak / 01</div><h2>มากกว่าแค่<br>สถานที่ท่องเที่ยว</h2></div>
<div class="intro-copy">
<p>TAK EXPLORE ถูกออกแบบให้การเที่ยวตากเริ่มต้นได้ง่ายขึ้น รวบรวมสถานที่ ร้านอาหาร และจุดน่าสนใจไว้ให้คุณเลือก แล้วเปลี่ยนสิ่งที่อยากไปให้กลายเป็นทริปที่เป็นของคุณเอง</p>
<div class="line"></div>
<div class="mini-stats">
<div class="mini-stat"><b>08+</b><span>จุดหมายแนะนำ</span></div>
<div class="mini-stat"><b>01</b><span>แผนที่สำหรับทริป</span></div>
<div class="mini-stat"><b>∞</b><span>เส้นทางในแบบคุณ</span></div>
</div>
</div>
</div>
</section>

<section class="dest-section container" id="destinations">
<div class="dest-head">
<div><div class="section-label">Selected destinations / 02</div><h2>สถานที่ท่องเที่ยว</h2></div>
<p>รวมสถานที่น่าสนใจของตากไว้ให้เลือกจากหน้าแรก กด + เพื่อเก็บเข้าทริป หรือกดดูเพิ่มเติมเพื่อชมสถานที่ทั้งหมด</p>
</div>
<div class="dest-grid">
<?php foreach ($home_places as $i => $place): ?>
<article class="dest-card" data-place="<?php echo htmlspecialchars($place['place_key']); ?>">
<div class="dest-photo" style="background-image:url('<?php echo htmlspecialchars($place['image_url']); ?>')"></div>
<span class="dest-tag"><?php echo $i === 0 ? 'ยอดฮิต' : ($i === 1 ? 'ธรรมชาติ' : 'แนะนำ'); ?></span>
<button class="dest-add" type="button" aria-pressed="false" aria-label="เพิ่ม<?php echo htmlspecialchars($place['name_place']); ?>"><span class="plus">+</span></button>
<div class="dest-info"><h3><?php echo htmlspecialchars($place['name_place']); ?></h3><p>จุดหมายที่น่าสนใจสำหรับทริปตากของคุณ</p><span class="dest-loc">● <?php echo htmlspecialchars($place['location_place']); ?></span></div>
</article>
<?php endforeach; ?>
</div>
<a class="dest-more" href="places.php">ดูเพิ่มเติม · สถานที่ท่องเที่ยวทั้งหมด →</a>
</section>

<section class="food-section" id="food">
<div class="container">
<div class="food-head">
<div><div class="section-label">Taste Tak / 03</div><h2>ร้านอาหาร & คาเฟ่</h2></div>
<p>พักระหว่างทางด้วยร้านอร่อย คาเฟ่น่านั่ง และร้านที่คนในพื้นที่แนะนำ ดูทั้งหมดต่อได้ในหน้าร้านอาหาร & คาเฟ่</p>
</div>
<div class="food-grid">
<?php foreach ($home_shops as $shop): ?>
<article class="food-card">
<div class="food-photo" style="background-image:url('<?php echo htmlspecialchars($shop['image_shop'] ?: 'images/hero-tak.jpeg'); ?>')"></div>
<div class="food-info">
<span class="food-tag"><?php echo htmlspecialchars($shop['category_shop'] ?: 'ร้านอาหาร & คาเฟ่'); ?></span>
<h3><?php echo htmlspecialchars($shop['name_shop']); ?></h3>
<p><?php echo htmlspecialchars($shop['description_shop'] ?: 'ร้านน่าสนใจสำหรับทริปของคุณ'); ?></p>
<span class="food-location">● <?php echo htmlspecialchars($shop['address_shop'] ?: 'จังหวัดตาก'); ?></span>
</div>
</article>
<?php endforeach; ?>
</div>
<a class="food-more" href="shops.php">ดูเพิ่มเติม · ร้านอาหาร & คาเฟ่ทั้งหมด →</a>
</div>
</section>

<section class="features" id="why">
<div class="container features-grid">
<div class="features-title"><div class="section-label">Why Tak Explore / 03</div><h2>วางแผนให้น้อยลง<br>ออกเดินทางให้มากขึ้น</h2><p>ไม่ต้องเปิดหลายหน้าเพื่อจำว่าที่ไหนอยากไป ระบบของเราช่วยให้คุณเก็บสถานที่ที่สนใจไว้ก่อน แล้วค่อยจัดทริปในจังหวะของคุณ</p></div>
<div class="feature-items">
<div class="feature-item"><div class="feature-num">01</div><div><h3>เลือกสถานที่ได้ทันที</h3><p>กด + จากหน้าแรกเพื่อเก็บสถานที่ที่สนใจลงในรายการทริป</p></div></div>
<div class="feature-item"><div class="feature-num">02</div><div><h3>จัดลำดับเส้นทาง</h3><p>ไปต่อที่ Trip Planner เพื่อจัดลำดับจุดหมายและวางแผนการเดินทาง</p></div></div>
<div class="feature-item"><div class="feature-num">03</div><div><h3>เก็บประวัติของคุณ</h3><p>เมื่อเข้าสู่ระบบ ระบบสามารถบันทึกสถานที่ที่คุณเลือกไว้กับโปรไฟล์</p></div></div>
</div>
</div>
</section>

<section class="cta container">
<div class="cta-box">
<div class="cta-content"><div class="section-label" style="color:rgba(255,255,255,.5)">Your next trip / 04</div><h2>ตากครั้งต่อไป<br>เริ่มจากตรงนี้</h2><p>เลือกสถานที่ที่อยากไป แล้วปล่อยให้การวางแผนกลายเป็นส่วนหนึ่งของการเดินทาง</p><a class="cta-btn" href="trip-planner.php">สร้างทริปของฉัน ↗</a></div>
</div>
</section>
</main>

<footer>
<div class="container footer-row"><div><div class="footer-brand">TAK EXPLORE</div><div>แพลตฟอร์มวางแผนท่องเที่ยวจังหวัดตาก</div></div><div>© <?php echo date('Y'); ?> TAK EXPLORE</div></div>
</footer>
</div>

<script>
const STORAGE_KEY='takTripPlaces';
function loadTrip(){try{return JSON.parse(localStorage.getItem(STORAGE_KEY))||[]}catch(e){return[]}}
function saveTrip(list){
 localStorage.setItem(STORAGE_KEY,JSON.stringify(list));
 <?php if($is_logged_in): ?>
 fetch('trip-planner.php?action=save_trip',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({places:list})}).catch(()=>{});
 <?php endif; ?>
}
let trip=loadTrip();
document.querySelectorAll('.dest-add').forEach(btn=>{
 const card=btn.closest('.dest-card'), id=card.dataset.place;
 if(trip.includes(id))btn.setAttribute('aria-pressed','true');
 btn.addEventListener('click',()=>{
  const on=btn.getAttribute('aria-pressed')==='true';
  trip=on?trip.filter(x=>x!==id):[...trip,id];
  btn.setAttribute('aria-pressed',String(!on));saveTrip(trip);
 });
});
const nav=document.getElementById('home-nav');
window.addEventListener('scroll',()=>nav.classList.toggle('scrolled',window.scrollY>40),{passive:true});
</script>
</body>
</html>
