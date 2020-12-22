/* добавляем существущий список проектов */
INSERT INTO projects (title, user_id)
VALUES ('Входящие', 1),
       ('Учеба', 1),
       ('Работа', 1),
       ('Домашние дела', 2),
       ('Авто', 1);

/* добавляем несколько пользователей */
INSERT INTO users (email, name, password)
VALUES ('first@mail.ru', 'Anna', 'secret'),
       ('second@mail.ru', 'Elena', 'supersecret');

/* добавляем существущий список задач */
INSERT INTO tasks (status, title, deadline, project_id, user_id)
VALUES (0, 'Собеседование в IT компании', STR_TO_DATE('01.12.2019', '%d.%m.%Y'), 3, 1),
       (0, 'Выполнить тестовое задание', STR_TO_DATE('25.12.2019', '%d.%m.%Y'), 3, 1),
       (1, 'Сделать задание первого раздела', STR_TO_DATE('21.12.2019', '%d.%m.%Y'), 2, 1),
       (0, 'Встреча с другом', STR_TO_DATE('22.12.2019', '%d.%m.%Y'), 1, 1),
       (0, 'Купить корм для кота', null, 4, 2),
       (0, 'Заказать пиццу', null, 4, 2);

/* получаем список из всех проектов для одного пользователя */
SELECT *
  FROM projects
 WHERE user_id = 1;

/* получаем список из всех задач для одного проекта */
SELECT *
  FROM tasks
 WHERE project_id = 4;

/* помечаем задачу как выполненную */
UPDATE tasks
   SET status = 1
 WHERE id = 1;

/* обновляем название задачи по ее идентификатору */
UPDATE tasks
   SET title = 'Написать письмо коллеге'
 WHERE id = 2;
