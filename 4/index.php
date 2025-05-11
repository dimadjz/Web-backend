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
                <h3>Ошибки в форме:</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <?php if (is_array($error)): ?>
                            <?php foreach ($error as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
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
                    <span class="error-message"><?php echo htmlspecialchars($errors['fullname']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group <?php echo isset($errors['phone']) ? 'has-error' : ''; ?>">
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" required 
                       value="<?php echo $fields['phone']; ?>">
                <?php if (isset($errors['phone'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['phone']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo $fields['email']; ?>">
                <?php if (isset($errors['email'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['email']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group <?php echo isset($errors['dob']) ? 'has-error' : ''; ?>">
                <label for="dob">Дата рождения:</label>
                <input type="date" id="dob" name="dob" required 
                       value="<?php echo $fields['dob']; ?>">
                <?php if (isset($errors['dob'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['dob']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group <?php echo isset($errors['gender']) ? 'has-error' : ''; ?>">
                <label>Пол:</label>
                <div>
                    <input type="radio" id="male" name="gender" value="male" required 
                           <?php echo ($fields['gender'] === 'male') ? 'checked' : ''; ?>>
                    <label for="male">Мужской</label>
                    <input type="radio" id="female" name="gender" value="female" required 
                           <?php echo ($fields['gender'] === 'female') ? 'checked' : ''; ?>>
                    <label for="female">Женский</label>
                </div>
                <?php if (isset($errors['gender'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['gender']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group <?php echo isset($errors['languages']) ? 'has-error' : ''; ?>">
                <label for="languages">Любимый язык программирования:</label>
                <select id="languages" name="languages[]" multiple="multiple" required>
                    <?php
                    $allLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                    foreach ($allLanguages as $lang): ?>
                        <option value="<?php echo htmlspecialchars($lang); ?>"
                            <?php echo in_array($lang, $fields['languages']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lang); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['languages'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['languages']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group <?php echo isset($errors['bio']) ? 'has-error' : ''; ?>">
                <label for="bio">Биография:</label>
                <textarea id="bio" name="bio" rows="5" required><?php echo $fields['bio']; ?></textarea>
                <?php if (isset($errors['bio'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['bio']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group <?php echo isset($errors['contract']) ? 'has-error' : ''; ?>">
                <input type="checkbox" id="contract" name="contract" required 
                       <?php echo $fields['contract'] ? 'checked' : ''; ?>>
                <label for="contract">С контрактом ознакомлен(а)</label>
                <?php if (isset($errors['contract'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['contract']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <button type="submit">Сохранить</button>
            </div>
        </form>
    </div>
</body>
</html>