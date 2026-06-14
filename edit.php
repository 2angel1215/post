<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

$id = (int)($_GET['id'] ?? $_POST['id']);
$me = $_SESSION['id'];

// 게시글 조회
$stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$post) {
    echo "존재하지 않는 게시글입니다.";
    exit;
}

// 권한 확인: 작성자 본인만
if ($me != $post['author_id']) {
    echo "작성자가 아닙니다.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    set_time_limit(60); // 업로드 처리 실행 시간 제한 (초)

    // 파일 검증 (형식/크기). 문제가 있으면 메시지 출력 후 중단
    $err = upload_error($_FILES['file'] ?? null);
    if ($err !== '') {
        echo $err;
        exit;
    }

    $has_file = isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK;

    // 업로드 횟수/속도 제한
    if ($has_file && upload_rate_exceeded($conn, $me)) {
        echo "업로드 횟수 제한을 초과했습니다. 잠시 후 다시 시도해주세요.";
        exit;
    }

    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = mysqli_prepare($conn, "UPDATE posts SET title = ?, content = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $id);
    mysqli_stmt_execute($stmt);

    // 선택한 기존 첨부파일 삭제
    if (!empty($_POST['delete_attachments'])) {
        foreach ($_POST['delete_attachments'] as $att_id) {
            delete_attachment($conn, (int)$att_id, $id);
        }
    }

    // 새 첨부파일 추가 (선택 사항). 저장 성공 시 횟수 기록
    if ($has_file && save_attachment($conn, $id, $_FILES['file'])) {
        record_upload($conn, $me);
    }

    header("Location: view.php?id=$id");
    exit;
}

// 현재 첨부파일 목록
$stmt = mysqli_prepare($conn, "SELECT * FROM attachments WHERE post_id = ? ORDER BY id ASC");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$attachments = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>게시글 수정</title>
</head>
<body>
    <h1>게시글 수정</h1>
    <form action="edit.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>"><br>
        <textarea name="content"><?= htmlspecialchars($post['content']) ?></textarea><br>

        <!-- 기존 첨부파일: 체크 시 삭제 -->
        <?php if (mysqli_num_rows($attachments) > 0) { ?>
        <p>기존 첨부파일 (삭제하려면 체크):</p>
        <ul>
            <?php while ($file = mysqli_fetch_assoc($attachments)) { ?>
            <li>
                <label>
                    <input type="checkbox" name="delete_attachments[]" value="<?= $file['id'] ?>">
                    <?= htmlspecialchars($file['original_name']) ?>
                </label>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>

        <!-- 새 파일 추가 -->
        <p>새 파일 추가: <input type="file" name="file"></p>

        <button type="submit">등록</button>
    </form>
    <a href="view.php?id=<?= $id ?>">취소</a>
</body>
</html>
