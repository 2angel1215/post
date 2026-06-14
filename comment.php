<?php
include 'config.php';
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
csrf_check();

$action = $_POST['action'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : null;
$me = $_SESSION['id'];

// 댓글의 작성자 id 조회 (수정/삭제 권한 확인용)
function comment_author($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT author_id FROM comments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return $row ? $row['author_id'] : null;
}

// 댓글 작성
if ($action == 'write') {
    $content = $_POST['content'];
    $post_id = (int)$_POST['post_id'];
    $author_id = $me; // 작성자는 로그인 사용자

    $stmt = mysqli_prepare($conn, "INSERT INTO comments (post_id, author_id, content) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iis", $post_id, $author_id, $content);
    mysqli_stmt_execute($stmt);
    header("Location: view.php?id=$post_id");
    exit;
}

// 댓글 수정
if ($action == 'edit') {
    $content = $_POST['content'];
    $post_id = (int)$_POST['post_id'];

    if (comment_author($conn, $id) != $me) {
        echo "작성자가 아닙니다.";
        exit;
    }
    $stmt = mysqli_prepare($conn, "UPDATE comments SET content = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $content, $id);
    mysqli_stmt_execute($stmt);
    header("Location: view.php?id=$post_id");
    exit;
}

// 댓글 삭제
if ($action == 'delete') {
    $post_id = (int)$_POST['post_id'];

    if (comment_author($conn, $id) != $me) {
        echo "작성자가 아닙니다.";
        exit;
    }
    $stmt = mysqli_prepare($conn, "DELETE FROM comments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: view.php?id=$post_id");
    exit;
}

exit;
?>
