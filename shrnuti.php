<?php

declare(strict_types=1);

spl_autoload_register(fn($trida) => require_once "$trida.class.php");

use Databaze as Db;

$db = new Db();



$html = file_get_contents("kod/html/shrnuti.html");


echo $html;