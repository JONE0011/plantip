<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างบัญชี | TAK EXPLORE</title>
    <link rel="stylesheet" href="modern.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--cream:#f5f1e8;--ink:#17221d;--muted:#69736d;--green:#173c31;--line:rgba(23,34,29,.14);--white:rgba(255,255,255,.88)}
        *{box-sizing:border-box}
        html,body{margin:0;min-height:100%;font-family:"Prompt",sans-serif;color:var(--ink)}
        body{background:#142c25}
        .register-cinematic{min-height:100vh;position:relative;overflow:hidden;background:#17372d}
        .register-scene{position:absolute;inset:0;background-image:linear-gradient(90deg,rgba(8,25,20,.88) 0%,rgba(8,25,20,.52) 42%,rgba(8,25,20,.22) 100%),url("images/hero-tak.jpeg");background-size:cover;background-position:center;transform:scale(1.03);animation:sceneIn 1.4s ease both}
        .register-scene:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.2),transparent 35%,rgba(0,0,0,.38))}
        .register-grain{position:absolute;inset:0;pointer-events:none;opacity:.09;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 180 180' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.7'/%3E%3C/svg%3E");mix-blend-mode:soft-light}
        .register-top{position:absolute;z-index:5;top:28px;left:42px;right:42px;display:flex;align-items:center;justify-content:space-between;color:#fff}
        .register-brand{font-size:13px;letter-spacing:.18em;text-transform:uppercase;font-weight:500}
        .register-brand strong{font-family:"Playfair Display",serif;font-size:22px;letter-spacing:.08em;margin-right:12px}
        .register-back{color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,.34);border-radius:999px;padding:10px 18px;background:rgba(255,255,255,.08);backdrop-filter:blur(12px);transition:.25s}
        .register-back:hover{background:rgba(255,255,255,.18);transform:translateY(-2px)}
        .register-layout{position:relative;z-index:2;min-height:100vh;display:grid;grid-template-columns:minmax(0,1fr) minmax(390px,470px);gap:8vw;align-items:center;padding:100px 7vw 55px}
        .register-copy{color:#fff;max-width:650px;animation:rise .9s .15s ease both}
        .register-word{font-family:"Playfair Display",serif;font-size:clamp(100px,18vw,280px);line-height:.72;letter-spacing:-.07em;opacity:.11;position:absolute;left:5vw;top:22vh;user-select:none}
        .register-caption{font-size:12px;letter-spacing:.25em;text-transform:uppercase;opacity:.78;margin-bottom:22px}
        .register-copy h1{font-family:"Playfair Display",serif;font-size:clamp(46px,5vw,78px);line-height:.98;font-weight:700;letter-spacing:-.035em;margin:0 0 24px;max-width:620px}
        .register-copy p{font-size:17px;line-height:1.9;font-weight:300;max-width:500px;color:rgba(255,255,255,.78);margin:0}
        .register-fact{margin-top:42px;display:flex;gap:34px;color:rgba(255,255,255,.78);font-size:12px}
        .register-fact span{display:block;color:#fff;font-size:20px;font-family:"Playfair Display",serif;margin-bottom:4px}
        .register-panel-wrap{animation:rise 1s .3s ease both}
        .register-panel{position:relative;padding:36px;background:var(--white);border:1px solid rgba(255,255,255,.55);border-radius:28px;box-shadow:0 30px 80px rgba(0,0,0,.3);backdrop-filter:blur(18px)}
        .register-kicker{font-size:11px;letter-spacing:.2em;text-transform:uppercase;color:#708078;margin-bottom:9px}
        .register-intro{margin:0 0 25px;font-size:30px;font-weight:600;letter-spacing:-.025em}
        .register-form{display:grid;gap:15px}
        .register-field{display:grid;gap:7px}
        .register-field span{font-size:12px;font-weight:500;color:#52605a}
        .register-field input{width:100%;border:1px solid var(--line);border-radius:13px;padding:14px 15px;font:inherit;font-size:14px;color:var(--ink);background:rgba(255,255,255,.72);outline:none;transition:.2s}
        .register-field input:focus{border-color:#55786b;box-shadow:0 0 0 4px rgba(55,103,85,.1);background:#fff}
        .register-submit{border:0;border-radius:14px;padding:15px 18px;background:var(--green);color:#fff;font:600 14px "Prompt",sans-serif;cursor:pointer;margin-top:5px;display:flex;align-items:center;justify-content:space-between;transition:.25s}
        .register-submit span{font-size:20px;font-weight:300}
        .register-submit:hover{transform:translateY(-2px);box-shadow:0 12px 25px rgba(23,60,49,.22)}
        .register-divider{display:flex;align-items:center;gap:10px;color:#9aa29e;font-size:11px;margin:20px 0}
        .register-divider:before,.register-divider:after{content:"";height:1px;background:var(--line);flex:1}
        .register-login{display:block;text-align:center;color:var(--green);text-decoration:none;font-size:13px;font-weight:500}
        .register-login:hover{text-decoration:underline}
        .register-steps{display:flex;gap:8px;margin-top:24px}
        .register-step{flex:1;height:4px;border-radius:99px;background:#dfe4e0}
        .register-step.active{background:var(--green)}
        .register-note{font-size:11px;color:#8a938e;text-align:center;margin:13px 0 0}
        @keyframes sceneIn{from{transform:scale(1.1);opacity:.65}to{transform:scale(1.03);opacity:1}}
        @keyframes rise{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
        @media(max-width:900px){
            .register-top{left:22px;right:22px;top:20px}.register-layout{grid-template-columns:1fr;gap:30px;padding:105px 22px 35px}.register-copy{display:none}.register-panel-wrap{max-width:500px;width:100%;margin:auto}.register-scene{background-position:center}.register-panel{padding:28px 24px}
        }
        @media(max-width:520px){
            .register-brand strong{font-size:18px}.register-brand{font-size:10px}.register-back{padding:8px 13px;font-size:12px}.register-panel{border-radius:22px;padding:25px 20px}.register-intro{font-size:26px}
        }
        @media(prefers-reduced-motion:reduce){.register-scene,.register-copy,.register-panel-wrap{animation:none}}
    </style>
</head>
<body>
<main class="register-cinematic">
    <div class="register-scene"></div>
    <div class="register-grain"></div>

    <header class="register-top">
        <div class="register-brand"><strong>TAK</strong> EXPLORE · เที่ยว ตาก</div>
        <a class="register-back" href="index.php">← กลับหน้าแรก</a>
    </header>

    <div class="register-layout">
        <section class="register-copy">
            <div class="register-word">TAK</div>
            <div class="register-caption">Your journey starts here · 01</div>
            <h1>เริ่มต้น<br>เรื่องราวของคุณ</h1>
            <p>สร้างบัญชี TAK EXPLORE แล้วออกไปค้นพบสถานที่ ร้านอาหาร และเรื่องราวดี ๆ ในจังหวัดตากในแบบที่เป็นคุณ</p>
            <div class="register-fact">
                <div><span>01</span>สร้างโปรไฟล์</div>
                <div><span>02</span>บันทึกสถานที่</div>
                <div><span>03</span>วางแผนทริป</div>
            </div>
        </section>

        <section class="register-panel-wrap">
            <div class="register-panel">
                <div class="register-kicker">Create your account</div>
                <h2 class="register-intro">เข้าร่วมการเดินทาง</h2>

                <form action="process-register.php" method="POST" class="register-form">
                    <label class="register-field">
                        <span>ชื่อผู้ใช้</span>
                        <input name="username_account" type="text" placeholder="ชื่อที่อยากให้เราเรียกคุณ" autocomplete="username" required>
                    </label>
                    <label class="register-field">
                        <span>อีเมล</span>
                        <input name="email_account" type="email" placeholder="you@example.com" autocomplete="email" required>
                    </label>
                    <label class="register-field">
                        <span>รหัสผ่านใหม่</span>
                        <input name="password_account1" type="password" placeholder="สร้างรหัสผ่านของคุณ" autocomplete="new-password" required>
                    </label>
                    <label class="register-field">
                        <span>ยืนยันรหัสผ่าน</span>
                        <input name="password_account2" type="password" placeholder="กรอกรหัสผ่านอีกครั้ง" autocomplete="new-password" required>
                    </label>
                    <button type="submit" class="register-submit">สร้างบัญชีและเริ่มเดินทาง <span>↗</span></button>
                </form>

                <div class="register-divider">หรือ</div>
                <a class="register-login" href="form-login.php">มีบัญชีอยู่แล้ว? เข้าสู่ระบบ</a>

                <div class="register-steps">
                    <div class="register-step active"></div>
                    <div class="register-step"></div>
                    <div class="register-step"></div>
                </div>
                <p class="register-note">ข้อมูลของคุณจะถูกใช้สำหรับบัญชี TAK EXPLORE เท่านั้น</p>
            </div>
        </section>
    </div>
</main>
</body>
</html>
