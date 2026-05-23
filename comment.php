<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

$action = $_POST['action'];
$id = $_POST['id'] ?? null;

// 댓글 작성
if ($action == 'write') {
    $content = $_POST['content'];
    $post_id = $_POST['post_id'];
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

    mysqli_query($conn, "INSERT INTO comments (post_id, author_id, content) VALUES ('$post_id', '$author_id', '$content')");
    header("Location: view.php?id=$post_id");
}

// 댓글 수정
if ($action == 'edit') {
    $content = $_POST['content'];
    $post_id = $_POST['post_id'];
    mysqli_query($conn, "UPDATE comments SET content = '$content' WHERE id = $id");
    header("Location: view.php?id=$post_id");
}

// 댓글 삭제
if ($action == 'delete') {
    $post_id = $_POST['post_id'];
    mysqli_query($conn, "DELETE FROM comments WHERE id = $id");
    header("Location: view.php?id=$post_id");
}

exit;
?>