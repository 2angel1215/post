<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    csrf_check();
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $created_at = date("Y-m-d H:i:s");
    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, created_at) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $username, $password, $created_at);
    try {
        mysqli_stmt_execute($stmt);
        header("Location: login.php");
        exit;
    } catch (mysqli_sql_exception $e) {
        // username UNIQUE 제약 위반 등 (이미 존재하는 아이디)
        header("Location: register.php?error=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>회원가입</title>
</head>
<body>
    <h1>회원가입</h1>
    <?php if (isset($_GET['error'])) echo "이미 사용 중인 아이디입니다."; ?>
    <form action="register.php" method="POST">
        <?= csrf_field() ?>
        <input type="text" name="username" placeholder="아이디"><br>
        <input type="password" name="password" placeholder="비밀번호"><br>
        <button type="submit">가입</button>
    </form>
</body>
</html>