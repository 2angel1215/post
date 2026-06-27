
<?php
include 'config.php';
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// 게시판 선택: 0 = 전체(통합), 1 = 자유게시판, 2 = 방명록
$board = isset($_GET['board']) ? (int)$_GET['board'] : 0;
if (!in_array($board, [0, 1, 2], true)) {
    $board = 0;
}
$board_names = [0 => '전체', 1 => '자유게시판', 2 => '방명록'];

// 정렬: newest(최신순, 기본) / oldest(오래된순)
$sort = ($_GET['sort'] ?? 'newest') === 'oldest' ? 'oldest' : 'newest';
$order = $sort === 'oldest' ? 'ASC' : 'DESC';

// 검색: 제목 / 본문 / 작성자 / 유저(유저 목록) / 전체
$type = $_GET['type'] ?? 'title';
$keyword = trim($_GET['keyword'] ?? '');

// 유저 검색 모드: 게시글 대신 유저 목록을 보여줌
$user_search = ($type === 'userlist');
$users = null;
$post = null;

if ($user_search) {
    if ($keyword !== '') {
        $like = '%' . $keyword . '%';
        $stmt = mysqli_prepare($conn,
            "SELECT users.id, users.username, users.created_at, COUNT(posts.id) AS post_count
             FROM users
             LEFT JOIN posts ON posts.author_id = users.id
             WHERE users.username LIKE ?
             GROUP BY users.id, users.username, users.created_at
             ORDER BY users.username ASC");
        mysqli_stmt_bind_param($stmt, "s", $like);
        mysqli_stmt_execute($stmt);
        $users = mysqli_stmt_get_result($stmt);
    }
} else {
    $where = [];
    $params = [];
    $types = '';

    // 특정 게시판만 보기 (전체는 필터 없음)
    if ($board === 1 || $board === 2) {
        $where[] = "posts.board = ?";
        $params[] = $board; $types .= 'i';
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
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>게시판</title>
</head>
<body>
    <!-- 제목 / 환영 / 글 작성 / 로그아웃 -->
    <table width="100%">
        <tr valign="bottom">
            <td nowrap><h1>게시글 목록 - <?= htmlspecialchars($board_names[$board]) ?></h1></td>
            <td nowrap><?= htmlspecialchars($_SESSION['username']) ?>님 환영합니다</td>
            <td nowrap>
                <form action="write.php" method="GET">
                    <input type="hidden" name="board" value="<?= $board ?>">
                    <button type="submit">글 작성</button>
                </form>
            </td>
            <td align="right" width="100%">
                <form action="logout.php" method="GET">
                    <button type="submit">로그아웃</button>
                </form>
            </td>
        </tr>
    </table>

    <!-- 게시판 선택 -->
    <form action="index.php" method="GET">
        <button type="submit" name="board" value="0">전체</button>
        <button type="submit" name="board" value="1">자유게시판</button>
        <button type="submit" name="board" value="2">방명록</button>
    </form>

    <!-- 검색 / 정렬 폼 -->
    <form action="index.php" method="GET">
        <input type="hidden" name="board" value="<?= $board ?>">
        <select name="type">
            <option value="title"    <?= $type === 'title'    ? 'selected' : '' ?>>제목</option>
            <option value="content"  <?= $type === 'content'  ? 'selected' : '' ?>>본문</option>
            <option value="user"     <?= $type === 'user'     ? 'selected' : '' ?>>작성자</option>
            <option value="userlist" <?= $type === 'userlist' ? 'selected' : '' ?>>유저</option>
            <option value="all"      <?= $type === 'all'      ? 'selected' : '' ?>>전체</option>
        </select>
        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="검색어">
        <select name="sort">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>최신순</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>오래된순</option>
        </select>
        <button type="submit">검색</button>
    </form>

    <?php if ($user_search) { ?>
        <!-- 유저 목록 -->
        <?php if ($users === null) { ?>
        <p>검색어를 입력하세요.</p>
        <?php } elseif (mysqli_num_rows($users) > 0) { ?>
        <table border='1'>
            <tr>
                <th>ID</th>
                <th>유저 이름</th>
                <th>가입일</th>
                <th>작성 글 수</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($users)) { ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td><?= (int)$row['post_count'] ?></td>
            </tr>
            <?php } ?>
        </table>
        <?php } else { ?>
        <p>검색 결과가 없습니다.</p>
        <?php } ?>
    <?php } else { ?>
        <!-- 게시글 목록 -->
        <table border='1'>
            <tr>
                <th>번호</th>
                <th>게시판</th>
                <th>제목</th>
                <th>작성자</th>
                <th>날짜</th>
            </tr>
            <?php $num = 1; while ($row = mysqli_fetch_assoc($post)) { ?>
            <tr>
                <td><?= $num++ ?></td>
                <td><?= htmlspecialchars($board_names[(int)$row['board']] ?? '') ?></td>
                <td><a href="view.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= $row['created_at'] ?></td>
            </tr>
            <?php } ?>
        </table>
    <?php } ?>
</body>
</html>
