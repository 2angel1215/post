<?php
include 'config.php';
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
csrf_check();

$id = (int)$_POST['id'];
$me = $_SESSION['id'];

// 게시글 작성자 확인
$stmt = mysqli_prepare($conn, "SELECT author_id FROM posts WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($post && $me == $post['author_id']) {
    // 첨부파일(디스크 + DB) 먼저 정리
    delete_post_attachments($conn, $id);
    // 댓글 정리
    $stmt = mysqli_prepare($conn, "DELETE FROM comments WHERE post_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    // 게시글 삭제
    $stmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: index.php");
} else {
    echo "작성자가 아닙니다.";
}
exit;
?>
