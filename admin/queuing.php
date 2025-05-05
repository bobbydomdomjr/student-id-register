<?php
session_start();

include('../db.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get current time
$now = date("Y-m-d H:i:s");

// Fetch active ads
$ads = mysqli_query($conn, "SELECT filename FROM ads WHERE start_time <= '$now' AND end_time >= '$now'");

// Fetch students currently in the queue
$queue = mysqli_query($conn, "SELECT firstname, lastname FROM student_registration WHERE status = 'Waiting'");

// Check if the query was successful
if (!$queue) {
    echo "Error fetching queue: " . mysqli_error($conn);
    exit();
}

// Update the student's status when called
if (isset($_POST['call_student'])) {
    $studentId = $_POST['student_id'];

    // Update the status of the student to 'Processing'
    $query = "UPDATE student_registration SET status = 'Processing' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
}

// Fetch the current serving student
$serving = mysqli_query($conn, "SELECT firstname, lastname FROM student_registration WHERE status = 'Processing' LIMIT 1");
$servingStudent = mysqli_fetch_assoc($serving);

// Check if the queuing system is enabled or disabled
$result = $conn->query("SELECT * FROM settings WHERE id = 1");
$config = $result->fetch_assoc();

// Check if the queuing system is disabled
if ($config['queuing_enabled'] == 0) {
    $announcement = "⚠️ Queuing System is currently disabled for 'cut-off' students. Please check back later. ⚠️";
} else {
    $announcement = "";  // No announcement when queuing system is enabled
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Queue Monitor</title>
    <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<!-- Bootstrap JS (for the carousel functionality) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(to bottom right, #eaf0f5, #f7f9fc);
            color: #333;
    font-family: 'Inter', sans-serif;
}
.ads-box {
    background-color: #f8f9fa; /* light gray background */
    padding: 20px;
    border-radius: 10px;
}
.carousel-indicators button {
    background-color: #007bff; /* Blue color */
    border-radius: 50%;
    width: 12px;
    height: 12px;
    transition: background-color 0.3s ease;
}

.carousel-indicators .active {
    background-color: #0056b3; /* Darker blue when active */
}

.carousel-indicators button:hover {
    background-color: #0056b3; /* Darker blue on hover */
}


.queue-box {
    background: #f8f9fa;
    transition: all 0.3s ease-in-out;
}

.queue-list-container {
    max-height: 300px;
    overflow-y: auto;
}

#serving-name {
    font-family: 'Courier New', monospace;
    letter-spacing: 1px;
}

.now-serving {
    background: linear-gradient(145deg, #e8f5e9, #ffffff);
    box-shadow: inset 0 0 10px rgba(40, 167, 69, 0.2);
    border-left: 5px solid #28a745;
}

.queue-list-container {
    margin-top: 20px;
}

.queue-list {
    list-style-type: none;
    padding-left: 0;
    font-size: 1.2rem;
    margin-top: 10px;
}

.queue-list li {
    padding: 8px;
    background-color: #f8f9fa;
    margin-bottom: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.queue-list li:nth-child(even) {
    background-color: #e9ecef;
}

.queue-list li:hover {
    background-color: #d4d6d9;
    cursor: pointer;
}

.queue-list li:empty {
    color: #aaa;
    font-style: italic;
}

.announcement {
    background: #ffc107;
    color: #212529;
    padding: 20px 40px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1.25rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}

.announcement-text {
    display: inline-block;
    white-space: nowrap;
    position: absolute;
    top: 30%; /* Vertically center */
    left: 50%; /* Horizontally center */
    transform: translate(-50%, -50%); /* Adjust the text to be perfectly centered */
    left: 50%;
    transform: translateX(-50%);
    animation: slideFromRight 10s linear infinite;
}

@keyframes slideFromRight {
    0% {
        transform: translateX(100%);
    }
    100% {
        transform: translateX(-100%);
    }
}

/* Responsive Styles */
@media (max-width: 767px) {
    .now-serving {
        font-size: 3rem; /* Reduce font size on smaller screens */
    }

    .queue-list li {
        font-size: 1rem; /* Reduce font size for queue list items */
    }

    .ads-box, .queue-box {
        margin-bottom: 15px; /* Reduce space between sections */
    }
}
#serving-name {
    font-family: 'Courier New', monospace;
    letter-spacing: 1px;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}
.scroll-wrapper {
    height: auto; /* Adjust height based on how many items to show */
    overflow: hidden;
    position: relative;
}

.queue-list {
    display: flex;
    flex-direction: column;
    animation: scroll-up 10s linear infinite;
}

@keyframes scroll-up {
    0% {
        transform: translateY(0);
    }
    100% {
        transform: translateY(-100%);
    }
}

  </style>
</head>
<body class="p-4"><div class="d-flex justify-content-between align-items-center mb-4">

  
    <div class="text-end">
        <span id="clock" class="fs-5 text-dark fw-semibold px-3 py-2 bg-light border rounded shadow-sm"></span>
    </div>


</div>
<div class="container-fluid">
    <div class="row g-4">

<!-- Advertisement Section -->
<div class="col-md-6">
    <div id="adsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
    <h5 class="text-center mb-4" style="font-weight: bold; color: #007bff;">
    <i class="bi bi-bullseye me-2"></i>Advertisements
</h5>

        <!-- Carousel Wrapper with a stylish box and border -->
        <div class="carousel-inner border rounded shadow-lg overflow-hidden">
            <?php 
            $firstAd = true; // Flag to highlight the first item as active
            while ($ad = mysqli_fetch_assoc($ads)): 
                $filename = $ad['filename'];
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $isVideo = in_array($extension, ['mp4', 'webm', 'ogg']);
            ?>

            <div class="carousel-item <?= $firstAd ? 'active' : '' ?>">
                <?php if ($isVideo): ?>
                    <video class="d-block w-100" autoplay muted loop id="video_<?= $firstAd ? 'first' : '' ?>" data-ad-index="<?= $firstAd ? 0 : 1 ?>" onended="handleVideoEnd(this)">
                        <source src="<?= $filename ?>" type="video/<?= $extension ?>">
                        Your browser does not support the video tag.
                    </video>
                <?php else: ?>
                    <img src="<?= $filename ?>" alt="Ad" class="d-block w-100">
                <?php endif; ?>
            </div>

            <?php $firstAd = false; endwhile; ?>
        </div>

        <!-- Carousel Indicators -->
        <div class="carousel-indicators">
            <?php 
            $i = 0;
            mysqli_data_seek($ads, 0); // Reset the pointer to the start of the ads array
            while ($ad = mysqli_fetch_assoc($ads)): 
            ?>
                <button type="button" data-bs-target="#adsCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i == 0 ? 'active' : '' ?>" aria-current="<?= $i == 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
            <?php 
            $i++;
            endwhile;
            ?>
        </div>
    </div>
</div>
<script>
    var isVideoPlaying = false;

    function handleVideoEnd(video) {
        // Mark video as finished
        isVideoPlaying = false;
        // Trigger the carousel to move to the next slide after the video finishes
        $('#adsCarousel').carousel('next');
    }

    // Listen for carousel slide events
    $('#adsCarousel').on('slide.bs.carousel', function (e) {
        // If the next slide contains a video, pause the carousel transition until the video finishes
        var nextSlide = $(e.relatedTarget);
        var video = nextSlide.find('video')[0];
        if (video && !isVideoPlaying) {
            // Prevent carousel from moving until the video finishes
            isVideoPlaying = true;
            video.play();
            e.preventDefault(); // Prevent default slide transition
        }
    });
</script>




    <!-- Infinite Background Sound with Volume Control -->
    <audio autoplay loop id="background-sound">
        <source src="/stu_reg/admin/sound/bg.mp3" type="audio/mp3">
        Your browser does not support the audio tag.
    </audio>

    <script>
        // Set the volume of the background audio to 75%
        document.getElementById('background-sound').volume = 0.75;

        const audio = document.getElementById('background-sound');

    // Set volume (optional)
    audio.volume = 0.2; // Volume from 0.0 to 1.0

    // Load the saved time from localStorage
    const savedTime = localStorage.getItem('bgAudioTime');
    if (savedTime) {
        audio.currentTime = parseFloat(savedTime);
    }

    // Continuously update the time every second
    setInterval(() => {
        localStorage.setItem('bgAudioTime', audio.currentTime);
    }, 1000);
    </script>


<!-- Queue and Now Serving Section -->
<div class="col-md-6">
    <div class="queue-box shadow p-4 rounded-4 bg-white">
    <h4 class="text-center fw-bold mb-4 text-primary">
    <i class="bi bi-person-lines-fill me-2"></i>🎯 Queue Monitor
</h4>
        
        <div class="now-serving text-center mb-4 p-3 rounded-3 bg-light border border-success">
            <h5 class="text-muted mb-2">Now Serving</h5>
            <h2 class="text-success fw-bold display-5" id="serving-name">
                <?= isset($servingStudent['firstname'], $servingStudent['lastname']) 
                    ? $servingStudent['firstname'] . ' ' . $servingStudent['lastname'] 
                    : '---' ?>
            </h2>
        </div>

        <div class="queue-list-container">
    <h6 class="fw-semibold text-secondary mb-3">🕐 In Queue:</h6>
    <div class="scroll-wrapper">
        <ul class="queue-list list-group">
            <?php
            $queueCount = mysqli_num_rows($queue);
            if ($queueCount > 0) {
                while ($student = mysqli_fetch_assoc($queue)) {
                    echo "<li class='list-group-item list-group-item-light fw-semibold'>" 
                        . $student['firstname'] . " " . $student['lastname'] 
                        . "</li>";
                }
            } else {
                echo "<li class='list-group-item text-muted'>No students in the queue.</li>";
            }
            ?>
        </ul>
    </div>
</div>

    </div>
</div>


<div class="container">
<?php if ($announcement): ?>
    <div class="announcement">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <span class="announcement-text"><?php echo $announcement; ?></span>
</div>
<?php endif; ?>

 </div>
<script>
// Function to play a sound before announcing
function playSound() {
    const audio = new Audio('/stu_reg/admin/sound/1.mp3');  // Relative path from the root
    audio.play();
}

// Function to call the student name via speech synthesis
function speak(text) {
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'en-US'; // Set language to English
    speechSynthesis.speak(utterance);
}

// Announce the serving student
const servingStudentName = document.getElementById('serving-name').innerText;
if (servingStudentName !== '---') {
    playSound();  // Play the sound first

    // Wait until sound plays (about 1 second) then announce
    setTimeout(() => {
        speak(`Now serving: ${servingStudentName}`);
    }, 2000);  // 2000ms delay to let the sound play first
}


// Function to announce new student in the queue
const queueList = document.getElementById('queue-list');
const queueItems = queueList.getElementsByTagName('li');
if (queueItems.length > 0) {
    const nextStudent = queueItems[0].innerText;
    speak(`Next student in the queue is ${nextStudent}`);
}

// To call the student when the "Now Serving" changes
function updateServingStudent(name) {
    document.getElementById('serving-name').innerText = name;
    playSound('file:///C:/xampp/htdocs/stu_reg/admin/sound/sound.mp3');
 // Add the sound file path
    speak(`Now serving: ${name}`);
}

// Example to call a student dynamically
// Example: updateServingStudent('John Doe'); add a sound first before calling
</script>
<script>
// Refresh the page every 30 seconds (30000 milliseconds)
setInterval(function() {
    location.reload();
}, 50000);  // 50000 ms = 30 seconds
</script>
<script>
    setInterval(() => {
        // Refresh "Now Serving"
        $('#serving-name').load('fetch_serving.php');

        // Refresh Queue List
        $('#queue-list').load('fetch_queue.php');
    }, 5000); // every 5 seconds
</script>
<script>
function updateClock() {
    const now = new Date();

    // Format time to 12-hour
    let hours = now.getHours();
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const seconds = now.getSeconds().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';

    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'

    const time = `${hours}:${minutes}:${seconds} ${ampm}`;

    // Format date (e.g., Saturday, Apr 12, 2025)
    const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' };
    const date = now.toLocaleDateString('en-US', options);

    document.getElementById('clock').textContent = `${date} | ${time}`;
}

setInterval(updateClock, 1000);
updateClock(); // initialize immediately
</script>

</div>
</div>
</div>
</body>
</html>
