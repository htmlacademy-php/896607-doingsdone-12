<main class="content__main">
    <h2 class="content__main-heading">Список задач</h2>

    <form class="search-form" action="index.php" method="get" autocomplete="off">
        <input class="search-form__input" type="text" name="search" value="<?php if ($search): ?><?=$search;?><? endif; ?>" placeholder="Поиск по задачам">

        <input class="search-form__submit" type="submit" name="" value="Искать">
    </form>

    <div class="tasks-controls">
        <nav class="tasks-switch">
            <a href="index.php" class="tasks-switch__item <?php if (!$filter): ?>tasks-switch__item--active<?php endif; ?>">Все задачи</a>
            <a href="index.php?filter=today<?php if ($category): ?><?="&category=$category";?><?php endif; ?>" class="tasks-switch__item <?php if ($filter === 'today'): ?>tasks-switch__item--active<?php endif; ?>">Повестка дня</a>
            <a href="index.php?filter=tomorrow<?php if ($category): ?><?="&category=$category";?><?php endif; ?>" class="tasks-switch__item <?php if ($filter === 'tomorrow'): ?>tasks-switch__item--active<?php endif; ?>">Завтра</a>
            <a href="index.php?filter=overdue<?php if ($category): ?><?="&category=$category";?><?php endif; ?>" class="tasks-switch__item <?php if ($filter === 'overdue'): ?>tasks-switch__item--active<?php endif; ?>">Просроченные</a>
        </nav>

        <label class="checkbox">
            <input class="checkbox__input visually-hidden show_completed" type="checkbox" <?php if ($show_complete_tasks === 1): ?>checked <?php endif; ?>>
            <span class="checkbox__text">Показывать выполненные</span>
        </label>
    </div>

    <table class="tasks">
    <?php if ($tasks): ?>
        <?php foreach ($tasks as $key => $task): ?>
            <?php if ($show_complete_tasks === 0 && $task['is_done']) {continue;} ?>
            <?php if ($filter && !filter_deadline($task['deadline'], $filter)) {continue;} ?>
            <tr class="tasks__item task <?php if ($task['is_done']): ?> task--completed<?php endif; ?> <?php if (task_urgency($task['deadline'])): ?> task--important <?php endif; ?>">
                <td class="task__select">
                    <label class="checkbox task__checkbox">
                        <input class="checkbox__input visually-hidden" type="checkbox" value="<?=$task['id']?>" <?php if ($task['is_done']): ?>checked <?php endif; ?>>
                        <span class="checkbox__text"><?=htmlspecialchars($task['title'])?></span>
                    </label>
                </td>
                <td class="task__file">
                    <?php if ($task['file_url']): ?><a class="download-link" href="<?=$task['file_url']?>"><?=$task['file_name']?></a><?php endif; ?>
                </td>
                <td class="task__date"><?=htmlspecialchars($task['deadline'])?></td>
                <td class="task__controls"></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <?php if ($search): ?>
            <tr class="tasks__item task">
                <td>
                    Ничего не найдено по вашему запросу
                </td>
            </tr>
        <?php endif; ?>
    <?php endif; ?>
    </table>
</main>
