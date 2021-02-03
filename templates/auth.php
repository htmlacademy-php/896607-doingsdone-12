<main class="content__main">
  <h2 class="content__main-heading">Вход на сайт</h2>

  <form class="form" action="" method="post" autocomplete="off">
    <div class="form__row">
      <label class="form__label" for="email">E-mail <sup>*</sup></label>

      <input class="form__input <?php if (isset($errors['email'])): ?>form__input--error<?php endif; ?>" type="text" name="email" id="email" value="<?php if (isset($likely_user['email'])): ?><?=$likely_user['email'];?><?php endif; ?>" placeholder="Введите e-mail">

      <?php if (isset($errors['email'])): ?>
        <p class="form__message"><?=$errors['email'];?></p>
      <?php endif; ?>
    </div>

<!-- пароль пользователю необходимо ввести заново в целях безопасности -->
    <div class="form__row">
      <label class="form__label" for="password">Пароль <sup>*</sup></label>

      <input class="form__input <?php if (isset($errors['password'])): ?>form__input--error<?php endif; ?>" type="password" name="password" id="password" value="" placeholder="Введите пароль">
    </div>

    <?php if (isset($errors['password'])): ?>
      <p class="form__message"><?=$errors['password'];?></p>
    <?php endif; ?>

    <div class="form__row form__row--controls">
      <?php if(isset($errors)): ?>
        <p class="error-message"><?=$form_error_message;?></p>
      <?php endif; ?>
      <input class="button" type="submit" name="" value="Войти">
    </div>
  </form>

</main>
