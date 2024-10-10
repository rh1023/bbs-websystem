<?php
// questionInput.php
// 質問を投稿する画面

session_start(); // セッションを開始
require 'includes/functions.php'; // 必要な関数を含むファイルをインクルード
include 'includes/header.php';

// デバッグ出力（通常時はコメントアウト）
/*
var_dump($_POST);
var_dump($_FILES);
$result = addQuestion($_SESSION['user_id'], $question, $isAnonymous, $_FILES['image']);
var_dump($result);
*/

// ログイン確認
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=questionInput.php'); // ログインしていない場合、ログインページにリダイレクト
    exit;
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // フォームが送信された場合の処理
    $question = $_POST['question'] ?? '';
    $userId = $_SESSION['user_id'] ?? 0;
    $isAnonymous = isset($_POST['is_anonymous']);
    $image = $_FILES['image'] ?? null;

    if (!empty($question)) {
        // 質問を追加
        $result = addQuestion($userId, $question, $isAnonymous, $image);
        if ($result) {
            // 質問の追加に成功した場合、質問一覧ページにリダイレクト
            header('Location: question.php');
            exit;
        } else {
            // 質問の追加に失敗した場合
            $err = '質問の登録に失敗しました。エラーログを確認してください。';
            error_log("Question submission failed for user $userId");
            // デバッグ情報の出力（本番環境では削除または無効化すること）
            var_dump($_POST);
            var_dump($_FILES);
            $result = addQuestion($_SESSION['user_id'], $question, $isAnonymous, $_FILES['image']);
            var_dump($result);
        }
    } else {
        $err = '質問を入力してください。';
    }
}

?>

<head>
    <meta charset="UTF-8">
    <title>質問入力画面</title>
</head>

<main class="question-input-page">
    <h1 class="question-input-title">質問入力</h1>
    <div class="question-input-actions">
        <!-- 戻るボタン -->
        <form action="question.php" method="get" class="question-input-back-form">
            <button type="submit" class="btn btn-second">戻る</button>
        </form>
        <!-- ログアウトボタン -->
        <form action="index.php" method="post" class="question-input-logout-form">
            <input type="hidden" name="logout" value="1">
            <button type="submit" class="btn btn-second">ログアウト</button>
        </form>
    </div>

    <?php if ($err): ?>
        <!-- エラーメッセージの表示 -->
        <p class="error-message"><?php echo htmlspecialchars($err); ?></p>
    <?php endif; ?>

    <!-- 質問入力フォーム -->
    <form action="questionInput.php" method="post" class="question-input-form" enctype="multipart/form-data">
        <div class="input-group">
            <label for="question" class="input-label">質問入力してください</label>
            <textarea id="question" name="question" class="form-textarea" required></textarea>
        </div>
        <div class="input-group">
            <label for="image" class="input-label">画像を選択してください（任意）</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        <div class="input-group">
            <label for="is_anonymous" class="input-label">
                <input type="checkbox" id="is_anonymous" name="is_anonymous" value="1">
                匿名で投稿する
            </label>
        </div>
        <div class="question-input-submit">
            <button type="submit" class="btn btn-full-width">質問を投稿する</button>
        </div>
    </form>
</main>

<?php
include 'includes/footer.html';
?>