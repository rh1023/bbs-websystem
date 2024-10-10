-- データベースの作成
CREATE DATABASE QAbbs;

-- データベースの選択
USE QAbbs;

-- usersテーブルの作成
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loginId VARCHAR(10) NOT NULL UNIQUE,
    password VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(10) NOT NULL,
    CONSTRAINT chk_loginId_length CHECK (LENGTH(loginId) >= 4),
    CONSTRAINT chk_password_length CHECK (LENGTH(password) >= 4)
);

-- questionsテーブルの作成
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    question VARCHAR(256) NOT NULL,
    date DATETIME NOT NULL,
    deleteFlg TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (userId) REFERENCES users(id)
);

-- answersテーブルの作成
CREATE TABLE answers (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    questionId INT NOT NULL,
    userId INT NOT NULL,
    answer VARCHAR(256) NOT NULL,
    date DATETIME NOT NULL,
    deleteFlg TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (questionId) REFERENCES questions(id),
    FOREIGN KEY (userId) REFERENCES users(id)
);

-- 既存のテーブルを削除
DROP TABLE IF EXISTS answers;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS users;

--既存テーブル確認
SHOW TABLES;

--テーブル構造の確認
-- データベースの使用
USE QAbbs;

-- users テーブルの構造確認
DESC users;

-- question テーブルの構造確認
DESC questions;

-- answer テーブルの構造確認
DESC answers;

--テーブルにデータがあるか確認
-- users テーブルのデータ確認
SELECT * FROM users;

-- question テーブルのデータ確認
SELECT * FROM questions;

-- answer テーブルのデータ確認
SELECT * FROM answers;


--サンプルデータ
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT,
    question TEXT,
    date DATETIME,
    deleteFlg TINYINT DEFAULT 0
);

--サンプルデータの挿入
--usersテーブル:サンプルユーザー
INSERT INTO users (loginId, password, name) VALUES
('user01', 'pass01', 'ペンギン'),
('user02', 'pass02', 'イルカ'),
('user03', 'pass03', 'アザラシ');

--usersテーブル:サンプルテーブル
INSERT INTO questions (userId, question, date) VALUES
(1, 'この質問が見えていますか？', '2024-08-13 10:00:00'),
(2, 'あなたの、好きな食べ物は？', '2024-08-13 11:30:00'),
(3, 'Novelbrightで好きな曲は？', '2024-08-13 14:15:00'),
(1, '私は、海の生き物ですか？', '2024-08-13 16:00:00');

-- answersテーブルにサンプルデータを追加
INSERT INTO answers (questionId, userId, answer, date) VALUES
(1, 2, 'バッチリ見えています！', '2024-08-13 10:30:00'),
(1, 3, '問題なく、閲覧できています。', '2024-08-13 10:45:00'),
(2, 1, '寿司', '2024-08-13 12:00:00'),
(3, 2, '開幕宣言', '2024-08-13 15:00:00'),
(4, 3, 'あなたは、地上では・・・？', '2024-08-13 16:30:00');

--新しいユーザーを作成する場合:
CREATE USER 'newuser'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON QAbbs.* TO 'newuser'@'localhost';
FLUSH PRIVILEGES;

--ユーザー作成解説
CREATE USER 'ユーザー名'@'ホスト名' IDENTIFIED BY 'パスワード';
CREATE USER 'newuser'@'localhost' IDENTIFIED BY 'password123';
この例では:
ユーザー名は 'newuser'
パスワードは 'password123'
ホスト名は 'localhost' (ローカルマシンからのアクセスのみを許可)


--自動ログイン用トークン作成
CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

--いいねボタン用
CREATE TABLE IF NOT EXISTS likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (user_id, question_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (question_id) REFERENCES questions(id)
);

--匿名投稿
--questions テーブルと answers テーブルに is_anonymous カラムを追加します。
ALTER TABLE questions ADD COLUMN is_anonymous BOOLEAN DEFAULT FALSE;
ALTER TABLE answers ADD COLUMN is_anonymous BOOLEAN DEFAULT FALSE;


--画像投稿
ALTER TABLE questions ADD COLUMN image_path VARCHAR(255);
ALTER TABLE answers ADD COLUMN image_path VARCHAR(255);

--質問クローズ機能
ALTER TABLE questions ADD COLUMN is_closed BOOLEAN DEFAULT FALSE;