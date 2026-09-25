<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เที่ยวตาก | วางแผนทริปจังหวัดตากของคุณ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root{
        --ink: #1d231d;
        --ink-soft: #4c554b;
        --cream: #faf8f3;
        --panel: #eef1e9;
        --green: #24593f;
        --green-deep: #163a28;
        --green-bright: #3f8f5f;
        --line: rgba(29,35,29,0.12);
        --radius: 14px;
    }

    *{ box-sizing: border-box; }
    html{ scroll-behavior: smooth; }
    body{
        margin: 0;
        font-family: 'Prompt', sans-serif;
        color: var(--ink);
        background: var(--cream);
    }

    a{ color: inherit; }
    img{ max-width: 100%; display: block; }
    .wrap{ max-width: 1440px; margin: 0 auto; padding: 0 1.5rem; }

    /* ---------- Nav ---------- */
    .nav{
        position: sticky;
        top: 0;
        z-index: 20;
        background: rgba(250,248,243,0.92);
        backdrop-filter: blur(6px);
        border-bottom: 1px solid var(--line);
    }
    .nav .wrap{
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 4.5rem;
    }
    .logo{
        display: flex;
        align-items: baseline;
        gap: 0.35rem;
        font-weight: 700;
        font-size: 1.25rem;
        text-decoration: none;
    }
    .logo b{ color: var(--green); }
    .logo small{ font-weight: 400; font-size: 0.7rem; color: var(--ink-soft); letter-spacing: 0.08em; }

    .nav-links{
        display: flex;
        gap: 2rem;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .nav-links a{
        text-decoration: none;
        font-size: 0.92rem;
        font-weight: 500;
        color: var(--ink-soft);
    }
    .nav-links a.active, .nav-links a:hover{ color: var(--green); }

    .nav-actions{ display: flex; align-items: center; gap: 1rem; }
    .btn-plan{
        background: var(--green);
        color: #fff;
        border: none;
        border-radius: 999px;
        padding: 0.65rem 1.4rem;
        font-family: 'Prompt', sans-serif;
        font-size: 0.88rem;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: background 0.2s ease;
    }
    .btn-plan:hover{ background: var(--green-deep); }

    @media (max-width: 900px){
        .nav-links{ display: none; }
    }

    /* ---------- Hero ---------- */
    .hero{
        position: relative;
        margin: 1.5rem auto 0;
        max-width: 1440px;
        border-radius: 22px;
        overflow: hidden;
        aspect-ratio: 16 / 7;
        max-height: 620px;
        min-height: 380px;
        display: flex;
        align-items: flex-end;
        background:
            linear-gradient(180deg, rgba(10,20,12,0.15) 0%, rgba(8,16,10,0.78) 92%),
            #14261a url('images/hero-tak.jpeg') center / cover no-repeat;
    }
    @media (max-width: 640px){
        .hero{ aspect-ratio: 3 / 4; max-height: none; }
    }
    .hero-content{
        position: relative;
        padding: 3rem clamp(1.5rem, 5vw, 3.5rem) 3.2rem;
        color: #fdfbf5;
        max-width: 46rem;
    }
    .hero-content .kicker{
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        letter-spacing: 0.14em;
        color: rgba(253,251,245,0.8);
        margin-bottom: 1rem;
    }
    .hero-content .kicker::before{
        content: "";
        width: 1.6rem; height: 1px;
        background: var(--green-bright);
    }
    .hero-content h1{
        font-size: clamp(3.5rem, 9vw, 6.5rem);
        line-height: 0.92;
        font-weight: 800;
        margin: 0 0 1rem;
        letter-spacing: -0.01em;
    }
    .hero-content h1 sup{
        font-size: 1rem;
        font-weight: 500;
        letter-spacing: 0.12em;
        vertical-align: super;
        margin-left: 0.5rem;
        color: rgba(253,251,245,0.7);
    }
    .hero-content p{
        font-size: 1.02rem;
        line-height: 1.7;
        color: rgba(253,251,245,0.86);
        max-width: 32rem;
        margin: 0 0 1.8rem;
    }
    .hero-ctas{ display: flex; gap: 0.9rem; flex-wrap: wrap; }
    .btn-solid{
        background: #fdfbf5;
        color: var(--green-deep);
        border: none;
        border-radius: 999px;
        padding: 0.85rem 1.6rem;
        font-weight: 600;
        font-size: 0.92rem;
        text-decoration: none;
        display: inline-block;
    }
    .btn-outline{
        border: 1px solid rgba(253,251,245,0.65);
        color: #fdfbf5;
        border-radius: 999px;
        padding: 0.85rem 1.6rem;
        font-weight: 500;
        font-size: 0.92rem;
        text-decoration: none;
        display: inline-block;
    }

    /* ---------- Why section ---------- */
    .why{
        display: grid;
        grid-template-columns: 1.15fr 1fr;
        gap: 3rem;
        padding: 5rem 0 4rem;
        align-items: start;
    }
    .why h2{
        font-size: clamp(1.6rem, 3vw, 2.1rem);
        line-height: 1.3;
        margin: 0 0 1rem;
        font-weight: 700;
    }
    .why > div:first-child p{
        color: var(--ink-soft);
        line-height: 1.75;
        max-width: 34rem;
        margin: 0;
        font-size: 0.98rem;
    }
    .feature-list{
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .feature{
        display: flex;
        gap: 1rem;
        background: var(--panel);
        border-radius: var(--radius);
        padding: 1.1rem 1.3rem;
    }
    .feature .icon{
        flex: none;
        width: 3rem; height: 3rem;
        border-radius: 10px;
        background: var(--green-deep);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .feature .icon svg{ width: 22px; height: 22px; }
    .feature h3{ margin: 0 0 0.25rem; font-size: 1rem; font-weight: 600; }
    .feature p{ margin: 0; font-size: 0.86rem; color: var(--ink-soft); line-height: 1.6; }

    @media (max-width: 860px){
        .why{ grid-template-columns: 1fr; padding-top: 3rem; }
    }

    /* ---------- Destinations ---------- */
    .destinations{
        background: var(--panel);
        border-radius: 26px;
        padding: 3rem clamp(1.25rem, 4vw, 3rem);
        margin-bottom: 4rem;
    }
    .destinations-head{
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 2rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }
    .destinations-head h2{ margin: 0; font-size: 1.6rem; font-weight: 700; }
    .destinations-head p{
        margin: 0.5rem 0 0;
        color: var(--ink-soft);
        max-width: 26rem;
        font-size: 0.92rem;
        line-height: 1.6;
    }

    .dest-grid{
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.2rem;
    }
    @media (max-width: 1000px){ .dest-grid{ grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 560px){ .dest-grid{ grid-template-columns: 1fr; } }

    .dest-card{
        position: relative;
        border-radius: var(--radius);
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1px 2px rgba(29,35,29,0.06);
    }
    .dest-card .photo{
        position: relative;
        aspect-ratio: 4 / 5;
        background: #cfd6c8 center / cover no-repeat;
    }
    .dest-card .tag{
        position: absolute;
        top: 0.7rem; left: 0.7rem;
        background: rgba(253,251,245,0.92);
        color: var(--green-deep);
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3rem 0.6rem;
        border-radius: 999px;
    }
    .add-btn{
        position: absolute;
        top: 0.7rem; right: 0.7rem;
        width: 2.1rem; height: 2.1rem;
        border-radius: 50%;
        border: none;
        background: var(--green);
        color: #fff;
        font-size: 1.1rem;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease, transform 0.2s ease;
    }
    .add-btn:hover{ background: var(--green-deep); transform: scale(1.06); }
    .add-btn[aria-pressed="true"]{ background: var(--ink); }
    .add-btn[aria-pressed="true"]::after{ content: "✓"; font-size: 0.95rem; }
    .add-btn[aria-pressed="true"] .plus{ display: none; }

    .dest-card .info{ padding: 0.9rem 1rem 1.1rem; }
    .dest-card .info h3{ margin: 0 0 0.3rem; font-size: 1rem; font-weight: 600; }
    .dest-card .info .meta{
        margin: 0 0 0.4rem;
        font-size: 0.8rem;
        color: var(--ink-soft);
    }
    .dest-card .info .loc{
        display: flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.78rem;
        color: var(--ink-soft);
    }
    .dest-card .info .loc svg{ width: 12px; height: 12px; flex: none; }

    .view-more{
        margin-top: 1.6rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 999px;
        padding: 0.6rem 1.3rem;
        font-size: 0.86rem;
        font-weight: 500;
        text-decoration: none;
        color: var(--ink);
    }

    /* ---------- Trip banner ---------- */
    .trip-banner{
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2rem;
        background: var(--green-deep);
        color: #fdfbf5;
        border-radius: 22px;
        padding: 2.4rem clamp(1.5rem, 4vw, 3rem);
        margin-bottom: 5rem;
        flex-wrap: wrap;
    }
    .trip-banner h2{ margin: 0 0 0.4rem; font-size: 1.4rem; font-weight: 700; }
    .trip-banner p{ margin: 0; color: rgba(253,251,245,0.78); font-size: 0.92rem; max-width: 30rem; }
    .trip-count{
        font-size: 0.85rem;
        color: rgba(253,251,245,0.85);
        background: rgba(253,251,245,0.12);
        border-radius: 999px;
        padding: 0.5rem 1rem;
    }

    /* ---------- Footer ---------- */
    footer{
        border-top: 1px solid var(--line);
        padding: 2.5rem 0 3rem;
        color: var(--ink-soft);
        font-size: 0.85rem;
    }
</style>
</head>
<body>

<nav class="nav">
    <div class="wrap">
        <a href="home.php" class="logo">เที่ยว<b>ตาก</b><small>&nbsp;TAK EXPLORE</small></a>
        <ul class="nav-links">
            <li><a href="home.php" class="active">หน้าแรก</a></li>
            <li><a href="#destinations">สถานที่เที่ยว</a></li>
            <li><a href="shops.php">ร้านอาหาร &amp; คาเฟ่</a></li>
            <li><a href="#">แพ็กเกจทริป</a></li>
            <li><a href="#">เกี่ยวกับเรา</a></li>
        </ul>
        <div class="nav-actions">
            <a href="trip-planner.php" class="btn-plan">วางแผนทริปของฉัน</a>
        </div>
    </div>
</nav>

<div class="wrap">

    <section class="hero">
        <div class="hero-content">
            <span class="kicker">จังหวัดตาก · ภาคเหนือตอนล่าง</span>
            <h1>ตาก<sup>TAK, THAILAND</sup></h1>
            <p>ดินแดนแห่งขุนเขา สายหมอก และชายแดนไทย–เมียนมา ตั้งแต่น้ำตกทีลอซูอันยิ่งใหญ่ ไปจนถึงทะเลหมอกยามเช้าที่ดอยมูเซอ เลือกจุดหมายที่ใช่ แล้วให้เราช่วยจัดลำดับทริปของคุณ</p>
            <div class="hero-ctas">
                <a href="trip-planner.php" class="btn-solid">เริ่มวางแผนทริป</a>
                <a href="#destinations" class="btn-outline">ดูสถานที่ทั้งหมด</a>
            </div>
        </div>
    </section>

    <section class="why">
        <div>
            <h2>ทำไมนักเดินทางเลือกวางแผนเที่ยวตากที่นี่</h2>
            <p>จากผืนป่าอุ้มผางสู่ตลาดชายแดนแม่สอด เรารวบรวมสถานที่เที่ยวทั่วจังหวัดตากไว้ในที่เดียว พร้อมระบบช่วยจัดลำดับเส้นทาง เพื่อให้ทริปของคุณราบรื่นตั้งแต่จุดแรกถึงจุดสุดท้าย</p>
        </div>
        <div class="feature-list">
            <div class="feature">
                <div class="icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20l6-11 4 6 3-5 5 10H3z"/></svg>
                </div>
                <div>
                    <h3>ครบทุกภูมิประเทศ</h3>
                    <p>น้ำตก ภูเขา ทะเลหมอก และเมืองชายแดน รวมไว้ในจังหวัดเดียว</p>
                </div>
            </div>
            <div class="feature">
                <div class="icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12h8M12 8v8"/></svg>
                </div>
                <div>
                    <h3>เลือกแล้วจัดลำดับได้เอง</h3>
                    <p>กดปุ่ม + เพื่อเพิ่มสถานที่เข้าทริป แล้วจัดลำดับก่อน-หลังตามที่คุณต้องการ</p>
                </div>
            </div>
            <div class="feature">
                <div class="icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20l4-16 4 12 4-8 4 12"/></svg>
                </div>
                <div>
                    <h3>ชุมชนช่วยกันเติมข้อมูล</h3>
                    <p>สมาชิกทุกคนเพิ่มร้านอาหาร คาเฟ่ และจุดเที่ยวใหม่ ๆ ได้เหมือนแผนที่ของเราเอง</p>
                </div>
            </div>
        </div>
    </section>

    <section class="destinations" id="destinations">
        <div class="destinations-head">
            <div>
                <h2>สถานที่เที่ยวยอดนิยม</h2>
                <p>ตั้งแต่น้ำตกกลางป่าลึกไปจนถึงทะเลหมอกบนยอดดอย กดปุ่ม + เพื่อเพิ่มลงในทริปของคุณ</p>
            </div>
        </div>

        <div class="dest-grid">

            <article class="dest-card" data-place="thi-lo-su">
                <div class="photo" style="background-image:url('images/dest-thilosu.jpg')">
                    <span class="tag">ยอดฮิต</span>
                    <button class="add-btn" type="button" aria-pressed="false" aria-label="เพิ่มน้ำตกทีลอซูลงในทริป">
                        <span class="plus">+</span>
                    </button>
                </div>
                <div class="info">
                    <h3>น้ำตกทีลอซู</h3>
                    <p class="meta">น้ำตกที่ใหญ่ที่สุดในไทย · อุทยานแห่งชาติอุ้มผาง</p>
                    <span class="loc">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C7.6 2 4 5.6 4 10c0 5.4 8 12 8 12s8-6.6 8-12c0-4.4-3.6-8-8-8zm0 11a3 3 0 110-6 3 3 0 010 6z"/></svg>
                        อ.อุ้มผาง
                    </span>
                </div>
            </article>

            <article class="dest-card" data-place="doi-musoe">
                <div class="photo" style="background-image:url('images/dest-doimusoe.jpg')">
                    <span class="tag">ทะเลหมอก</span>
                    <button class="add-btn" type="button" aria-pressed="false" aria-label="เพิ่มดอยมูเซอลงในทริป">
                        <span class="plus">+</span>
                    </button>
                </div>
                <div class="info">
                    <h3>ดอยมูเซอ</h3>
                    <p class="meta">จุดชมทะเลหมอกยามเช้า · อากาศเย็นตลอดปี</p>
                    <span class="loc">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C7.6 2 4 5.6 4 10c0 5.4 8 12 8 12s8-6.6 8-12c0-4.4-3.6-8-8-8zm0 11a3 3 0 110-6 3 3 0 010 6z"/></svg>
                        อ.เมืองตาก
                    </span>
                </div>
            </article>

            <article class="dest-card" data-place="bhumibol-dam">
                <div class="photo" style="background-image:url('images/dest-bhumibol.jpg')">
                    <span class="tag">ธรรมชาติ</span>
                    <button class="add-btn" type="button" aria-pressed="false" aria-label="เพิ่มเขื่อนภูมิพลลงในทริป">
                        <span class="plus">+</span>
                    </button>
                </div>
                <div class="info">
                    <h3>เขื่อนภูมิพล</h3>
                    <p class="meta">เขื่อนหินถมแห่งแรกของไทย · ล่องเรือชมทะเลสาบ</p>
                    <span class="loc">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C7.6 2 4 5.6 4 10c0 5.4 8 12 8 12s8-6.6 8-12c0-4.4-3.6-8-8-8zm0 11a3 3 0 110-6 3 3 0 010 6z"/></svg>
                        อ.สามเงา
                    </span>
                </div>
            </article>

            <article class="dest-card" data-place="mae-sot-market">
                <div class="photo" style="background-image:url('images/dest-maesot.jpg')">
                    <span class="tag">ชายแดน</span>
                    <button class="add-btn" type="button" aria-pressed="false" aria-label="เพิ่มตลาดริมเมยลงในทริป">
                        <span class="plus">+</span>
                    </button>
                </div>
                <div class="info">
                    <h3>ตลาดริมเมย</h3>
                    <p class="meta">ตลาดชายแดนไทย–เมียนมา · ของกิน ของฝากหลากหลาย</p>
                    <span class="loc">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C7.6 2 4 5.6 4 10c0 5.4 8 12 8 12s8-6.6 8-12c0-4.4-3.6-8-8-8zm0 11a3 3 0 110-6 3 3 0 010 6z"/></svg>
                        อ.แม่สอด
                    </span>
                </div>
            </article>

        </div>

        <a href="#" class="view-more">ดูสถานที่ทั้งหมด</a>
    </section>

    <section class="trip-banner">
        <div>
            <h2>ทริปของคุณตอนนี้</h2>
            <p>เพิ่มสถานที่ที่สนใจแล้วไปจัดลำดับเส้นทางต่อได้ที่หน้าวางแผนทริป ระบบจะช่วยเรียงเส้นทางให้อัตโนมัติ</p>
        </div>
        <span class="trip-count" id="trip-count">ยังไม่ได้เลือกสถานที่</span>
    </section>

</div>

<footer>
    <div class="wrap">© <?php echo date('Y'); ?> เที่ยวตาก · แพลตฟอร์มวางแผนท่องเที่ยวจังหวัดตาก</div>
</footer>

<script>
    // Shared trip list: stored in localStorage so trip-planner.php (drag-to-reorder
    // + Leaflet map) reads and writes the exact same list. Order isn't enforced here;
    // trip-planner.php owns ordering, this page only adds/removes membership.
    const STORAGE_KEY = 'takTripPlaces';
    const countLabel = document.getElementById('trip-count');

    function loadTrip(){
        try{ return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; }
        catch(e){ return []; }
    }
    function saveTrip(list){
        localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
    }
    function updateCount(list){
        countLabel.textContent = list.length === 0
            ? 'ยังไม่ได้เลือกสถานที่'
            : `เลือกไว้ ${list.length} ที่ · ไปจัดลำดับที่หน้าวางแผนทริป`;
    }

    let trip = loadTrip();
    updateCount(trip);

    document.querySelectorAll('.add-btn').forEach(btn => {
        const card = btn.closest('.dest-card');
        const placeId = card.dataset.place;
        if(trip.includes(placeId)) btn.setAttribute('aria-pressed', 'true');

        btn.addEventListener('click', () => {
            const pressed = btn.getAttribute('aria-pressed') === 'true';
            btn.setAttribute('aria-pressed', String(!pressed));
            trip = pressed ? trip.filter(id => id !== placeId) : [...trip, placeId];
            saveTrip(trip);
            updateCount(trip);
        });
    });
</script>

</body>
</html>