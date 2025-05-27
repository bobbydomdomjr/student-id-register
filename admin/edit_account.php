<?php
// admin/edit_account.php
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD']==='GET' && isset($_GET['id'])) {
    // fetch JSON for modal
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT id,username,role FROM admin WHERE id=? AND role IN ('admin','staff')");
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc() ?: [];
    header('Content-Type: application/json');
    echo json_encode($u);
    exit;
}

// otherwise handle POST update
$id       = (int)$_POST['id'];
$username = trim($_POST['username']);
$password = $_POST['password'];
$role     = $_POST['role'];

if (!$id || !$username || !in_array($role,['admin','staff'])) {
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Invalid update.'];
    header('Location: user_management.php');
    exit;
}

// prevent renaming superadmin or editing others?
// e.g. if($id===1) { … }

// update username+role
$stmt = $conn->prepare("UPDATE admin SET username=?, role=? WHERE id=?");
$stmt->bind_param('ssi',$username,$role,$id);
$stmt->execute();

// update password if given
if ($password!=='') {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE admin SET password=? WHERE id=?");
    $stmt->bind_param('si',$hash,$id);
    $stmt->execute();
}

$_SESSION['flash'] = ['type'=>'success','msg'=>'Account updated.'];
header('Location: user_management.php');
