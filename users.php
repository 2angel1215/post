<?php
include 'config.php';
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$keyword = trim($_GET['keyword'] ?? '');
$users = null;

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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>유저 검색</title>
</head>
<body>
    <h1>유저 검색</h1>
    <a href="index.php">목록으로</a>

    <form action="users.php" method="GET">
        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="유저 이름">
        <button type="submit">검색</button>
    </form>

    <?php if ($users !== null) { ?>
        <?php if (mysqli_num_rows($users) > 0) { ?>
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
    <?php } ?>
</body>
</html>
