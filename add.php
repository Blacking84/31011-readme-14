<?php

require_once ("init.php");


if (empty($_SESSION)) {
    header("Location: index.php");
}

$content_types = get_content_types($link);

$ctype = filter_input(INPUT_GET, 'ctype');
if (!$ctype) {
    $ctype = 1;
}

foreach ($content_types as $value){
    $id = $value['type'];
    $type_name[$id] = $value['id'];
}
$ctype_name = array_search($ctype, $type_name);

$errors = [];
$data_post = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    foreach ($_POST as $key => $value) {
        $_POST[$key] = htmlspecialchars($value);
    }

    if (empty($_POST['header'])) {
        $errors['header']['header'] = "Заголовок";
        $errors['header']['text'] = "Не заполнено обязательное поле.";
    }
    $header = $_POST['header'];
    $data_post['header'] = $_POST['header'];
    $data_post['tags'] = $_POST['tags'];

    switch ($ctype_name) {
        case 'text':
            if (empty($_POST['post'])) {
                $errors['post']['header'] = "Текст поста";
                $errors['post']['text'] = "Не заполнено обязательное поле.";
            }
            $post = $_POST['post'];
            $data_post['post'] = $_POST['post'];
            $sql = <<<SQL
                INSERT INTO `posts` (`user_id`, `type_id`, `header`, `post`, `author_quote`, `image_link`, `video_link`, `site_link`)
                       VALUES       ($user_id, $ctype, "$header", "$post", NULL, NULL, NULL, NULL);
            SQL;
            break;
        case 'quote':
            if (empty($_POST['post'])) {
                $errors['post']['header'] = "Текст цитаты";
                $errors['post']['text'] = "Не заполнено обязательное поле.";
            }
            if (empty($_POST['author_quote'])) {
                $errors['author_quote']['header'] = "Автор цитаты";
                $errors['author_quote']['text'] = "Не заполнено обязательное поле.";
            }
            $post = $_POST['post'];
            $data_post['post'] = $_POST['post'];
            $data_post['author_quote'] = $_POST['author_quote'];
            $sql = <<<SQL
                INSERT INTO `posts` (`user_id`, `type_id`, `header`, `post`, `author_quote`, `image_link`, `video_link`, `site_link`)
                       VALUES       ($user_id, $ctype, "$header", "$post", "$_POST[author_quote]", NULL, NULL, NULL);
            SQL;
            break;
        case 'photo':
            $filter_url = "";
            if ($_FILES['uploadfile']['tmp_name']){
                if ($_FILES['uploadfile']['tmp_name']){
                    $result_upload = upload_file($_FILES['uploadfile']['tmp_name']);
                    if ($result_upload !== true) {
                        $errors['photo']['header'] = "Фото";
                        $errors['photo']['text'] = $result_upload;
                    }
                }
            } elseif ($_POST['photo_link']) {
                $filter_url = filter_var($_POST['photo_link'], FILTER_VALIDATE_URL);
                if ($filter_url !== false) {
                    $file = file_get_contents($filter_url);
                    if ($file !== false) {
                        $tmp_name = "tmp_" . uniqid();
                        file_put_contents("uploads/" . $tmp_name, $file);
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $tmp_type = finfo_file($finfo, "uploads/" . $tmp_name);
                        finfo_close($finfo);
                        switch ($tmp_type){
                            case 'image/jpeg': $type_file = ".jpg"; break;
                            case 'image/png': $type_file = ".png"; break;
                            case 'image/gif': $type_file = ".gif"; break;
                            default: $type_file = false;
                        }
                        if ($type_file !== false) {
                            $new_name = uniqid() . $type_file;
                            rename("uploads/" . $tmp_name, "uploads/" . $new_name);
                        } else {
                            unlink ("uploads/" . $tmp_name);
                            $errors['photo']['header'] = "Ссылка на фото";
                            $errors['photo']['text'] = "Не верный тип файла по ссылке";
                        }
                    } else {
                        $errors['photo']['header'] = "Ссылка на фото";
                        $errors['photo']['text'] = "Не удалось закачать фото";
                    }
                } else {
                    $errors['photo']['header'] = "Ссылка на фото";
                    $errors['photo']['text'] = "Неверный формат ссылки";
                }
            } else {
                $errors['photo']['header'] = "Фото";
                $errors['photo']['text'] = "Не выбран файл";
            }
            $data_post['filter_url'] = $filter_url;
            $sql = <<<SQL
                INSERT INTO `posts` (`user_id`, `type_id`, `header`, `post`, `author_quote`, `image_link`, `video_link`, `site_link`)
                       VALUES       ($user_id, $ctype, "$header", "$new_name", NULL, "$new_name", NULL, NULL);
            SQL;
            break;
        case 'video':
            $filter_url = "";
            if (!empty($_POST['video_link'])) {
                $filter_url = filter_var($_POST['video_link'], FILTER_VALIDATE_URL);
                if ($filter_url === true) {
                    $check = check_youtube_url($filter_url);
                    if ($check !== true) {
                        $errors['video_link']['header'] = "Ссылка на YOUTUBE";
                        $errors['video_link']['text'] = $check;
                    }
                } else {
                    $errors['video_link']['header'] = "Ссылка на YOUTUBE";
                    $errors['video_link']['text'] = "Неверный формат ссылки";
                }
            } else {
                $errors['video_link']['header'] = "Ссылка YOUTUBE";
                $errors['video_link']['text'] = "Не заполнено обязательное поле.";
            }
            $data_post['filter_url'] = $filter_url;
            $sql = <<<SQL
                INSERT INTO `posts` (`user_id`, `type_id`, `header`, `post`, `author_quote`, `image_link`, `video_link`, `site_link`)
                       VALUES       ($user_id, $ctype, "$header", "$filter_url", NULL, NULL, "$filter_url", NULL);
            SQL;
            break;
        case 'link':
            $filter_url = "";
            if (!empty($_POST['site_link'])) {
                $filter_url = filter_var($_POST['site_link'], FILTER_VALIDATE_URL);
                if ($filter_url === false) {
                    $errors['site_link']['header'] = "Ссылка";
                    $errors['site_link']['text'] = "Неверный формат ссылки";
                }
            } else {
                $errors['site_link']['header'] = "Ссылка";
                $errors['site_link']['text'] = "Не заполнено обязательное поле.";
            }
            $data_post['filter_url'] = $filter_url;
            $sql = <<<SQL
                INSERT INTO `posts` (`user_id`, `type_id`, `header`, `post`, `author_quote`, `image_link`, `video_link`, `site_link`)
                       VALUES       ($user_id, $ctype, "$header", "$filter_url", NULL, NULL, NULL, "$filter_url");
            SQL;
            break;
        default:
            $errors['ctype']['header'] = "Категория публикации";
            $errors['ctype']['text'] = "Выбрана не существующая категория.";
    }

    $post_tags = [];
    if ($_POST['tags']) {
        $post_tags = explode(" ", $_POST['tags']);
        if (count($post_tags) === 0) {
            $errors['ctype']['header'] = "Теги";
            $errors['ctype']['text'] = "Не указаны хештеги.";
        }
    } else {
        $errors['tags']['header'] = "Теги";
        $errors['tags']['text'] = "Не указаны хештеги.";
    }


    if (count($errors) === 0) {
        $post_id = db_insert($link, $sql);

        $hashtagsString = "'" . implode("', '", $post_tags) . "'";
        $sql = 'SELECT `id`, `hashtag` FROM `hashtags` WHERE `hashtag` IN (' . $hashtagsString . ');';
        $tag_id = db_get_all($link, $sql);
        $hashtagsIds = array_column($tag_id, 'id', 'hashtag');

        $new_tags = [];
        foreach ($post_tags as $post_tag) {
            if (!isset($hashtagsIds[$post_tag])) {
                $new_tags[] = $post_tag;
            }
        }

        $new_tags_string = implode("'), ('", $new_tags);
        $sql_new_tags = "INSERT INTO `hashtags` (`hashtag`) VALUES ('" . $new_tags_string . "');";

        if ($new_tags_string === true) {
            db_insert($link, $sql_new_tags);
        }

        $sql = 'SELECT `id` FROM `hashtags` WHERE `hashtag` IN (' . $hashtagsString . ');';
        $tag_id = db_get_all($link, $sql);
        $hashtagsIds = array_column($tag_id, 'id');

        $old_tags_string = $post_id . "', '" . implode("'), ('" . $post_id . "', '", $hashtagsIds);
        $sql_old_tags = "INSERT INTO `posts_hashtags` (`post_id`, `hashtag_id`) VALUES ('" . $old_tags_string . "');";

        if ($old_tags_string === true) {
            db_insert($link, $sql_old_tags);
        }

        $sess_user_id = $_SESSION['user_id'];

        $sql = <<<SQL
            SELECT uu.login login_user, us.login login_subscribed, us.email email_subscribed
            FROM `subscriptions` s
            INNER JOIN `users` uu ON s.user_id = uu.id
            INNER JOIN `users` us ON s.subscribed_id = us.id
            WHERE s.user_id = $sess_user_id;
        SQL;

        $users_subscriptions = db_get_all($link, $sql);

        foreach ($users_subscriptions as $user_subscriptions) {
            $message = new Email();
            $message->to($user_subscriptions['email_subscribed']);
            $message->from("mail@readme.academy");
            $message->subject("Новая публикация от пользователя " . $user_subscriptions['login_user']);
            $message->text("Здравствуйте, " . $user_subscriptions['login_subscribed'] . ". Пользователь " . $user_subscriptions['login_user'] . " только что опубликовал новую запись „" . $_POST['header'] . "“. Посмотрите её на странице пользователя: http://" . $_SERVER['HTTP_HOST'] . "/profile.php?user_id=" . $sess_user_id);
         }

        header("Location: post.php?id=" . $post_id);
    }
}

$page_content = include_template('add.php', ['content_types' => $content_types, 'ctype' => (int)$ctype, 'ctype_name' => $ctype_name, 'errors' => $errors, 'data_post' => $data_post]);
$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'readme: добавление публикации']);


print($layout_content);
