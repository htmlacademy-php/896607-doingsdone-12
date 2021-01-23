<?php
$show_complete_tasks = rand(0, 1);
$title = 'Дела в порядке';
/* создаем переменные на случай отсутствия конктакта с базой */
$username = 'Константин';
$projects = [];
$tasks = [];
$checking_result = 0;

/* выбираем первого пользователя для примера */
$user_id = 1;

function return_error($error_code) {
    http_response_code($error_code);
}

function task_urgency($task) {
    if ($task['deadline']) {
        $cur_date = date_create('now');
        $deadline_date = date_create($task['deadline']);
        if ($deadline_date <= $cur_date) {return true;}
    }
    return false;
}

function checking_id_in_projects($connect, $id_number, $user_id) {
    if (preg_match('/\d{1,}$/', $id_number)) {
        $sql_id_checking = "SELECT id FROM projects WHERE id = '$id_number' AND user_id = '$user_id'";
        $search_result = mysqli_query($connect, $sql_id_checking);
        return mysqli_num_rows($search_result) > 0;
    }
    return false;
}

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
/* перезаписываем имя пользователя */
    $sql_username = "SELECT name FROM users WHERE id = '$user_id'";
    $result = mysqli_query($con, $sql_username);
    $username = mysqli_fetch_assoc($result)['name'];
/* перезаписываем проекты пользователя */
    /* запрос можно упростить, когда будет уверенность, что у всех задач правильные user_id */
    $sql_projects = "SELECT p.id, p.title, p.user_id, task_count
                       FROM projects p
                       LEFT JOIN
                            (SELECT project_id, COUNT(id) AS task_count
                               FROM (SELECT * FROM tasks WHERE user_id = '$user_id') AS user_tasks
                           GROUP BY project_id) AS t
                         ON p.id = project_id
                      WHERE p.user_id = '$user_id'";
    $result = mysqli_query($con, $sql_projects);
    $projects = mysqli_fetch_all($result, MYSQLI_ASSOC);
/* перезаписываем задачи пользователя с учетом параметра запроса */
    $sql_tasks = "SELECT t.title, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, p.title AS category, is_done, file_url, file_name FROM tasks t JOIN projects p ON t.project_id = p.id AND t.user_id = '$user_id'";
    if ($category) {
       $checking_result = checking_id_in_projects($con, $category, $user_id);
       if ($checking_result) {
          $sql_tasks = $sql_tasks . " AND t.project_id = $category";
       }
    } else $checking_result = 1;
    $result = mysqli_query($con, $sql_tasks);
    $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);

/* создаем страницу, если в запросе не задан несуществующий проект */
    if ($checking_result) {
        require_once('helpers.php');

        if (!isset($content)) {
            $content = include_template('main.php', ['tasks' => $tasks, 'show_complete_tasks' => $show_complete_tasks]);
        }

        $navigation = include_template('navigation.php', ['projects' => $projects, 'category' => $category]);

        $page = include_template('layout.php', ['title' => $title, 'username' => $username, 'content' => $content, 'navigation' => $navigation]);

        print($page);
    } else {
        return_error(404);
    }
}

?>
