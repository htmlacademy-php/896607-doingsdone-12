<?php
$show_complete_tasks = rand(0, 1);
$title = 'Дела в порядке';
/* проверяем сессию */
if(session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
} else {
    $user = [];
}

require_once('helpers.php');

/* формируем страницу для авторизованного пользователя */
if ($user) {
    $user_id = $user['id'];

/* определяем выбранный проект */
    if (isset($_GET['category'])) {
        $category = $_GET['category'];
    } else {
        $category = '';
    }

/* получаем данные из базы */
    if (!isset($db)) {
        $db = require_once('config/db.php');
    }

    $con = mysqli_connect($db['host'], $db['user'], $db['password'], $db['database']);

    if ($con) {
        mysqli_set_charset($con, 'utf8');

/* перезаписываем проекты пользователя */
    /* запрос можно упростить, когда будет уверенность, что у всех задач правильные user_id */
        $sql_projects = "SELECT p.id, p.title, p.user_id, task_count
                           FROM projects p
                           LEFT JOIN
                                (SELECT project_id, COUNT(id) AS task_count
                                   FROM (SELECT * FROM tasks WHERE user_id = $user_id) AS user_tasks
                               GROUP BY project_id) AS t
                             ON p.id = project_id
                          WHERE p.user_id = '$user_id'";
        $result = mysqli_query($con, $sql_projects);
        $projects = mysqli_fetch_all($result, MYSQLI_ASSOC);
/* перезаписываем задачи пользователя с учетом параметра запроса */
        $sql_tasks = "SELECT t.title, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, p.title AS category, is_done, file_url, file_name FROM tasks t JOIN projects p ON t.project_id = p.id AND t.user_id = $user_id";
        if ($category) {
           $checking_result = checking_id_in_projects($con, $category, $user_id);
           if ($checking_result) {
              $sql_tasks = $sql_tasks . " AND t.project_id = $category";
           }
        } else {
            $checking_result = 1;
        }
        $result = mysqli_query($con, $sql_tasks);
        $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
/* создаем контент для пользователя */
        if ($checking_result) {
            if (!isset($content)) {
                $content = include_template('main.php', ['tasks' => $tasks, 'show_complete_tasks' => $show_complete_tasks]);
            }
            $content_side = include_template('navigation.php', ['projects' => $projects, 'category' => $category]);
        } else {
            return_error(404);
        }
    }
} else {
/* создаем контент для анонимного пользователя */
    if (!isset($content)) {
        $content = include_template('guest.php');
    }
    if (!isset($content_side)) {
        $content_side = NULL;
    }
}

/* создаем страницу */
$page = include_template('layout.php', ['title' => $title, 'user' => $user, 'content' => $content, 'content_side' => $content_side]);

print($page);

?>
