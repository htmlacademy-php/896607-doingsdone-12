<?php
/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
 *
 * Примеры использования:
 * is_date_valid('2019-01-01'); // true
 * is_date_valid('2016-02-29'); // true
 * is_date_valid('2019-04-31'); // false
 * is_date_valid('10.10.2010'); // false
 * is_date_valid('10/10/2010'); // false
 *
 * @param string $date Дата в виде строки
 *
 * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
 */
function is_date_valid(string $date) : bool {
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false && array_sum(date_get_last_errors()) === 0;
}

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt($link, $sql, $data = []) {
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
        die($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            }
            else if (is_string($value)) {
                $type = 's';
            }
            else if (is_double($value)) {
                $type = 'd';
            }

            if ($type) {
                $types .= $type;
                $stmt_data[] = $value;
            }
        }

        $values = array_merge([$stmt, $types], $stmt_data);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
}

/**
 * Возвращает корректную форму множественного числа
 * Ограничения: только для целых чисел
 *
 * Пример использования:
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     get_noun_plural_form(
 *         $remaining_minutes,
 *         'минута',
 *         'минуты',
 *         'минут'
 *     );
 * Результат: "Я поставил таймер на 5 минут"
 *
 * @param int $number Число, по которому вычисляем форму множественного числа
 * @param string $one Форма единственного числа: яблоко, час, минута
 * @param string $two Форма множественного числа для 2, 3, 4: яблока, часа, минуты
 * @param string $many Форма множественного числа для остальных чисел
 *
 * @return string Рассчитанная форма множественнго числа
 */
function get_noun_plural_form (int $number, string $one, string $two, string $many): string
{
    $number = (int) $number;
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    switch (true) {
        case ($mod100 >= 11 && $mod100 <= 20):
            return $many;

        case ($mod10 > 5):
            return $many;

        case ($mod10 === 1):
            return $one;

        case ($mod10 >= 2 && $mod10 <= 4):
            return $two;

        default:
            return $many;
    }
}

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function include_template($name, array $data = []) {
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

/* ДОБАВЛЕННЫЕ ФУНКЦИИ */

/* для отображения задач */
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

/* для добавления задачи */
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

/* для добавления проекта */
/* проверяем название проекта */
function checking_new_project($project, $connect, $user_id) {
    if (!checking_uniqe_value($connect, 'projects', 'title', $project, "user_id = $user_id")) {
        return 'Проект с таким названием уже существует';
    }
        return NULL;
}

/* для регистрации пользователя */
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
