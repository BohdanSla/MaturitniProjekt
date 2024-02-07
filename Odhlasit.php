<?php

declare(strict_types=1);

session_start();

unset($_SESSION["user"]);
$_SESSION["last_timestamp"] = time();
session_regenerate_id();
header("Location: ucet.php");
