<?php
// Функция для получения читаемых названий полей
function getFieldLabel($field) {
    $labels = [
        'fullname' => 'ФИО',
        'phone' => 'Телефон',
        'email' => 'Email',
        'dob' => 'Дата рождения',
        'gender' => 'Пол',
        'languages' => 'Языки программирования',
        'bio' => 'Биография',
        'contract' => 'Ознакомление с контрактом',
        'db_error' => 'Ошибка базы данных'
    ];
    return $labels[$field] ?? $field;
}

// Функция для форматирования сообщений об ошибках
function formatErrorMessage($error, $defaultMessage) {
    if (is_array($error)) {
        return htmlspecialchars(implode(', ', $error));
    }
    return htmlspecialchars($error ?: $defaultMessage);
}

// Отображение сообщения об успехе
if (isset($_COOKIE['form_success'])) {
    $successMessage = htmlspecialchars($_COOKIE['form_success']);
    setcookie('form_success', '', time() - 3600, '/');
}

// Получаем ошибки из cookies
$errors = [];
if (isset($_COOKIE['form_errors'])) {
    $errors = json_decode($_COOKIE['form_errors'], true);
    setcookie('form_errors', '', time() - 3600, '/');
}

// Получаем сохраненные значения полей из cookies
$fields = [
    'fullname' => '',
    'phone' => '',
    'email' => '',
    'dob' => '',
    'gender' => '',
    'languages' => [],
    'bio' => '',
    'contract' => ''
];

foreach ($fields as $key => $value) {
    if (isset($_COOKIE['form_' . $key])) {
        if ($key === 'languages') {
            $fields[$key] = json_decode($_COOKIE['form_' . $key], true);
        } else {
            $fields[$key] = htmlspecialchars($_COOKIE['form_' . $key]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Форма регистрации</title>
</head>
<body>
    <div class="form-container">
        <h2>Форма регистрации</h2>
        
        <?php if (isset($successMessage)): ?>
            <div class="success-message"><?= $successMessage ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <h3>Исправьте следующие ошибки:</h3>
                <ul>
                    <?php foreach ($errors as $field => $errorList): ?>
                        <?php if (is_array($errorList)): ?>
                            <?php foreach ($errorList as $error): ?>
                                <li><strong><?= getFieldLabel($field) ?>:</strong> <?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><strong><?= getFieldLabel($field) ?>:</strong> <?= htmlspecialchars($errorList) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="submit.php" method="post">
            <!-- ФИО -->
            <div class="form-group <?= isset($errors['fullname']) ? 'has-error' : '' ?>">
                <label for="fullname">ФИО:</label>
                <input type="text" id="fullname" name="fullname" required maxlength="150" 
                       value="<?= $fields['fullname'] ?>">
                <?php if (isset($errors['fullname'])): ?>
                    <span class="error-message"><?= formatErrorMessage($errors['fullname'], 'ФИО должно содержать только буквы и пробелы (максимум 150 символов)') ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Телефон -->
            <div class="form-group <?= isset($errors['phone']) ? 'has-error' : '' ?>">
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" required 
                       value="<?= $fields['phone'] ?>">
                <?php if (isset($errors['phone'])): ?>
                    <span class="error-message"><?= formatErrorMessage($errors['phone'], 'Введите телефон в формате +7 (XXX) XXX-XX-XX') ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Email -->
            <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?= $fields['email'] ?>">
                <?php if (isset($errors['email'])): ?>
                    <span class="error-message"><?= formatErrorMessage($errors['email'], 'Введите корректный email (пример: user@example.com)') ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Дата рождения -->
            <div class="form-group <?= isset($errors['dob']) ? 'has-error' : '' ?>">
                <label for="dob">Дата рождения:</label>
                <input type="date" id="dob" name="dob" required 
                       value="<?= $fields['dob'] ?>">
                <?php if (isset($errors['dob'])): ?>
                    <span class="error-message"><?= formatErrorMessage($errors['dob'], 'Введите дату в формате ДД.ММ.ГГГГ') ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Пол -->
            <div class="form-group <?= isset($errors['gender']) ? 'has-error' : '' ?>">
                <label>Пол:</label>
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" id="male" name="gender" value="male" required 
                               <?= ($fields['gender'] === 'male') ? 'checked' : '' ?>>
                        <label for="male">Мужской</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="female" name="gender" value="female" required 
                               <?= ($fields['gender'] === 'female') ? 'checked' : '' ?>>
                        <label for="female">Женский</label>
                    </div>
                </div>
                <?php if (isset($errors['gender'])): ?>
                    <span class="error-message"><?= formatErrorMessage($errors['gender'], 'Выберите пол') ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Языки программирования -->
            <div class="form-group <?= isset($errors['languages']) ? 'has-error' : '' ?>">
                <label for="languages">Любимый язык программирования (выберите один или несколько):</label>
                <select id="languages" name="languages[]" multiple="multiple" required>
                    <?php
                    $allLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                    foreach ($allLanguages as $lang): ?>
                        <option value="<?= htmlspecialchars($lang) ?>"
                            <?= in_array($lang, $fields['languages']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lang) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['languages'])): ?>
                    <span class="error-message"><?= formatErrorMessage($errors['languages'], 'Выберите хотя бы один язык программирования') ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Биография -->
            <div class="form-group <?= isset($errors['bio']) ? 'has-error' : '' ?>">
                <label for="bio">Биография:</label>
                <textarea id="bio" name="bio" rows="5" required><?= $fields['bio'] ?></textarea>
                <?php if (isset($errors['bio'])): ?>
                    <span class="error-message"><?= formatErrorMessage($errors['bio'], 'Расскажите о себе (максимум 500 символов)') ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Контракт -->
            <div class="form-group <?= isset($errors['contract']) ? 'has-error' : '' ?>">
                <div class="checkbox-group">
                    <input type="checkbox" id="contract" name="contract" required 
                           <?= $fields['contract'] ? 'checked' : '' ?>>
                    <label for="contract">С контрактом ознакомлен(а)</label>
                </div>
                <?php if (isset($errors['contract'])): ?>
                    <span class="error-message"><?= formatErrorMessage($errors['contract'], 'Необходимо подтвердить ознакомление с контрактом') ?></span>
                <?php endif; ?>
            </div>
            
            <button type="submit">Сохранить</button>
        </form>
    </div>
</body>
</html>