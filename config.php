<?php

$conn = mysqli_connect("localhost","root","","K_KNOCK");

if(!$conn) {
    die("db 연결 실패".mysqli_connect_error());
}

// 한글 깨짐 방지
mysqli_set_charset($conn, "utf8mb4");

// 업로드 파일 저장 위치: 웹 루트(/var/www/html) 밖에 두어 URL로 직접 접근/실행 불가
define('UPLOAD_DIR', '/var/www/board_uploads/');

// 업로드 파일 최대 크기 (2MB) — php.ini의 upload_max_filesize(2M)와 일치
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);

// 업로드 횟수/속도 제한: UPLOAD_RATE_WINDOW초 동안 최대 UPLOAD_RATE_LIMIT개
define('UPLOAD_RATE_LIMIT', 10);
define('UPLOAD_RATE_WINDOW', 600); // 600초 = 10분

// 업로드 허용 확장자 (화이트리스트). 실행 파일/스크립트(php, exe 등)는 모두 차단
function allowed_upload_exts() {
    return ['jpg','jpeg','png','gif','bmp','webp',
            'pdf','txt','csv','zip',
            'doc','docx','xls','xlsx','ppt','pptx','hwp','hwpx'];
}

// 업로드 파일 검증. 문제없으면 빈 문자열, 문제가 있으면 에러 메시지를 반환
// (첨부는 선택이므로 파일이 없으면 통과)
function upload_error($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return '파일 크기가 너무 큽니다. (최대 2MB)';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return '파일 업로드에 실패했습니다.';
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        return '파일 크기가 너무 큽니다. (최대 2MB)';
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, allowed_upload_exts(), true)) {
        return '허용되지 않은 파일 형식입니다. (실행 파일/스크립트는 업로드할 수 없습니다)';
    }
    return '';
}

// 최근 기간 내 업로드 횟수가 한도를 초과했는지 확인 (true면 차단해야 함)
function upload_rate_exceeded($conn, $user_id) {
    $window = UPLOAD_RATE_WINDOW;
    $stmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS c FROM upload_log
         WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $window);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return ((int)$row['c']) >= UPLOAD_RATE_LIMIT;
}

// 업로드 1건을 기록 (횟수 제한 집계용)
function record_upload($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "INSERT INTO upload_log (user_id) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
}

// 업로드된 파일을 저장하고 attachments 테이블에 기록
function save_attachment($conn, $post_id, $file) {
    // 파일이 선택되지 않았거나 업로드 에러면 무시
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return false;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $original = $file['name'];
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

    // 허용되지 않은 확장자 / 크기 초과는 저장 거부 (이중 안전장치)
    if (!in_array($ext, allowed_upload_exts(), true)) {
        return false;
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        return false;
    }
    // 충돌 방지를 위해 고유한 저장 파일명 생성
    $stored = uniqid('f', true) . ($ext !== '' ? '.' . $ext : '');
    $dest = UPLOAD_DIR . $stored;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return false;
    }

    // DB에는 저장 파일명만 기록 (실제 경로는 UPLOAD_DIR 기준)
    $stored_path = $stored;
    $size = (int)$file['size'];

    $stmt = mysqli_prepare($conn, "INSERT INTO attachments (post_id, original_name, stored_path, size_bytes) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issi", $post_id, $original, $stored_path, $size);
    mysqli_stmt_execute($stmt);
    return true;
}

// 첨부파일 1건 삭제 (디스크 파일 + DB 행). 해당 게시글의 첨부만 삭제하도록 post_id 확인
function delete_attachment($conn, $att_id, $post_id) {
    $stmt = mysqli_prepare($conn, "SELECT stored_path FROM attachments WHERE id = ? AND post_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $att_id, $post_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    if (!$row) {
        return false;
    }

    $path = UPLOAD_DIR . $row['stored_path'];
    if (is_file($path)) {
        @unlink($path);
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM attachments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $att_id);
    mysqli_stmt_execute($stmt);
    return true;
}

// 게시글의 모든 첨부파일 삭제 (게시글 삭제 시 사용)
function delete_post_attachments($conn, $post_id) {
    $stmt = mysqli_prepare($conn, "SELECT stored_path FROM attachments WHERE post_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $path = UPLOAD_DIR . $row['stored_path'];
        if (is_file($path)) {
            @unlink($path);
        }
    }
    $stmt = mysqli_prepare($conn, "DELETE FROM attachments WHERE post_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
}
?>
