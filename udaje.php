<?php

declare(strict_types=1);

session_start();



spl_autoload_register(fn($trida):bool|int => require_once "$trida.class.php");

use Databaze as Db;

$db = new Db();

$html = file_get_contents("kod/html/udaje.html");

if(isset($_SESSION["user"])) {

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
        header("Location: shrnuti.php");
    }

} else {
    $html = ":)";
}


echo $html;