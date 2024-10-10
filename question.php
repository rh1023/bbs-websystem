<?php
// question.php
// 質問一覧を新しいデータから順に表示し、検索機能を提供する

global $pdo;

session_start();
require_once 'includes/functions.php'; // 必要な関数を含むファイルをインクルード
include 'includes/header.php';

$err = '';
$questions = []; // 質問一覧を格納する配列

// 検索クエリの取得
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// 質問一覧を取得
try {
    if (!empty($search_query)) {
        // 検索クエリがある場合は検索結果を取得
        $questions = searchQuestions($pdo, $search_query, false);
    } else {
        // 検索クエリがない場合は全質問を取得
        $questions = getQuestion();
    }
    if ($questions === false) {
        $err = '質問の取得に失敗しました。';
    }
} catch (Exception $e) {
    $err = 'エラーが発生しました: ' . $e->getMessage();
    error_log('Error in question search: ' . $e->getMessage());
}

// 削除処理
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $questionId = isset($_POST['questionId']) ? $_POST['questionId'] : null;
    if ($questionId) {
        // deleteQuestion関数を使用して質問を削除
        if (!deleteQuestion($questionId)) {
            $err = '質問の削除に失敗しました。';
        } else {
            // 削除成功時に質問一覧を更新
            $questions = getQuestion();
        }
    } else {
        $err = '質問IDが指定されていません。';
    }
}

// ログインユーザーの確認
$isLoggedIn = isset($_SESSION['user_id']);

// いいね処理
if (isset($_POST['like']) && $isLoggedIn) {
    $questionId = $_POST['question_id'];
    $userId = $_SESSION['user_id'];

    // ユーザーがすでにいいねしているか確認
    if (hasUserLiked($userId, $questionId)) {
        // いいねを削除
        removeLike($userId, $questionId);
    } else {
        // いいねを追加
        addLike($userId, $questionId);
    }

    // 現在のページにリダイレクトして表示を更新
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
    exit;
}
?>

<head>
    <meta charset="UTF-8">
    <title>質問一覧画面</title>
</head>

<main class="question-page">
    <h1 class="question-title">質問一覧</h1>
    <div class="question-actions">
        <?php if ($isLoggedIn): ?>
            <!-- ログイン済みの場合、質問投稿とログアウトボタンを表示 -->
            <form action="questionInput.php" method="get">
                <button type="submit" class="btn">質問をする</button>
            </form>
            <form action="index.php" method="post" class="question-logout-form">
                <input type="hidden" name="logout" value="1">
                <button type="submit" class="btn btn-second">ログアウト</button>
            </form>
        <?php else: ?>
            <!-- 未ログインの場合、ログインボタンを表示 -->
            <form action="index.php" method="get" class="question-login-form">
                <button type="submit" class="btn btn-second">ログイン</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($err): ?>
        <!-- エラーメッセージがある場合に表示 -->
        <p class="error-message"><?php echo htmlspecialchars($err); ?></p>
    <?php endif; ?>

    <?php if (!empty($search_query)): ?>
        <!-- 検索クエリがある場合、検索結果であることを表示 -->
        <p class="search-result">検索結果: "<?php echo htmlspecialchars($search_query); ?>"</p>
    <?php endif; ?>

    <div class="question-list">
        <?php if (empty($questions)): ?>
            <p class="no-questions">質問がありません。</p>
        <?php else: ?>
            <?php foreach ($questions as $q): ?>
                <div class="question-item">
                    <h2 class="question-item-title">
                        <?php echo $q['is_anonymous'] ? '匿名ユーザー' : htmlspecialchars($q['username']); ?>の質問
                    </h2>
                    <p class="question-item-content"><?php echo nl2br(htmlspecialchars($q['question'])); ?></p>


                    <?php if (!empty($q['image_path']) && file_exists($q['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars(str_replace($_SERVER['DOCUMENT_ROOT'], '', $q['image_path'])); ?>" alt="質問画像" class="question-image">
                    <?php endif; ?>


                    <p class="question-item-date">投稿日：<?php echo htmlspecialchars($q['date']); ?></p>
                    <p class="question-item-answers">回答数：<?php echo isset($q['answer_count']) ? $q['answer_count'] : getAnswerCount($q['id']); ?></p>
                    <div class="question-item-actions">
                        <!-- 質問詳細ページへのリンク -->
                        <form action="detail.php" method="get" class="question-detail-form">
                            <input type="hidden" name="questionId" value="<?php echo htmlspecialchars($q['id']); ?>">
                            <button type="submit" class="btn btn-detail">詳細</button>
                        </form>

                        <?php if ($isLoggedIn): ?>
                            <!-- いいねボタン -->
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="question-like-form">
                                <input type="hidden" name="question_id" value="<?php echo htmlspecialchars($q['id']); ?>">
                                <button type="submit" name="like" class="btn btn-good <?php echo hasUserLiked($_SESSION['user_id'], $q['id']) ? 'btn-liked' : 'btn-like'; ?>">
                                    いいね (<?php echo getLikeCount($q['id']); ?>)
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if ($isLoggedIn && $_SESSION['user_id'] == $q['userId']): ?>
                            <!-- 質問の削除ボタン（質問投稿者のみ表示） -->
                            <form action="question.php" method="post" class="question-delete-form">
                                <input type="hidden" name="questionId" value="<?php echo htmlspecialchars($q['id']); ?>">
                                <button type="submit" name="delete" value="1" class="btn btn-delete">削除</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php
include 'includes/footer.html';
?>