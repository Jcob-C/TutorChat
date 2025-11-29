# **Database Setup**

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

# **AI Generated UI Design Prompt**

```
Update this existing code to match TutorChat's purple and black theme with Bootstrap 5. Improve the layout to be modern and professional while keeping all functionality intact.

INCLUDE CSS:
Add this link in <head>:
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> (for the chat branding icon)
<link rel="stylesheet" href="../assets/theme.css">
<link rel="stylesheet" href="../assets/popupMessage.css">

FOR REFERENCE THIS IS THE "../assets/theme.css":
:root {
    --primary-purple: #6f42c1;
    --dark-purple: #5a32a3;
    --black: #1a1a1a;
}
body {
    background: linear-gradient(135deg, var(--black) 0%, var(--dark-purple) 100%);
}
.text-brand { 
    color: var(--primary-purple) !important; 
}
.btn-brand {
    background: var(--primary-purple);
    border-color: var(--primary-purple);
    color: white;
}
.btn-brand:hover {
    background: var(--dark-purple);
    border-color: var(--dark-purple);
    color: white;
}
.link-brand { 
    color: var(--primary-purple); 
}
.link-brand:hover { 
    color: var(--dark-purple); 
}

IMPORTANT:
Do NOT use <style></style> blocks. All styling must use Bootstrap classes and theme.css only.

THEME CLASSES (defined in theme.css):
- Purple buttons: btn-brand
- Purple links: link-brand
- Purple icons/accents: text-brand
- Background: Dark gradient (applied to body)
- White cards with: shadow-lg border-0 rounded-3
- White/light text on dark backgrounds: text-white or text-white-50

FONT SIZES & WEIGHTS (based on Bootstrap classes used):
- Logo / Branding Heading (`display-4 fw-bold`): ~2.5rem (40px), bold
- Branding subtitle (`text-white-50` paragraph): ~1rem (16px), normal weight
- Card Title (`h2.card-title fw-bold`): ~2rem (32px), bold
- Form Labels (`form-label`): ~1rem (16px), normal weight
- Buttons (`btn-lg fw-semibold` by default for btn-brand): ~1.25rem (20px), semi-bold
- Links (`link-brand fw-semibold`): ~1rem (16px), semi-bold
- Footer / Small Text (`small text-white-50`): ~0.875rem (14px), normal weight
- Default paragraph text: ~1rem (16px), normal weight

BRANDING:
The TutorChat logo must appear somewhere in the layout:
<div class="text-center mb-4">
    <h1 class="display-4 fw-bold text-white">
        <i class="bi bi-chat-dots-fill text-brand"></i> TutorChat
    </h1>
</div>

Keep all (PHP,JS) logic and functionality unchanged.
```