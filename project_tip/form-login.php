<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ | เที่ยวตาก</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="modern.css">
<style>
*{box-sizing:border-box}
html,body{margin:0;min-height:100%;font-family:'Prompt',sans-serif}
body{background:#f7f8f3;color:#17221b}
.login-page{min-height:100vh;position:relative;background:#14261a url('images/hero-tak.jpeg') center/cover no-repeat}
.login-page::before{content:'';position:absolute;inset:0;background:linear-gradient(90deg,rgba(8,20,12,.28),rgba(8,20,12,.1))}
.login-back{position:absolute;top:24px;left:28px;z-index:5;color:#fff;text-decoration:none;font-size:.86rem;padding:.65rem 1rem;border:1px solid rgba(255,255,255,.35);border-radius:999px;background:rgba(10,30,18,.28);backdrop-filter:blur(8px)}
.login-back:hover{background:rgba(10,30,18,.55)}
.login-panel{position:absolute;right:0;top:0;min-height:100vh;width:min(540px,92vw);background:#f7f8f3;padding:76px 52px 44px;box-shadow:-24px 0 70px rgba(0,0,0,.24);display:flex;align-items:center}
.login-content{width:100%;max-width:410px;margin:auto}
.login-kicker{display:block;color:#4f8063;font-size:.7rem;font-weight:700;letter-spacing:.16em;margin-bottom:.8rem}
.login-content h1{margin:0;color:#14291d;font-size:clamp(2rem,4vw,2.7rem);line-height:1.1;letter-spacing:-.035em}
.login-intro{margin:.9rem 0 2rem;color:#667168;font-size:.9rem;line-height:1.75}
.login-form{display:grid;gap:1rem}
.login-field{display:grid;gap:.45rem}
.login-field span{font-size:.78rem;font-weight:600;color:#2d4737}
.login-field input{width:100%;border:1px solid rgba(23,34,27,.13);border-radius:14px;background:#fff;padding:.95rem 1rem;outline:none;font:400 .9rem 'Prompt',sans-serif;color:#17221b;transition:border-color .2s,box-shadow .2s}
.login-field input:focus{border-color:#4d9369;box-shadow:0 0 0 4px rgba(77,147,105,.12)}
.login-submit{margin-top:.35rem;border:0;border-radius:14px;padding:.95rem 1.1rem;background:linear-gradient(135deg,#1f6044,#123b2a);color:#fff;font:600 .9rem 'Prompt',sans-serif;cursor:pointer;box-shadow:0 12px 26px rgba(31,96,68,.2)}
.login-submit:hover{transform:translateY(-2px);box-shadow:0 16px 30px rgba(31,96,68,.27)}
.login-submit span{float:right;font-size:1.15rem}
.login-divider{display:flex;align-items:center;gap:12px;color:#9aa39c;font-size:.75rem;margin:1.5rem 0 1rem}
.login-divider:before,.login-divider:after{content:'';height:1px;background:rgba(23,34,27,.1);flex:1}
.login-register{text-align:center;margin:0;color:#667168;font-size:.84rem}
.login-register a{color:#1f6044;font-weight:700;text-decoration:none}
.login-register a:hover{text-decoration:underline}
.login-note{text-align:center;margin:1.5rem auto 0;max-width:340px;color:#8a928b;font-size:.72rem;line-height:1.65}
.login-place{position:absolute;left:42px;bottom:36px;color:rgba(255,255,255,.9);font-size:clamp(3.5rem,9vw,7rem);font-weight:800;line-height:.8;letter-spacing:-.06em;z-index:2}
@media(max-width:700px){
 .login-page{background-position:center}
 .login-panel{width:100%;min-height:100vh;padding:78px 24px 38px;background:rgba(247,248,243,.97)}
 .login-back{left:18px;top:18px}
 .login-place{display:none}
}
</style>
</head>
<body>
<main class="login-page">
<a href="home.php" class="login-back">← กลับหน้าแรก</a>
<div class="login-place">TAK</div>
<section class="login-panel">
<div class="login-content">
<span class="login-kicker">TAK EXPLORE · TRAVEL PLANNER</span>
<h1>ยินดีต้อนรับกลับมา</h1>
<p class="login-intro">เข้าสู่ระบบเพื่อเก็บสถานที่ท่องเที่ยวที่คุณสนใจ และวางแผนทริปเที่ยวจังหวัดตากในแบบของคุณ</p>
<form action="process-login.php" method="POST" class="login-form" novalidate>
<label class="login-field"><span>อีเมล</span><input name="email_account" type="email" placeholder="you@example.com" autocomplete="email" required></label>
<label class="login-field"><span>รหัสผ่าน</span><input name="password_account" type="password" placeholder="••••••••" autocomplete="current-password" required></label>
<button type="submit" class="login-submit">เข้าสู่ระบบ <span>→</span></button>
</form>
<div class="login-divider"><span>หรือ</span></div>
<p class="login-register">ยังไม่มีบัญชี? <a href="form-register.php">สร้างบัญชีใหม่</a></p>
<p class="login-note">วางแผนเส้นทาง เก็บสถานที่โปรด และกลับมาทริปของคุณได้ทุกเมื่อ</p>
</div>
</section>
</main>
</body>
</html>
