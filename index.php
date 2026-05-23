<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

$post = mysqli_query($conn, "SELECT posts.*, users.username FROM posts JOIN users ON posts.author_id = users.id ORDER BY posts.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>게시판</title>
</head>
<body>
    <h1>게시글 목록</h1>
    <a href="logout.php">로그아웃</a>
    <a href="write.php">글 작성</a>

    <table border='1'>
        <tr>
            <th>번호</th>
            <th>제목</th>
            <th>작성자</th>
            <th>날짜</th>
        </tr>
        <?php $num = 1; while ($row = mysqli_fetch_assoc($post)) { ?>
        <tr>
            <td><?= $num++ ?></td>
            <td><a href="view.php?id=<?= $row['id'] ?>"><?= $row['title'] ?></a></td>
            <td><?= $row['username'] ?></td>
            <td><?= $row['created_at'] ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>