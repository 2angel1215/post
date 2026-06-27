<?php
include 'config.php';
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$id = (int)$_GET['id'];
$me = $_SESSION['id'];

// 게시글 조회 (작성자 이름 JOIN)
$stmt = mysqli_prepare($conn, "SELECT posts.*, users.username FROM posts JOIN users ON posts.author_id = users.id WHERE posts.id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$post) {
    echo "존재하지 않는 게시글입니다.";
    exit;
}

// 첨부파일 목록
$stmt = mysqli_prepare($conn, "SELECT * FROM attachments WHERE post_id = ? ORDER BY id ASC");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$attachments = mysqli_stmt_get_result($stmt);

// 댓글 목록 조회
$stmt = mysqli_prepare($conn, "SELECT comments.*, users.username FROM comments JOIN users ON comments.author_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at ASC");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$comments = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($post['title']) ?></title>
</head>
<body>
    <!-- 제목 / 작성자·날짜(우측 상단) + 바로 아랫줄 수정·삭제·목록 버튼 -->
    <table width="100%">
        <tr valign="top">
            <td nowrap><h1><?= htmlspecialchars($post['title']) ?></h1></td>
            <td align="right" width="100%" nowrap>
                작성자: <?= htmlspecialchars($post['username']) ?> | 날짜: <?= $post['created_at'] ?>
                <br>
                <!-- 버튼: 한 줄 유지를 위해 안쪽 테이블, 우측 정렬 -->
                <table align="right">
                    <tr>
                        <?php if ($me == $post['author_id']) { ?>
                        <td nowrap>
                            <form action="edit.php" method="GET">
                                <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                <button type="submit">수정</button>
                            </form>
                        </td>
                        <td nowrap>
                            <form action="delete.php" method="POST" onsubmit="return confirm('삭제하시겠습니까?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                <button type="submit">삭제</button>
                            </form>
                        </td>
                        <?php } ?>
                        <td nowrap>
                            <form action="index.php" method="GET">
                                <button type="submit">목록</button>
                            </form>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

    <!-- 첨부파일 -->
    <?php if (mysqli_num_rows($attachments) > 0) { ?>
    <h3>첨부파일</h3>
    <ul>
        <?php while ($file = mysqli_fetch_assoc($attachments)) { ?>
        <li>
            <a href="download.php?id=<?= $file['id'] ?>"><?= htmlspecialchars($file['original_name']) ?></a>
            (<?= number_format((int)$file['size_bytes']) ?> bytes)
        </li>
        <?php } ?>
    </ul>
    <?php } ?>

    <hr>

    <!-- 댓글 제목 + 입력 박스(제목 라인 옆) -->
    <table width="100%">
        <tr valign="middle">
            <td nowrap><h2>댓글</h2></td>
            <td width="100%">
                <form action="comment.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="write">
                    <input type="hidden" name="post_id" value="<?= $id ?>">
                    <table>
                        <tr valign="top">
                            <td><textarea name="content" rows="3" cols="80" placeholder="댓글"></textarea></td>
                            <td><button type="submit">등록</button></td>
                        </tr>
                    </table>
                </form>
            </td>
        </tr>
    </table>
    <?php while ($row = mysqli_fetch_assoc($comments)) { ?>
    <!-- 댓글 한 건: 좌측 내용 / 우측 정렬 수정·삭제 버튼 -->
    <table width="100%">
        <tr valign="top">
            <td width="100%"><b><?= htmlspecialchars($row['username']) ?></b> : <?= htmlspecialchars($row['content']) ?></td>
            <?php if ($me == $row['author_id']) { ?>
            <td nowrap>
                <form action="comment_edit.php" method="GET">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="post_id" value="<?= $id ?>">
                    <button type="submit">수정</button>
                </form>
            </td>
            <td nowrap>
                <form action="comment.php" method="POST" onsubmit="return confirm('삭제하시겠습니까?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="post_id" value="<?= $id ?>">
                    <button type="submit">삭제</button>
                </form>
            </td>
            <?php } ?>
        </tr>
    </table>
    <hr>
    <?php } ?>

</body>
</html>
