<?php

require_once ("init.php");

$errors = [];
$auth_user = [];
$data_post = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['email'])) {
        $errors['email']['header'] = "E-mail";
        $errors['email']['text'] = "Не заполнено обязательное поле.";
    } else {
        $filter_email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if ($filter_email !== false) {
            $filter_email = htmlspecialchars($filter_email);
            $sql = 'SELECT * FROM `users` WHERE `email` = "' . $filter_email . '" LIMIT 1;';
            $auth_user = db_get_one($link, $sql);
        } else {
            $errors['email']['header'] = "E-mail";
            $errors['email']['text'] = "Не верный формат e-mail.";
        }
        $data_post['email'] = $filter_email;
    }
    if (empty($_POST['password'])) {
        $errors['password']['header'] = "Пароль";
        $errors['password']['text'] = "Не заполнено обязательное поле.";
    }

    if ($auth_user !== false) {
        if (!password_verify($_POST['password'], $auth_user['password'])) {
            $errors['password']['header'] = "Пароль";
            $errors['password']['text'] = "Не верный пароль.";
        }
    } else {
        $errors['email']['header'] = "E-mail";
        $errors['email']['text'] = "Не верный e-mail.";
    }

    if (count($errors) === 0){
        $_SESSION['user_id'] = $auth_user['id'];
        $_SESSION['login'] = $auth_user['login'];
        $_SESSION['email'] = $auth_user['email'];
        $_SESSION['avatar'] = $auth_user['avatar'];
        header("Location: feed.php");
    }
}

if (!empty($_SESSION)) {
    header("Location: feed.php");
} else {
    $layout_content = include_template('noauth.php', ['errors' => $errors, 'data_post' => $data_post]);
    print($layout_content);
}
