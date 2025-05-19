-- Создание таблицы языков программирования
CREATE TABLE IF NOT EXISTS programming_languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- Создание таблицы заявок (анкет)
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    patronymic VARCHAR(50),
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    dob DATE NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Создание таблицы связи заявок и языков программирования
CREATE TABLE IF NOT EXISTS application_languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    language_id INT NOT NULL,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES programming_languages(id) ON DELETE CASCADE,
    UNIQUE KEY (application_id, language_id)
);

-- Создание таблицы пользователей
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    application_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

-- Вставка начальных данных (языки программирования)
INSERT INTO programming_languages (name) VALUES 
('PHP'),
('JavaScript'),
('Python'),
('Java'),
('C#'),
('C++'),
('Ruby'),
('Go'),
('Swift'),
('TypeScript'),
('Kotlin'),
('Scala'),
('Rust'),
('Dart'),
('Perl'),
('R'),
('Haskell'),
('Lua'),
('SQL'),
('Bash');

-- Создание индексов для улучшения производительности
CREATE INDEX idx_applications_email ON applications(email);
CREATE INDEX idx_users_login ON users(login);
CREATE INDEX idx_application_languages_app_id ON application_languages(application_id);
CREATE INDEX idx_application_languages_lang_id ON application_languages(language_id);