<?php
include 'config.php';
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    csrf_check();
    set_time_limit(60); // 업로드 처리 실행 시간 제한 (초)

    // 파일 검증 (형식/크기). 문제가 있으면 메시지 출력 후 중단
    $err = upload_error($_FILES['file'] ?? null);
    if ($err !== '') {
        echo $err;
        exit;
    }

    $has_file = isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK;

    // 업로드 횟수/속도 제한
    if ($has_file && upload_rate_exceeded($conn, $_SESSION['id'])) {
        echo "업로드 횟수 제한을 초과했습니다. 잠시 후 다시 시도해주세요.";
        exit;
    }

    $title = $_POST['title'];
    $content = $_POST['content'];
    $author_id = $_SESSION['id']; // 작성자는 로그인한 사용자

    $stmt = mysqli_prepare($conn, "INSERT INTO posts (title, content, author_id) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $author_id);
    mysqli_stmt_execute($stmt);
    $post_id = mysqli_insert_id($conn);

    // 첨부파일 저장 (선택 사항). 저장 성공 시 횟수 기록
    if ($has_file && save_attachment($conn, $post_id, $_FILES['file'])) {
        record_upload($conn, $_SESSION['id']);
    }

    header("Location: view.php?id=$post_id");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>게시글 작성</title>
</head>
<body>
    <h1>게시글 작성</h1>
    <form action="write.php" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="text" name="title" placeholder="제목"><br>
        <textarea name="content" placeholder="내용"></textarea><br>
        <input type="file" name="file"><br>
        <button type="submit">등록</button>
    </form>
    <a href="index.php">목록</a>
</body>
</html>
