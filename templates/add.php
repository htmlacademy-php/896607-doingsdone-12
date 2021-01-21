<main class="content__main">
  <h2 class="content__main-heading">Добавление задачи</h2>

  <form class="form"  action="" method="POST" autocomplete="off" enctype="multipart/form-data">
    <div class="form__row">
      <label class="form__label" for="name">Название <sup>*</sup></label>

      <input class="form__input <?php if (isset($errors['name'])): ?> form__input--error<?php endif; ?>" type="text" name="name" id="name" value="<?php if (isset($task['name'])): ?><?=$task['name'];?><?php endif; ?>" placeholder="Введите название">
      <?php if (isset($errors['name'])): ?>
        <p class="form__message"><?=$errors['name'];?></p>
      <?php endif; ?>
    </div>

    <div class="form__row">
      <label class="form__label" for="project">Проект <sup>*</sup></label>

      <select class="form__input form__input--select <?php if (isset($errors['project'])): ?> form__input--error<?php endif; ?>" name="project" id="project">
        <?php foreach ($categories as $key => $category): ?>
          <option value="<?=$category['id'];?>"><?=$category['title'];?></option>
        <?php endforeach; ?>
      </select>
      <?php if (isset($errors['project'])): ?>
        <p class="form__message"><?=$errors['project'];?></p>
      <?php endif; ?>
    </div>

    <div class="form__row">
      <label class="form__label" for="date">Дата выполнения</label>

      <input class="form__input form__input--date <?php if (isset($errors['date'])): ?> form__input--error<?php endif; ?>" type="text" name="date" id="date" value="" placeholder="Введите дату в формате ГГГГ-ММ-ДД">
      <?php if (isset($errors['date'])): ?>
        <p class="form__message"><?=$errors['date'];?></p>
      <?php endif; ?>
    </div>

    <div class="form__row">
      <label class="form__label" for="file">Файл</label>

      <div class="form__input-file">
        <input class="visually-hidden" type="file" name="file" id="file" value="">

        <label class="button button--transparent" for="file">
          <span>Выберите файл</span>
        </label>
      </div>
    </div>

    <div class="form__row form__row--controls">
      <input class="button" type="submit" name="" value="Добавить">
    </div>
  </form>
<!--
  В ТЗ: "Если по итогам выполнения процесса отправки возникли ошибки заполнения формы, то эти ошибки должны быть показаны красным текстом под необходимыми полями, а под самой формой появится сообщение «Пожалуйста, исправьте ошибки в форме»."
  В задании сообщение под формой не упоминается - если не будет позже, добавить? -->
</main>
