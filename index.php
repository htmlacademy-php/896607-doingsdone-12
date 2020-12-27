<?php
$show_complete_tasks = rand(0, 1);
$title = 'Дела в порядке';
/* создаем переменные на случай отсутствия конктакта с базой */
$username = 'Константин';
$projects = [];
$tasks = [];

/* выбираем первого пользователя для примера */
$user_id = 1;
$con = mysqli_connect('localhost', 'root', 'root','doingsdone');
if ($con) {
    mysqli_set_charset($con, 'utf8');
/* перезаписываем имя пользователя */
    $sql_username = "SELECT name FROM users WHERE id = '$user_id'";
    $result = mysqli_query($con, $sql_username);
    $username = mysqli_fetch_assoc($result)['name'];
/* перезаписываем проекты пользователя */
    $sql_projects = "SELECT * FROM projects WHERE user_id = '$user_id'";
    $result = mysqli_query($con, $sql_projects);
    $projects = mysqli_fetch_all($result, MYSQLI_ASSOC);
/* перезаписываем задачи пользователя */
    $sql_tasks = "SELECT t.title, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, p.title AS category, is_done FROM tasks t JOIN projects p ON t.project_id = p.id AND t.user_id = '$user_id'";
    $result = mysqli_query($con, $sql_tasks);
    $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/* без дополнительного обращения к базе */
function task_count($tasks, $project)
{
    $quantity = 0;
    foreach ($tasks as $key => $task) {
        if ($task['category'] === $project) {
            $quantity++;
        }
    }
    return $quantity;
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

require('helpers.php');

$content = include_template('main.php', ['projects' => $projects, 'tasks' => $tasks, 'show_complete_tasks' => $show_complete_tasks ]);
$page = include_template('layout.php', ['title' => $title, 'username' => $username, 'content' => $content]);

print($page);
?>
