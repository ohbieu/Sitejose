<?php
session_start();
session_unset();
session_destroy();
header('Location: /jogo3/login.php');
exit;
