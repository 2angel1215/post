<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $username = $_POST['username'];

    // users 테이블에서 username 조회
    $user_result = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($user_result) == 0) {
        // 없으면 자동 생성
        mysqli_query($conn, "INSERT INTO users (username, password) VALUES ('$username', '')");
        $author_id = mysqli_insert_id($conn);
    } else {
        $user = mysqli_fetch_assoc($user_result);
        $author_id = $user['id'];
    }

    mysqli_query($conn, "INSERT INTO posts (title, content, author_id) VALUES ('$title', '$content', '$author_id')");
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>게시글 작성</title>
</head>
<body>
    <h1>게시글 작성</h1>
    <form action="write.php" method="POST">
        <input type="text" name="username" placeholder="작성자 이름"><br>
        <input type="text" name="title" placeholder="제목"><br>
        <textarea name="content" placeholder="내용"></textarea><br>
        <input type="file" name="file">
        <button type="submit">등록</button>
    </form>
</body>
</html>