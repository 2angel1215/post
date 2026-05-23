<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

$id = $_POST['id'];
$check_username = $_POST['check_username'];

// 게시글 작성자 확인
$result = mysqli_query($conn, "SELECT author_id FROM posts WHERE id = $id");
$post = mysqli_fetch_assoc($result);

$user_result = mysqli_query($conn, "SELECT id FROM users WHERE username = '$check_username'");
$user = mysqli_fetch_assoc($user_result);

if ($user && $user['id'] == $post['author_id']) {
    mysqli_query($conn, "DELETE FROM posts WHERE id = $id");
    header("Location: index.php");
} else {
    echo "작성자가 아닙니다.";
}
exit;
?>