<?php

require_once ("init.php");

if (empty($_SESSION)) {
    header("Location: index.php");
}

$post = [];
$post_comments = [];
$errors = [];
$data_comment = '';

$post_id = filter_input(INPUT_GET, 'id');
if ($post_id) {
    $sql = <<<SQL
        UPDATE `posts`
        SET posts.view = posts.view + 1
        WHERE id = $post_id;
    SQL;

    db_update ($link, $sql);

    $where_sql = "WHERE p.id = " . $post_id;
    $order_sql = "ORDER BY p.view DESC";
    $limit_sql = "LIMIT 1";

    $post = get_posts($link, $where_sql, $order_sql, $limit_sql);

} else {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
}

$sql_tags = <<<SQL
        SELECT h.hashtag FROM `posts_hashtags` ph
        INNER JOIN `hashtags` h ON h.id = ph.hashtag_id
        WHERE ph.post_id = $post[id];
    SQL;

$post_tags = db_get_all($link, $sql_tags);

$post_id = $post['id'];
$sql_comments = <<<SQL
    SELECT u.id user_id, u.login, u.avatar, c.post, c.date
    FROM `comments` c
    LEFT JOIN `users` u ON u.id = c.user_id
    WHERE c.post_id = $post_id
    ORDER BY c.date DESC;
SQL;

$post_comments = db_get_all($link, $sql_comments);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_POST['post_id'] = htmlspecialchars($_POST['post_id']);

    if (empty($_POST['comment'])) {
        $errors['comment']['header'] = "Комментарий";
        $errors['comment']['text'] = "Не заполнено обязательное поле.";
    } else {
        $_POST['comment'] = trim($_POST['comment']);
        $lenght_comment = strlen($_POST['comment']);
        if ($lenght_comment >= 4) {
            $_POST['comment'] = htmlspecialchars($_POST['comment']);
        } else {
            $errors['comment']['header'] = "Комментарий";
            $errors['comment']['text'] = "Слишком короткий комментарий.";
        }
    }
    $data_comment = $_POST['comment'];

    if (count($errors) === 0) {
        $sql = 'SELECT p.user_id FROM `posts` p WHERE p.id = ' . $_POST['post_id'] . ' LIMIT 1;';
        $post_comment = db_get_one($link, $sql);
        if ($post_comment !== false) {
            $sql = 'INSERT INTO `comments` (`user_id`, `post_id`, `post`) VALUES (' . $_SESSION['user_id'] . ', ' . $_POST['post_id'] . ', "' . $_POST['comment'] . '")';
            db_insert($link, $sql);
            header("Location: profile.php?user_id=" . $post_comment['user_id']);
        }
    }
}

$page_content = include_template('post_' . $post['type'] . '.php', ['post' => $post]);
$page_content = include_template('post.php', ['content' => $page_content,'post' => $post, 'tags' => $post_tags, 'post_comments' => $post_comments, 'errors' => $errors, 'data_comment' => $data_comment]);
$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'readme: публикация']);


print($layout_content);
