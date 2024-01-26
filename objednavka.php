<?php

declare(strict_types=1);

session_start();



spl_autoload_register(fn(string $trida):int|bool => require_once "$trida.class.php");

use Databaze as Db;

$db = new Db();

$html = file_get_contents("kod/html/objednavka.html");

if ($_SESSION["user"]) {
    # code...   

    $html = str_replace("[@zprava]","<p>Vaše objednávka s č. $cislo je v systému!</p>",$html);
}

echo $html;