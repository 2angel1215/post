<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT original_name, stored_path FROM attachments WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$file = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$file) {
    http_response_code(404);
    echo "파일을 찾을 수 없습니다.";
    exit;
}

// 경로 탈출(path traversal) 방지: 실제 경로가 업로드 디렉터리 안에 있는지 확인
$path = realpath(UPLOAD_DIR . $file['stored_path']);
$base = realpath(rtrim(UPLOAD_DIR, '/'));

if ($path === false || $base === false || strpos($path, $base . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)) {
    http_response_code(404);
    echo "파일을 찾을 수 없습니다.";
    exit;
}

// 원본 파일명에서 헤더를 깨뜨릴 수 있는 문자(따옴표/제어문자) 제거
$name = basename($file['original_name']);
$ascii_name = preg_replace('/[\x00-\x1f"\\\\]/', '_', $name);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('X-Content-Type-Options: nosniff'); // 브라우저 MIME 스니핑 차단
// ASCII 폴백 + UTF-8 파일명(한글 등) 동시 제공
header("Content-Disposition: attachment; filename=\"$ascii_name\"; filename*=UTF-8''" . rawurlencode($name));
header('Cache-Control: private'); // 로그인 사용자 전용 파일이므로 공유 캐시에 저장 금지
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
?>
