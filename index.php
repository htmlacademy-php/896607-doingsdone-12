<?php
require_once('helpers.php');
require_once('functions.php');
$title = 'Дела в порядке';
$date_filters = ['today', 'tomorrow', 'overdue'];

$user = [];
$checking_result = 0;
$tasks = [];
$show_complete_tasks = 0;
$search = '';
$filter = NULL;
$category = NULL;

/* если сценарий вызывается не с готовым контентом, запускаем сессию */
if(!isset($content) && session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
/* проверяем наличие данных о пользователе в сессии */
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    if (isset($_SESSION['show_completed'])) {
        $show_complete_tasks = (int)$_SESSION['show_completed'];
    }
}

/* формируем страницу для авторизованного пользователя */
if ($user) {
    $user_id = $user['id'];

/* определяем выбранный проект */
    if (isset($_GET['category'])) {
        $category = $_GET['category'];
    }
    remember('category', $category);

/* получаем параметры поиска */
    if (isset($_GET['search'])) {
        $search = trim($_GET['search'], " ");
    }

/* проверяем наличие фильтров по сроку */
    if (isset($_GET['filter']) && in_array($_GET['filter'], $date_filters)) {
        $filter = $_GET['filter'];
    }
    remember('filter', $filter);

/* получаем данные из базы */
    if (!isset($con)) {
        $con = require_once('init.php');
    }

    if ($con) {
        mysqli_set_charset($con, 'utf8');

/* при изменении статуса задачи */
        if (isset($_GET['task_id']) && isset($_GET['check'])) {
            $task_id = filter_input(INPUT_GET, 'task_id', FILTER_SANITIZE_NUMBER_INT);
            $new_status = filter_input(INPUT_GET, 'check', FILTER_SANITIZE_NUMBER_INT);
            invert_task_status($task_id, $new_status, $user['id'], $con);
        }

        if (isset($_GET['show_completed'])) {
            invert_show_completed();
        }

/* перезаписываем проекты пользователя */
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
        $sql_tasks = "SELECT t.title, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, p.title AS category, is_done, file_url, file_name, t.id FROM tasks t JOIN projects p ON t.project_id = p.id AND t.user_id = $user_id";
        if (isset($category)) {
           $checking_result = checking_id_in_projects($con, $category, $user_id);
           if ($checking_result) {
              $sql_tasks = $sql_tasks . " AND t.project_id = $category";
           }
        } else {
            $checking_result = 1;
        }
/* добавляем условие поиска, если оно задано */
        if ($search) {
            $search = mysqli_real_escape_string($con, $search);
            $sql_tasks = $sql_tasks . " AND MATCH t.title AGAINST ('$search')";
        }

        $result = mysqli_query($con, $sql_tasks);
        $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
/* создаем контент для пользователя */
        if ($checking_result) {
            if (!isset($content)) {
                $content = include_template('main.php', ['tasks' => $tasks, 'show_complete_tasks' => $show_complete_tasks, 'search' => $search, 'filter' => $filter, 'category' => $category]);
            }
            $content_side = include_template('navigation.php', ['projects' => $projects, 'category' => $category]);
        }
    }
}

/* создаем контент для анонимного пользователя */
if (!isset($content)) {
    $content = include_template('guest.php');
}
if (!isset($content_side)) {
    $content_side = NULL;
}

/* создаем страницу */
if (!$user || $checking_result) {
    $page = include_template('layout.php', ['title' => $title, 'user' => $user, 'content' => $content, 'content_side' => $content_side]);

    print($page);
} else {
    return_error(404);
}
?>
