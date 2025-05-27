<?php
// admin/queuing.php
session_start();
require_once('../db.php');

// Fetch active ads
$now = date('Y-m-d H:i:s');
$stmt = $conn->prepare("
  SELECT filename
    FROM ads
   WHERE start_time <= ?
     AND end_time   >= ?
   ORDER BY id
");
$stmt->bind_param('ss', $now, $now);
$stmt->execute();
$ads = $stmt->get_result();
$stmt->close();

// Queue enabled?
$stmt = $conn->prepare("SELECT queuing_enabled FROM settings WHERE id=1");
$stmt->execute();
$stmt->bind_result($qe);
$stmt->fetch();
$stmt->close();
$announcement = $qe ? '' : "⚠️ Queuing disabled. Check back later.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Queue Monitor</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="../dist/axios/axios.min.js"></script>
  <style>
    body{background:linear-gradient(to bottom right,#eaf0f5,#f7f9fc);font-family:'Inter',sans-serif;}
    .now-serving{background:linear-gradient(145deg,#e8f5e9,#fff);box-shadow:inset 0 0 10px rgba(40,167,69,0.2);border-left:5px solid #28a745;font-family:'Courier New',monospace;letter-spacing:1px;animation:pulse 1.5s infinite;}
    @keyframes pulse{0%{transform:scale(1);opacity:1}50%{transform:scale(1.1);opacity:0.8}100%{transform:scale(1);opacity:1}}
    .queue-list .list-group-item{font-size:1.1rem;border:none;margin-bottom:.5rem;border-radius:.375rem;box-shadow:0 1px 4px rgba(0,0,0,0.1);}
    .announcement{background:#ffc107;color:#212529;padding:1rem 2rem;border-radius:.5rem;font-weight:600;font-size:1.25rem;margin-bottom:1rem;overflow:hidden;position:relative;box-shadow:0 0 15px rgba(0,0,0,0.1);}
    .announcement-text{position:absolute;white-space:nowrap;animation:slideFromRight 10s linear infinite;}
    @keyframes slideFromRight{0%{transform:translateX(100%)}100%{transform:translateX(-100%)}}
  </style>
</head>
<body class="p-4">
  <audio autoplay loop id="bg-sound">
    <source src="sound/bg.mp3" type="audio/mp3">
  </audio>

  <div class="container-fluid">
    <div class="row g-4">
      <!-- Ads Carousel -->
      <div class="col-md-6">
        <h5 class="text-center mb-3 text-primary">Advertisements</h5>
        <div id="adsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
          <div class="carousel-inner">
            <?php $first=true; $ads->data_seek(0);
            while($r=$ads->fetch_assoc()):
              $f = htmlspecialchars($r['filename']);
              $e = strtolower(pathinfo($f,PATHINFO_EXTENSION));
              $vid = in_array($e,['mp4','webm','ogg']);
            ?>
            <div class="carousel-item <?= $first?'active':'' ?>">
              <?php if($vid): ?>
                <video class="d-block w-100" autoplay muted loop>
                  <source src="<?= $f ?>" type="video/<?= $e ?>">
                </video>
              <?php else: ?>
                <img src="<?= $f ?>" class="d-block w-100">
              <?php endif; ?>
            </div>
            <?php $first=false; endwhile; ?>
          </div>
          <button class="carousel-control-prev" data-bs-target="#adsCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
          </button>
          <button class="carousel-control-next" data-bs-target="#adsCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
          </button>
        </div>
      </div>

      <!-- Queue Monitor -->
      <div class="col-md-6">
        <h5 class="text-center mb-3 text-success">Queue Monitor</h5>
        <?php if($announcement): ?>
          <div class="announcement">
            <span class="announcement-text"><?= htmlspecialchars($announcement) ?></span>
          </div>
        <?php endif; ?>

        <div class="text-center mb-4">
          <div class="text-muted">Now Serving</div>
          <div id="serving-name" class="now-serving display-5 text-success">---</div>
        </div>
        <ul id="queue-ul" class="list-group queue-list">
          <li class="list-group-item text-center text-muted">Loading…</li>
        </ul>
      </div>
    </div>
  </div>

  <script>
    document.getElementById('bg-sound').volume = 0.2;

    function playSound() {
      let a = new Audio('sound/1.mp3');
      a.volume = 0.7;
      a.play();
    }
    function speak(text) {
      let u = new SpeechSynthesisUtterance(text);
      u.lang = 'en-US';
      speechSynthesis.speak(u);
    }

    let lastNo = null;
    function refresh() {
      axios.get('fetch_queue.php')
        .then(({ data }) => {
          const serv = data.serving;
          const name = serv ? `${serv.firstname} ${serv.lastname}` : '---';
          const no   = serv ? serv.studentno : null;

          // update UI
          document.getElementById('serving-name').textContent = name;
          const ul = document.getElementById('queue-ul');
          ul.innerHTML = '';
          (data.queue.length ? data.queue : ['No students waiting.'])
            .forEach(n => {
              let li = document.createElement('li');
              li.className = 'list-group-item';
              li.textContent = n;
              ul.appendChild(li);
            });

          // On new serve
          if (no && no !== lastNo) {
            playSound();
            setTimeout(()=> speak(`Now serving: ${name}`), 2000);
            lastNo = no;
          }
          // On notify
          if (data.notify && no) {
            playSound();
            setTimeout(()=> speak(`Calling the attention of ${name}`), 2000);
          }
        })
        .catch(console.error);
    }
    setInterval(refresh, 5000);
    refresh();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
