<?php
// changePassword.php
// ユーザーがパスワードを変更するためのページ

session_start();
require_once 'includes/functions.php';
include 'includes/header.php';

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    // ログインしていない場合、ログインページにリダイレクト
    header('Location: index.php');
    exit;
}

$err = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // フォームが送信された場合の処理
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // 入力チェック
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $err = 'すべての項目を入力してください。';
    } elseif ($newPassword !== $confirmPassword) {
        $err = '新しいパスワードと確認用パスワードが一致しません。';
    } elseif (strlen($newPassword) < 4) {  // 最小文字数を4に設定（テスト用）
        $err = '新しいパスワードは4文字以上である必要があります。';
    } else {
        // パスワード変更処理
        $userId = $_SESSION['user_id'];
        if (changePassword($userId, $currentPassword, $newPassword)) {
            $success = 'パスワードが正常に変更されました。';
        } else {
            $err = '現在のパスワードが正しくないか、パスワードの変更に失敗しました。';
        }
    }
}
?>

<main>
    <h1>パスワード変更</h1>
    <?php if ($err): ?>
        <!-- エラーメッセージの表示 -->
        <p class="error-message"><?php echo htmlspecialchars($err); ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <!-- 成功メッセージの表示 -->
        <p class="success-message"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <!-- パスワード変更フォーム -->
    <form action="changePassword.php" method="post">
        <div class="form-group">
            <label for="current_password">現在のパスワード:</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">新しいパスワード:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">新しいパスワード（確認）:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn">パスワードを変更</button>
    </form>
</main>

<?php 
include 'includes/footer.html';
?>