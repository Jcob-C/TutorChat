# Database Setup

```sql
CREATE DATABASE chatbot_tutor;
USE chatbot_tutor;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    verified BOOLEAN NOT NULL DEFAULT FALSE,
    email VARCHAR(255) NOT NULL UNIQUE,
    nickname VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE verification_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    code INT UNSIGNED NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```