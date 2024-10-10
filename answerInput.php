<?php
// answerInput.php
// 質問に対する回答を入力する画面

session_start(); // セッションを開始
require 'includes/functions.php'; // 必要な関数を含むファイルをインクルード
include 'includes/header.php';

// ログイン確認
if (!isset($_SESSION['user_id'])) {
    // ログインしていない場合、ログインページにリダイレクト
    header('Location: index.php?page=answerInput.php');
    exit;
}

$err = '';
$question = []; // 質問情報を格納する配列
$answers = []; // 回答一覧を格納する配列

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // POSTリクエストの場合、回答の登録処理を行う
    $questionId = isset($_POST['questionId']) ? $_POST['questionId'] : null;
    $answer = isset($_POST['answer']) ? $_POST['answer'] : '';
    $isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0; // 匿名投稿のチェック

    if ($questionId && !empty($answer)) {
        // 回答をデータベースに登録
        if (addAnswer($_SESSION['user_id'], $questionId, $answer, $isAnonymous)) {
            // 登録成功時は質問詳細画面にリダイレクト
            header('Location: detail.php?questionId=' . urlencode($questionId));
            exit;
        } else {
            $err = '回答の登録に失敗しました。';
        }
    } else {
        $err = '回答を入力してください。';
    }
} else {
    // GETリクエストの場合、質問情報を取得
    $questionId = isset($_GET['questionId']) ? $_GET['questionId'] : null;
    if ($questionId) {
        // データベースから質問情報を取得
        $question = getQuestionById($questionId);
        if (!$question) {
            $err = '指定された質問が見つかりません。';
        } else {
            // 質問に対する回答を取得
            $answers = getAnswersByQuestionId($questionId);
        }
    } else {
        $err = '質問が指定されていません。';
    }
}
?>

<head>
    <meta charset="UTF-8">
    <title>回答入力画面</title>
</head>

<main class="answer-input-page">
    <h1 class="answer-input-title">回答入力</h1>
    <div class="answer-input-actions">
        <!-- 戻るボタン -->
        <form action="detail.php" method="get" class="answer-input-back-form">
            <input type="hidden" name="questionId" value="<?php echo htmlspecialchars($question['id']); ?>">
            <button type="submit" class="btn btn-second">戻る</button>
        </form>
        <!-- ログアウトボタン -->
        <form action="index.php" method="post" class="answer-input-logout-form">
            <input type="hidden" name="logout" value="1">
            <button type="submit" class="btn btn-second">ログアウト</button>
        </form>
    </div>

    <?php if ($err): ?>
        <!-- エラーメッセージの表示 -->
        <p class="error-message"><?php echo htmlspecialchars($err); ?></p>
    <?php else: ?>
        <!-- 質問内容の表示 -->
        <div class="answer-input-question">
            <h2 class="answer-input-question-title"><?php echo htmlspecialchars($question['name']); ?>の質問</h2>
            <p class="answer-input-question-content"><?php echo nl2br(htmlspecialchars($question['question'])); ?></p>
            <p class="answer-input-question-date">投稿日：<?php echo htmlspecialchars($question['date']); ?></p>
        </div>

        <!-- 既存の回答の表示 -->
        <div class="answer-input-answers">
            <h3 class="answer-input-answers-title">回答</h3>
            <?php if (empty($answers)): ?>
                <p class="answer-input-no-answers">まだ回答はありません。</p>
            <?php else: ?>
                <?php foreach ($answers as $answer): ?>
                    <div class="answer-input-answer-item">
                        <h4 class="answer-input-answer-name">
                            <?php echo $answer['is_anonymous'] ? '匿名ユーザー' : htmlspecialchars($answer['name']); ?>の回答
                        </h4>
                        <p class="answer-input-answer-content"><?php echo nl2br(htmlspecialchars($answer['answer'])); ?></p>
                        <p class="answer-input-answer-date">投稿日：<?php echo htmlspecialchars($answer['date']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 回答入力フォーム -->
        <form action="answerInput.php" method="post" class="answer-input-form">
            <input type="hidden" name="questionId" value="<?php echo htmlspecialchars($question['id']); ?>">
            <div class="input-group">
                <label for="answer" class="input-label">回答入力してください</label>
                <textarea id="answer" name="answer" class="form-textarea" required></textarea>
                <input type="file" name="image" accept="image/*">
            </div>
            <div class="input-group">
                <label for="is_anonymous" class="input-label">
                    <input type="checkbox" id="is_anonymous" name="is_anonymous" value="1">
                    匿名で投稿する
                </label>
            </div>
            <div class="answer-input-submit">
                <button type="submit" class="btn btn-full-width">回答を登録する</button>
            </div>
        </form>
    <?php endif; ?>
</main>

<?php
include 'includes/footer.html';
?>