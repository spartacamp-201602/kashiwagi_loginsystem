<?php

require_once('config.php');
require_once('functions.php');

// ログインしている前提 => ログインしていない人を弾く必要がある
// $_SESSION['id'] を持っている => ログインしている
// $_SESSION['id'] を持っていない => ログインしていない

session_start();

if (empty($_SESSION['id']))
{
    header('Location: login.php');
    exit;
}

$dbh = connectDatabase();

$sql = 'select * from users where id = :id';
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':id', $_SESSION['id']);
$stmt->execute();

// $user => 現在ログイン中のuserのレコード情報が配列で入ってる
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$name = $user['name'];
$password = $user['password'];

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $name = $_POST['name'];
    $password = $_POST['password'];

    // バリデーション処理
    $errors = array();

    if (empty($name))
    {
        $errors[] = 'ユーザネームが未入力です';
    }

    if (empty($password))
    {
        $errors[] = 'パスワードが未入力です';
    }

    // 重複チェック
    $sql = 'select * from users where name = :name and id != :id';
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':id', $_SESSION['id']);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row)
    {
        $errors[] = 'すでにユーザネームが使われています';
    }

    // バリデーション通過
    if (empty($errors))
    {
        $sql = 'update users set name = :name, password = :password ';
        $sql.= 'where id = :id';
        // $dbh = connectDatabase(); 書かなくてもよい(上でやっている)
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':id', $_SESSION['id']);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':password', $password);

        $stmt->execute();

        header('Location: index.php');
        exit;
    }

}



?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザ情報編集画面</title>
    <style>
    .error {
        color: red;
        list-style: none;
    }
    </style>
</head>
<body>
    <h1>ユーザ情報編集</h1>

    <?php if (isset($errors)): ?>
    <div class="error">
        <?php foreach ($errors as $error): ?>
        <li><?php echo $error ?></li>
        <?php endforeach ?>
    </div>
    <?php endif ?>

    <form action="" method="post">
        ユーザネーム:
        <input type="text" name="name" value="<?php echo $name; ?>">
        <br>
        パスワード:
        <input type="text" name="password" value="<?php echo $password; ?>">
        <br>
        <input type="submit" value="編集する">
    </form>
    <a href="index.php">戻る</a>
</body>
</html>