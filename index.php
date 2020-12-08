<?php
// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);
$title = 'Дела в порядке';
$username = 'Константин';
$projects = ['Входящие', 'Учеба', 'Работа', 'Домашние дела', 'Авто'];
$tasks = [
    [
        'task' => 'Собеседование в IT компании',
        'date' => '01.12.2019',
        'category' => 'Работа',
        'is_done' => false
    ],
    [
        'task' => 'Выполнить тестовое задание',
        'date' => '25.12.2019',
        'category' => 'Работа',
        'is_done' => false
    ],
    [
        'task' => 'Сделать задание первого раздела',
        'date' => '21.12.2019',
        'category' => 'Учеба',
        'is_done' => true
    ],
    [
        'task' => 'Встреча с другом',
        'date' => '22.12.2019',
        'category' => 'Входящие',
        'is_done' => false
    ],
    [
        'task' => 'Купить корм для кота',
        'date' => null,
        'category' => 'Домашние дела',
        'is_done' => false
    ],
    [
        'task' => 'Заказать пиццу',
        'date' => null,
        'category' => 'Домашние дела',
        'is_done' => false
    ]
];
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

require('helpers.php');

$content = include_template('main.php', ['projects' => $projects, 'tasks' => $tasks, 'show_complete_tasks' => $show_complete_tasks ]);
$page = include_template('layout.php', ['title' => $title, 'username' => $username, 'content' => $content]);

print($page);
?>
