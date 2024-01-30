<?php

declare(strict_types=1);

session_start();



spl_autoload_register(fn(string $trida):int|bool => require_once "$trida.class.php");

use Databaze as Db;

$db = new Db();

$html = file_get_contents("kod/html/objednavka.html");

$cisloObjednavky = str_replace("cisloObjednavky:","",file_get_contents("temp.txt"));

// !popupravit 100%

if ($_SESSION["user"]) {
    # code...   

    $html = str_replace("[@zprava]","<p>Vaše objednávka s č. $cisloObjednavky  je v systému!</p><a href='index.php'>Nakupovat dál</a>",$html);
}

echo $html;