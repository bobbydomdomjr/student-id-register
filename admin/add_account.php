<?php
// admin/add_account.php
require_once __DIR__ . '/../includes/init.php';

$username = trim($_POST['username']);
$password = $_POST['password'];
$role     = $_POST['role'];

if (!$username || !$password || !in_array($role, ['admin','staff'])) {
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Invalid input.'];
    header('Location: user_management.php');
    exit;
}

// ensure unique
$stmt = $conn->prepare("SELECT id FROM admin WHERE username=?");
$stmt->bind_param('s',$username);
$stmt->execute();
if($stmt->get_result()->num_rows){
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Username already exists.'];
    header('Location: user_management.php');
    exit;
}

// insert
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO admin (username,password,role) VALUES (?,?,?)");
$stmt->bind_param('sss',$username,$hash,$role);
$stmt->execute();

$_SESSION['flash'] = ['type'=>'success','msg'=>'Account created.'];
header('Location: user_management.php');
