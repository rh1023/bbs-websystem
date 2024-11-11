<?php
//functions.php

// データベース接続情報
// XAMMP使用時のデータベース構築
// const DB_HOST = '127.0.0.1';  // データベースサーバーのホスト名またはIPアドレス
// const DB_NAME = 'QAbbs';      // 使用するデータベース名
// const DB_USER = 'root';       // データベースへのアクセスに使用するユーザー名
// const DB_PASS = '';           // データベースへのアクセスに使用するパスワード（空の場合はセキュリティ上のリスクがあります）
// const DB_CHARSET = 'utf8mb4'; // データベースの文字セット（絵文字対応のUTF-8）

// データベース接続情報
const DB_HOST = 'db';              // Docker Composeで設定したMySQLのサービス名
const DB_NAME = 'QAbbs';            // データベース名
const DB_USER = 'user';             // MySQLのユーザー名
const DB_PASS = 'user_password';    // MySQLのパスワード
const DB_CHARSET = 'utf8mb4';       // 文字セット


// グローバル変数としてPDOオブジェクトを宣言し、データベース接続を確立
global $pdo;
$pdo = connect();

/**
 * データベースに接続する関数
 * 
 * @return PDO データベース接続オブジェクト
 * @throws Exception データベース接続に失敗した場合
 */
function connect(): PDO
{
    try {
        // PDOオブジェクトを作成し、データベースに接続
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);

        //echo "データベース接続に成功しました！"; // 追加のデバッグメッセージ
        
        // PDOの動作設定
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);        // エラーモードを例外に設定
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);   // デフォルトのフェッチモードを連想配列に設定
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);                // プリペアドステートメントのエミュレーションを無効化

        // アップロードディレクトリの作成と権限設定
        $upload_dir = __DIR__ . '/../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);  // ディレクトリが存在しない場合、作成
        }
        if (!is_writable($upload_dir)) {
            chmod($upload_dir, 0755);        // ディレクトリが書き込み可能でない場合、権限を変更
        }

        return $pdo;
    } catch (PDOException $e) {
        // 接続エラーの場合、ログに記録し例外をスロー
        error_log('Connection failed: ' . $e->getMessage());
        throw new Exception('データベース接続エラー');
    }
}

/**
 * ユーザー認証を行う関数
 * 
 * @param string $userId ログインID
 * @param string $userPw パスワード
 * @return bool 認証成功時はtrue、失敗時はfalse
 */
function isUser(string $userId, string $userPw): bool
{
    $pdo = connect();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE loginId = :userId AND password = :userPw');
    $stmt->execute(['userId' => $userId, 'userPw' => $userPw]);
    return $stmt->fetchColumn() > 0;  // ユーザーが存在すればtrue、しなければfalse
}

/**
 * ユーザー情報を取得する関数
 * 
 * @param string $userId ログインID
 * @param string $userPw パスワード
 * @return array|false ユーザー情報の連想配列、失敗時はfalse
 */
