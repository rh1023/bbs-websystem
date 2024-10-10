<?php
// header.php
// このファイルはウェブサイトのヘッダー部分を定義します

declare(strict_types=1);

// 検索クエリの取得
// GETパラメータから'search'を取得し、存在しない場合は空文字を設定
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <!-- スタイルシートの読み込み -->
    <link rel="stylesheet" href="css/styles.css">
</head>

<header>
    <!-- サイトのメインページへのリンク -->
    <a href="index.php">
        <h1>質問掲示板</h1>
    </a>
    <div class="header-bottom">
        <!-- 質問一覧ページへのリンク -->
        <a href="question.php">質問一覧へ</a>
        <!-- 検索フォーム -->
        <div class="container">
            <form action="question.php" method="get">
                <!-- 検索入力フィールド。XSS対策のためhtmlspecialcharsを使用 -->
                <input type="text" class="container-input" name="search" placeholder="質問を検索" value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="btn-container">検索</button>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- ログインしているユーザーにのみパスワード変更リンクを表示 -->
        <p class="change-password"><a href="changePassword.php">パスワード変更</a></p>
    <?php endif; ?>

</header>