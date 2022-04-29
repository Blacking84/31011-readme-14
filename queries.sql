-- Добавление типов контента
INSERT INTO `content_types` (`name`, `type`, `icon`)
       VALUES               ('Текст', 'text', 'text.png'),
                            ('Цитата', 'quote', 'quote.png'),
                            ('Картинка', 'photo', 'photo.png'),
                            ('Видео', 'video', 'video.png'),
                            ('Ссылка', 'link', 'link.png');

-- Добавление пользователей
INSERT INTO `users` (`login`, `email`, `password`, `avatar`)
       VALUES       ('Алексей', 'alexe@lugachev.ru', 'newpass', 'userpic-mark.jpg'),
                    ('Ирина', 'irina@lugacheva.ru', 'oldpass', 'userpic-larisa-small.jpg'),
                    ('Владислав', 'vladislav@lugachev.ru', 'newoldpass', 'userpic.jpg'),
                    ('Ева', 'eva@lugacheva.ru', 'oldnewpass', 'userpic-larisa-small.jpg');

-- Добавление постов
INSERT INTO `posts` (`user_id`, `type_id`, `header`, `post`, `author_quote`, `image_link`, `video_link`, `site_link`)
       VALUES       (1, 2, 'Цитата', 'Мы в жизни любим только раз, а после ищем лишь похожих', 'Неизвестный Автор', NULL, NULL, NULL),
                    (2, 1, 'Игра престолов', 'Не могу дождаться начала финального сезона своего любимого сериала! В массиве с постами для текстового поста укажите в содержимом очень длинный текст. Проверьте, что этот текст корректно обрезается с добавлением ссылки. Затем проверьте короткий текст, который должен отображаться без изменений. В массиве с постами для текстового поста укажите в содержимом очень длинный текст. Проверьте, что этот текст корректно обрезается с добавлением ссылки. Затем проверьте короткий текст, который должен отображаться без изменений.', NULL, NULL, NULL, NULL),
                    (2, 1, 'Игра престолов', 'Не могу дождаться начала финального сезона своего любимого сериала!', NULL, NULL, NULL, NULL),
                    (3, 3, 'Наконец, обработал фотки!', 'rock-medium.jpg', NULL, 'rock-medium.jpg', NULL, NULL),
                    (3, 3, 'Моя мечта', 'coast-medium.jpg', NULL, 'coast-medium.jpg', NULL, NULL),
                    (4, 5, 'Лучшие курсы', 'www.htmlacademy.ru', NULL, NULL, NULL, 'www.htmlacademy.ru');

-- Добавление комментариев
INSERT INTO `comments` (`user_id`, `post_id`, `post`)
       VALUES          (4, 3, 'Тоже жду с нетерпением'),
                       (3, 1, 'Очень жизненно'),
                       (1, 6, 'Поддержу!');


-- Получаем список постов с сортировкой по популярности вместе с именами авторов
-- и типом контента
SELECT p.id, u.login, u.email, c.name post_type, p.post, p.author_quote, p.image_link, p.video_link, p.site_link FROM `posts` p
         INNER JOIN `users` u ON p.user_id = u.id
         INNER JOIN `content_types` c ON p.type_id = c.id
         ORDER BY p.view DESC;

-- Получаем список постов конкретного пользователя
SELECT p.id, u.login, u.email, c.name post_type, p.post, p.author_quote, p.image_link, p.video_link, p.site_link FROM `posts` p
         INNER JOIN `users` u ON p.user_id = u.id
         INNER JOIN `content_types` c ON p.type_id = c.id
         WHERE u.login = 'Ирина';

-- Получаем список комментариев для одного поста с логином автора комментария
SELECT c.id, p.header, p.post, c.post comment, u.login comment_author FROM `comments` c
         INNER JOIN `posts` p ON c.post_id = p.id
         INNER JOIN `users` u ON c.user_id = u.id
         WHERE p.id = 3;

-- Добавляем лайк к посту
INSERT INTO `likes` (`user_id`, `post_id`)
       VALUES       (1, 6);

-- Подписываемся на пользователя
INSERT INTO `subscriptions` (`user_id`, `subscribed_id`)
       VALUES               (1, 2);
