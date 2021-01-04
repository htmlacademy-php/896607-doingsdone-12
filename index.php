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

/* определяем выбранный проект */
if (isset($_GET['category'])) {
    $category = $_GET['category'];
} else {
    $category = '';
}

function return_error($error_code) {
    http_response_code($error_code);
}

function task_urgency($task)
{
    if ($task['deadline']) {
        $cur_date = date_create('now');
        $deadline_date = date_create($task['deadline']);
        if ($deadline_date <= $cur_date) {return true;}
    }
    return false;
}

/* получаем данные из базы */
$con = mysqli_connect('localhost', 'root', 'root','doingsdone');
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
/* перезаписываем задачи пользователя */
    $sql_tasks = "SELECT t.title, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, p.title AS category, is_done FROM tasks t JOIN projects p ON t.project_id = p.id AND t.user_id = '$user_id'";
/* проверяем наличие и действительность парамента запроса номера проекта */
    if ($category) {
        if (preg_match('/\d{1,}$/', $category)) {
            $sql_project_checking = "SELECT id FROM projects WHERE id = '$category'";
            $checking_result = mysqli_query($con, $sql_project_checking);
            if (mysqli_num_rows($checking_result) > 0) {
                $sql_tasks = $sql_tasks . " AND t.project_id = $category";
                $checking_result = 1;
            } else {
                $checking_result = 0;
            }
        } else {
            $checking_result = 0;
        }
    } else {
        $checking_result = 1;
    }
    $result = mysqli_query($con, $sql_tasks);
    $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
/* создаем страницу, если в запросе не задан несуществующий проект */
    if ($checking_result) {
        require('helpers.php');

        $content = include_template('main.php', ['projects' => $projects, 'tasks' => $tasks, 'show_complete_tasks' => $show_complete_tasks, 'category' => $category]);
        $page = include_template('layout.php', ['title' => $title, 'username' => $username, 'content' => $content]);

        print($page);
    } else {
        return_error(404);
    }
}
?>
