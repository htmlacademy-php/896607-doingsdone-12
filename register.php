<?php
require_once('helpers.php');

/* временно, пока не сделали нормальный вход на сайт */
    $is_user = false;

/* формируем боковое меню */
    $content_side = include_template('content_side_unregistered.php');

/* подключаемся к базе данных */
if (!isset($db)) {
    $db = require_once('config/db.php');
}

$con = mysqli_connect($db['host'], $db['user'], $db['password'], $db['database']);

if ($con) {
    mysqli_set_charset($con, 'utf8');

    $content = include_template('register.php');

/* при попытке отправки формы */
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
/* валидация полученных данных */
        $required = ['email', 'password', 'name'];
        $errors = [];

        $rules = [
            'email' => function($email) use ($con) {
                return checking_email($email, $con);
            }
        ];

        $user = filter_input_array(INPUT_POST, ['email' => FILTER_DEFAULT, 'password' => FILTER_DEFAULT, 'name' => FILTER_DEFAULT], true);

        foreach ($user as $key => $value) {
            if (isset($rules[$key])) {
                $rule = $rules[$key];
                $errors[$key] = $rule($value);
            }
            if (empty($value)) {
                if (in_array($key, $required)) {
                    $errors[$key] = 'Это поле должно быть заполнено';
                }
                $user[$key] = NULL;
            }
        }

        $errors = array_filter($errors);
/* сохраняем задачу либо показываем ошибки */
        if (!count($errors)) {
            $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
            $sql_user_insert = 'INSERT INTO users (email, password, name) VALUES (?, ?, ?)';
            $stmt = db_get_prepare_stmt($con, $sql_user_insert, $user);
            $result = mysqli_stmt_execute($stmt);

            if ($result) {
                header('Location: index.php');
            }
        } else {
            $user['password'] = '';
            $content = include_template('register.php', ['user' => $user, 'errors' => $errors]);
        }

    }

    print(include_template('../index.php', ['db' => $db, 'content_side' => $content_side, 'content' => $content, 'is_user' => $is_user]));
}
?>
