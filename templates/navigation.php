<section class="content__side">
    <h2 class="content__side-heading">Проекты</h2>

    <nav class="main-navigation">
        <ul class="main-navigation__list">
            <?php foreach ($projects as $project): ?>
            <li class="main-navigation__list-item <?php if ($category === $project['id']): ?>main-navigation__list-item--active<?php endif; ?>">
                <a class="main-navigation__list-item-link" href="index.php?category=<?=$project['id'];?>"><?=htmlspecialchars($project['title']);?></a>
                <span class="main-navigation__list-item-count"><?= (int) $project['task_count'];?></span>
            </li>
        <?php endforeach; ?>
        </ul>
    </nav>

    <a class="button button--transparent button--plus content__side-button"
       href="add-project.php" target="project_add">Добавить проект</a>
</section>
