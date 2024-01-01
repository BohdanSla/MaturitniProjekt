<?php

declare(strict_types=1);

session_start();

unset($_SESSION["user"]);
session_regenerate_id();



header("Location: ucet.php");