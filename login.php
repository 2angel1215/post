<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    csrf_check();
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true); // 로그인 시 세션 ID 재발급 (세션 고정 방지)
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
    <table>
        <tr>
            <td><h1>로그인</h1></td>
            <td>
                <form action="register.php" method="GET">
                    <button type="submit">회원가입</button>
                </form>
            </td>
        </tr>
    </table>
    <?php if (isset($_GET['error'])) echo "로그인 실패"; ?>
    <form action="login.php" method="POST">
        <?= csrf_field() ?>
        <table>
            <tr>
                <td>
                    <input type="text" name="username" placeholder="아이디"><br>
                    <input type="password" name="password" placeholder="비밀번호">
                </td>
                <td>
                    <button type="submit">로그인</button>
                </td>
            </tr>
        </table>
    </form>
</body>
</html>