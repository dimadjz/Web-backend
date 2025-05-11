<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма регистрации</title>
    <link rel="stylesheet" href="styles.css">
    <?php
    // Отображение сообщения об успехе
    if (isset($_COOKIE['form_success'])) {
        echo '<div class="success-message">'.htmlspecialchars($_COOKIE['form_success']).'</div>';
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
</head>
<body>
    <div class="form-container">
        <h2>Форма регистрации</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <h3>Исправьте следующие ошибки:</h3>
                <ul>
                    <?php foreach ($errors as $field => $errorList): ?>
                        <?php if (is_array($errorList)): ?>
                            <?php foreach ($errorList as $error): ?>
                                <li><strong><?php echo getFieldLabel($field); ?>:</strong> <?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><strong><?php echo getFieldLabel($field); ?>:</strong> <?php echo htmlspecialchars($errorList); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="submit.php" method="post">
            <div class="form-group <?php echo isset($errors['fullname']) ? 'has-error' : ''; ?>">
                <label for="fullname">ФИО:</label>
                <input type="text" id="fullname" name="fullname" required maxlength="150" 
                       value="<?php echo $fields['fullname']; ?>">
                <?php if (isset($errors['fullname'])): ?>
                    <span class="error-message"><?php echo formatErrorMessage($errors['fullname'], 'ФИО должно содержать только буквы и пробелы (максимум 150 символов)'); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group <?php echo isset($errors['phone']) ? 'has-error' : ''; ?>">
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" required 
                       value="<?php echo $fields['phone']; ?>">
                <?php if (isset($errors['phone'])): ?>
                    <span class="error-message"><?php echo formatErrorMessage($errors['phone'], 'Введите телефон в формате +7 (XXX) XXX-XX-XX'); ?></span>
                <?php endif; ?>
            </div>
            
            
            <div class="form-group">
                <button type="submit">Сохранить</button>
            </div>
        </form>
    </div>
    
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
    ?>
</body>
</html>