<?php

declare(strict_types=1);

spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

use Databaze as Db;


$html = file_get_contents("kod/html/produkt.html");

if(isset($_GET["nazev"])) {
    $db = new Db();

    $stmt = $db->prepare("SELECT produkt.nazev,produkt.popis,produkt.cena,produkt.cena_ve_sleve,produkt.hodnoceni_produktu,
    znacka.nazev AS znacka,sport.nazev AS sport,
    obrazek.src
    FROM produkt
    JOIN znacka ON znacka.id = produkt.id_znacky
    JOIN sport ON sport.id = produkt.id_sportu
    JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id
    JOIN obrazek ON obrazek.id =  obrazky_k_produktu.id_obrazku
    WHERE produkt.nazev = :nazev");

    $stmt->execute([":nazev" => $_GET["nazev"]]);

    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($arr) > 0) {
        # code...
        $cenaVeSleve = "";
        $src = "obrazky/" . $arr[0]["src"];

        if (isset($value["cena-ve-sleve"])) {
            # code...
            $cenaVeSleve = zformulujCenu(strval($cenaVeSleve));
        }

        $html = preg_replace("/\[@nazev\]/",$arr[0]["nazev"],$html);
        $html = preg_replace("/\[@popis\]/",$arr[0]["popis"],$html);
        $html = preg_replace("/\[@cena\]/",zformulujCenu(strval($arr[0]["cena"])),$html);
        $html = preg_replace("/\[@cena-ve-sleve\]/",$cenaVeSleve,$html);
        $html = preg_replace("/\[@znacka\]/",$arr[0]["znacka"],$html);
        $html = preg_replace("/\[@sport\]/",$arr[0]["sport"],$html);
        $html = preg_replace("/\[@hlavni-obrazek\]/",$src,$html);
        $html = preg_replace("/\[@hodnoceni-produktu\]/",strval($arr[0]["hodnoceni_produktu"]),$html);
    } else {
        $html .= "hledaný produkt nebyl nalezen...";
    }


} else {
    $html .= "Něco je blbě...";
}


echo $html;

function zformulujCenu(string $cena): string {

    $zformulovanaCena = "";
    $delka = mb_strlen($cena);

    for ($i=$delka - 1; $i >= 0; $i--) { 
        # code...
        $zformulovanaCena = $cena[$i] . $zformulovanaCena;
        if (($delka - $i) % 3 == 0 && $i > 0) {
            $zformulovanaCena = " " . $zformulovanaCena;
        }
    }
    return $cena = $zformulovanaCena . " Kč";
}
