-- データベースの作成
CREATE DATABASE IF NOT EXISTS QAbbs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE QAbbs;

-- テーブルと制約の作成
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loginId VARCHAR(10) NOT NULL UNIQUE,
    password VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    CONSTRAINT chk_loginId_length CHECK (LENGTH(loginId) >= 4),
    CONSTRAINT chk_password_length CHECK (LENGTH(password) >= 4)
)CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    question VARCHAR(256) NOT NULL,
    date DATETIME NOT NULL,
    deleteFlg TINYINT(1) NOT NULL DEFAULT 0,
    is_anonymous BOOLEAN DEFAULT FALSE,
    image_path VARCHAR(255),
    is_closed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (userId) REFERENCES users(id)
)CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS answers (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    questionId INT NOT NULL,
    userId INT NOT NULL,
    answer VARCHAR(256) NOT NULL,
    date DATETIME NOT NULL,
    deleteFlg TINYINT(1) NOT NULL DEFAULT 0,
    is_anonymous BOOLEAN DEFAULT FALSE,
    image_path VARCHAR(255),
    FOREIGN KEY (questionId) REFERENCES questions(id),
    FOREIGN KEY (userId) REFERENCES users(id)
)CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
)CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (user_id, question_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (question_id) REFERENCES questions(id)
)CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- サンプルデータの挿入
-- INSERT INTO users (loginId, password, name) VALUES
-- ('user01', 'pass01', 'ペンギン'),
-- ('user02', 'pass02', 'イルカ'),
-- ('user03', 'pass03', 'アザラシ');

-- INSERT INTO questions (userId, question, date) VALUES
-- (1, 'この質問が見えていますか？', '2024-08-13 10:00:00'),
-- (2, 'あなたの、好きな食べ物は？', '2024-08-13 11:30:00'),
-- (3, 'Novelbrightで好きな曲は？', '2024-08-13 14:15:00'),
-- (1, '私は、海の生き物ですか？', '2024-08-13 16:00:00');

-- INSERT INTO answers (questionId, userId, answer, date) VALUES
-- (1, 2, 'バッチリ見えています！', '2024-08-13 10:30:00'),
-- (1, 3, '問題なく、閲覧できています。', '2024-08-13 10:45:00'),
-- (2, 1, '寿司', '2024-08-13 12:00:00'),
-- (3, 2, '開幕宣言', '2024-08-13 15:00:00'),
-- (4, 3, 'あなたは、地上では・・・？', '2024-08-13 16:30:00');
