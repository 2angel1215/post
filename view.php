<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

$id = (int)$_GET['id'];
$me = $_SESSION['id'];

// 게시글 조회 (작성자 이름 JOIN)
$stmt = mysqli_prepare($conn, "SELECT posts.*, users.username FROM posts JOIN users ON posts.author_id = users.id WHERE posts.id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$post) {
    echo "존재하지 않는 게시글입니다.";
    exit;
}

// 첨부파일 목록
$stmt = mysqli_prepare($conn, "SELECT * FROM attachments WHERE post_id = ? ORDER BY id ASC");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$attachments = mysqli_stmt_get_result($stmt);

// 댓글 목록 조회
$stmt = mysqli_prepare($conn, "SELECT comments.*, users.username FROM comments JOIN users ON comments.author_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at ASC");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$comments = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($post['title']) ?></title>
</head>
<body>
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <p>작성자: <?= htmlspecialchars($post['username']) ?> | 날짜: <?= $post['created_at'] ?></p>
    <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

    <!-- 첨부파일 -->
    <?php if (mysqli_num_rows($attachments) > 0) { ?>
    <h3>첨부파일</h3>
    <ul>
        <?php while ($file = mysqli_fetch_assoc($attachments)) { ?>
        <li>
            <a href="download.php?id=<?= $file['id'] ?>"><?= htmlspecialchars($file['original_name']) ?></a>
            (<?= number_format((int)$file['size_bytes']) ?> bytes)
        </li>
        <?php } ?>
    </ul>
    <?php } ?>

    <!-- 수정/삭제 버튼: 작성자 본인에게만 노출 -->
    <?php if ($me == $post['author_id']) { ?>
    <a href="edit.php?id=<?= $post['id'] ?>">수정</a>
    <form action="delete.php" method="POST" style="display:inline" onsubmit="return confirm('삭제하시겠습니까?');">
        <input type="hidden" name="id" value="<?= $post['id'] ?>">
        <button type="submit">삭제</button>
    </form>
    <?php } ?>

    <a href="index.php">목록</a>

    <hr>

    <h2>댓글</h2>
    <?php while ($row = mysqli_fetch_assoc($comments)) { ?>
    <p>
        <b><?= htmlspecialchars($row['username']) ?></b> : <?= htmlspecialchars($row['content']) ?>

        <!-- 댓글 수정/삭제: 작성자 본인에게만 노출 -->
        <?php if ($me == $row['author_id']) { ?>
        <a href="comment_edit.php?id=<?= $row['id'] ?>&post_id=<?= $id ?>">수정</a>
        <form action="comment.php" method="POST" style="display:inline" onsubmit="return confirm('삭제하시겠습니까?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <input type="hidden" name="post_id" value="<?= $id ?>">
            <button type="submit">삭제</button>
        </form>
        <?php } ?>
    </p>
    <?php } ?>

    <!-- 댓글 작성 폼 (작성자는 로그인 사용자) -->
    <form action="comment.php" method="POST">
        <input type="hidden" name="action" value="write">
        <input type="hidden" name="post_id" value="<?= $id ?>">
        <textarea name="content" placeholder="댓글 내용"></textarea>
        <button type="submit">댓글 등록</button>
    </form>
</body>
</html>
