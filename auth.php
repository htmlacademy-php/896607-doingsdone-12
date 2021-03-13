<?php
require_once('helpers.php');
require_once('functions.php');

if(session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (isset($_SESSION['user'])) {
    header('Location: index.php');
} else {
    $user = [];
}

$form_error_message = 'Пожалуйста, исправьте ошибки в форме';

/* формируем боковое меню */
$content_side = include_template('content_side_unregistered.php');

/* подключаемся к базе данных */
if (!isset($con)) {
    $con = require_once('init.php');
}

if ($con) {
    mysqli_set_charset($con, 'utf8');

    $content = include_template('auth.php');

/* при попытке отправки формы */
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
/* валидация*/
        $required = ['email', 'password'];
        $errors = [];

        $rules = [
            'email' => function($email) use ($con) {
                return checking_email($email, $con, false);
            }
        ];

        $likely_user = filter_input_array(INPUT_POST, ['email' => FILTER_DEFAULT, 'password' => FILTER_DEFAULT], true);

        foreach ($likely_user as $key => $value) {
            if (isset($rules[$key])) {
                $rule = $rules[$key];
                $errors[$key] = $rule($value);
            }
            if (empty($value)) {
                if (in_array($key, $required)) {
                    $errors[$key] = 'Это поле должно быть заполнено';
                }
                $likely_user[$key] = NULL;
            }
        }

        $errors = array_filter($errors);

/* проверяем пароль */
        if (!count($errors)) {
            $mail = $likely_user['email'];
            $user = get_user($mail, $con);

            if (password_verify($likely_user['password'], $user['password'])) {
/* записываем в сессию */
                if(session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }
                $_SESSION['user'] = $user;
                header('Location: index.php');
            } else {
                $form_error_message = 'Вы ввели неверный email/пароль';
                $errors['password'] = 'Это поле должно быть заполнено';
            }
        }
        $likely_user['password'] = '';
        $content = include_template('auth.php', ['likely_user' => $likely_user, 'errors' => $errors, 'form_error_message' => $form_error_message]);


/* вывод ошибки */
    }
    print(include_template('../index.php', ['con' => $con, 'content_side' => $content_side, 'content' => $content, 'user' => $user]));
}
?>
