## **Database Setup**

```sql
CREATE DATABASE chatbot_tutor;
USE chatbot_tutor;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activated BOOLEAN NOT NULL DEFAULT TRUE,
    acc_role VARCHAR(255) NOT NULL DEFAULT 'learner',
    email VARCHAR(255) NOT NULL UNIQUE,
    nick VARCHAR(255) NOT NULL,
    pass VARCHAR(255) NOT NULL
);

CREATE TABLE verification_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    code INT NOT NULL,
    expires TIMESTAMP NOT NULL
);

CREATE TABLE topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    descr TEXT NOT NULL,
    clicks INT NOT NULL DEFAULT 0
);

CREATE TABLE feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    descr TEXT NOT NULL,
    created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE tutor_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    topic_id INT NOT NULL,
    pre_score INT NOT NULL,
    post_score INT NOT NULL,
    messages JSON NOT NULL,
    concluded DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
);
```

## **Project Structure**

```
/
├── api/            # Backend endpoints for JS
├── assets/         # CSS, images, fonts
├── config/         # Environment configs (DB, API keys)
├── lib/            # Third-party libraries or shared classes
├── page/           # User-facing pages
├── utils/          # Helper functions
│     └── database/ # CRUD functions per database table
└── index.php       # Entry point
```

## **Folder Purposes**

### **/page — User Pages**

* All browser-visible pages.
* Contains mainly PHP and HTML.
* Try to utilize `/api`, `/utils`, and `/utils/database` to avoid writing messy backend logic here.

### **/api — Backend Endpoints**

* Handles AJAX/fetch requests.
* Validates input → calls utils/database → returns data.
* Keep simple: no SQL directly here.

### **/assets — Static Files**

* Stylesheets, images, fonts.
* No backend logic or sensitive data.

### **/config — Environment Settings**

* Database credentials.
* API keys.
* Never hardcode secrets elsewhere.

### **/lib — Libraries**

* External libraries or shared internal classes.
* Not for app-specific business logic.

### **/utils — Helper Functions**

* Shared utility logic (validation, mini-systems, authentication, etc.).
* Keep files focused on one topic.

### **/utils/database — CRUD by Table**

* Each database table has its own file (e.g., `users.php`, `posts.php`).
* Contains **all SQL queries**.
* No SQL in `/api` or `/page`.
