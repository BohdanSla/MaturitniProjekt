<?php

declare(strict_types=1);

session_start();



spl_autoload_register(fn($trida):bool|int => require_once "$trida.class.php");

use Databaze as Db;

$db = new Db();

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

$html = file_get_contents("kod/html/udaje.html");

$timeout = '';


if(isset($_SESSION["user"])) {

    $timeout = '<script defer src="kod/js/timeout.js"></script>';
    

    $stmt = $db->prepare('SELECT jmeno,prijmeni,email,telefonni_cislo,mesto,ulice,psc FROM uzivatel WHERE id = :id;
    SELECT produkt.nazev,obrazek.src FROM produkt JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku JOIN oblibene_produkty ON oblibene_produkty.id_produktu = produkt.id JOIN uzivatel ON uzivatel.id = oblibene_produkty.id_uzivatele WHERE obrazek.src LIKE "%main%" AND id_uzivatele = :id GROUP BY oblibene_produkty.id_produktu;');

    $stmt->execute([":id" => $_SESSION["user"]]);

    $arr = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $html = str_replace("[@jmeno]",$arr["jmeno"],$html);
    $html = str_replace("[@prijmeni]",$arr["prijmeni"],$html);
    $html = str_replace("[@email]",$arr["email"],$html);
    $html = str_replace("[@telefonniCislo]",$arr["telefonni_cislo"],$html);
    $html = str_replace("[@mesto]",$arr["mesto"],$html);
    $html = str_replace("[@ulice]",$arr["ulice"],$html);
    $html = str_replace("[@psc]",$arr["psc"],$html);

    if(isset($_POST["odeslat"])) {
        $emailRegex = "/^[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z]{2,4}$/";
        $telefonniCisloregex = "/(^(\+[0-9]{1,4} )?([0-9]{3} ){2}[0-9]{3}$)|(^(\+[0-9]{1,4})?[0-9]{9}$)/";
        # code...
        if(isset($_POST["jmeno"]) && isset($_POST["prijmeni"]) && isset($_POST["email"]) && isset($_POST["telefonniCislo"]) && isset($_POST["psc"]) && isset($_POST["ulice"]) && isset($_POST["mesto"])) {
            
            
            if(preg_match($telefonniCisloregex,$_POST["telefonniCislo"]) && preg_match($emailRegex,$_POST["email"])) {

                
                $stmt = $db->prepare("UPDATE uzivatel SET jmeno= :jmeno,prijmeni= :prijmeni,email= :email,telefonni_cislo= :telefonniCislo,psc= :psc,ulice= :ulice,mesto= :mesto WHERE id = :id");
        
                $stmt->execute([":jmeno" => htmlspecialchars($_POST["jmeno"]),":prijmeni" => htmlspecialchars($_POST["prijmeni"]),":email" => htmlspecialchars($_POST["email"]),":telefonniCislo" => htmlspecialchars($_POST["telefonniCislo"]),":psc" => htmlspecialchars($_POST["psc"]),":ulice" => htmlspecialchars($_POST["ulice"]),":mesto" => htmlspecialchars($_POST["mesto"]),":id" => $_SESSION["user"]]);

                header("Location: shrnuti.php");
            }
        } 
    }
} else {
    $html = ":)";
}

$html = str_replace("[@timeout]",$timeout,$html);

echo $html;