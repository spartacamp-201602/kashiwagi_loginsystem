<?php

session_start();

unset($_SESSION['id']);

session_destroy(); // $_SESSIONの中身がすべて消される

header('Location: login.php');

exit;