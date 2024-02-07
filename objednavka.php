<?php

declare(strict_types=1);

session_start();



spl_autoload_register(fn(string $trida):int|bool => require_once "$trida.class.php");

use Databaze as Db;

$inactivity_time = 15 * 60;

if(isset($_SESSION["user"])) {
    if (isset($_SESSION['last_timestamp']) && (time() - $_SESSION['last_timestamp']) > $inactivity_time) {
        //Redirect user to login page
        header("Location: odhlasit.php");
      }else{
        // Regenerate new session id and delete old one to prevent session fixation attack
        session_regenerate_id(true);
    
        // Update the last timestamp
        $_SESSION['last_timestamp'] = time();
    }
}


$db = new Db();

$html = file_get_contents("kod/html/objednavka.html");

// !popupravit?
$cisloObjednavky = $_SESSION["id"];

$timeout = '';

if ($_SESSION["user"]) {
    # code...
    $timeout = '<script defer src="kod/js/timeout.js"></script>';
    
    $html = str_replace("[@timeout]",$timeout,$html);

    $html = str_replace("[@zprava]","<p>Vaše objednávka s č. $cisloObjednavky  je v systému!</p><a href='index.php'>Nakupovat dál</a>",$html);
}

echo $html;