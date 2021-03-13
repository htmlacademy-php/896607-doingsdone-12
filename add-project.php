<?php
require_once('helpers.php');
require_once('functions.php');

/* проверяем сессию, анонимного пользователя переадресуем на вход */
session_start();
if (isset($_SESSION['user'])) {
$user = $_SESSION['user'];
$user_id = $user['id'];
} else {
    header('Location: index.php');
}

/* подключаемся к базе данных */
if (!isset($con)) {
    $con = require_once('init.php');
}

if ($con) {
    mysqli_set_charset($con, 'utf8');

/* создаем форму */
    $content = include_template('add-project.php');

/* при попытке отправки формы */
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
/* валидация полученных данных */
        $required = ['name'];
        $errors = [];

        $rules = [
            'name' => function($value) use ($con, $user_id) {
                return checking_new_project($value, $con, $user_id);
            }
        ];

        $new_project = filter_input_array(INPUT_POST, ['name' => FILTER_DEFAULT], true);

        foreach ($new_project as $key => $value)  {
            if (isset($rules[$key])) {
                $rule = $rules[$key];
                $errors[$key] = $rule($value);
            }
            if (empty($value)) {
                if (in_array($key, $required)) {
                    $errors[$key] = 'Это поле должно быть заполнено';
                }
                $task[$key] = NULL;
            }
        }

        $errors = array_filter($errors);

        if (!count($errors)) {
    /* сохраняем проект */
            $new_project['user'] = $user_id;
            $sql_project_insert = 'INSERT INTO projects (title, user_id) VALUES (?, ?)';
             $stmt = db_get_prepare_stmt($con, $sql_project_insert, $new_project);
             $result = mysqli_stmt_execute($stmt);
    /* переадресация при успехе */
            if ($result) {
                header('Location: index.php');
            }
        } else {
            $content = include_template('add-project.php', ['new_project' => $new_project, 'errors' => $errors]);
        }
    }
    print(include_template('../index.php', ['con' => $con,'content' => $content]));

}
?>
