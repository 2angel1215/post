
<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

// 게시판 선택: 1 = 전체 글, 2 = 오늘 글
$board = isset($_GET['board']) ? (int)$_GET['board'] : 1;
if ($board !== 2) {
    $board = 1;
}

// 정렬: newest(최신순, 기본) / oldest(오래된순)
$sort = ($_GET['sort'] ?? 'newest') === 'oldest' ? 'oldest' : 'newest';
$order = $sort === 'oldest' ? 'ASC' : 'DESC';

// 검색: 제목 / 본문 / 작성자 / 전체
$type = $_GET['type'] ?? 'title';
$keyword = trim($_GET['keyword'] ?? '');

$where = [];
$params = [];
$types = '';

// 2번 게시판은 오늘 작성된 글만
if ($board === 2) {
    $where[] = "DATE(posts.created_at) = CURDATE()";
}

if ($keyword !== '') {
    $like = '%' . $keyword . '%';
    if ($type === 'content') {
        $where[] = "posts.content LIKE ?";
        $params[] = $like; $types .= 's';
    } elseif ($type === 'user') {
        $where[] = "users.username LIKE ?";
        $params[] = $like; $types .= 's';
    } elseif ($type === 'all') {
        $where[] = "(posts.title LIKE ? OR posts.content LIKE ? OR users.username LIKE ?)";
        $params[] = $like; $params[] = $like; $params[] = $like; $types .= 'sss';
    } else { // title
        $where[] = "posts.title LIKE ?";
        $params[] = $like; $types .= 's';
    }
}

$sql = "SELECT posts.*, users.username FROM posts JOIN users ON posts.author_id = users.id";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY posts.created_at $order";

$stmt = mysqli_prepare($conn, $sql);
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$post = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>게시판</title>
</head>
<body>
    <h1>게시글 목록</h1>
    <p>
        <?= htmlspecialchars($_SESSION['username']) ?>님 환영합니다 |
        <a href="logout.php">로그아웃</a>
        <a href="write.php">글 작성</a>
        <a href="users.php">유저 검색</a>
    </p>

    <!-- 게시판 선택 -->
    <p>
        <a href="index.php?board=1">[1번 게시판: 전체 글]</a>
        <a href="index.php?board=2">[2번 게시판: 오늘 글]</a>
        &nbsp; 현재: <b><?= $board === 2 ? '오늘 글' : '전체 글' ?></b>
    </p>

    <!-- 검색 / 정렬 폼 -->
    <form action="index.php" method="GET">
        <input type="hidden" name="board" value="<?= $board ?>">
        <select name="type">
            <option value="title"   <?= $type === 'title'   ? 'selected' : '' ?>>제목</option>
            <option value="content" <?= $type === 'content' ? 'selected' : '' ?>>본문</option>
            <option value="user"    <?= $type === 'user'    ? 'selected' : '' ?>>작성자</option>
            <option value="all"     <?= $type === 'all'     ? 'selected' : '' ?>>전체</option>
        </select>
        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="검색어">
        <select name="sort">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>최신순</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>오래된순</option>
        </select>
        <button type="submit">검색</button>
    </form>

    <table border='1'>
        <tr>
            <th>번호</th>
            <th>제목</th>
            <th>작성자</th>
            <th>날짜</th>
        </tr>
        <?php $num = 1; while ($row = mysqli_fetch_assoc($post)) { ?>
        <tr>
            <td><?= $num++ ?></td>
            <td><a href="view.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= $row['created_at'] ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
