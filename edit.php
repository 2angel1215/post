<?php
include 'config.php';

$id = $_GET['id'] ?? $_POST['id'];
$check_username = $_GET['check_username'] ?? $_POST['check_username'];

// 작성자 확인
$result = mysqli_query($conn, "SELECT posts.*, users.username FROM posts JOIN users ON posts.author_id = users.id WHERE posts.id = $id");
$post = mysqli_fetch_assoc($result);

if ($post['username'] !== $check_username) {
    echo "작성자가 아닙니다.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    mysqli_query($conn, "UPDATE posts SET title = '$title', content = '$content' WHERE id = $id");
    header("Location: view.php?id=$id");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>게시글 수정</title>
</head>
<body>
    <h1>게시글 수정</h1>
    <form action="edit.php" method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="check_username" value="<?= $check_username ?>">
        <input type="text" name="title" value="<?= $post['title'] ?>"><br>
        <textarea name="content"><?= $post['content'] ?></textarea><br>
        <button type="submit">등록</button>
    </form>
</body>
</html>