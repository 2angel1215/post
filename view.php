<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

$id = $_GET['id'];

// 게시글 조회 (작성자 이름 JOIN)
$result = mysqli_query($conn, "SELECT posts.*, users.username FROM posts JOIN users ON posts.author_id = users.id WHERE posts.id = $id");
$post = mysqli_fetch_assoc($result);

// 댓글 목록 조회
$comments = mysqli_query($conn, "SELECT comments.*, users.username FROM comments JOIN users ON comments.author_id = users.id WHERE comments.post_id = $id ORDER BY created_at ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $post['title'] ?></title>
</head>
<body>
    <h1><?= $post['title'] ?></h1>
    <p>작성자: <?= $post['username'] ?> | 날짜: <?= $post['created_at'] ?></p>
    <p><?= $post['content'] ?></p>

    <!-- 수정 버튼 -->
    <form action="edit.php" method="GET" id="edit-form">
        <input type="hidden" name="id" value="<?= $post['id'] ?>">
        <input type="hidden" name="check_username" id="edit-username">
        <button type="button" onclick="
            var name = prompt('작성자 이름을 입력하세요');
            if (name) {
                document.getElementById('edit-username').value = name;
                document.getElementById('edit-form').submit();
            }
        ">수정</button>
    </form>

    <!-- 삭제 버튼 -->
    <form action="delete.php" method="POST" id="delete-form">
        <input type="hidden" name="id" value="<?= $post['id'] ?>">
        <input type="hidden" name="check_username" id="delete-username">
        <button type="button" onclick="
            var name = prompt('작성자 이름을 입력하세요');
            if (name) {
                document.getElementById('delete-username').value = name;
                document.getElementById('delete-form').submit();
            }
        ">삭제</button>
    </form>

    <a href="index.php">목록</a>

    <hr>

    <h2>댓글</h2>
    <?php while ($row = mysqli_fetch_assoc($comments)) { ?>
    <p><?= $row['username'] ?> : <?= $row['content'] ?></p>

    <!-- 댓글 삭제 -->
    <form action="comment.php" method="POST">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <input type="hidden" name="post_id" value="<?= $id ?>">
        <button type="submit">삭제</button>
    </form>

   <!-- 댓글 수정 -->
    <form action="comment_edit.php" method="GET" id="comment-edit-form-<?= $row['id'] ?>">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <input type="hidden" name="post_id" value="<?= $id ?>">
        <input type="hidden" name="check_username" id="comment-edit-username-<?= $row['id'] ?>">
        <button type="button" onclick="
            var name = prompt('작성자 이름을 입력하세요');
            if (name) {
                document.getElementById('comment-edit-username-<?= $row['id'] ?>').value = name;
                document.getElementById('comment-edit-form-<?= $row['id'] ?>').submit();
            }
        ">수정</button>
    </form>

    <?php } ?>

    <!-- 댓글 작성 폼 -->
    <form action="comment.php" method="POST">
        <input type="hidden" name="action" value="write">
        <input type="hidden" name="post_id" value="<?= $id ?>">
        <input type="text" name="username" placeholder="작성자 이름">
        <textarea name="content" placeholder="댓글 내용"></textarea>
        <button type="submit">댓글 등록</button>
    </form>
</body>
</html>