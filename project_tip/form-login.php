<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{
            --gold: #d9b25f;
            --line: rgba(255,255,255,0.55);
        }

        *{ box-sizing: border-box; }

        html, body{
            margin: 0;
            padding: 0;
            height: 100%;
        }

        body{
            font-family: 'Prompt', sans-serif;
            color: #fff;
        }

        .stage{
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: #111 url('bg-login.jpg') center center / cover no-repeat;
        }

        .stage::before{
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(10,12,10,0.72) 0%, rgba(10,12,10,0.42) 42%, rgba(10,12,10,0.08) 68%);
        }

        .panel{
            position: relative;
            width: 100%;
            max-width: 26rem;
            margin-left: min(9vw, 6.5rem);
            padding: 2rem 1.5rem;
        }

        .panel h1{
            font-size: clamp(1.5rem, 2.6vw, 1.9rem);
            font-weight: 600;
            letter-spacing: 0.03em;
            margin: 0 0 2.2rem;
            text-shadow: 0 2px 14px rgba(0,0,0,0.35);
        }

        .field{
            display: flex;
            align-items: flex-end;
            gap: 0.75rem;
            padding-bottom: 0.6rem;
            margin-bottom: 1.7rem;
            border-bottom: 1px solid var(--line);
        }

        .field svg{
            flex: none;
            width: 18px;
            height: 18px;
            opacity: 0.9;
            margin-bottom: 2px;
        }

        .field input{
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: #fff;
            font-family: 'Prompt', sans-serif;
            font-size: 0.95rem;
            font-weight: 400;
            letter-spacing: 0.06em;
            padding: 0.2rem 0;
        }

        .field input::placeholder{
            color: rgba(255,255,255,0.85);
            letter-spacing: 0.08em;
        }

        .field:focus-within{ border-bottom-color: var(--gold); }

        .submit{
            width: 100%;
            margin-top: 0.6rem;
            padding: 0.9rem 1rem;
            border: none;
            border-radius: 4px;
            background: rgba(238,236,230,0.92);
            color: #23241f;
            font-family: 'Prompt', sans-serif;
            font-size: 0.92rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .submit:hover{ background: #fff; transform: translateY(-1px); }

        .submit:focus-visible{
            outline: 2px solid var(--gold);
            outline-offset: 3px;
        }

        .switch{
            display: block;
            text-align: center;
            margin-top: 1rem;
            font-size: 0.8rem;
            letter-spacing: 0.04em;
            color: rgba(255,255,255,0.85);
        }

        .switch a{
            color: var(--gold);
            font-weight: 600;
            text-decoration: none;
            margin-left: 0.3rem;
        }

        .switch a:hover{ text-decoration: underline; }

        @media (max-width: 640px){
            .stage{ align-items: flex-end; }
            .panel{
                margin: 0;
                padding: 2.5rem 1.5rem 3rem;
                max-width: none;
            }
            .stage::before{
                background: linear-gradient(180deg, rgba(10,12,10,0.15) 0%, rgba(10,12,10,0.82) 78%);
            }
        }
    </style>
</head>
<body>

    <main class="stage">
        <section class="panel">
            <h1>เข้าสู่ระบบ</h1>

            <form action="process-login.php" method="POST" novalidate>
                <div class="field">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <path d="M3 7l9 6 9-6"/>
                    </svg>
                    <input name="email_account" type="email" placeholder="อีเมล" required>
                </div>

                <div class="field">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="8" r="4.5"/>
                        <path d="M11.3 11.3L21 21m-6-6l3-3m-6.5-2.5a4.5 4.5 0 1 1 0-9"/>
                    </svg>
                    <input name="password_account" type="password" placeholder="รหัสผ่าน" required>
                </div>

                <button type="submit" class="submit">เข้าสู่ระบบ</button>

                <span class="switch">
                    ยังไม่มีบัญชีใช่ไหม<a href="form-register.php">สร้างบัญชีใหม่</a>
                </span>
            </form>
        </section>
    </main>

</body>
</html>