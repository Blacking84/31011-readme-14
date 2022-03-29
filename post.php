<?php

require_once ("init.php");

$post = [];

$post_id = filter_input(INPUT_GET, 'id');
if ($post_id) {
    $sql = <<<SQL
        UPDATE `posts`
        SET posts.view = posts.view + 1
        WHERE id = $post_id;
    SQL;

    db_update ($link, $sql);

    $sql = <<<SQL
        SELECT p.id, u.login, u.email, u.avatar, c.type, p.header, p.post,
            p.author_quote, p.image_link, p.video_link, p.site_link, p.date, p.view,
            COUNT(com.post_id) comments_count, COUNT(l.post_id) likes_count,
            COUNT(s.subscribed_id) subscribed, COUNT(p1.user_id) posts
        FROM `posts` p
        INNER JOIN `users` u ON p.user_id = u.id
        INNER JOIN `content_types` c ON p.type_id = c.id
        LEFT JOIN `comments` com ON p.id = com.post_id
        LEFT JOIN `likes` l ON p.id = l.post_id
        LEFT JOIN `subscriptions` s ON p.user_id = s.user_id
        LEFT JOIN `posts` p1 ON p.user_id = p1.user_id
        WHERE p.id = $post_id
        GROUP BY com.post_id, l.post_id, s.subscribed_id, p1.user_id
        ORDER BY p.view DESC
        LIMIT 1;
    SQL;

} else {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
}

$post = db_get($link, $sql);

$page_content = include_template('post_' . $post[0]['type'] . '.php', ['post' => $post[0]]);

$layout_content = include_template('post.php', ['content' => $page_content, 'title' => 'readme: публикация', 'post' => $post[0]]);

print($layout_content);

?>
