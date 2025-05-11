<?php
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

$errors = [];
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

// Получаем данные из POST или из cookies
foreach ($fields as $key => $value) {
    if (isset($_POST[$key])) {
        $fields[$key] = $_POST[$key];
    } elseif (isset($_COOKIE['form_' . $key])) {
        $fields[$key] = $_COOKIE['form_' . $key];
        if ($key === 'languages') {
            $fields[$key] = json_decode($_COOKIE['form_' . $key], true);
        }
    }
}

// Валидация данных
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
} elseif (!in_array($fields['gender'], ['male', 'female', 'other'])) {
    $errors['gender'] = "Некорректный пол.";
}

if (isset($fields['languages']) && count($fields['languages']) === 0) {
    $errors['languages'] = "Выберите хотя бы один язык программирования.";
}

if (isset($fields['bio']) && strlen($fields['bio']) > 500) {
    $errors['bio'] = "Биография не должна превышать 500 символов.";
}

if (empty($fields['contract'])) {
    $errors['contract'] = "Необходимо ознакомиться с контрактом.";
}

// Если есть ошибки, сохраняем их в cookies и перенаправляем обратно
if (count($errors) > 0) {
    // Сохраняем данные формы в cookies на сессию
    foreach ($fields as $key => $value) {
        if ($key === 'languages') {
            setcookie('form_' . $key, json_encode($value), 0, '/');
        } else {
            setcookie('form_' . $key, $value, 0, '/');
        }
    }
    
    // Сохраняем ошибки в cookies
    setcookie('form_errors', json_encode($errors), 0, '/');
    
    // Перенаправляем обратно на форму
    header('Location: index.html');
    exit;
}

// Если ошибок нет, сохраняем данные в БД и в cookies на год
$fullname = trim($fields['fullname']);
$nameParts = explode(' ', $fullname);
$last_name = $nameParts[0] ?? '';
$first_name = $nameParts[1] ?? '';
$patronymic = $nameParts[2] ?? null;

try {
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

    // Сохраняем данные в cookies на год
    foreach ($fields as $key => $value) {
        if ($key === 'languages') {
            setcookie('form_' . $key, json_encode($value), time() + (365 * 24 * 60 * 60), '/');
        } else {
            setcookie('form_' . $key, $value, time() + (365 * 24 * 60 * 60), '/');
        }
    }
    
    // Удаляем ошибки, если они были
    setcookie('form_errors', '', time() - 3600, '/');
    
    echo "<p style='color: green;'>Данные успешно сохранены!</p>";

} catch (PDOException $e) {
    die("Ошибка при сохранении данных: " . $e->getMessage());
}

// Вывод списка заявок (остается без изменений)
try {
    $stmt = $db->query("SELECT a.id, a.first_name, a.last_name, a.patronymic, a.phone, a.email, a.bio, GROUP_CONCAT(pl.name SEPARATOR ', ') AS languages 
                        FROM applications a 
                        LEFT JOIN application_languages al ON a.id = al.application_id 
                        LEFT JOIN programming_languages pl ON al.language_id = pl.id 
                        GROUP BY a.id");
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($applications) > 0) {
        echo "<h2>Список заявок</h2>";
        echo "<table border='1' cellpadding='10' cellspacing='0'>";
        echo "<tr><th>ID</th><th>Фамилия</th><th>Имя</th><th>Отчество</th><th>Телефон</th><th>Email</th><th>Биография</th><th>Языки</th></tr>";
        foreach ($applications as $app) {
            echo "<tr>";
            echo "<td>{$app['id']}</td>";
            echo "<td>{$app['last_name']}</td>";
            echo "<td>{$app['first_name']}</td>";
            echo "<td>{$app['patronymic']}</td>";
            echo "<td>{$app['phone']}</td>";
            echo "<td>{$app['email']}</td>";
            echo "<td>" . nl2br(htmlspecialchars($app['bio'])) . "</td>";
            echo "<td>{$app['languages']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Заявок нет.</p>";
    }
} catch (PDOException $e) {
    die("Ошибка получения данных: " . $e->getMessage());
}
?>