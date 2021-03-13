<?php
require_once('helpers.php');
require_once('functions.php');

session_start();
if (isset($_SESSION['user'])) {
    header('Location: index.php');
} else {
    $user = [];
}
/* формируем боковое меню */
$content_side = include_template('content_side_unregistered.php');

/* подключаемся к базе данных */
if (!isset($con)) {
    $con = require_once('init.php');
}

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
                return checking_email($email, $con, true);
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
/* сохраняем пользователя либо показываем ошибки */
        if (!count($errors)) {
            $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
            $sql_user_insert = 'INSERT INTO users (email, password, name) VALUES (?, ?, ?)';
            $stmt = db_get_prepare_stmt($con, $sql_user_insert, $user);
            $result = mysqli_stmt_execute($stmt);

            if ($result) {
                if(session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }
                $_SESSION['user'] = get_user($user['email'], $con);
                header('Location: index.php');
            }
        } else {
            $user['password'] = '';
            $content = include_template('register.php', ['user' => $user, 'errors' => $errors]);
        }

    }

    print(include_template('../index.php', ['con' => $con, 'content_side' => $content_side, 'content' => $content, 'user' => $user]));
}
?>
