<?php

require_once ("init.php");

if (!empty($_SESSION)) {
    header("Location: index.php");
}

$errors = [];
$data_post = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['email'])) {
        $errors['email']['header'] = "E-mail";
        $errors['email']['text'] = "Не заполнено обязательное поле.";
    }
    $filter_email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if ($filter_email !== false) {
        $filter_email = htmlspecialchars($filter_email);
        $sql = 'SELECT * FROM `users` WHERE `email` = "' . $filter_email . '" LIMIT 1;';
        $user = db_get_one($link, $sql);
        if (!empty($user)){
            $errors['email']['header'] = "E-mail";
            $errors['email']['text'] = "Такой e-mail уже зарегистрирован.";
        }
    } else {
        $errors['email']['header'] = "E-mail";
        $errors['email']['text'] = "Не верный формат e-mail.";
    }
    if (empty($_POST['login'])) {
        $errors['login']['header'] = "Логин";
        $errors['login']['text'] = "Не заполнено обязательное поле.";
    }
    if (empty($_POST['password'])) {
        $errors['password']['header'] = "Пароль";
        $errors['password']['text'] = "Не заполнено обязательное поле.";
    }
    if (empty($_POST['password-repeat'])) {
        $errors['password-repeat']['header'] = "Повтор пароля";
        $errors['password-repeat']['text'] = "Не заполнено обязательное поле.";
    }
    if ($_POST['password'] !== $_POST['password-repeat']) {
        $errors['password-repeat']['header'] = "Повтор пароля";
        $errors['password-repeat']['text'] = "Введенные пароли не совпадают.";
    }

    if ($_FILES['uploadfile']['tmp_name']){
        $result_upload = upload_file($_FILES['uploadfile']['tmp_name']);
        if ($result_upload !== true) {
            $errors['photo']['header'] = "Фото";
            $errors['photo']['text'] = $result_upload;
        }
    }

    if (count($errors) === 0){
        $login = htmlspecialchars($_POST['login']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $sql = <<<SQL
            INSERT INTO `users` (`login`, `email`, `password`, `avatar`)
                   VALUES ("$login", "$filter_email", "$password", "$new_name");
        SQL;
        db_insert($link, $sql);
        header("Location: index.php");
    }

    $data_post['email'] = $_POST['email'];
    $data_post['login'] = $_POST['login'];
}

$page_content = include_template('registration.php', ['errors' => $errors, 'data_post' => $data_post]);
$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'readme: регистрация']);


print($layout_content);
