<?php
session_start();
if (isset($_GET['reset'])) { session_destroy(); header('Location: index.php'); exit; }
$_SESSION += ['level' => 1, 'att' => 0, 'name' => 'Anonymous Survivor', 't3' => 0];

// every wrong click reports here so PHP keeps the attempt count
if (isset($_GET['hit'])) { $_SESSION['att']++; echo json_encode(['att' => $_SESSION['att']]); exit; }

// levels 1,2,4,5 are won in JS, then report here to advance
if (isset($_GET['complete']) && (int)$_GET['complete'] === $_SESSION['level'] && $_SESSION['level'] !== 3) {
    $_SESSION['level']++; header('Location: index.php'); exit;
}

$level = $_SESSION['level'];
$moods = [1 => '😐 Weird', 2 => '😭 Annoying', 3 => '😡 Frustrating', 4 => '🤯 Chaotic', 5 => '💀 ABSOLUTE DISASTER', 6 => '🏆 Survivor'];
$imgs = [];
foreach (glob(__DIR__ . '/images/*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE) as $f) $imgs[] = 'images/' . basename($f);

// ---------- LEVEL 3: FORM FROM HELL ----------
$errors = []; $v = ['name' => '', 'age' => '', 'email' => '', 'pw' => ''];
if ($level === 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['t3']++; $_SESSION['att']++;
    $t = $_SESSION['t3'];
    foreach ($v as $k => $_) $v[$k] = trim($_POST[$k] ?? '');
    extract($v, EXTR_PREFIX_ALL, 'in');

    if ($in_name === '') $errors[] = "Name is required. (Also, please don't have one.)";
    elseif ($t < 3 && strlen($in_name) >= 3) $errors[] = "Name must be shorter than 3 letters AND longer than 5. Pick both.";

    if ($t <= 2) {
        $errors[] = is_numeric($in_age) ? "Age must NOT be a number." : "Age must be a number.";
    } elseif (!preg_match('/\d/', $in_age) || !preg_match('/[a-z]/i', $in_age)) {
        $errors[] = "Age must be a number... and also words. Try something like '20ish'.";
    }

    if (strpos($in_email, '@') === false) $errors[] = "Email needs an @. It's called ‘email’, not ‘mail’.";
    elseif ($t < 4 && strpos($in_email, '.') !== false) $errors[] = "Email must not contain dots. Dots are overrated.";

    if (stripos($in_pw, 'potato') === false) $errors[] = "Password must contain the word 'potato'. Don't ask.";
    elseif ($t < 4 && strlen($in_pw) > 6) $errors[] = "Password is too long. Also too short. Fix both.";
    if ($in_pw !== '' && $in_pw === $in_name) $errors[] = "Password can't be your name. Nice try, genius.";

    if (!$errors) {
        $_SESSION['name'] = $in_name; $_SESSION['level'] = 4;
        header('Location: index.php'); exit;
    }
}
$try3 = $_SESSION['t3'];
$att = $_SESSION['att'];
$sanity = max(0, 100 - $att * 2);

// ---------- helpers for random ugly styles ----------
$fonts = ['Comic Sans MS', 'Papyrus', 'Impact', 'Courier New', 'cursive', 'fantasy', 'Times New Roman', 'Brush Script MT'];
function ugly($fonts) {
    return sprintf('background:hsl(%d,100%%,50%%);color:hsl(%d,100%%,50%%);font-family:%s;font-size:%dpx;transform:rotate(%ddeg);margin:%dpx;',
        mt_rand(0, 360), mt_rand(0, 360), $fonts[array_rand($fonts)], mt_rand(10, 34), mt_rand(-12, 12), mt_rand(2, 30));
}
?><!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Two Sister and a System-BAD UX/UI</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="level<?= $level ?>.css"></head>
<body class="l<?= $level ?>">
<div id="bar">LEVEL <?= min($level, 5) ?>/5 — <?= $moods[$level] ?> | ATTEMPTS: <b id="att"><?= $att ?></b> | SANITY: <b id="san"><?= $sanity ?></b>%</div>
<h1>THE WORST WEBSITE EVER</h1>
<div id="msg">&nbsp;</div>

<?php if ($level === 1): // ---------------- LEVEL 1 ---------------- ?>
<h2>Level 1: Please proceed. (Somewhere.)</h2>
<?php
$labels = ['START', 'BEGIN', 'CONTINUE', "DON'T CLICK", 'PLAY', 'NEXT LEVEL', 'CLICK ME', 'FREE ROBUX', 'GO', 'LOGIN', 'DOWNLOAD MORE RAM', 'DEFINITELY START', 'BACK', 'SKIP'];
$real = array_rand($labels);
foreach ($labels as $i => $label) {
    $isReal = $i === $real;
    // the real button is the same as the rest… except it's the only one that works
    echo '<button style="' . ugly($fonts) . '" data-r="' . (int)$isReal . '">' . $label . '</button>';
}
?>
<p style="font-size:9px">Hint: there is no hint. Good luck.</p>
<script>
const wrong=["That's a decoy. Like your career.","Nope. Try believing harder.","This button does absolutely nothing.","It said DON'T CLICK. Why did you click?","Error: button is on vacation.","You have chosen... poorly.","Have you tried turning yourself off and on again?","This button works only on Tuesdays.","Wrong. But confidently wrong. Respect.","404: Correct button not found here.","Loading... just kidding.","Fun fact: you're losing."];
document.querySelectorAll('button[data-r]').forEach(b=>b.onclick=()=>{
  if(b.dataset.r==='1'){location='?complete=1';return}
  hit();say(wrong[Math.floor(Math.random()*wrong.length)]);
});
</script>

<?php elseif ($level === 2): // ---------------- LEVEL 2 ---------------- ?>
<h2>Level 2: Just click YES. It's easy.</h2>
<button id="no" style="font-size:40px;background:red;color:#fff">NO</button>
<button id="yes" class="abs" style="left:40vw;top:50vh;font-size:30px;background:lime;color:#000;z-index:5">YES</button>
<script>
const M=["Bro, it's literally right there.","Skill issue.","It moved. Again. Shocking.","Are you even trying?","Your mouse is broken. (It's not. It's you.)","The button fears you.","Have you considered a career change?","Almost. Not really.","I believe in you. (I don't.)","OK, that one was close. Lying.","Fine. FINE."];
let d=0;const yes=document.getElementById('yes');
function run(){
  if(d>=12)return; d++; hit();
  yes.style.left=rnd(2,75)+'vw'; yes.style.top=rnd(18,80)+'vh';
  say(M[Math.min(d-1,M.length-1)]+' (dodges: '+d+')');
  if(d>=12)say("...okay, it's tired. Click it. (dodges: 12)");
}
yes.addEventListener('mouseenter',run);
yes.addEventListener('touchstart',e=>{if(d<12){e.preventDefault();run()}});
yes.onclick=()=>{ if(d>=12) location='?complete=2'; else run(); };
document.getElementById('no').onclick=()=>{hit();say("You clicked NO? Wrong answer. Try YES if you can catch it.")};
</script>

<?php elseif ($level === 3): // ---------------- LEVEL 3 ---------------- ?>
<h2>Level 3: Register. (Attempt #<?= $try3 + 1 ?>)</h2>
<?php foreach ($errors as $e): ?><div class="err">❌ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
<form method="post" autocomplete="off">
  <label>Name (must be shorter AND longer)</label><input name="name" value="<?= htmlspecialchars($v['name']) ?>">
  <label>Age (a number, or NOT a number)</label><input name="age" value="<?= htmlspecialchars($v['age']) ?>">
  <label>Email (with @, but no dots)</label><input name="email" value="<?= htmlspecialchars($v['email']) ?>">
  <label>Password (contains 'potato')</label><input name="pw" type="text" value="<?= htmlspecialchars($v['pw']) ?>">
  <button style="font-size:11px;background:#ccc">submit (maybe)</button>
</form>
<p style="font-size:14px"><?= $try3 >= 3 ? "Psst… the rules are getting softer. Try '20ish'." : "PHP is watching you." ?></p>

<?php elseif ($level === 4): // ---------------- LEVEL 4 ---------------- ?>
<h2>Level 4: Read these important notices.</h2>
<p>Popups closed: <b id="cl">0</b>/8</p>
<div id="done" style="display:none"><a href="?complete=4" style="font-size:40px;color:#f0f">▶ NEXT (finally)</a></div>

<?php elseif ($level === 5): // ---------------- LEVEL 5 ---------------- ?>
<h2 id="h2">FINAL BOSS: catch YES + close 5 popups + don't click the decoys</h2>
<p id="tasks" style="background:#000;padding:4px">☐ Catch YES &nbsp; ☐ Close 5 popups (0/5)</p>
<div id="decoys"></div>
<button id="yes" class="abs" style="left:40vw;top:50vh;font-size:26px;background:lime;color:#000;z-index:5">YES</button>
<div id="fin" style="display:none;position:relative;z-index:40;background:#fff;color:#000;padding:14px;border:8px solid red;max-width:520px">
  <h2>FINAL QUESTION: What is 2 + 2?</h2><div id="ch"></div>
</div>

<?php else: // ---------------- CERTIFICATE ---------------- ?>
<div class="cert">
  <h1 style="text-shadow:none">🏅 CERTIFICATE OF SURVIVAL 🏅</h1>
  <p>This certifies that</p>
  <h2 style="font-family:Papyrus,fantasy"><?= htmlspecialchars($_SESSION['name']) ?></h2>
  <p>survived THE WORST WEBSITE EVER and still thinks 2 + 2 = FISH.</p>
  <p><b>Sanity remaining:</b> <?= $sanity ?>%<br><b>Total attempts:</b> <?= $att ?><br>
  <b>Rank:</b> <?= $att < 30 ? 'Suspiciously Good At This' : ($att < 70 ? 'Average Victim' : 'Legendary Disaster') ?></p>
  <p style="font-size:11px">Not valid anywhere. Please do not frame this.</p>
  <button onclick="location='?reset=1'" style="font-size:24px;background:#0f0">PLAY AGAIN</button>
</div>
<?php endif; ?>

<script>
// ---------- shared helpers ----------
const IMGS=<?= json_encode($imgs) ?>, FONTS=<?= json_encode($fonts) ?>;
let att=<?= (int)$att ?>;
const rnd=(a,b)=>Math.floor(Math.random()*(b-a+1))+a;
const pick=a=>a[rnd(0,a.length-1)];
const col=()=>`hsl(${rnd(0,360)},100%,${rnd(45,75)}%)`;
const say=t=>document.getElementById('msg').textContent=t;
function upd(){document.getElementById('att').textContent=att;document.getElementById('san').textContent=Math.max(0,100-att*2)}
function hit(){att++;fetch('?hit=1');upd()}

// popups (used in levels 4 and 5)
let closed=0,open=0,onClose=()=>{};
function pop(text,img){
  const p=document.createElement('div');p.className='pop';
  p.style.cssText=`left:${rnd(2,55)}vw;top:${rnd(8,60)}vh;background:${col()};font-family:${pick(FONTS)};transform:rotate(${rnd(-9,9)}deg)`;
  p.innerHTML=(img?`<img src="${img}" alt="">`:'')+`<p>${text}</p><button class="ok">OK</button><button class="x" style="${pick(['top:0;left:0','bottom:0;right:0','top:0;right:0','bottom:0;left:0'])}">x</button>`;
  p.querySelector('.ok').onclick=()=>{ if(open<10){pop(pick(POPS),imgOrNull());pop(pick(POPS),imgOrNull())} };
  p.querySelector('.x').onclick=()=>{p.remove();open--;closed++;const c=document.getElementById('cl');if(c)c.textContent=closed;onClose()};
  document.body.appendChild(p);open++;
}
const imgOrNull=()=>IMGS.length&&document.body.classList.contains('l5')?pick(IMGS):null;
const POPS=document.body.classList.contains('l5')?
 ["BRO WHAT ARE YOU DOING 😭","YOUR PERFORMANCE HAS BEEN RECORDED.","THE GROUP CHAT WILL HEAR ABOUT THIS.","SKILL ISSUE DETECTED 🚨","This is going in the yearbook.","Screenshot taken. Sent to everyone.","Your mouse has been detected."]:
 ["Your mouse has been detected.","You are currently using a website.","Your screen is on. Congratulations.","Please confirm you are a human (you look unsure).","Your cursor is 3 pixels too far left.","Cookies detected. They're not yours.","Breaking: you clicked something.","Update available for your patience.","You have 0 new notifications. Panic."];
</script>

<?php if ($level === 4): ?>
<script>
onClose=()=>{
  if(closed<8&&(open===0||Math.random()<.6))pop(pick(POPS),null);
  if(closed>=8&&open===0)document.getElementById('done').style.display='block';
};
for(let i=0;i<3;i++)pop(pick(POPS),null);
</script>
<?php elseif ($level === 5): ?>
<script>
// chaos: colors + fonts + fake errors
setInterval(()=>{document.body.style.background=col();document.body.style.fontFamily=pick(FONTS);document.getElementById('h2').style.color=col()},600);
const ERR=["ERROR 404: Sanity not found","FATAL: user is too slow","Virus detected: 'Skill Issue.exe'","Your RAM has left the chat","Windows XP wants to talk to you","Warning: 1 (one) brain cell in use","Disk C: is emotionally unavailable"];
setInterval(()=>{const t=document.createElement('div');t.className='toast';t.textContent='⚠ '+pick(ERR);t.style.left=rnd(0,70)+'vw';t.style.top=rnd(5,90)+'vh';document.body.appendChild(t);setTimeout(()=>t.remove(),2200)},1800);
// random meme popup on a timer
const timer=setInterval(()=>{if(open<4)pop(pick(POPS),imgOrNull())},6000);
let yesCaught=false,ydodge=0,finShown=false;
const yes=document.getElementById('yes');
function move(el){el.style.left=rnd(2,75)+'vw';el.style.top=rnd(20,85)+'vh'}
function run(){if(ydodge>=10||yesCaught)return;ydodge++;hit();move(yes);say('YES dodged you '+ydodge+' times. Bro, it\'s literally right there.');}
yes.addEventListener('mouseenter',run);
yes.addEventListener('touchstart',e=>{if(ydodge<10){e.preventDefault();run()}});
yes.onclick=()=>{if(ydodge<10){run();return}yesCaught=true;yes.remove();say('YES caught. Impressive. Unfortunately.');check()};
// decoy buttons (Level 1) that dodge (Level 2) and spawn meme popups
['START','DON\'T CLICK','CONTINUE','FREE ROBUX','NEXT LEVEL','FISH?'].forEach(l=>{
  const b=document.createElement('button');b.textContent=l;b.className='abs';
  b.style.cssText+=`background:${col()};color:${col()};font:${rnd(12,32)}px ${pick(FONTS)};left:${rnd(2,75)}vw;top:${rnd(20,85)}vh;transform:rotate(${rnd(-15,15)}deg)`;
  b.onmouseenter=()=>{if(Math.random()<.4)move(b)};
  b.onclick=()=>{hit();say('WRONG BUTTON. Everyone has been notified.');pop(pick(POPS),imgOrNull());move(b)};
  document.getElementById('decoys').appendChild(b);
});
onClose=()=>{ if(Math.random()<.3&&!finShown)pop(pick(POPS),imgOrNull()); check() };
function check(){
  document.getElementById('tasks').textContent=(yesCaught?'☑':'☐')+' Catch YES   '+(closed>=5?'☑':'☐')+' Close 5 popups ('+Math.min(closed,5)+'/5)';
  if(yesCaught&&closed>=5&&!finShown){
    finShown=true;clearInterval(timer);
    document.querySelectorAll('.pop').forEach(p=>p.remove());open=0;
    document.getElementById('fin').style.display='block';
    const ch=document.getElementById('ch');
    ['3','4','22','FISH'].sort(()=>Math.random()-.5).forEach(a=>{
      const b=document.createElement('button');b.textContent=a;
      b.style.cssText=`background:${col()};font:${rnd(16,34)}px ${pick(FONTS)};margin:6px`;
      b.onclick=()=>{
        if(a==='FISH'){say('FISH. Correct. 🐟😂');setTimeout(()=>location='?complete=5',700)}
        else{hit();say(a==='4'?"Math says 4. This website says NO.":"Wrong. Think fishier.");pop(pick(POPS),imgOrNull())}
      };ch.appendChild(b);
    });
  }
}
for(let i=0;i<3;i++)pop(pick(POPS),imgOrNull());
</script>
<?php endif; ?>
</body></html>