<?php
// Включение отображения ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user = 'u68841';
$pass = '3842702';

try {
    $db = new PDO('mysql:host=localhost;dbname=u68841', $user, $pass, [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

// Инициализация массива для полей формы
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

// Получаем данные из POST
foreach ($fields as $key => $value) {
    if (isset($_POST[$key])) {
        $fields[$key] = $_POST[$key];
    }
}

// Валидация данных
$errors = [];

if (empty($fields['fullname'])) {
    $errors['fullname'] = "ФИО обязательно.";
} elseif (!preg_match("/^[a-zA-Zа-яА-Я\s]{1,150}$/u", $fields['fullname'])) {
    $errors['fullname'] = "ФИО должно содержать только буквы и пробелы, не более 150 символов.";
}

if (empty($fields['phone'])) {
    $errors['phone'] = "Телефон обязателен.";
} elseif (!preg_match("/^\+?[0-9\s\-()]{7,15}$/", $fields['phone'])) {
    $errors['phone'] = "Телефон должен содержать только цифры, пробелы, скобки и дефисы (7-15 символов).";
}

if (empty($fields['email'])) {
    $errors['email'] = "Email обязателен.";
} elseif (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Некорректный email. Пример: example@domain.com";
}

if (empty($fields['dob'])) {
    $errors['dob'] = "Дата рождения обязательна.";
} elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fields['dob'])) {
    $errors['dob'] = "Дата рождения должна быть в формате ГГГГ-ММ-ДД.";
}

if (empty($fields['gender'])) {
    $errors['gender'] = "Пол обязателен.";
} elseif (!in_array($fields['gender'], ['male', 'female'])) {
    $errors['gender'] = "Некорректный пол.";
}

if (empty($fields['languages']) || count($fields['languages']) === 0) {
    $errors['languages'] = "Выберите хотя бы один язык программирования.";
}

if (empty($fields['bio'])) {
    $errors['bio'] = "Биография обязательна.";
} elseif (strlen($fields['bio']) > 500) {
    $errors['bio'] = "Биография не должна превышать 500 символов.";
}

if (empty($fields['contract'])) {
    $errors['contract'] = "Необходимо ознакомиться с контрактом.";
}

// Если есть ошибки - сохраняем данные в cookies и возвращаем на форму
if (count($errors) > 0) {
    // Сохраняем данные формы в cookies
    foreach ($fields as $key => $value) {
        if ($key === 'languages') {
            setcookie('form_' . $key, json_encode($value), time() + 3600, '/');
        } else {
            setcookie('form_' . $key, $value, time() + 3600, '/');
        }
    }
    
    // Сохраняем ошибки в cookies
    setcookie('form_errors', json_encode($errors), time() + 3600, '/');
    
    // Перенаправляем обратно на форму
    header('Location: index.php');
    exit;
}

// Если ошибок нет - сохраняем в БД
try {
    $fullname = trim($fields['fullname']);
    $nameParts = explode(' ', $fullname);
    $last_name = $nameParts[0] ?? '';
    $first_name = $nameParts[1] ?? '';
    $patronymic = $nameParts[2] ?? null;

    $stmt = $db->prepare("INSERT INTO applications (first_name, last_name, patronymic, phone, email, dob, gender, bio) 
                        VALUES (:first_name, :last_name, :patronymic, :phone, :email, :dob, :gender, :bio)");
    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':patronymic' => $patronymic,
        ':phone' => $fields['phone'],
        ':email' => $fields['email'],
        ':dob' => $fields['dob'],
        ':gender' => $fields['gender'],
        ':bio' => $fields['bio']
    ]);

    $applicationId = $db->lastInsertId();
    
    foreach ($fields['languages'] as $language) {
        $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) 
                            VALUES (:application_id, (SELECT id FROM programming_languages WHERE name = :language))");
        $stmt->execute([
            ':application_id' => $applicationId,
            ':language' => $language
        ]);
    }

    // Очищаем cookies с ошибками
    setcookie('form_errors', '', time() - 3600, '/');
    
    // Сохраняем данные в cookies на будущее (на год)
    foreach ($fields as $key => $value) {
        if ($key === 'languages') {
            setcookie('form_' . $key, json_encode($value), time() + (365 * 24 * 60 * 60), '/');
        } else {
            setcookie('form_' . $key, $value, time() + (365 * 24 * 60 * 60), '/');
        }
    }
    
    // Перенаправляем с сообщением об успехе
    setcookie('form_success', 'Данные успешно сохранены!', time() + 60, '/');
    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    // В случае ошибки БД сохраняем сообщение об ошибке
    setcookie('form_errors', json_encode(['db_error' => 'Ошибка при сохранении данных']), time() + 3600, '/');
    header('Location: index.php');
    exit;
}
?>