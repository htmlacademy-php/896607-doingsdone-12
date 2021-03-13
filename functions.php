<?php
/* ДОБАВЛЕННЫЕ ФУНКЦИИ */
/* возвращаем ошибку */
function return_error($error_code) {
    http_response_code($error_code);
}

/* определяем срочность задачи */
function task_urgency($deadline) {
    $cur_date = date_format(date_create('now'), "Y-m-d");
    $deadline_date = date_format(date_create($deadline), "Y-m-d");
    if ($deadline) {
        return $deadline_date < $cur_date;
    }
    return false;
}

/* фильтр по срочности */
function filter_deadline($deadline, $day) {
    if ($day === 'overdue') {
        return task_urgency($deadline);
    } else {
        $filter_date = date_format(date_create($day), "Y-m-d");
        $deadline_date = date_format(date_create($deadline), "Y-m-d");
        if ($deadline) {
            return $filter_date === $deadline_date;
        }
    }
    return false;
}

/* проверяем наличие выбранного проекта из параметров запроса */
function checking_id_in_projects($connect, $id_number, $user_id) {
    if (filter_var($id_number, FILTER_VALIDATE_INT)) {
        $sql_id_checking = "SELECT id FROM projects WHERE id = $id_number AND user_id = $user_id";
        $search_result = mysqli_query($connect, $sql_id_checking);
        return mysqli_num_rows($search_result) > 0;
    }
    return false;
}

/* проверяем уникальность значения в заданном столбце таблицы */
function checking_uniqe_value($connect, $table, $column, $value, $conditions) {
    $sql_values = "SELECT * FROM $table WHERE $column = '" . $value . "'";
    if ($conditions) {
        $sql_values = $sql_values . " AND $conditions";
    }

    $search_result = mysqli_query($connect, $sql_values);

    return mysqli_num_rows($search_result) < 1;
}

/* проверяем валидность даты выполнения задачи */
function checking_date($date) {
    if (!$date) {
        return NULL;
    }
    if (is_date_valid($date) && !task_urgency($date)) {
        return NULL;
    }
    return 'Дата указывается в формате ГГГГ-ММ-ДД и не может быть раньше текущей даты';
}

/* проверяем наличие выбранного проекта при добавлении задачи */
function checking_project($number, $projects) {
    if (in_array($number, array_column($projects,'id'))) {
        return NULL;
    };
    return 'Не найдено указанного проекта';
}

/* проверяем название проекта */
function checking_new_project($project, $connect, $user_id) {
    if (!checking_uniqe_value($connect, 'projects', 'title', $project, "user_id = $user_id")) {
        return 'Проект с таким названием уже существует';
    }
        return NULL;
}

/* проверяем валидность email */
function checking_email($email, $connect, $is_uniqe) {
    if (!$email) {
        return NULL;
    }
    /* далее проверяем, что он мейл и что уникальный */
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'E-mail введен некорректно';
    }
    if ($is_uniqe) {
        if (!checking_uniqe_value($connect, 'users', 'email', $email, '')) {
            return 'Пользователь с таким e-mail уже зарегистрирован';
        }
    }
    return NULL;
}

/* получаем из базы данные о текущем пользователе */
function get_user($email, $connect) {
    $sql_user_search = "SELECT * FROM users WHERE email = '$email'";
    $search_result = mysqli_query($connect, $sql_user_search);
    return mysqli_fetch_assoc($search_result);
}

/* функции для сохранения текущего представления */
function remember($name, $value) {
    if(session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION[$name] = $value;
}

function keep_view() {
    $view = '';
    if(session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $category = $_SESSION['category'];
    $filter = $_SESSION['filter'];
    if ($category) {
        $view = "?category=$category";
    }
    if ($filter) {
        if ($view) {
            $view = $view . "&filter=$filter";
        } else {
            $view = "?filter=$filter";
        }
    }

    header("Location: index.php$view");
}

/* функция для изменения статуса задачи (выполнена / не выполнена) */
function invert_task_status($id, $new_status, $user_id, $connect) {
    mysqli_query($connect, "START TRANSACTION");

    $result1 = mysqli_query($connect, "SELECT * FROM tasks WHERE id = $id AND user_id = $user_id");
    $task_status = mysqli_fetch_assoc($result1)['is_done'];

    if ($task_status !== $new_status) {
        $sql_task_change = "UPDATE tasks SET is_done = $new_status WHERE id = $id";
        $result2 = mysqli_query($connect, $sql_task_change);
    }

    if ($result1 && $result2) {
      mysqli_query($connect, "COMMIT");
    }
    else {
      mysqli_query($connect, "ROLLBACK");
    }
    header('Location: index.php');
}

/* функция для изменения режима показа выполненных задач */
function invert_show_completed() {
    $show_complete_tasks = filter_input(INPUT_GET, 'show_completed', FILTER_SANITIZE_NUMBER_INT);
    remember('show_completed', $show_complete_tasks);
    keep_view();
}
?>