function getUser(string $userId, string $userPw): array|false
{
    $pdo = connect();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE loginId = :userId AND password = :userPw');
    $stmt->execute(['userId' => $userId, 'userPw' => $userPw]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
}

/**
 * 新規ユーザーを登録する関数
 * 
 * @param string $viewName 表示名
 * @param string $userId ログインID
 * @param string $userPw パスワード
 * @return bool 登録成功時はtrue、失敗時はfalse
 */
function addUser(string $viewName, string $userId, string $userPw): bool
{
    $pdo = connect();
    $stmt = $pdo->prepare('INSERT INTO users (name, loginId, password) VALUES (:name, :userId, :userPw)');
    return $stmt->execute(['name' => $viewName, 'userId' => $userId, 'userPw' => $userPw]);
}

/**
 * 全ての質問を取得する関数
 * 
 * @return array|false 質問の配列、失敗時はfalse
 */
function getQuestion(): array|false
{
    $pdo = connect();
    // 質問、ユーザー名、回答数を取得するためにJOINとサブクエリを使用
    $stmt = $pdo->query('
    SELECT
        q.id,
        q.question,
        q.date,
        q.userId,
        q.image_path,
        q.is_anonymous,
        u.name AS username,
        (SELECT COUNT(*) FROM answers a WHERE a.questionId = q.id AND a.deleteFlg = 0) AS answer_count
    FROM questions q
    JOIN users u ON q.userId = u.id
    WHERE q.deleteFlg = 0
    ORDER BY q.date DESC, q.id DESC
    ');
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: false;
}

/**
 * 新しい質問を追加する関数
 * 
 * @param int $userId ユーザーID
 * @param string $question 質問内容
 * @param bool $isAnonymous 匿名投稿かどうか
 * @param array|null $image アップロードされた画像情報
 * @return bool 追加成功時はtrue、失敗時はfalse
 */
function addQuestion(int $userId, string $question, bool $isAnonymous = false, $image = null): bool {
    $pdo = connect();
    $pdo->beginTransaction();
    
    try {
        $image_path = null;
        if ($image && $image['error'] == UPLOAD_ERR_OK) {
            $image_path = uploadImage($image);
            if ($image_path === false) {
                error_log("Image upload failed. File: " . $image['name'] . ", Error: " . $image['error']);
                throw new Exception("Image upload failed");
            }
        }
        $stmt = $pdo->prepare('INSERT INTO questions (userId, question, date, is_anonymous, image_path) VALUES (:userId, :question, NOW(), :isAnonymous, :image_path)');
        $result = $stmt->execute([
            'userId' => $userId,
            'question' => $question,
            'isAnonymous' => $isAnonymous ? 1 : 0,
            'image_path' => $image_path
        ]);
        
        if (!$result) {
            throw new Exception("Failed to insert question into database");
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error in addQuestion: " . $e->getMessage());
        return false;
    }
}

/**
 * 質問を論理削除する関数
 * 
 * @param int $questionId 削除する質問のID
 * @return bool 削除成功時はtrue、失敗時はfalse
 */
function deleteQuestion(int $questionId): bool
{
    $pdo = connect();
    $pdo->beginTransaction();
    try {
        $stmt1 = $pdo->prepare('UPDATE questions SET deleteFlg = 1 WHERE id = :questionId');
        $stmt2 = $pdo->prepare('UPDATE answers SET deleteFlg = 1 WHERE questionId = :questionId');
        $stmt1->execute(['questionId' => $questionId]);
        $stmt2->execute(['questionId' => $questionId]);
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

/**
 * 特定の質問を取得する関数
 * 
 * @param int $questionId 取得する質問のID
 * @return array|false 質問情報の連想配列、失敗時はfalse
 */
// function getQuestionById(int $questionId): array|false
// {
//     $pdo = connect();
//     $stmt = $pdo->prepare('
//     SELECT q.id, q.question, q.date, q.userId, q.image_path, q.is_anonymous, u.name
//     FROM questions q
//     JOIN users u ON q.userId = u.id
//     WHERE q.id = :questionId AND q.deleteFlg = 0
//     ');
//     $stmt->execute(['questionId' => $questionId]);
//     return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
// }
function getQuestionById(int $questionId): array|false
{
    $pdo = connect();
    $stmt = $pdo->prepare('
    SELECT q.id, q.question, q.date, q.userId, q.image_path, q.is_anonymous, u.name
    FROM questions q
    JOIN users u ON q.userId = u.id
    WHERE q.id = :questionId AND q.deleteFlg = 0
    ');
    $stmt->execute(['questionId' => $questionId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // echo "Debug: getQuestionById result:<br>";
    // var_dump($result);
    
    return $result ?: false;
}

/**
 * 特定の質問に対する回答を取得する関数
 * 
 * @param int $questionId 質問のID
 * @return array|false 回答の配列、失敗時はfalse
 */
function getAnswersByQuestionId(int $questionId): array|false
{
    $pdo = connect();
    $stmt = $pdo->prepare('
    SELECT a.id, a.answer, a.date, a.userId, a.image_path, a.is_anonymous, u.name
    FROM answers a
    JOIN users u ON a.userId = u.id
    WHERE a.questionId = :questionId AND a.deleteFlg = 0
    ORDER BY a.date DESC, a.id DESC
    ');
    $stmt->execute(['questionId' => $questionId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: false;
}

/**
 * 特定の質問に対する回答数を取得する関数
 * 
 * @param int $questionId 質問のID
 * @return int 回答数
 */
function getAnswerCount($questionId) {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM answers WHERE questionId = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$questionId]);
    return $stmt->fetchColumn();
}

/**
 * 新しい回答を追加する関数
 * 
 * @param int $userId ユーザーID
 * @param int $questionId 質問ID
 * @param string $answer 回答内容
 * @param bool $isAnonymous 匿名回答かどうか
 * @return bool 追加成功時はtrue、失敗時はfalse
 */
function addAnswer(int $userId, int $questionId, string $answer, bool $isAnonymous = false): bool
{
    $pdo = connect();
    $stmt = $pdo->prepare('INSERT INTO answers (userId, questionId, answer, date, is_anonymous) VALUES (:userId, :questionId, :answer, NOW(), :isAnonymous)');
    return $stmt->execute([
        'userId' => $userId,
        'questionId' => $questionId,
        'answer' => $answer,
        'isAnonymous' => $isAnonymous ? 1 : 0
    ]);
}

/**
 * 回答を論理削除する関数
 * 
 * @param int $answerId 削除する回答のID
 * @return bool 削除成功時はtrue、失敗時はfalse
 */
function deleteAnswer(int $answerId): bool
{
    $pdo = connect();
    $stmt = $pdo->prepare('UPDATE answers SET deleteFlg = 1 WHERE id = :answerId');
    return $stmt->execute(['answerId' => $answerId]);
}

/**
 * 質問を検索する関数
 * 
 * @param PDO $pdo PDOオブジェクト
 * @param string $query 検索クエリ
 * @param bool $debug デバッグモードフラグ
 * @return array 検索結果の配列
 * @throws Exception データベース接続エラー時
 */
function searchQuestions($pdo, $query, $debug = false) {
    if (!$pdo) {
        throw new Exception('データベース接続が確立されていません。');
    }
    $sql = "SELECT q.*, u.name AS username 
            FROM questions q 
            JOIN users u ON q.userId = u.id 
            WHERE q.question LIKE :query AND q.deleteFlg = 0
            ORDER BY q.date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['query' => '%' . $query . '%']);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // デバッグ出力
    if ($debug) {
        echo "Search query: " . $query . "<br>";
        echo "SQL: " . $sql . "<br>";
        echo "Result count: " . count($result) . "<br>";
        var_dump($result);
    }
    return $result;
}


/**
 * 自動ログイン用のトークンを作成する関数
 * 
 * @param int $userId ユーザーID
 * @return string 生成されたトークン
 */
function createRememberToken($userId) {
    global $pdo;
    $token = bin2hex(random_bytes(32)); // セキュアなランダムトークンを生成
    $expires = date('Y-m-d H:i:s', strtotime('+1 week'));
    $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $token, $expires]);
    return $token;
}

/**
 * 記憶トークンを使用してユーザー情報を取得する関数
 * 
 * @param string $token 記憶トークン
 * @return array|false ユーザー情報の連想配列、失敗時はfalse
 */
function getUserByRememberToken($token) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.* 
        FROM users u 
        JOIN remember_tokens rt ON u.id = rt.user_id 
        WHERE rt.token = ? AND rt.expires > NOW()
    ");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * 記憶トークンを更新する関数
 * 
 * @param int $userId ユーザーID
 * @return string 新しく生成されたトークン
 */
function updateRememberToken($userId) {
    global $pdo;
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 week'));
    
    $stmt = $pdo->prepare("
        UPDATE remember_tokens 
        SET token = ?, expires = ? 
        WHERE user_id = ?
    ");
    $stmt->execute([$token, $expires, $userId]);
    return $token;
}

/**
 * ユーザーが質問に「いいね」をしているかチェックする関数
 * 
 * @param int $userId ユーザーID
 * @param int $questionId 質問ID
 * @return bool 「いいね」している場合はtrue、していない場合はfalse
 */
function hasUserLiked($userId, $questionId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE user_id = ? AND question_id = ?");
    $stmt->execute([$userId, $questionId]);
    return $stmt->fetchColumn() > 0;
}

/**
 * 質問の「いいね」数を取得する関数
 * 
 * @param int $questionId 質問ID
 * @return int 「いいね」の数
 */
function getLikeCount($questionId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE question_id = ?");
    $stmt->execute([$questionId]);
    return $stmt->fetchColumn();
}

/**
 * 質問に「いいね」を追加する関数
 * 
 * @param int $userId ユーザーID
 * @param int $questionId 質問ID
 * @return bool 追加成功時はtrue、失敗時はfalse
 */
function addLike($userId, $questionId) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT IGNORE INTO likes (user_id, question_id) VALUES (?, ?)");
    return $stmt->execute([$userId, $questionId]);
}

/**
 * 質問の「いいね」を削除する関数
 * 
 * @param int $userId ユーザーID
 * @param int $questionId 質問ID
 * @return bool 削除成功時はtrue、失敗時はfalse
 */
function removeLike($userId, $questionId) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND question_id = ?");
    return $stmt->execute([$userId, $questionId]);
}

/**
 * ユーザーのパスワードを変更する関数
 * 
 * @param int $userId ユーザーID
 * @param string $currentPassword 現在のパスワード
 * @param string $newPassword 新しいパスワード
 * @return bool 変更成功時はtrue、失敗時はfalse
 */
function changePassword($userId, $currentPassword, $newPassword) {
    global $pdo;
    
    try {
        // 現在のパスワードを確認
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user || $user['password'] !== $currentPassword) {
            return false;
        }
        
        // パスワードを更新
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $result = $stmt->execute([$newPassword, $userId]);

        return $result;
    } catch (PDOException $e) {
        error_log("Password change error: " . $e->getMessage());
        return false;
    }
}

/**
 * 画像をアップロードする関数
 * 
 * @param array $file $_FILES配列の要素
 * @return string|false アップロードされた画像のパス、失敗時はfalse
 */
function uploadImage($file) {
    $target_dir = __DIR__ . '/../uploads/';
    
    // ディレクトリが存在しない場合は作成
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            error_log("Failed to create upload directory");
            return false;
        }
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    
    // 画像ファイルかどうかをチェック
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        error_log("Uploaded file is not an image");
        return false;
    }
    
    // ファイルサイズをチェック (5MB制限)
    if ($file["size"] > 5000000) {
        error_log("File is too large");
        return false;
    }
    
    // 特定のファイル形式のみを許可
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        error_log("Invalid file format");
        return false;
    }
    
    // ユニークなファイル名を生成
    $new_filename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    // 一時ファイルが読み取り可能かを確認
    if (!is_readable($file["tmp_name"])) {
        error_log("Temporary file is not readable");
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // アップロードされたファイルの権限を設定
        // chmod($target_file, 0644);
        return $target_file;
    } else {
        error_log("Failed to move uploaded file. PHP Error: " . error_get_last()['message']);
        return false;
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // ウェブサーバーのルートディレクトリからの相対パスを返す
        return '/uploads/' . basename($target_file);
    } else {
        error_log("Failed to move uploaded file. PHP Error: " . error_get_last()['message']);
        return false;
    }
}

/**
 * 質問をクローズする関数
 * 
 * @param int $questionId 質問ID
 * @return bool クローズ成功時はtrue、失敗時はfalse
 */
function closeQuestion(int $questionId): bool {
    $pdo = connect();
    $stmt = $pdo->prepare('UPDATE questions SET is_closed = TRUE WHERE id = :questionId');
    return $stmt->execute(['questionId' => $questionId]);
}

/**
 * 質問がクローズされているかチェックする関数
 * 
 * @param int $questionId 質問ID
 * @return bool クローズされている場合はtrue、そうでない場合はfalse
 */
function isQuestionClosed(int $questionId): bool {
    $pdo = connect();
    $stmt = $pdo->prepare('SELECT is_closed FROM questions WHERE id = :questionId');
    $stmt->execute(['questionId' => $questionId]);
    return (bool)$stmt->fetchColumn();
}

/**
 * 質問の所有者かどうかをチェックする関数
 * 
 * @param int $questionId 質問ID
 * @param int $userId ユーザーID
 * @return bool 所有者の場合はtrue、そうでない場合はfalse
 */
function isQuestionOwner($questionId, $userId) {
    $pdo = connect();
    $stmt = $pdo->prepare('SELECT userId FROM questions WHERE id = :questionId');
    $stmt->execute(['questionId' => $questionId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result && $result['userId'] == $userId;
}

?>
