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
$admin = '';

if (isset($_SESSION["user"])) {
    # code...
    $timeout = '<script defer src="kod/js/timeout.js"></script>';
    $stmt = $db->prepare("SELECT id_role FROM uzivatel WHERE id = :id");
    $stmt->execute([":id" => $_SESSION["user"]]);

    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if($arr[0]["id_role"] == 1) {
        $admin = '<a href="administrace.php"><li><img src="obrazky/naradi_ikona.svg" alt="naradi_ikona">Administrace</li></a>';
    }
    
    $html = str_replace("[@zprava]","<p>Vaše objednávka s č. $cisloObjednavky  je v systému!</p><a href='index.php'>Nakupovat dál</a>",$html);
} else {
    if(isset($_SESSION["id"])) {
        unset($_SESSION["kosik"]);
        unset($_SESSION["udaje"]);
        unset($_SESSION["kod"]);
        session_regenerate_id();
    
        $html = str_replace("[@zprava]","<p>Vaše objednávka s č. $cisloObjednavky  je v systému!</p><b>Nezapomeňte si číslo objednávky uložit!</b><a href='index.php'>Nakupovat dál</a>",$html);
    }
}

$html = str_replace("[@admin]",$admin,$html);
$html = str_replace("[@timeout]",$timeout,$html);

echo $html;