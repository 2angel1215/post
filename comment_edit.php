<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

$id = (int)($_GET['id'] ?? $_POST['id']);
$post_id = (int)($_GET['post_id'] ?? $_POST['post_id']);
$me = $_SESSION['id'];

// 댓글 조회 및 권한 확인
$stmt = mysqli_prepare($conn, "SELECT * FROM comments WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$comment = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$comment) {
    echo "존재하지 않는 댓글입니다.";
    exit;
}
if ($me != $comment['author_id']) {
    echo "작성자가 아닙니다.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = $_POST['content'];
    $stmt = mysqli_prepare($conn, "UPDATE comments SET content = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $content, $id);
    mysqli_stmt_execute($stmt);
    header("Location: view.php?id=$post_id");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>댓글 수정</title>
</head>
<body>
    <h1>댓글 수정</h1>
    <form action="comment_edit.php" method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="post_id" value="<?= $post_id ?>">
        <textarea name="content"><?= htmlspecialchars($comment['content']) ?></textarea><br>
        <button type="submit">수정</button>
    </form>
    <a href="view.php?id=<?= $post_id ?>">취소</a>
</body>
</html>
