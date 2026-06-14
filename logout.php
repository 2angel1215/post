<?php
include 'config.php'; // 세션 시작
$_SESSION = [];
session_destroy();
header("Location: login.php");
exit;
?>