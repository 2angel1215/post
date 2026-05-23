<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    $user = mysqli_fetch_assoc($result);
    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        header("Location: login.php?error=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>로그인</title>
</head>
<body>
    <h1>로그인</h1>
    <?php if (isset($_GET['error'])) echo "로그인 실패"; ?>
    <form action="login.php" method="POST">
        <input type="text" name="username" placeholder="아이디"><br>
        <input type="password" name="password" placeholder="비밀번호"><br>
        <button type="submit">로그인</button>
    </form>
    <a href="register.php">회원가입</a>
</body>
</html>