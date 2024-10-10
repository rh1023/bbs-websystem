<?php
// detail.php
// 質問詳細と回答を表示する画面

// ini_set('display_errors', 1);
// error_reporting(E_ALL);
// echo "Debug: Script is running<br>";


session_start();
require 'includes/functions.php';
include 'includes/header.php';


// echo "Debug: After includes<br>";
// var_dump($question);
// var_dump($answers);



$err = '';
$successMessage = ''; // 成功メッセージを格納する変数
$question = []; // 質問情報を格納する配列
$answers = []; // 回答一覧を格納する配列

// 質問情報を取得
$questionId = isset($_GET['questionId']) ? $_GET['questionId'] : null;
if ($questionId) {
    // getQuestionById関数を使用して質問情報を取得
    $question = getQuestionById($questionId);
    if (!$question) {
        $err = '質問が見つかりません。';
    }
} else {
    $err = '質問が指定されていません。';
}

// ログインユーザーの確認
$isLoggedIn = isset($_SESSION['user_id']);

// 質問クローズ処理
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['close_question'])) {
    if ($isLoggedIn && $_SESSION['user_id'] == $question['userId']) {
        if (closeQuestion($questionId)) {
            $successMessage = '質問がクローズされました。';
            $question['is_closed'] = true; // 質問の状態を更新
        } else {
            $err = '質問のクローズに失敗しました。';
        }
    } else {
        $err = '質問をクローズする権限がありません。';
    }
}

// 回答一覧を取得
if (!$err) {
    try {
        // getAnswersByQuestionId関数を使用して回答一覧を取得
        $answers = getAnswersByQuestionId($questionId);
        if ($answers === false) {
            // 回答がない場合は空の配列をセット
            $answers = [];
        } else {
            // 回答を日付の昇順（古い順）でソート
            usort($answers, function ($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });
        }
    } catch (Exception $e) {
        $err = 'エラーが発生しました: ' . $e->getMessage();
    }
}

// 回答の削除処理
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $answerId = isset($_POST['answerId']) ? $_POST['answerId'] : null;
    if ($answerId) {
        // deleteAnswer関数を使用して回答を削除
        if (!deleteAnswer($answerId)) {
            $err = '回答の削除に失敗しました。';
        } else {
            // 削除成功時に回答一覧を更新
            $answers = getAnswersByQuestionId($questionId);
            if ($answers === false) {
                $answers = [];
            } else {
                // 更新された回答リストを再度昇順（古い順）でソート
                usort($answers, function ($a, $b) {
                    return strtotime($a['date']) - strtotime($b['date']);
                });
            }
        }
    } else {
        $err = '回答IDが指定されていません。';
    }
}
?>

<head>
    <meta charset="UTF-8">
    <title>質問詳細画面</title>
</head>

