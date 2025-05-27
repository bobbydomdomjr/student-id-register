<?php
// admin/fetch_queue.php
global $conn;
session_start();
require_once('../db.php');
header('Content-Type: application/json');

$out = [
  'serving' => null,
  'queue'   => [],
  'notify'  => false,
];

// 1) Now Serving
$stmt = $conn->prepare("
  SELECT studentno, firstname, lastname
    FROM student_registration
   WHERE now_serving=1
   LIMIT 1
");
$stmt->execute();
$serv = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($serv) {
  $out['serving'] = $serv;

  // 2) Check notified flag
  $chk = $conn->prepare("
    SELECT notified 
      FROM student_registration
     WHERE studentno=?
  ");
  $chk->bind_param('s', $serv['studentno']);
  $chk->execute();
  $chk->bind_result($notified);
  $chk->fetch();
  $chk->close();

  if ($notified) {
    $out['notify'] = true;
    // reset
    $upd = $conn->prepare("
      UPDATE student_registration
         SET notified=0
       WHERE studentno=?
    ");
    $upd->bind_param('s', $serv['studentno']);
    $upd->execute();
    $upd->close();
  }
}

// 3) Build waiting list: first processing (not serving), then pending
// a) processing
$q1 = $conn->prepare("
  SELECT firstname, lastname
    FROM student_registration
   WHERE status='processing' AND now_serving=0
   ORDER BY updated_at ASC
");
$q1->execute();
$res1 = $q1->get_result();
while ($r = $res1->fetch_assoc()) {
  $out['queue'][] = $r['firstname'].' '.$r['lastname'];
}
$q1->close();

// b) then pending
$q2 = $conn->prepare("
  SELECT firstname, lastname
    FROM student_registration
   WHERE status='pending'
   ORDER BY registration_date ASC
");
$q2->execute();
$res2 = $q2->get_result();
while ($r = $res2->fetch_assoc()) {
  $out['queue'][] = $r['firstname'].' '.$r['lastname'];
}
$q2->close();

echo json_encode($out);
