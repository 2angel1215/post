<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $created_at = date("Y-m-d H:i:s");
    mysqli_query($conn, "INSERT INTO users (username, password, created_at) VALUES ('$username', '$password', '$created_at')");
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>회원가입</title>
</head>
<body>
    <h1>회원가입</h1>
    <form action="register.php" method="POST">
        <input type="text" name="username" placeholder="아이디"><br>
        <input type="password" name="password" placeholder="비밀번호"><br>
        <button type="submit">가입</button>
    </form>
</body>
</html>