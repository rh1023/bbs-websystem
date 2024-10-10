<?php
// userAdd.php
// 利用者を登録するための画面

session_start();
require 'includes/functions.php'; // 必要な関数を含むファイルをインクルード
include 'includes/header.php';

$err = '';
$page = isset($_GET['page']) ? $_GET['page'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // フォームが送信された場合の処理
    $viewName = $_POST['viewName'];
    $userId = $_POST['userId'];
    $pass = $_POST['pass'];

    // 入力条件（文字数制限）のチェック
    if (strlen($userId) < 4 || strlen($userId) > 50) {
        $err = 'ユーザーIDは4文字以上50文字以下である必要があります。';
    } elseif (strlen($pass) < 4) {
        $err = 'パスワードは4文字以上である必要があります。';
    } else {
        // 利用者登録処理
        if (addUser($viewName, $userId, $pass)) { // addUser関数を使用してユーザーを登録
            header('Location: index.php'); // 登録成功時にログイン画面にリダイレクト
            exit;
        } else {
            $err = 'ユーザー登録に失敗しました。';
        }
    }
}
?>

<head>
    <meta charset="UTF-8">
    <title>利用者登録画面</title>
</head>

<main>
    <h1>利用者登録</h1>
    <?php if ($err): ?>
        <!-- エラーメッセージの表示 -->
        <p class="error-message"><?php echo htmlspecialchars($err); ?></p>
    <?php endif; ?>

    <div class="form-container">
        <!-- ユーザー登録フォーム -->
        <form action="userAdd.php" method="post">
            <div class="form-group">
                <label for="viewName">表示名</label>
                <input type="text" id="viewName" name="viewName" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="userId">ユーザーID (4文字以上50文字以下)</label>
                <input type="text" id="userId" name="userId" class="form-input" required minlength="4" maxlength="50">
            </div>
            <div class="form-group">
                <label for="pass">パスワード (4文字以上)</label>
                <input type="password" id="pass" name="pass" class="form-input" required minlength="4">
            </div>

            <!-- 登録ボタン -->
            <button type="submit" class="btn">登録</button>
            <input type="hidden" name="page" value="<?php echo htmlspecialchars($page); ?>">
        </form>

        <!-- 戻るボタン -->
        <form action="index.php">
            <button type="submit" class="btn btn-second">戻る</button>
        </form>
    </div>
</main>

<?php
include 'includes/footer.html';
?>