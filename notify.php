<?php
require_once('vendor/autoload.php');

$con = require_once('init.php');

if ($con) {
    mysqli_set_charset($con, 'utf8');

/* получаем задачи с данными пользователей */
    $today = date_format(date_create('now'), "Y-m-d");

    $sql_tasks_list = "SELECT t.id, title, user_id, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline_day, name, email FROM tasks t LEFT JOIN users u ON u.id = user_id WHERE is_done = 0 AND deadline = '$today'";
    $result = mysqli_query($con, $sql_tasks_list);
    $urgent_tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);

/* формируем массив данных для отправки сообщений */
    $letters = [];
    $users = array_column($urgent_tasks, 'user_id');
    $users = array_unique($users);

    foreach ($users as $user) {
        $filter_user = function($task) use ($user) {
            return $task['user_id'] === $user;
        };

        $users_urgent_tasks = array_filter($urgent_tasks, $filter_user);

        $tasks_list = '';
        foreach ($users_urgent_tasks as $urgent_task) {
            $title = $urgent_task['title'];
            $date = $urgent_task['deadline_day'];
            $tasks_list = $tasks_list . "задача «" . $title . "» на $date, ";
        }
        $tasks_list = substr($tasks_list, 0, -2);
        $first_key = array_key_first($users_urgent_tasks);
        $name = $users_urgent_tasks[$first_key]['name'];

        $letter = [];
        $letter['email'] = $users_urgent_tasks[$first_key]['email'];
        $letter['user'] = $name;
        $letter['text'] = "Уважаемый $name. У вас запланирована $tasks_list";

        array_push($letters, $letter);
    }

/* отправляем сообщения */
    $success_mail_count = 0;
    $error_mail_count = 0;

    foreach ($letters as $mail) {
        $transport = new Swift_SmtpTransport("smtp.mailtrap.io", 25);
        $transport->setUsername("f099927c8fda1b");
        $transport->setPassword("14496f60f41c19");
        $mailer = new Swift_Mailer($transport);
        $message = new Swift_Message();
        $message->setSubject("Уведомление от сервиса «Дела в порядке»");
        $message->setFrom(['keks@phpdemo.ru' => 'Дела в порядке']);
        $message->addTo($mail['email'],$mail['user']);

        $message->setBody($mail['text'], 'text/plain');

        $result = $mailer->send($message);

        if ($result) {
            $success_mail_count = $success_mail_count +1;
        } else {
            $error_mail_count = $error_mail_count + 1;
        }
    }

    print("Успешно отправлено писем: $success_mail_count, ошибки при отправке: $error_mail_count");
}
?>
