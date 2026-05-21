<?php
include 'config.php';

$id = $_GET['id'] ?? $_POST['id'];
$post_id = $_GET['post_id'] ?? $_POST['post_id'];
$check_username = $_GET['check_username'] ?? $_POST['check_username'];

// 작성자 확인
$result = mysqli_query($conn, "SELECT comments.*, users.username FROM comments JOIN users ON comments.author_id = users.id WHERE comments.id = $id");
$comment = mysqli_fetch_assoc($result);

if ($comment['username'] !== $check_username) {
    echo "작성자가 아닙니다.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = $_POST['content'];
    mysqli_query($conn, "UPDATE comments SET content = '$content' WHERE id = $id");
    header("Location: view.php?id=$post_id");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>댓글 수정</title>
</head>
<body>
    <h1>댓글 수정</h1>
    <form action="comment_edit.php" method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="post_id" value="<?= $post_id ?>">
        <input type="hidden" name="check_username" value="<?= $check_username ?>">
        <textarea name="content"><?= $comment['content'] ?></textarea><br>
        <button type="submit">수정</button>
    </form>
</body>
</html>