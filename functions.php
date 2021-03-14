<?php
/* ДОБАВЛЕННЫЕ ФУНКЦИИ */
/**
* Отображает на странице ошибку на основе кода ошибки
*
* @param int $error_code Код ошибки
*/
function return_error($error_code) {
    http_response_code($error_code);
}

/**
* Определяет срочность задачи (срочной считается задача, до срока выполнения которой осталось не более 24 часов)
*
* @param string $deadline Дата выполнения задачи в виде сроки
*
* @return bool true при наличии $deadline, до которого осталось не более 24 часов, иначе false
*/
function task_urgency($deadline) {
    $cur_date = date_format(date_create('now'), "Y-m-d");
    $deadline_date = date_format(date_create($deadline), "Y-m-d");
    if ($deadline) {
        return $deadline_date <= $cur_date;
    }
    return false;
}

/**
* Определяет срочность задачи (срочной считается задача, до срока выполнения которой осталось не более 24 часов)
*
* @param string $deadline Дата выполнения задачи в виде сроки
* @param string $day Выбранный для отображения фильтр в виде строки
*
* @return bool true при наличии $deadline,соответствующего выбранному фильтру, иначе false
*/
function filter_deadline($deadline, $day) {
    if ($deadline) {
        $deadline_date = date_format(date_create($deadline), "Y-m-d");
        if ($day === 'overdue') {
            $filter_date = date_format(date_create('yesterday'), "Y-m-d");
            return $filter_date >= $deadline_date;
        }
        if ($day === 'today' || $day === 'tomorrow') {
            $filter_date = date_format(date_create($day), "Y-m-d");
            return $filter_date === $deadline_date;
        }
    }

    return false;
}

/**
* Определяет наличие проекта с заданным id у пользователя с заданным id
*
* @param $connect mysqli Ресурс соединения
* @param int $id_number id проекта
* @param int $user_id id пользователя
*
* @return bool true при наличии проекта с заданным id у пользователя с заданным id, иначе false
*/
function checking_id_in_projects($connect, $id_number, $user_id) {
    if (filter_var($id_number, FILTER_VALIDATE_INT)) {
        $sql_id_checking = "SELECT id FROM projects WHERE id = $id_number AND user_id = $user_id";
        $search_result = mysqli_query($connect, $sql_id_checking);
        return mysqli_num_rows($search_result) > 0;
    }
    return false;
}

/**
* Проверяе наличие аналогичного переданному значения в заданном столбце таблицы, с учетом дополнительных условий отбора значений (например, у пользователя с заданным id)
*
* @param $connect mysqli Ресурс соединения
* @param string $table Название таблицы
* @param string $column Название столбца
* @param string $value Значение
* @param string $column Название столбца
* @param string $conditions Дополнительные условия отбора значений
*
* @return bool true при отсутствии аналогичного значения в заданном столбце (с учетом дополнительных условий), иначе false
*/
function checking_uniqe_value($connect, $table, $column, $value, $conditions) {
    $sql_values = "SELECT * FROM $table WHERE $column = '" . $value . "'";
    if ($conditions) {
        $sql_values = $sql_values . " AND $conditions";
    }

    $search_result = mysqli_query($connect, $sql_values);

    return mysqli_num_rows($search_result) < 1;
}

/**
* Проверяет правильность указания срока выполнения задачи - дата должна быть валидна (соответствовать формату 'ГГГГ-ММ-ДД') и не должна относиться к прошедшему времени
*
* @param string $date Дата в формате 'ГГГГ-ММ-ДД'
*
* @return string Текст ошибки в случае невыполнения условий (невалидная или прошедшая дата), иначе NULL
*/
function checking_date($date) {
    if (!$date) {
        return NULL;
    }
    if (is_date_valid($date) && !filter_deadline($date, 'overdue')) {
        return NULL;
    }
    return 'Дата указывается в формате ГГГГ-ММ-ДД и не может быть раньше текущей даты';
}

/**
* Проверяет наличие выбранного номера в столбце id массива проектов
*
* @param int $number Номер проекта
* @param array $projects Массив проектов, содержащий столбец id
*
* @return string Текст ошибки в случае отсутствия проекта с выбранным номером, иначе NULL
*/
function checking_project($number, $projects) {
    if (in_array($number, array_column($projects,'id'))) {
        return NULL;
    };
    return 'Не найдено указанного проекта';
}

/**
* Проверяет уникальность названия проекта для данного пользователя
*
* @param string $project Название проекта
* @param $connect mysqli Ресурс соединения
* @param int $user_id Номер id пользователя
*
* @return string Текст ошибки в случае неуникального названия, иначе NULL
*/
function checking_new_project($project, $connect, $user_id) {
    if (!checking_uniqe_value($connect, 'projects', 'title', $project, "user_id = $user_id")) {
        return 'Проект с таким названием уже существует';
    }
        return NULL;
}

/**
* Проверяет валидность (опциально - уникальность) переданного электронного адреса
*
* @param string $email Электронный адрес
* @param $connect mysqli Ресурс соединения
* @param bool $is_uniqe Указание на необходимость проверки уникальности (true)
*
* @return string Текст ошибки в случае невалидного (при проверке уникальности - и/или неуникального) названия, иначе NULL
*/
function checking_email($email, $connect, $is_uniqe) {
    if (!$email) {
        return NULL;
    }

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

/**
* Возвращает данные о зарегистрированном пользователе с указанным email
*
* @param string $email Электронный адрес
* @param $connect mysqli Ресурс соединения
*
* @return array Массив данных о пользователе с указанным email, если такой существует, иначе NULL
*/
function get_user($email, $connect) {
    $sql_user_search = "SELECT * FROM users WHERE email = '$email'";
    $search_result = mysqli_query($connect, $sql_user_search);
    if ($search_result) {
        return mysqli_fetch_assoc($search_result);
    }
    return NULL;
}

/**
* Сохранение заданного параметра в сессию при наличии активной сессии, иначе переадресация на страницу авторизации
*
* @param string $name Название параметра
* @param string $value Значение параметра
*/
function remember($name, $value) {
    if(session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION[$name] = $value;
    } else {
        header('Location: auth.php');
    }
}

/**
* Сохранение выбранного представления при изменениях, требующих перезагрузки страницы, при наличии активной сессии, иначе - переадресация на страницу авторизации
*/
function keep_view() {
    $view = '';
    if(session_status() === PHP_SESSION_ACTIVE) {
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
    } else {
        header('Location: auth.php');
    }
}

/**
* Инвертирование статуса задачи при клике по соответствующему чекбоксу (выполнена / не выполнена), перезагружает страницу после попытки изменения статуса
*
* @param int $id Номер id задачи
* @param int $new_status Новый статус задачи
* @param int $user_id Номер id пользователя
* @param $connect mysqli Ресурс соединения
*/
function invert_task_status($id, $new_status, $user_id, $connect) {
    mysqli_query($connect, "START TRANSACTION");

    $result1 = mysqli_query($connect, "SELECT * FROM tasks WHERE id = $id AND user_id = $user_id");
    $result2 = NULL;
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

/**
* Изменение выбранного режима показа/сокрытия выполненых задач с сохранением текущего представления
*/
function invert_show_completed() {
    $show_complete_tasks = filter_input(INPUT_GET, 'show_completed', FILTER_SANITIZE_NUMBER_INT);
    remember('show_completed', $show_complete_tasks);
    keep_view();
}
?>
