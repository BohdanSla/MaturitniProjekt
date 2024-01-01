<?php

declare(strict_types=1);

session_start();



spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

use Databaze as Db;
$db = new Db();



if(isset($_SESSION["user"])) {
    $html = file_get_contents("kod/html/ucet.html");

    $stmt = $db->prepare('SELECT jmeno,prijmeni,email,telefonni_cislo,mesto,ulice,psc FROM uzivatel WHERE email = :email;
    SELECT produkt.nazev,obrazek.src FROM produkt JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku JOIN oblibene_produkty ON oblibene_produkty.id_produktu = produkt.id JOIN uzivatel ON uzivatel.id = oblibene_produkty.id_uzivatele WHERE obrazek.src LIKE "%main%" AND id_uzivatele = (SELECT id FROM uzivatel WHERE email = :email) GROUP BY oblibene_produkty.id_produktu;');

    $stmt->execute([":email" => $_SESSION["user"]]);

    $arr = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $html = str_replace("[@jmeno]",$arr["jmeno"],$html);
    $html = str_replace("[@prijmeni]",$arr["prijmeni"],$html);
    $html = str_replace("[@email]",$arr["email"],$html);
    $html = str_replace("[@telefonniCislo]",$arr["telefonni_cislo"],$html);
    $html = str_replace("[@mesto]",$arr["mesto"],$html);
    $html = str_replace("[@ulice]",$arr["ulice"],$html);
    $html = str_replace("[@psc]",$arr["psc"],$html);
    
    $stmt->nextRowSet();
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    if(count($arr) > 0) {
        $oblibene = "";
        $oblibenyObrazek = "";

        foreach ($arr as $key => $value) {
            # code...
            $src = "obrazky/" . $value["src"];

            /////////////////////////
            // !
    
            // if($value["jeOblibeny"] < 0) {
            //     $oblibenyObrazek = "obrazky/srdce_cervene_prazdne_ikona.svg";
            // } else {
            //     $oblibenyObrazek = "obrazky/srdce_cervene_plne_ikona.svg";
            // }
            // <img src="' . $oblibenyObrazek .'">
    
            $oblibene .= '<div><section><img src="' . $src . '"><h3>' . $value["nazev"] . '</h3></section><a href="produkt.php?nazev=' . urlencode($value["nazev"]) .'">Podívat se</a></div>';
        }

        $html = str_replace("[@oblibene]",$oblibene,$html);
    }


} else {
    $html = file_get_contents("kod/html/login.html");


    if(isset($_POST["odeslat"])) {
        $stmt = $db->prepare("SELECT email,heslo FROM uzivatel WHERE email = :email");

        $stmt->execute([":email" => $_POST["email"]]);

        $uzivatel = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($uzivatel) {
            # code...
            if (password_verify($_POST["heslo"],$uzivatel["heslo"])) {
                $_SESSION["user"] = $uzivatel["email"];
                header("Location: ucet.php");
                # code...
            } else {

            }
        }
    }
}

echo $html;
