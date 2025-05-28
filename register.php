<?php
global $conn;
session_start();
require_once __DIR__ . '/db.php';

// 1) Check queuing_enabled
$res = $conn->query("SELECT queuing_enabled FROM settings WHERE id=1");
if (!$res || $res->fetch_assoc()['queuing_enabled']==0) {
    echo '<p>Registration closed.</p>';
    exit();
}

// 2) CSRF validation
if ($_SERVER['REQUEST_METHOD']!=='POST'
    || !isset($_POST['csrf_token'])
    || $_POST['csrf_token'] !== $_SESSION['csrf_token']
) {
    http_response_code(400);
    echo 'Invalid request.';
    exit();
}

// 3) Gather + validate required
$fields = ['studentno','lastname','firstname','dob','email','gender','phone','course','yearlevel','block','address','contactname','contactno','relationship'];
foreach ($fields as $f) {
    if (empty($_POST[$f])) {
        echo "<script>alert('Missing $f');window.location='new_student.php';</script>";
        exit();
    }
}
extract(array_map('trim', $_POST));

// 4) Duplicate studentno?
$stmt = $conn->prepare("SELECT 1 FROM student_registration WHERE studentno=?");
$stmt->bind_param('s',$studentno);
$stmt->execute();
if ($stmt->get_result()->num_rows) {
    echo "<script>alert('Student No exists');window.location='new_student.php';</script>";
    exit();
}

// 5) Insert
$sql = "INSERT INTO student_registration
  (studentno,lastname,firstname,middleinitial,dob,email,gender,phone,course,yearlevel,block,address,contactname,contactno,relationship)
 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'sssssssssssssss',
    $studentno,$lastname,$firstname,$middleinitial,$dob,
    $email,$gender,$phone,$course,$yearlevel,
    $block,$address,$contactname,$contactno,$relationship
);
if (!$stmt->execute()) {
    echo "DB error: " . $stmt->error;
    exit();
}

// 6) Success tooltip + redirect
?>
<!DOCTYPE html><html><head>
    <style>
        @keyframes fadeIn { from{opacity:0} to{opacity:1} }
        #successTooltip {
            position:fixed;top:50%;left:50%;
            transform:translate(-50%,-50%);
            background:#fff;padding:30px;border-radius:12px;
            box-shadow:0 8px 25px rgba(0,0,0,.15);
            animation:fadeIn .5s ease-out forwards;
            text-align:center;
        }
        #successTooltip .icon { font-size:36px;color:#4CAF50;margin-bottom:15px; }
        #successTooltip h2 { margin:0 0 10px;color:#2E7D32; }
    </style>
</head><body>
<div id="successTooltip">
    <div class="icon">✅</div>
    <h2>Registration Successful</h2>
    <p>Please wait for your name to be called.<br>Thank you!</p>
</div>
<script>
    setTimeout(()=>window.location='index.html', 5000);
</script>
</body></html>
