<?php
// admin/delete_account.php
require_once __DIR__ . '/../includes/init.php';

$id = (int)($_GET['id'] ?? 0);
// block superadmin (usually id=1)
if (!$id || $id===1) {
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Cannot delete this account.'];
    header('Location: user_management.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM admin WHERE id=? AND role IN ('admin','staff')");
$stmt->bind_param('i',$id);
$stmt->execute();

$_SESSION['flash'] = ['type'=>'success','msg'=>'Account deleted.'];
header('Location: user_management.php');