<main class="detail-page">
    <h1 class="detail-title">質問詳細</h1>
    <div class="detail-actions">
        <!-- 戻るボタン -->
        <form action="question.php" method="get" class="detail-back-form">
            <button type="submit" class="btn btn-second">戻る</button>
        </form>

        <?php if ($isLoggedIn): ?>
            <!-- ログアウトボタン -->
            <form action="index.php" method="post" class="detail-logout-form">
                <input type="hidden" name="logout" value="1">
                <button type="submit" class="btn btn-second">ログアウト</button>
            </form>
        <?php else: ?>
            <!-- ログインボタン -->
            <form action="index.php" method="get" class="detail-login-form">
                <button type="submit" class="btn btn-second">ログイン</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- エラーメッセージの表示 -->
    <?php if ($err): ?>
        <p class="error-message"><?php echo htmlspecialchars($err); ?></p>
    <?php endif; ?>

    <!-- 成功メッセージの表示 -->
    <?php if ($successMessage): ?>
        <p class="success-message"><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>

    <?php if (!$err): ?>
        <!-- 質問の詳細表示 -->
        <div class="detail-question">
            <h2 class="detail-question-title">
                <?php echo $question['is_anonymous'] ? '匿名ユーザー' : htmlspecialchars($question['name']); ?>の質問
            </h2>
            <p class="detail-question-content"><?php echo nl2br(htmlspecialchars($question['question'])); ?></p>

            <?php if (!empty($question['image_path'])): ?>
                <?php
                $image_path = $question['image_path'];
                $web_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $image_path);
                // echo "Debug: Full image path = " . $image_path . "<br>";
                // echo "Debug: Web image path = " . $web_path . "<br>";
                ?>
                <img src="<?php echo htmlspecialchars($web_path); ?>" alt="質問画像" class="question-image">
            <?php endif; ?>

            <p class="detail-question-date">投稿日：<?php echo htmlspecialchars($question['date']); ?></p>

            <!-- 質問のクローズ状態の表示と操作 -->
            <?php if (isset($question['is_closed']) && $question['is_closed']): ?>
                <p class="alert alert-info">この質問はクローズされています。</p>
            <?php elseif ($isLoggedIn && $_SESSION['user_id'] == $question['userId']): ?>
                <form action="detail.php?questionId=<?php echo htmlspecialchars($questionId); ?>" method="post">
                    <input type="hidden" name="close_question" value="1">
                    <button type="submit" class="btn btn-warning">質問をクローズ</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- 回答一覧の表示 -->
        <div class="detail-answers">
            <h3 class="detail-answers-title">回答</h3>
            <?php if (empty($answers)): ?>
                <p class="detail-no-answers">まだ回答はありません。</p>
            <?php else: ?>
                <?php foreach ($answers as $a): ?>
                    <div class="detail-answer-item">
                        <h4 class="detail-answer-name">
                            <?php echo $a['is_anonymous'] ? '匿名ユーザー' : htmlspecialchars($a['name']); ?>の回答
                        </h4>
                        <p class="detail-answer-content"><?php echo nl2br(htmlspecialchars($a['answer'])); ?></p>

                        <?php if (!empty($question['image_path'])): ?>
                            <!-- <?php
                            $image_path = $question['image_path'];
                            $web_path = '/uploads/' . basename($image_path);
                            ?> -->
                            <!-- <p>Debug: Image path = <?php echo htmlspecialchars($web_path); ?></p> -->
                            <img src="<?php echo htmlspecialchars($web_path); ?>" alt="質問画像" class="question-image">
                        <?php else: ?>
                            <!-- <p>Debug: No image path found for question</p> -->
                        <?php endif; ?>

                        <p class="detail-answer-date">投稿日：<?php echo htmlspecialchars($a['date']); ?></p>
                        <!-- 回答の削除ボタン（回答者のみ表示） -->
                        <?php if ($isLoggedIn && $_SESSION['user_id'] == $a['userId']): ?>
                            <form action="detail.php?questionId=<?php echo htmlspecialchars($questionId); ?>" method="post" class="detail-delete-form">
                                <input type="hidden" name="answerId" value="<?php echo htmlspecialchars($a['id']); ?>">
                                <button type="submit" name="delete" value="1" class="btn btn-delete">削除</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- 回答フォームの表示（ログイン済みかつ質問がクローズされていない場合） -->
    <?php if ($isLoggedIn && (!isset($question['is_closed']) || !$question['is_closed'])): ?>
        <div class="detail-answer-action">
            <form action="answerInput.php" method="get" class="detail-answer-form">
                <input type="hidden" name="questionId" value="<?php echo htmlspecialchars($questionId); ?>">
                <button type="submit" class="btn">回答をする</button>
            </form>
        </div>
    <?php elseif (isset($question['is_closed']) && $question['is_closed']): ?>
        <p class="alert alert-warning">この質問はクローズされているため、新しい回答を投稿できません。</p>
    <?php endif; ?>
</main>

<?php
include 'includes/footer.html';
?>