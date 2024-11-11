<?php
mb_internal_encoding("UTF-8");
header('Content-Type: text/html; charset=utf-8');

// index.php
// ログインを促すための画面。GET時は画面表示、POST時はログイン判定で画面遷移する。
session_start();
include 'includes/header.php';
require 'includes/functions.php';
$err = '';
$page = isset($_GET['page']) ? $_GET['page'] : '';
// ログアウト処理
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    // 自動ログイン用のクッキーを削除
    setcookie('remember_me', '', time() - 3600, '/');
    header('Location: index.php');
    exit;
}
// 自動ログイン処理
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    $user = getUserByRememberToken($token);
    if ($user) {
        // セッションにユーザー情報を設定
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        // トークンを更新
        $newToken = updateRememberToken($user['id']);
        setcookie('remember_me', $newToken, time() + (7 * 24 * 60 * 60), '/', '', true, true);
    }
}
// ログイン状態をチェック
$isLoggedIn = isset($_SESSION['user_id']);
// POSTリクエストが送信された場合、ログイン処理を行う
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$isLoggedIn) {
    $userId = isset($_POST['userId']) ? $_POST['userId'] : '';
    $userPw = isset($_POST['userPw']) ? $_POST['userPw'] : '';
    $rememberMe = isset($_POST['remember_me']) ? true : false;
    if (empty($userId) || empty($userPw)) {
        $err = 'ユーザーIDとパスワードを入力してください。';
    } else {
        try {
            if (isUser($userId, $userPw)) {
                $user = getUser($userId, $userPw);
                if ($user) {
                    // セッションにユーザー情報を設定
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    // 自動ログイン処理
                    if ($rememberMe) {
                        $token = createRememberToken($user['id']);
                        setcookie('remember_me', $token, time() + (7 * 24 * 60 * 60), '/', '', true, true);
                    }
                    header('Location: question.php');
                    exit;
                }
            } else {
                $err = 'ユーザーIDまたはパスワードが正しくありません。';
            }
        } catch (Exception $e) {
            $err = 'エラーが発生しました: ' . $e->getMessage();
        }
    }
}
?>
<main>
    <?php if ($isLoggedIn): ?>
        <!-- ログイン済みの場合の表示 -->
        <h1>ようこそ</h1>
        <p>ログインしています。<br>ようこそ、<?php echo htmlspecialchars($_SESSION['user_name']); ?>さん！</p>
        <div class="form-container">
            <form action="question.php" method="get">
                <button type="submit" class="btn btn-full-width">質問一覧へ</button>
            </form>
            <form action="index.php" method="post">
                <input type="hidden" name="logout" value="1">
                <button type="submit" class="btn btn-full-width">ログアウト</button>
            </form>
        </div>
    <?php else: ?>
        <!-- 未ログインの場合の表示 -->
        <h1>ログイン</h1>
        <?php if ($err): ?>
            <p class="error-message"><?php echo htmlspecialchars($err); ?></p>
        <?php endif; ?>
        <div class="form-container">
            <!-- ログインフォーム -->
            <form action="index.php" method="post">
                <div class="form-group">
                    <label for="userId" class="form-label">利用者ID</label>
                    <input type="text" id="userId" name="userId" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="userPw" class="form-label">パスワード</label>
                    <input type="password" id="userPw" name="userPw" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="remember_me" class="form-label btn-full-width">
                        <input type="checkbox" id="remember_me" name="remember_me">ログイン状態を保持する（1週間）
                    </label>
                </div>
                <button type="submit" class="btn btn-full-width">ログイン</button>
            </form>
            <!-- 新規登録ボタン -->
            <form action="userAdd.php">
                <button type="submit" class="btn btn-full-width">新規登録</button>
                <input type="hidden" name="page" value="<?php echo htmlspecialchars($page); ?>">
            </form>
        </div>
    <?php endif; ?>
</main>

<?php
include 'includes/footer.html';
?>