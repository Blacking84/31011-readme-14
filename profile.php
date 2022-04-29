<?php

require_once ("init.php");

if (empty($_SESSION)) {
    header("Location: index.php");
}

$user_id = filter_input(INPUT_GET, 'user_id');
if (empty($user_id)) {
    $user_id = $_SESSION['user_id'];
}
$user_id = htmlspecialchars($user_id);

$sql = <<<SQL
    SELECT u.id, u.login, u.date, u.avatar, COUNT(DISTINCT p.id) posts, COUNT(DISTINCT s.id) subscribed
    FROM `users` u
    LEFT JOIN `posts` p ON u.id = p.user_id
    LEFT JOIN `subscriptions` s ON u.id = s.subscribed_id
    WHERE u.id = $user_id
    GROUP BY u.id
    LIMIT 1;
SQL;

$profile = db_get_one($link, $sql);
if ($profile === false) {
    header("Location: profile.php");
}

$sql = 'SELECT * FROM `subscriptions` WHERE `user_id` = ' . $_SESSION['user_id'] . ' AND `subscribed_id` = ' . $user_id . ';';
$subscr_profile = db_get_one($link, $sql);

$show = filter_input(INPUT_GET, 'show');
if (empty($show)) {
    $show = 'posts';
}
$show = htmlspecialchars($show);

$subscribeds = [];
$likes = [];
$posts = [];
$post_tags = [];

switch ($show) {
    case 'likes':
        $sql = <<<SQL
            SELECT u.id user_id, u.login, u.avatar, p.id post_id, c.type type_post, l.date date_like
            FROM `likes` l
            LEFT JOIN `posts` p ON l.post_id = p.id
            LEFT JOIN `users` u ON l.user_id = u.id
            LEFT JOIN `content_types` c ON p.type_id = c.id
            WHERE p.user_id = $profile[id]
            ORDER BY l.date DESC
        SQL;
        $likes = db_get_all($link, $sql);
        break;

    case 'subscriptions':
        $sql = <<<SQL
            SELECT u.id, u.login, u.date reg_date, u.avatar,  COUNT(DISTINCT p.id) posts,
                   COUNT(DISTINCT s2.user_id) subscribed, COUNT(DISTINCT s3.user_id) me_subscribed
            FROM `subscriptions` s1
            LEFT JOIN `users` u ON s1.subscribed_id = u.id
            LEFT JOIN `posts` p ON s1.subscribed_id = p.user_id
            LEFT JOIN `subscriptions` s2 ON s1.subscribed_id = s2.subscribed_id
            LEFT JOIN `subscriptions` s3 ON s3.user_id = $_SESSION[user_id] AND s3.subscribed_id = u.id
            WHERE s1.user_id = $profile[id]
            GROUP BY u.id
        SQL;
        $subscribeds = db_get_all($link, $sql);
        break;

    case 'posts':
    default:
        $show = 'posts';

        $where_sql = "WHERE u.id = " . $user_id;
        $order_sql = 'ORDER BY p.date DESC';
        $posts = get_posts($link, $where_sql, $order_sql);

        foreach($posts as $key => $post){
            $post_id = $post['id'];
            $sql_tags = <<<SQL
                SELECT h.hashtag FROM `posts_hashtags` ph
                INNER JOIN `hashtags` h ON h.id = ph.hashtag_id
                WHERE ph.post_id = $post_id;
            SQL;
            $post_tag = db_get_all($link, $sql_tags);
            $posts[$key]['tags'] = array_column($post_tag, 'hashtag');
        }
}

$page_content = include_template('profile_' . $show . '.php', ['subscribeds' => $subscribeds, 'user_id' => $profile['id'], 'likes' => $likes, 'posts' => $posts]);
$page_content = include_template('profile.php', ['content' => $page_content, 'show' => $show, 'profile' => $profile, 'subscr_profile' => $subscr_profile]);
$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'readme: профиль']);

print($layout_content);
