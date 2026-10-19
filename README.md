<?php
$name          = "Emerjay A. Jumamil";
$email         = "emerjay.jumamil@gmail.com";
$gmailCompose  = "https://mail.google.com/mail/?view=cm&fs=1&to=" . urlencode($email);
$messenger     = "https://m.me/kurounishanaiga009";
$resumeLink    = "https://drive.google.com/file/d/1c1odnZCzWxeLn9GCy2DX0Lc21U2OsG98/view?usp=sharing";
$capstoneLink   = "https://drive.google.com/file/d/1GJ5sMoxHddjFhgAiviZGHEFS4MD3sNl5/view?usp=sharing";
$year          = date("Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= htmlspecialchars($name) ?> — Digital Portfolio</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;700;800&family=Space+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="icon" href="favicon.png" type="image/png">
<style>
  :root{
    --bg:#0A0E13;
    --bg-raised:#0F151C;
    --line:#1B2A2E;
    --cyan:#2FE6D2;
    --cyan-soft:#8FF3E6;
    --text:#E9F3F3;
    --muted:#7E939A;
    --panel-border:#1F3338;
    box-sizing:border-box;
    padding-top:env(safe-area-inset-top,0px);
    padding-bottom:env(safe-area-inset-bottom,0px);
  }
  *{box-sizing:inherit; margin:0; padding:0;}
  html{scroll-padding-top:env(safe-area-inset-top,0px); scroll-behavior:smooth;}
  html,body{height:100%;}
  body{
    background:var(--bg);
    color:var(--text);
    font-family:'Space Grotesk', system-ui, sans-serif;
    line-height:1.5;
    overflow-x:hidden;
  }
  a{color:inherit; text-decoration:none;}
  img{max-width:100%; display:block;}
  .wrap{max-width:1180px; margin:0 auto; padding:0 28px;}

  /* ---------- background grid + glow ---------- */
  .grid-bg{
    position:absolute; inset:0;
    background-image:
      linear-gradient(var(--line) 1px, transparent 1px),
      linear-gradient(90deg, var(--line) 1px, transparent 1px);
    background-size:34px 34px;
    mask-image:linear-gradient(to bottom, rgba(0,0,0,.9), rgba(0,0,0,.15) 80%);
    opacity:.55;
    pointer-events:none;
  }
  .node{
    position:absolute; width:7px; height:7px; border-radius:50%;
    background:var(--cyan);
    box-shadow:0 0 14px 3px rgba(47,230,210,.65);
  }
  .stem{
    position:absolute; width:1px; background:linear-gradient(to bottom, rgba(47,230,210,.5), transparent);
  }

  /* ---------- nav ---------- */
  header{position:relative; z-index:3; padding:26px 0 0;}
  nav.wrap{display:flex; align-items:center; justify-content:space-between; gap:24px;}
  .brand{display:flex; align-items:center; gap:10px; font-family:'Orbitron',sans-serif; font-weight:700; font-size:18px; letter-spacing:.5px;}
  .brand .mark{
    width:26px; height:26px;
    background:conic-gradient(from 220deg, var(--cyan), transparent 65%);
    clip-path:polygon(0 0, 100% 30%, 60% 100%, 0 70%);
  }
  .navlinks{display:flex; align-items:center; gap:4px; border:1px solid var(--panel-border); border-radius:999px; padding:6px; background:rgba(15,21,28,.6); backdrop-filter:blur(6px);}
  .navlinks a{font-size:13.5px; color:var(--muted); padding:8px 16px; border-radius:999px; transition:color .2s, background .2s;}
  .navlinks a:hover, .navlinks a.active{color:var(--text); background:rgba(47,230,210,.08);}
  .navlinks a.active{box-shadow:inset 0 0 0 1px rgba(47,230,210,.35);}
  .cta-pill{
    font-size:13.5px; font-weight:500; padding:10px 20px; border-radius:999px;
    background:rgba(47,230,210,.12); border:1px solid var(--cyan); color:var(--cyan-soft);
    white-space:nowrap;
  }
  .navtoggle{display:none;}
  .navtoggle{
    border:1px solid var(--panel-border); border-radius:999px; padding:9px 12px;
    background:rgba(15,21,28,.8); color:var(--cyan-soft); font:inherit; cursor:pointer;
  }
  .navtoggle:focus-visible,.navlinks a:focus-visible{outline:2px solid var(--cyan); outline-offset:3px;}

  /* ---------- hero ---------- */
  .hero{position:relative; padding:96px 0 220px; text-align:center;}
  .eyebrow{font-size:13px; color:var(--muted); letter-spacing:.08em; margin-bottom:22px;}
  .eyebrow span{color:var(--cyan-soft);}
  h1{
    font-family:'Orbitron', sans-serif; font-weight:800;
    font-size:clamp(34px, 6.4vw, 74px);
    line-height:1.08; letter-spacing:.01em;
    text-shadow:0 0 38px rgba(47,230,210,.18);
  }
  .hero p.lede{
    max-width:46ch; margin:26px auto 0; color:var(--muted); font-size:16.5px;
  }
  .hero-ctas{display:flex; gap:14px; justify-content:center; margin-top:38px; flex-wrap:wrap;}
  .btn{
    font-size:14px; font-weight:500; padding:13px 26px; border-radius:999px;
    display:inline-flex; align-items:center; gap:8px;
  }
  .btn-fill{background:var(--cyan); color:#06201C; box-shadow:0 0 26px rgba(47,230,210,.35);}
  .btn-outline{border:1px solid var(--panel-border); color:var(--text);}
  .btn-outline:hover{border-color:var(--cyan);}

  /* ---------- section shell ---------- */
  section{position:relative; z-index:2; padding:90px 0;}
  .kicker{font-size:13px; color:var(--cyan-soft); margin-bottom:10px;}
  h2{font-family:'Orbitron', sans-serif; font-weight:700; font-size:clamp(24px,3.4vw,34px); letter-spacing:.01em;}

  /* ---------- about ---------- */
  .about-grid{display:grid; grid-template-columns:1.1fr .9fr; gap:56px; margin-top:38px; align-items:start;}
  .about-grid p{color:var(--muted); font-size:15.5px; max-width:56ch; margin-top:16px;}
  .stats{display:grid; grid-template-columns:1fr 1fr; gap:14px;}
  .stat{border:1px solid var(--panel-border); border-radius:4px; padding:20px; position:relative;}
  .stat::before,.stat::after{content:""; position:absolute; width:10px; height:10px; border:1px solid var(--cyan); opacity:.7;}
  .stat::before{top:-1px; left:-1px; border-right:none; border-bottom:none;}
  .stat::after{bottom:-1px; right:-1px; border-left:none; border-top:none;}
  .stat .num{font-family:'Orbitron',sans-serif; font-size:26px; color:var(--cyan-soft);}
  .stat .label{font-size:12.5px; color:var(--muted); margin-top:4px;}

  /* ---------- projects ---------- */
  .projects{display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-top:38px;}
  .card{
    border:1px solid var(--panel-border); border-radius:4px; padding:26px;
    background:linear-gradient(180deg, rgba(47,230,210,.03), transparent 40%);
    position:relative; transition:border-color .2s, transform .2s;
  }
  .card:hover{border-color:var(--cyan); transform:translateY(-3px);}
  .card .tag{font-size:11.5px; color:var(--cyan-soft); border:1px solid var(--panel-border); display:inline-block; padding:3px 10px; border-radius:999px; margin-bottom:16px;}
  .card h3{font-family:'Orbitron',sans-serif; font-size:17px; margin-bottom:10px;}
  .card p{font-size:14px; color:var(--muted); margin-bottom:18px;}
  .card .stack{display:flex; flex-wrap:wrap; gap:8px; margin-bottom:18px;}
  .card .stack span{font-size:11.5px; color:var(--muted); border:1px solid var(--panel-border); padding:3px 9px; border-radius:4px;}
  .card .go{font-size:13px; color:var(--cyan); display:inline-flex; align-items:center; gap:6px;}

  /* ---------- skills ---------- */
  .skillrow{display:flex; flex-wrap:wrap; gap:10px; margin-top:34px;}
  .chip{border:1px solid var(--panel-border); padding:9px 16px; border-radius:999px; font-size:13.5px; color:var(--text);}
  .chip b{color:var(--cyan-soft); font-weight:500;}
  
  /* ---------- photography ---------- */
  .photo-grid{display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-top:34px;}
  .photo-card{position:relative; overflow:hidden; min-height:220px; border:1px solid var(--panel-border); border-radius:4px; background:linear-gradient(145deg, rgba(47,230,210,.08), rgba(47,230,210,.015));}
  .photo-card img{display:block; width:100%; height:100%; min-height:220px; object-fit:cover; filter:saturate(.82); transition:transform .35s, filter .35s;}
  .photo-card:hover img{transform:scale(1.04); filter:saturate(1);}
  .photo-card figcaption{position:absolute; inset:auto 0 0; padding:32px 16px 14px; color:var(--text); font-size:13px; background:linear-gradient(transparent, rgba(0,0,0,.78));}

  /* ---------- videography ---------- */
  .video-grid{display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-top:34px;}
  .video-card{position:relative; overflow:hidden; min-height:220px; border:1px solid var(--panel-border); border-radius:4px; background:linear-gradient(145deg, rgba(47,230,210,.08), rgba(47,230,210,.015));}
  .video-card a{display:block; position:relative; height:100%; text-decoration:none;}
  .video-card img{display:block; width:100%; height:100%; min-height:220px; object-fit:cover; filter:saturate(.82); transition:transform .35s, filter .35s;}
  .video-card:hover img{transform:scale(1.04); filter:saturate(1);}
  .video-card .play{position:absolute; inset:50% auto auto 50%; transform:translate(-50%,-50%); width:62px; height:62px; border-radius:50%; background:rgba(0,0,0,.55); border:1px solid rgba(47,230,210,.7); display:grid; place-items:center; color:var(--text); font-size:20px; box-shadow:0 0 18px rgba(47,230,210,.2);}
  .video-card figcaption{position:absolute; inset:auto 0 0; padding:32px 16px 14px; color:var(--text); font-size:13px; background:linear-gradient(transparent, rgba(0,0,0,.78));}
  @media (max-width:760px){.photo-grid,.video-grid{grid-template-columns:1fr 1fr;}}
  @media (max-width:480px){.photo-grid,.video-grid{grid-template-columns:1fr;}}

  /* ---------- contact ---------- */
  .contact{text-align:center; padding-bottom:70px;}
  .contact h2{font-size:clamp(26px,4.4vw,44px);}
  .contact p{color:var(--muted); margin:16px auto 32px; max-width:42ch;}

  footer{border-top:1px solid var(--panel-border); padding:26px 0; position:relative; z-index:2;}
  footer .wrap{display:flex; justify-content:space-between; align-items:center; font-size:12.5px; color:var(--muted); flex-wrap:wrap; gap:10px;}
  footer a:hover{color:var(--cyan-soft);}

  .back-to-top{
    position:fixed; right:24px; bottom:24px; z-index:10;
    min-height:48px; padding:0 18px; border:1px solid var(--cyan); border-radius:24px;
    background:var(--bg-raised); color:var(--cyan-soft); font-size:14px; font-weight:600;
    cursor:pointer; box-shadow:0 0 18px rgba(47,230,210,.2);
  }
  .back-to-top:hover{background:rgba(47,230,210,.16);}
  .back-to-top:focus-visible{outline:2px solid var(--cyan); outline-offset:3px;}
  .back-to-top[hidden]{display:none;}

  @media (max-width:760px){
    .navlinks{display:none;}
    nav.wrap{position:relative; gap:10px;}
    .navtoggle{display:inline-flex; align-items:center; justify-content:center;}
    .navlinks.is-open{
      display:flex; position:absolute; top:calc(100% + 12px); right:28px; z-index:5;
      flex-direction:column; align-items:stretch; min-width:180px; border-radius:12px;
      background:var(--bg-raised); box-shadow:0 12px 30px rgba(0,0,0,.35);
    }
    .navlinks.is-open a{padding:10px 14px;}
    .about-grid{grid-template-columns:1fr;}
    .projects{grid-template-columns:1fr;}
    .hero{padding:70px 0 160px;}
  }
  @media (max-width:480px){
    .brand{font-size:15px;}
    .cta-pill{font-size:12px; padding:9px 12px;}
    .navlinks.is-open{right:0;}
  }
</style>
</head>
<body>

<header>
  <div class="grid-bg" style="height:640px;"></div>
  <nav class="wrap">
    <div class="brand"><span class="mark"></span>EMERJAY</div>
    <button class="navtoggle" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="primary-navigation">☰</button>
    <div class="navlinks" id="primary-navigation">
      <a href="#top" class="active">Home</a>
      <a href="#about">About</a>
      <a href="#work">Work</a>
      <a href="#skills">Skills</a>
      <a href="#contact">Contact</a>
    </div>
    <a class="cta-pill" href="<?= htmlspecialchars($gmailCompose) ?>">Get in touch ›</a>
  </nav>
</header>

<main>
  <section class="hero" id="top">
    <div class="eyebrow">MULTIMEDIA, IT STUDENT &amp; INTERNET OF THINGS DEVELOPER &nbsp;·&nbsp; <span>OPEN TO INTERNSHIP / WORK</span></div>
    <h1>I BUILD SYSTEMS <br> &amp; <br> CREATE COMPOSITIONS<br>THAT THINK</h1>
    <p class="lede">I design and build practical IoT systems <br> &amp; <br> I create immersive digital experiences</p>
    <div class="hero-ctas">
      <a class="btn btn-fill" href="#work">View Projects</a>
      <a class="btn btn-outline" href="<?= htmlspecialchars($resumeLink) ?>" target="_blank"> View Resume</a>
    </div>

    <!-- decorative circuit nodes -->
    <div class="node" style="left:6%; bottom:40px;"></div>
    <div class="stem" style="left:6%; bottom:40px; height:90px;"></div>
    <div class="node" style="left:16%; bottom:90px;"></div>
    <div class="stem" style="left:16%; bottom:90px; height:60px;"></div>
    <div class="node" style="left:30%; bottom:20px;"></div>
    <div class="stem" style="left:30%; bottom:20px; height:120px;"></div>
    <div class="node" style="left:70%; bottom:60px;"></div>
    <div class="stem" style="left:70%; bottom:60px; height:80px;"></div>
    <div class="node" style="left:84%; bottom:30px;"></div>
    <div class="stem" style="left:84%; bottom:30px; height:110px;"></div>
    <div class="node" style="left:93%; bottom:100px;"></div>
    <div class="stem" style="left:93%; bottom:100px; height:50px;"></div>
  </section>

  <section id="about">
    <div class="wrap">
      <div class="kicker">About</div>
      <div class="about-grid">
        <div>
          <h2>Grounded in hardware, comfortable on the web.</h2>
          <p>I'm an IT student who likes projects that cross the line between physical and digital — wiring up sensors, writing the logic that reads them, and shipping a dashboard people actually want to check. My recent focus has been low-cost IoT systems for real-world contribution for environmental sustainability.</p>
        </div>
        <div class="stats">
          <div class="stat"><div class="num">2</div><div class="label">Projects Built</div></div>
          <div class="stat"><div class="num">IoT</div><div class="label">Hardware + web</div></div>
          <div class="stat"><div class="num">BSIT</div><div class="label">Class of 2026</div></div>
          <div class="stat"><div class="num">PHP & PYTHON</div><div class="label">Primary stack</div></div>
        </div>
      </div>
      <div class="about-grid">
        <div>
          <h2>Creative, problem-solving professional with a passion for Multimedia.</h2>
          <p>I'm a creative professional with a passion for multimedia, specializing in video production and editing. I enjoy bringing ideas to life through visual storytelling and technical execution.</p>
        </div>
        <div class="stats">
          <div class="stat"><div class="num">Creative & Technical Director</div><div class="label">from a previous job and in academic settings</div></div>
          <div class="stat"><div class="num">Adobe Products</div><div class="label">Adobe After Effects, Adobe Premiere Pro, Adobe Photoshop, Adobe Lightroom</div></div>
          <div class="stat"><div class="num">Photo & Videography</div><div class="label">Digital photography and video production</div></div>
          <div class="stat"><div class="num">Public Relations</div><div class="label">Experience in managing public image and communications</div></div>
        </div>
      </div>
    </div>
  </section>

  <section id="work">
    <div class="wrap">
      <div class="kicker">Selected Work</div>
      <h2>A few things I've built / currently building &amp; Contributing</h2>
      <div class="projects">
        <div class="card">
          <span class="tag">IoT · Capstone</span>
          <h3>EnerBridge</h3>
          <p>A low-cost building energy management prototype using a Raspberry Pi and distributed I.O.T sensors to detect occupancy and automatically control room loads.</p>
          <div class="stack"><span>Raspberry Pi</span><span>Laravel</span><span>MySQL</span><span>IoT</span></div>
          <a class="go" href="<?= htmlspecialchars($capstoneLink) ?>" target="_blank">View project ›</a>
        </div>
        <div class="card">
          <span class="tag">MULTIMEDIA</span>
          <h3>Video: Jesus Reigns Ministries - 40th Anniversary</h3>
          <p>Introductory video for Jesus Reigns Ministries' 40th anniversary celebration.</p>
          <div class="stack"><span>Adobe After Effects</span><span>Adobe Premiere Pro</span><span>Voice Talent</span></div>
          <a class="go" href="https://drive.google.com/file/d/1ecPA25EPBr8S7DP0LVM2Um49iJi4_aQ0/view?usp=sharing" target="_blank">View project ›</a>
        </div>
        <div class="card">
          <span class="tag">Raspberry Pi &amp; Internet Of Things</span>
          <h3>Video: Journey to Raspberry Pi-Based Project: Robotics</h3>
          <p>Raspberry Pi-Based Project: Robotics</p>
          <div class="stack"><span>Raspberry Pi</span><span>IOT Sensors and Motor Controller</span><span>Tutorial/VLog</span></div>
          <a class="go" href="https://drive.google.com/file/d/1T8D7FZl2ww4TMYr5YlGjqIhkPo4BHFvk/view?usp=sharing" target="_blank">View project ›</a>
        </div>
      </div>
    </div>
  </section>

  <section id="photography">
    <div class="wrap">
      <div class="kicker">Photography</div>
      <h2>Some of my photography works</h2>
      <div class="photo-grid">
        <figure class="photo-card">
          <img src="images/Photomanipulation.jpg" alt="Photo 3">
          <figcaption>Photo Manipulation</figcaption>
        </figure>
        <figure class="photo-card">
          <img src="images/Events.jpg" alt="Photo 1">
          <figcaption>Event Photography</figcaption>
        </figure>
        <figure class="photo-card">
          <img src="images/Storytelling.jpg" alt="Photo 2">
          <figcaption>Storytelling</figcaption>
        </figure>
        <figure class="photo-card">
          <img src="images/Sportsphotography.jpg" alt="Photo 4">
          <figcaption>Sports Photography</figcaption>
        </figure>
        <figure class="photo-card">
          <img src="images/Portraiture.jpg" alt="Photo 3">
          <figcaption>Portraiture</figcaption>
        </figure>
        <figure class="photo-card">
          <img src="images/Digiart.jpg" alt="Photo 3">
          <figcaption>Digital Arts</figcaption>
        </figure>
      </div>
    </div>
  </section>

  <section id="videography">
    <div class="wrap">
      <div class="kicker">Videography</div>
      <h2>Some of my video works</h2>
      <div class="video-grid">
        <figure class="video-card">
          <a href="https://drive.google.com/file/d/1ecPA25EPBr8S7DP0LVM2Um49iJi4_aQ0/view?usp=sharing" target="_blank" rel="noopener">
            <img src="images/ChurchAnniversary.jpg" alt="Church anniversary video preview">
            <span class="play">▶</span>
            <figcaption>Jesus Reigns Ministries — 40th Anniversary</figcaption>
          </a>
        </figure>
        <figure class="video-card">
          <a href="https://www.youtube.com/watch?v=CG1HMcEOJ5w" target="_blank" rel="noopener">
            <img src="images/ShortFilm.jpg" alt="Short film video preview">
            <span class="play">▶</span>
            <figcaption>Short Film Project</figcaption>
          </a>
        </figure>
        <figure class="video-card">
          <a href="https://www.facebook.com/share/v/1CSbzhUjTn/" target="_blank" rel="noopener">
            <img src="images/JRCCMM.jpg" alt="School Event">
            <span class="play">▶</span>
            <figcaption>School Event</figcaption>
          </a>
        </figure>
      </div>
    </div>
  </section>



  <section id="skills">
    <div class="wrap">
      <div class="kicker">Skills: Information Technology</div>
      <h2>Tools I reach for</h2>
      <div class="skillrow">
        <span class="chip"><b>Languages</b> · PHP, Python, JavaScript</span>
        <span class="chip"><b>Framework</b> · Laravel </span>
        <span class="chip"><b>Hardware</b> · Raspberry Pi, IoT sensors</span>
        <span class="chip"><b>Data</b> · MySQL</span>
        <span class="chip"><b>Design</b> · Figma</span>
      </div>
    </div>
  </section>
  <section id="skills">
    <div class="wrap">
      <div class="kicker">Skills: Multimedia</div>
      <h2>Softwares Used</h2>
      <div class="skillrow">
        <span class="chip"><b>Video Editing Softwares:</b> · Adobe Premiere Pro, Adobe After Effects, Capcut</span>
        <span class="chip"><b>Photo Editing Softwares:</b> · Adobe Photoshop, Adobe Lightroom</span>
        <span class="chip"><b>Camera Experiences:</b> · Canon 500D, 1100D, 1200D, 200D, Sony a6000, a6400, Fujifilm XT100</span>
      </div>
    </div>
  </section>

<section id="skills">
    <div class="wrap">
      <h2>Certifications</h2>
      <div class="skillrow">
        <span class="chip"><b>UNITECH EDUCATIONAL PROGRAM CORPORATION</b> · COMPUTER LITERACY PROGRAM - August, 2014<br></span> 
      </div><BR>
      <div class="kicker">  ---MODULES TAKEN---</div>
        <span class="chip"> · Adobe Flash Professional </span>
        <span class="chip"> · Adobe Photoshop </span>
        <span class="chip"> · Basic HTML </span> 
        <span class="chip"> · Microsoft Office Suite (WORD, EXCEL, POWERPOINT) </span>
        <span class="chip"> · MS Windows Operating System </span>
    </div>
  </section>
  
  <section class="contact" id="contact">
    <div class="wrap">
      <div class="kicker" style="text-align:center;">Contact</div>
      <h2>Let's work together.</h2>
      <p>Open to internships, freelance work, and any hardware-meets-software opportunities. Reach out directly — I usually reply within a day.</p>
      <div class="hero-ctas">
        <a class="btn btn-fill" href="<?= htmlspecialchars($gmailCompose) ?>">Email me</a>
        <a class="btn btn-outline" href="<?= htmlspecialchars($messenger) ?>">Messenger</a>
      </div>
    </div>
  </section>
</main>

<footer>
  <div class="wrap">
    <span>&copy; <?= $year ?> <?= htmlspecialchars($name) ?>. All rights reserved.</span>
    <span><a href="<?= htmlspecialchars($gmailCompose) ?>"><?= htmlspecialchars($email) ?></a></span>
  </div>
</footer>

<button class="back-to-top" id="back-to-top" type="button" aria-label="Back to top" hidden>Back to Top</button>

<script>
  (() => {
    const backToTop = document.querySelector('#back-to-top');
    const updateBackToTop = () => {
      backToTop.hidden = window.scrollY < 400;
    };
    window.addEventListener('scroll', updateBackToTop, { passive: true });
    backToTop.addEventListener('click', () => {
      const startY = window.scrollY;
      const startTime = performance.now();
      const duration = 500;
      const animateScroll = now => {
        const progress = Math.min((now - startTime) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        window.scrollTo(0, startY * (1 - eased));
        if (progress < 1) requestAnimationFrame(animateScroll);
      };
      requestAnimationFrame(animateScroll);
    });
    updateBackToTop();

    const toggle = document.querySelector('.navtoggle');
    const menu = document.querySelector('.navlinks');
    const links = [...menu.querySelectorAll('a[href^="#"]')];
    const sections = links
      .map(link => document.querySelector(link.getAttribute('href')))
      .filter(Boolean);

    const closeMenu = () => {
      menu.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-label', 'Open navigation');
    };

    toggle.addEventListener('click', () => {
      const isOpen = toggle.getAttribute('aria-expanded') !== 'true';
      menu.classList.toggle('is-open', isOpen);
      toggle.setAttribute('aria-expanded', String(isOpen));
      toggle.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
    });

    links.forEach(link => link.addEventListener('click', closeMenu));
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape') closeMenu();
    });

    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (!entry.isIntersecting) return;
          links.forEach(link => {
            const active = link.getAttribute('href') === `#${entry.target.id}`;
            link.classList.toggle('active', active);
            if (active) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
          });
        });
      }, { rootMargin: '-30% 0px -60% 0px' });
      sections.forEach(section => observer.observe(section));
    }
  })();
</script>

</body>
</html>
