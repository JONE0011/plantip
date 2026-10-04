<?php
session_start();
$open_connect = 1;
require('connect.php');

$place_images = [
    'thi-lo-su' => 'images/places/thi-lo-su.jpg',
    'doi-musoe' => 'images/places/doi-musoe.jpg',
    'bhumibol-dam' => 'images/places/bhumibol-dam.jpg',
    'mae-sot-market' => 'images/places/mae-sot-market.jpg',
    'lan-sang' => 'images/places/lan-sang.jpg',
    'taksin-maharat' => 'images/places/taksin-maharat.jpg',
    'wat-borommathat' => 'images/places/wat-borommathat.jpg',
    'friendship-bridge' => 'images/places/friendship-bridge.jpg',
];

$places = [];
$result = mysqli_query($connect, "SELECT id_place, place_key, name_place, location_place, image_place FROM place ORDER BY id_place");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['image_url'] = $place_images[$row['place_key']] ?? ($row['image_place'] ?? '');
        $places[] = $row;
    }
}
$featured_keys = ['thi-lo-su','doi-musoe','bhumibol-dam','lan-sang'];
$featured = [];
$others = [];
foreach ($places as $p) {
    if (in_array($p['place_key'], $featured_keys, true)) $featured[] = $p;
    else $others[] = $p;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>สถานที่เที่ยว | TAK EXPLORE</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="modern.css">
<style>
:root{--ink:#18231e;--muted:#68736c;--paper:#faf9f5;--cream:#f0eee5;--green:#183d31;--line:rgba(24,35,30,.13);--gold:#d6b875}
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:var(--paper);color:var(--ink);font-family:"Prompt",sans-serif}a{text-decoration:none;color:inherit}.container{width:min(1380px,calc(100% - 48px));margin:auto}
.nav{position:fixed;z-index:50;top:0;left:0;right:0;background:rgba(250,249,245,.9);backdrop-filter:blur(18px);border-bottom:1px solid var(--line)}
.nav .container{height:78px;display:flex;align-items:center;gap:28px}.logo{font:600 24px "Playfair Display",serif;color:var(--green);letter-spacing:.05em}.logo span{font:500 10px "Prompt",sans-serif;letter-spacing:.18em;color:var(--muted);margin-left:9px}.links{display:flex;gap:28px;margin:auto}.links a{font-size:13px;color:var(--muted);padding:28px 0;border-bottom:2px solid transparent}.links a.active,.links a:hover{color:var(--green);border-bottom-color:var(--green)}.plan{background:var(--green);color:#fff;border-radius:999px;padding:11px 18px;font-size:12px;font-weight:600}
.hero{padding:150px 0 65px;background:linear-gradient(180deg,#edf1e9,#faf9f5);position:relative;overflow:hidden}.hero:after{content:"TAK";position:absolute;right:-15px;top:65px;font:600 270px/.7 "Playfair Display",serif;color:rgba(24,61,49,.055);letter-spacing:-.08em}.hero-grid{position:relative;z-index:1;display:grid;grid-template-columns:1.15fr .85fr;gap:80px;align-items:end}.eyebrow{font-size:10px;letter-spacing:.25em;text-transform:uppercase;color:#7c8780}.hero h1{font:600 clamp(50px,7vw,92px)/.92 "Playfair Display",serif;letter-spacing:-.05em;margin:12px 0 20px}.hero p{max-width:620px;color:var(--muted);font-size:15px;line-height:2;margin:0}.hero-side{border-left:1px solid var(--line);padding-left:28px}.hero-side b{font:600 42px "Playfair Display",serif;color:var(--green)}.hero-side span{display:block;color:var(--muted);font-size:11px;margin-top:4px}.hero-side p{font-size:12px;line-height:1.8;margin-top:15px}
.section{padding:75px 0}.section-head{display:flex;justify-content:space-between;align-items:end;gap:30px;margin-bottom:28px}.section-label{font-size:10px;letter-spacing:.22em;text-transform:uppercase;color:#7d8780}.section h2{font:600 clamp(34px,4.5vw,58px)/1 "Playfair Display",serif;letter-spacing:-.04em;margin:8px 0 0}.section-head p{max-width:420px;font-size:12px;line-height:1.8;color:var(--muted);margin:0}
.feature-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:15px}.card{position:relative;min-height:450px;border-radius:24px;overflow:hidden;color:#fff;background:#244437}.card:nth-child(n+2){margin-top:45px;min-height:390px}.photo{position:absolute;inset:0;background:center/cover no-repeat;transition:transform .7s}.card:after{content:"";position:absolute;inset:0;background:linear-gradient(0deg,rgba(4,16,11,.88),transparent 62%)}.card:hover .photo{transform:scale(1.06)}.tag{position:absolute;z-index:2;top:16px;left:16px;background:rgba(250,249,245,.92);color:var(--green);padding:6px 11px;border-radius:999px;font-size:10px;font-weight:600}.card-body{position:absolute;z-index:2;left:24px;right:24px;bottom:22px}.card-body h3{font:600 30px/1.05 "Playfair Display",serif;margin:0 0 8px}.card-body p{font-size:11px;line-height:1.7;color:rgba(255,255,255,.72);margin:0}.card-body .location{display:block;font-size:10px;color:rgba(255,255,255,.65);margin-top:11px}.add{position:absolute;z-index:3;right:16px;top:16px;width:39px;height:39px;border-radius:50%;border:1px solid rgba(255,255,255,.45);background:rgba(8,25,18,.28);color:#fff;font-size:20px;cursor:pointer;backdrop-filter:blur(10px)}.add.active{background:#fff;color:var(--green)}.add.active{font-size:0}.add.active:after{content:"✓";font-size:15px}
.all-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:15px}.small-card{background:#fff;border:1px solid var(--line);border-radius:20px;overflow:hidden;transition:.25s}.small-card:hover{transform:translateY(-5px);box-shadow:0 15px 35px rgba(20,40,30,.09)}.small-photo{aspect-ratio:1.2;background:center/cover}.small-info{padding:16px}.small-info h3{font-size:15px;margin:0 0 5px}.small-info p{font-size:10px;color:var(--muted);margin:0 0 13px}.small-add{width:100%;border:0;background:var(--green);color:#fff;border-radius:999px;padding:9px;font:500 11px "Prompt",sans-serif;cursor:pointer}.small-add.active{background:#26312c}
.cta{background:var(--green);color:#fff;padding:75px 0}.cta .container{display:flex;justify-content:space-between;align-items:center;gap:40px}.cta h2{font:600 clamp(38px,5vw,65px)/1 "Playfair Display",serif;margin:8px 0}.cta p{color:rgba(255,255,255,.68);font-size:13px;line-height:1.8;max-width:550px}.cta-btn{display:inline-flex;white-space:nowrap;background:#f5f1e8;color:var(--green);padding:13px 21px;border-radius:999px;font-size:12px;font-weight:600}
footer{padding:28px 0;color:#7a847e;font-size:11px;border-top:1px solid var(--line)}.footer-row{display:flex;justify-content:space-between}
@media(max-width:900px){.links{display:none}.hero-grid{grid-template-columns:1fr;gap:30px}.hero-side{border-left:0;border-top:1px solid var(--line);padding:20px 0 0}.feature-grid{grid-template-columns:1fr 1fr}.card:first-child{grid-column:1/-1}.card:nth-child(n+2){margin-top:0}.all-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:560px){.container{width:calc(100% - 30px)}.nav .container{height:68px}.logo span{display:none}.plan{padding:9px 13px}.hero{padding-top:115px}.section-head{display:block}.section-head p{margin-top:14px}.feature-grid,.all-grid{grid-template-columns:1fr}.card,.card:nth-child(n+2){min-height:410px}.cta .container{display:block}.cta-btn{margin-top:12px}}
</style>
</head>
<body>
<nav class="nav">
<div class="container">
<a class="logo" href="home.php">TAK<span>EXPLORE · เที่ยวตาก</span></a>
<div class="links">
<a href="home.php">หน้าแรก</a>
<a class="active" href="places.php">สถานที่เที่ยว</a>
<a href="shops.php">ร้านอาหาร &amp; คาเฟ่</a>
<a href="trip-planner.php">วางแผนทริป</a>
</div>
<div style="display:flex;align-items:center;gap:10px"><?php include("profile-widget.php"); ?><a class="plan" href="contribute.php">+ เพิ่มข้อมูล</a></div>
</div>
</nav>

<header class="hero">
<div class="container hero-grid">
<div><div class="eyebrow">Discover Tak · Selected Places</div><h1>สถานที่เที่ยว<br>ที่อยากให้คุณไป</h1><p>รวมจุดหมายที่น่าสนใจของจังหวัดตาก ตั้งแต่ธรรมชาติ น้ำตก ภูเขา เขื่อน ไปจนถึงจุดเที่ยวในเมือง เลือกที่ชอบแล้วเพิ่มเข้าทริปของคุณได้ทันที</p></div>
<div class="hero-side"><b><?php echo count($places); ?>+</b><span>จุดหมายใน TAK EXPLORE</span><p>เริ่มจากสถานที่ยอดนิยม หรือเลื่อนลงเพื่อค้นพบมุมอื่น ๆ ของตาก</p></div>
</div>
</header>

<section class="section">
<div class="container">
<div class="section-head"><div><div class="section-label">Editor's selection / 01</div><h2>สถานที่ยอดนิยม<br>แนะนำให้ลองไป</h2></div><p>4 จุดหมายเด่นสำหรับคนที่อยากเริ่มเที่ยวตากแบบไม่ต้องคิดนาน</p></div>
<div class="feature-grid">
<?php foreach ($featured as $i => $p): ?>
<article class="card">
<div class="photo" style="background-image:url('<?php echo htmlspecialchars($p['image_url']); ?>')"></div>
<span class="tag"><?php echo $i===0?'ยอดฮิต':($i===1?'ทะเลหมอก':($i===2?'ธรรมชาติ':'ป่า & น้ำตก')); ?></span>
<button class="add" type="button" data-place="<?php echo htmlspecialchars($p['place_key']); ?>" aria-label="เพิ่ม <?php echo htmlspecialchars($p['name_place']); ?>">+</button>
<div class="card-body"><h3><?php echo htmlspecialchars($p['name_place']); ?></h3><p>จุดหมายที่น่าสนใจสำหรับทริปตากของคุณ</p><span class="location">● <?php echo htmlspecialchars($p['location_place']); ?></span></div>
</article>
<?php endforeach; ?>
</div>
</div>
</section>

<section class="section" style="padding-top:0;background:var(--cream)">
<div class="container" style="padding-top:75px">
<div class="section-head"><div><div class="section-label">Explore more / 02</div><h2>ยังมีอีกหลายที่ให้ค้นพบ</h2></div><p>สถานที่อื่น ๆ ที่คุณสามารถเลือกเพิ่มเข้าทริปได้</p></div>
<div class="all-grid">
<?php foreach ($others as $p): ?>
<article class="small-card">
<div class="small-photo" style="background-image:url('<?php echo htmlspecialchars($p['image_url']); ?>')"></div>
<div class="small-info"><h3><?php echo htmlspecialchars($p['name_place']); ?></h3><p><?php echo htmlspecialchars($p['location_place']); ?></p><button class="small-add" type="button" data-place="<?php echo htmlspecialchars($p['place_key']); ?>">+ เพิ่มเข้าทริป</button></div>
</article>
<?php endforeach; ?>
</div>
</div>
</section>

<section class="cta"><div class="container"><div><div class="section-label" style="color:rgba(255,255,255,.5)">Your trip / 03</div><h2>เจอสถานที่ที่ใช่แล้ว?</h2><p>เพิ่มสถานที่ที่สนใจ แล้วไปจัดลำดับการเดินทางต่อใน Trip Planner</p></div><a class="cta-btn" href="trip-planner.php">วางแผนทริปของฉัน ↗</a></div></section>
<footer><div class="container footer-row"><div>TAK EXPLORE · เที่ยวตากในแบบของคุณ</div><div>© <?php echo date('Y'); ?> TAK EXPLORE</div></div></footer>

<script>
const KEY='takTripPlaces';
function getTrip(){try{return JSON.parse(localStorage.getItem(KEY))||[]}catch(e){return[]}}
function setTrip(list){localStorage.setItem(KEY,JSON.stringify(list));}
let trip=getTrip();
document.querySelectorAll('[data-place]').forEach(btn=>{
 const id=btn.dataset.place;
 const update=()=>{const active=trip.includes(id);btn.classList.toggle('active',active);if(btn.classList.contains('small-add'))btn.textContent=active?'✓ อยู่ในทริปแล้ว':' + เพิ่มเข้าทริป';else btn.textContent=active?'✓':'+'};
 update();
 btn.addEventListener('click',()=>{trip=trip.includes(id)?trip.filter(x=>x!==id):[...trip,id];setTrip(trip);update();});
});
</script>
</body>
</html>
