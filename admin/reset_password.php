<?php
// admin/reset_password.php
require_once __DIR__ . '/../includes/init.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id || $id===1) {
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Cannot reset this account.'];
    header('Location: user_management.php');
    exit;
}

// fetch role
$stmt = $conn->prepare("SELECT role FROM admin WHERE id=?");
$stmt->bind_param('i',$id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
if (!$r) {
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Account not found.'];
    header('Location: user_management.php');
    exit;
}

$newPass = $r['role'] . '123';
$hash    = password_hash($newPass, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE admin SET password=? WHERE id=?");
$stmt->bind_param('si',$hash,$id);
$stmt->execute();

$_SESSION['flash'] = ['type'=>'success','msg'=>"Password reset to “{$newPass}”"];
header('Location: user_management.php');
