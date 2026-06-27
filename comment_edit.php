<?php
include 'config.php';
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

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
    csrf_check();
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
    <form id="editform" action="comment_edit.php" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="post_id" value="<?= $post_id ?>">
        <textarea name="content"><?= htmlspecialchars($comment['content']) ?></textarea>
    </form>
    <!-- 수정 / 취소 버튼 한 줄 배치 (수정은 form 속성으로 위 폼을 제출) -->
    <table>
        <tr>
            <td>
                <button type="submit" form="editform">수정</button>
            </td>
            <td>
                <form action="view.php" method="GET">
                    <input type="hidden" name="id" value="<?= $post_id ?>">
                    <button type="submit">취소</button>
                </form>
            </td>
        </tr>
    </table>
</body>
</html>
