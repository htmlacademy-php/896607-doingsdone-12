            <main class="content__main">
                <h2 class="content__main-heading">Список задач</h2>

                <form class="search-form" action="index.php" method="post" autocomplete="off">
                    <input class="search-form__input" type="text" name="" value="" placeholder="Поиск по задачам">

                    <input class="search-form__submit" type="submit" name="" value="Искать">
                </form>

                <div class="tasks-controls">
                    <nav class="tasks-switch">
                        <a href="/" class="tasks-switch__item tasks-switch__item--active">Все задачи</a>
                        <a href="/" class="tasks-switch__item">Повестка дня</a>
                        <a href="/" class="tasks-switch__item">Завтра</a>
                        <a href="/" class="tasks-switch__item">Просроченные</a>
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
                        <tr class="tasks__item task <?php if ($task['is_done']): ?> task--completed<?php endif; ?> <?php if (task_urgency($task)): ?> task--important <?php endif; ?>">
                            <td class="task__select">
                                <label class="checkbox task__checkbox">
                                    <input class="checkbox__input visually-hidden" type="checkbox" <?php if ($task['is_done']): ?>checked <?php endif; ?>>
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
                <?php endif; ?>
                </table>
            </main>
