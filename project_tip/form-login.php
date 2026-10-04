<?php
session_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ | TAK EXPLORE</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="modern.css">
<style>
:root{--g:#173d2b;--g2:#2b6a4a;--ink:#14251b;--muted:#6f776f;--line:rgba(20,37,27,.12)}
*{box-sizing:border-box}html,body{margin:0;min-height:100%;font-family:'Prompt',sans-serif}body{background:#0c1711;color:var(--ink)}
.login-cinematic{min-height:100vh;position:relative;overflow:hidden;isolation:isolate;background:#102117}
.login-scene{position:absolute;inset:0;z-index:-3;background:linear-gradient(90deg,rgba(8,24,14,.12),rgba(8,24,14,.03) 52%,rgba(8,24,14,.58)),url('images/hero-tak.jpeg') center/cover no-repeat;transform:scale(1.035);animation:sceneIn 1.4s ease both}
.login-scene:before{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(4,17,10,.22),transparent 28%,rgba(4,17,10,.5)),radial-gradient(circle at 20% 45%,rgba(118,172,134,.2),transparent 34%)}
.login-grain{position:absolute;inset:-50%;z-index:-2;opacity:.06;pointer-events:none;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 180 180' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");animation:grain .35s steps(2) infinite}
.login-top{position:absolute;top:0;left:0;right:0;z-index:5;display:flex;justify-content:space-between;align-items:center;padding:24px clamp(20px,4vw,54px);color:#fff}
.login-brand{display:flex;align-items:center;gap:.7rem;text-decoration:none;color:#fff}.login-brand-mark{width:38px;height:38px;border:1px solid rgba(255,255,255,.4);border-radius:50%;display:grid;place-items:center;font-family:'Playfair Display',serif;font-size:1.05rem;background:rgba(255,255,255,.08);backdrop-filter:blur(12px)}
.login-brand-text{font-weight:700;letter-spacing:-.02em}.login-brand-text small{display:block;font-size:.55rem;letter-spacing:.2em;font-weight:400;opacity:.68}
.login-back{color:#fff;text-decoration:none;font-size:.76rem;font-weight:500;border:1px solid rgba(255,255,255,.28);border-radius:999px;padding:.55rem .9rem;background:rgba(5,22,12,.18);backdrop-filter:blur(12px);transition:.25s ease}.login-back:hover{background:rgba(255,255,255,.13);transform:translateY(-2px)}
.login-word{position:absolute;z-index:-1;left:clamp(20px,5vw,72px);bottom:clamp(35px,8vh,85px);color:rgba(255,255,255,.88);font-size:clamp(7rem,21vw,21rem);font-weight:800;line-height:.72;letter-spacing:-.105em;user-select:none;pointer-events:none;text-shadow:0 20px 70px rgba(0,0,0,.18);animation:wordIn 1.1s cubic-bezier(.22,1,.36,1) both}
.login-caption{position:absolute;left:clamp(24px,5vw,74px);top:50%;transform:translateY(-50%);color:#fff;max-width:410px;animation:captionIn 1s .15s cubic-bezier(.22,1,.36,1) both}
.login-caption .eyebrow{display:flex;align-items:center;gap:.65rem;font-size:.64rem;letter-spacing:.23em;font-weight:600;text-transform:uppercase;margin-bottom:1rem;color:rgba(255,255,255,.76)}.login-caption .eyebrow:before{content:'';width:32px;height:1px;background:#b6d3bd}
.login-caption h1{margin:0;font-family:'Playfair Display',serif;font-size:clamp(2.4rem,5vw,5rem);font-weight:600;line-height:.98;letter-spacing:-.055em}.login-caption p{margin:1.1rem 0 0;color:rgba(255,255,255,.76);font-size:.82rem;line-height:1.8;max-width:350px}
.login-fact{position:absolute;left:clamp(24px,5vw,74px);bottom:27px;color:rgba(255,255,255,.64);font-size:.58rem;letter-spacing:.12em}
.login-panel-wrap{position:absolute;z-index:4;right:clamp(18px,5vw,72px);top:50%;transform:translateY(-50%);width:min(470px,calc(100vw - 40px));animation:panelIn 1s .08s cubic-bezier(.22,1,.36,1) both}
.login-panel{position:relative;background:rgba(250,248,241,.94);border:1px solid rgba(255,255,255,.75);border-radius:28px;padding:clamp(28px,4vw,46px);box-shadow:0 35px 100px rgba(0,0,0,.32);backdrop-filter:blur(22px)}
.login-panel:before{content:'';position:absolute;inset:8px;border:1px solid rgba(23,61,43,.07);border-radius:22px;pointer-events:none}.login-panel>*{position:relative}
.login-kicker{display:block;color:var(--g2);font-size:.61rem;font-weight:700;letter-spacing:.2em;margin-bottom:.75rem}
.login-panel h2{margin:0;color:var(--ink);font-family:'Playfair Display',serif;font-size:clamp(2rem,4vw,2.75rem);line-height:1.05;letter-spacing:-.045em}.login-intro{margin:.8rem 0 1.7rem;color:var(--muted);font-size:.78rem;line-height:1.75}
.login-form{display:grid;gap:.85rem}.login-field{display:grid;gap:.38rem}.login-field span{font-size:.68rem;font-weight:600;color:#36503f}
.login-field input{width:100%;border:1px solid var(--line);border-radius:13px;background:rgba(255,255,255,.75);padding:.88rem .95rem;outline:none;font:400 .82rem 'Prompt',sans-serif;color:var(--ink);transition:.2s ease}.login-field input::placeholder{color:#a2aaa2}.login-field input:focus{background:#fff;border-color:#4b8766;box-shadow:0 0 0 4px rgba(75,135,102,.1)}
.login-submit{margin-top:.3rem;border:0;border-radius:13px;padding:.9rem 1rem;background:var(--g);color:#fff;font:600 .78rem 'Prompt',sans-serif;cursor:pointer;box-shadow:0 12px 26px rgba(23,61,43,.22);transition:.22s ease}.login-submit:hover{background:#0f2d1f;transform:translateY(-2px);box-shadow:0 17px 30px rgba(23,61,43,.28)}.login-submit span{float:right;font-size:1.05rem}
.login-divider{display:flex;align-items:center;gap:10px;color:#a0a59f;font-size:.65rem;margin:1.25rem 0 .9rem}.login-divider:before,.login-divider:after{content:'';height:1px;background:var(--line);flex:1}
.login-register{text-align:center;margin:0;color:#727a73;font-size:.72rem}.login-register a{color:var(--g2);font-weight:700;text-decoration:none}.login-register a:hover{text-decoration:underline}
.login-destination{display:flex;gap:.55rem;margin-top:1.1rem}.login-destination span{flex:1;border:1px solid rgba(23,61,43,.09);border-radius:10px;padding:.48rem .55rem;background:rgba(255,255,255,.5);color:#687269;font-size:.58rem;text-align:center}
.login-note{margin:1.25rem auto 0;padding-top:1rem;border-top:1px solid var(--line);text-align:center;color:#969c96;font-size:.61rem;line-height:1.7}
@keyframes sceneIn{from{opacity:.5;transform:scale(1.08)}to{opacity:1;transform:scale(1.035)}}@keyframes wordIn{from{opacity:0;transform:translateX(-40px)}to{opacity:1;transform:none}}@keyframes captionIn{from{opacity:0;transform:translate(-25px,-50%)}to{opacity:1;transform:translate(0,-50%)}}@keyframes panelIn{from{opacity:0;transform:translate(40px,-50%)}to{opacity:1;transform:translate(0,-50%)}}@keyframes grain{0%{transform:translate(0,0)}25%{transform:translate(2%,-1%)}50%{transform:translate(-1%,2%)}75%{transform:translate(1%,1%)}100%{transform:translate(0,0)}}
@media(max-width:900px){.login-caption{top:25%;transform:none;max-width:55%}.login-caption h1{font-size:clamp(2rem,6vw,3.5rem)}.login-panel-wrap{right:22px;width:min(430px,48vw)}.login-word{font-size:clamp(6rem,19vw,12rem);bottom:12vh}}
@media(max-width:700px){.login-cinematic{min-height:100svh;overflow:auto}.login-scene{position:fixed;background-position:38% center}.login-top{padding:16px 18px}.login-caption{position:relative;left:auto;top:auto;transform:none;padding:92px 22px 0;max-width:none}.login-caption .eyebrow{margin-bottom:.65rem}.login-caption h1{font-size:2.5rem}.login-caption p{font-size:.72rem;margin-top:.7rem}.login-word{left:18px;bottom:34px;font-size:6.5rem;opacity:.5}.login-fact{display:none}.login-panel-wrap{position:relative;right:auto;top:auto;transform:none;width:auto;margin:55px 14px 28px}.login-panel{border-radius:23px;padding:27px 22px}.login-destination{display:none}.login-panel:before{inset:6px;border-radius:18px}}
@media(prefers-reduced-motion:reduce){*,*:before,*:after{animation:none!important;transition:none!important}}
</style>
</head>
<body>
<main class="login-cinematic">
<div class="login-scene"></div><div class="login-grain"></div>
<header class="login-top">
<a href="home.php" class="login-brand"><span class="login-brand-mark">ต</span><span class="login-brand-text">เที่ยว ตาก<small>TAK EXPLORE</small></span></a>
<a href="home.php" class="login-back">← กลับหน้าแรก</a>
</header>
<div class="login-word" aria-hidden="true">TAK</div>
<section class="login-caption">
<div class="eyebrow">DISCOVER TAK · THAILAND</div>
<h1>ออกไปพบ<br>ตากในแบบของคุณ</h1>
<p>เก็บสถานที่ที่อยากไป วางแผนเส้นทาง และสร้างทริปของคุณเองในจังหวัดตาก</p>
</section>
<div class="login-fact">NATURE · CULTURE · LOCAL LIFE</div>
<div class="login-panel-wrap">
<section class="login-panel">
<span class="login-kicker">TAK EXPLORE / MEMBER</span>
<h2>ยินดีต้อนรับ<br>กลับมา</h2>
<p class="login-intro">เข้าสู่ระบบเพื่อกลับไปยังสถานที่และทริปที่คุณบันทึกไว้</p>
<form action="process-login.php" method="POST" class="login-form" novalidate>
<label class="login-field"><span>อีเมล</span><input name="email_account" type="email" placeholder="you@example.com" autocomplete="email" required></label>
<label class="login-field"><span>รหัสผ่าน</span><input name="password_account" type="password" placeholder="••••••••" autocomplete="current-password" required></label>
<button type="submit" class="login-submit">เข้าสู่ระบบ <span>↗</span></button>
</form>
<div class="login-divider"><span>สมาชิกใหม่</span></div>
<p class="login-register">ยังไม่มีบัญชี? <a href="form-register.php">สร้างบัญชีใหม่</a></p>
<div class="login-destination" aria-hidden="true"><span>🏞️ ธรรมชาติ</span><span>☕ คาเฟ่</span><span>🛍️ ร้านท้องถิ่น</span></div>
<p class="login-note">วางแผนเส้นทาง เก็บสถานที่โปรด และกลับมาทริปของคุณได้ทุกเมื่อ</p>
</section>
</div>
</main>
</body>
</html>