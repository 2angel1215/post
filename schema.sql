-- K_KNOCK 게시판 DB 스키마 (구조만, 데이터 없음)
-- 서버에서 실행:  sudo mysql < schema.sql

CREATE DATABASE IF NOT EXISTS K_KNOCK
  DEFAULT CHARACTER SET utf8mb4;

USE K_KNOCK;

-- 사용자
CREATE TABLE IF NOT EXISTS users (
  id         INT NOT NULL AUTO_INCREMENT,
  username   VARCHAR(50)  NOT NULL,
  password   VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 게시글 (board: 1=자유게시판, 2=방명록)
CREATE TABLE IF NOT EXISTS posts (
  id         INT NOT NULL AUTO_INCREMENT,
  title      VARCHAR(200) NOT NULL,
  content    TEXT,
  author_id  INT NOT NULL,
  board      TINYINT NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY author_id (author_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 댓글
CREATE TABLE IF NOT EXISTS comments (
  id         INT NOT NULL AUTO_INCREMENT,
  post_id    INT NOT NULL,
  author_id  INT NOT NULL,
  content    TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY post_id (post_id),
  KEY author_id (author_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 첨부파일
CREATE TABLE IF NOT EXISTS attachments (
  id            INT NOT NULL AUTO_INCREMENT,
  post_id       INT NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_path   VARCHAR(500) NOT NULL,
  size_bytes    BIGINT DEFAULT NULL,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY post_id (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 업로드 횟수 제한 로그
CREATE TABLE IF NOT EXISTS upload_log (
  id         INT NOT NULL AUTO_INCREMENT,
  user_id    INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_user_time (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
