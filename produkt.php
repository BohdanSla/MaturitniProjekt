<?php

declare(strict_types=1);

session_start();



spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

use Databaze as Db;



$html = file_get_contents("kod/html/produkt.html");

$db = new Db();

if(isset($_GET["nazev"])) {

    $stmt = $db->prepare('SELECT produkt.nazev,produkt.popis,produkt.cena,produkt.cena_ve_sleve,produkt.hodnoceni_produktu,
    znacka.nazev AS znacka,sport.nazev AS sport
    FROM produkt
    JOIN znacka ON znacka.id = produkt.id_znacky
    JOIN sport ON sport.id = produkt.id_sportu
    WHERE produkt.nazev = :nazev;
    SELECT barva.nazev,obrazek.src FROM obrazek JOIN obrazky_k_produktu ON obrazky_k_produktu.id_obrazku = obrazek.id JOIN produkt ON produkt.id = obrazky_k_produktu.id_produktu JOIN barva ON barva.id = obrazky_k_produktu.id_barvy WHERE produkt.nazev = :nazev ORDER BY barva.nazev;
    SELECT barva.nazev AS barva, velikost.nazev AS velikost, mnozstvi.pocet FROM mnozstvi JOIN barva ON barva.id = mnozstvi.id_barvy JOIN velikost ON velikost.id = mnozstvi.id_velikosti JOIN produkt ON produkt.id = mnozstvi.id_produktu WHERE mnozstvi.id_produktu = (SELECT id FROM produkt WHERE nazev = :nazev) ORDER BY velikost.nazev;
    SELECT uzivatel.jmeno AS jmeno,uzivatel.prijmeni, recenze.recenze, recenze.pocet_hvezd FROM recenze JOIN uzivatel ON uzivatel.id = recenze.id_uzivatele WHERE recenze.id_produktu = (SELECT id FROM produkt WHERE nazev = :nazev LIMIT 1);
    SELECT material.nazev,materialy_produktu.procento_materialu
    FROM produkt 
    JOIN materialy_produktu ON materialy_produktu.id_produktu = produkt.id
    JOIN material ON material.id = materialy_produktu.id_materialu
    WHERE produkt.nazev = :nazev;');

    $stmt->execute([":nazev" => rawurldecode($_GET["nazev"])]);

    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($arr) > 0) {
        # code...
        $cena = "<h2>" . zformulujCenu(strval($arr[0]["cena"])) . "</h2>";

        if (isset($arr[0]["cena_ve_sleve"])) {
            # code...
            $cena = "<b>Původní cena: <s>" . zformulujCenu(strval($arr[0]["cena"])) . "</s></b><h2>Akční cena: " . zformulujCenu(strval($arr[0]["cena_ve_sleve"])) . "</h2>";
        }

        $html = str_replace("[@nazev]",$arr[0]["nazev"],$html);
        $html = str_replace("[@popis]",$arr[0]["popis"],$html);
        $html = str_replace("[@cena]",$cena,$html);

        $html = str_replace("[@znacka]",$arr[0]["znacka"],$html);
        $html = str_replace("[@sport]",$arr[0]["sport"],$html);
        $html = str_replace("[@hodnoceni-produktu]",strval($arr[0]["hodnoceni_produktu"]),$html);
    } else {
        $html .= "hledaný produkt nebyl nalezen...";
    }



    $stmt->nextRowset();
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($arr) > 0) {

        $obrazky = "";

        foreach ($arr as $key => $value) {

            # code...
            $src  = "obrazky/" . $value["src"];
            $obrazky .= '<option value="obrazky/' . $value["src"] . '" data-barva=' . $value["nazev"] . '></option>';
        }

        $html = str_replace("[@ostatni-obrazky]",$obrazky,$html);
    }

    $stmt->nextRowset();
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $velikosti = "";

    foreach ($arr as $key => $value) {
        # code...
        $pocet = intval($value["pocet"]);
        $skladem = "";

        if($pocet == 0) {
            $skladem = "Není skladem";
        } else if($pocet < 5) {
            $skladem = "Skladem $pocet ks";
        } else {
            $skladem = "Skladem";
        }

        $velikosti .= '<option data-barva="' . $value["barva"] . '" data-velikost="' . $value["velikost"] . '" data-skladem="' . $skladem . '"></option>';
    }
    $html = str_replace("[@velikosti]",$velikosti,$html);



    $stmt->nextRowset();
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $recenze = '';

    if(count($arr) > 0) {
        foreach ($arr as $key => $value) {
            # code...
            $recenze .= '<div><div class="jmeno-hodnoceni"><h3>' . $value["jmeno"] . ' ' . $value["prijmeni"] .'</h3><div><b>' . $value["pocet_hvezd"] . '/5 </b><img src="obrazky/hvezda_ikona.svg"></div></div><p>' . $value["recenze"] .'</p></div>';
        }
    } else {
        $recenze = "<b>Zatím tu nejsou žádné recenze</b>";
    }

    $html = str_replace("[@recenze]",$recenze,$html);

    $stmt->nextRowset();
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $materialy = "";

    foreach ($arr as $key => $value) {
        # code...
        $materialy .= $value["nazev"] . ": " . $value["procento_materialu"] . " %";
    }

    $html = str_replace("[@materialy]",$materialy,$html);

} else {
    $html .= "Něco je blbě...";
}

if(isset($_SESSION["user"])) {
    if(isset($_POST["odeslat"])) {
        $stmt = $db->prepare("INSERT INTO objednavka (id_uzivatele,jeObjednana) VALUES ((SELECT id FROM uzivatel WHERE email = :email LIMIT 1),:jeObjednana)");

        $stmt->execute(["email" => $_SESSION["user"],"jeObjednana" => 0]);

        $stmt = $db->prepare("INSERT INTO produkty_v_objednavce (id_produktu, id_objednavky, id_barvy, id_velikosti, mnozstvi) VALUES ((SELECT id FROM produkt WHERE nazev = :nazev),(SELECT id FROM objednavka WHERE jeObjednana = 0),(SELECT id FROM barva WHERE nazev = :barva),(SELECT id FROM velikost WHERE nazev = :velikost),:mnozstvi)");

        $stmt->execute([":mnozstvi" => $_POST["mnozstvi"],":velikost" => $_POST["velikost"],":barva" => $_POST["barva"],":nazev" => $_POST["puvodniNazev"]]);
    }

    if(isset($_POST["oblibene"])) {
        $stmt = $db->prepare("SELECT id_produktu,id_uzivatele FROM oblibene_produkty WHERE id_produktu =(SELECT id FROM produkt WHERE nazev = :nazev) AND id_uzivatele = (SELECT id FROM uzivatel WHERE email = :email)");

        $stmt->execute(["email" => $_SESSION["user"],":nazev" => $_POST["puvodniNazev"]]);

        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($arr) > 0) {
            $stmt = $db->prepare("DELETE FROM oblibene_produkty WHERE id_produktu =(SELECT id FROM produkt WHERE nazev = :nazev) AND id_uzivatele = (SELECT id FROM uzivatel WHERE email = :email)");
        } else {
            $stmt = $db->prepare("INSERT INTO oblibene_produkty (id_produktu,id_uzivatele) VALUES ((SELECT id FROM produkt WHERE nazev = :nazev),(SELECT id FROM uzivatel WHERE email = :email LIMIT 1))");
        }
        
        $stmt->execute(["email" => $_SESSION["user"],":nazev" => $_POST["puvodniNazev"]]);
    }

    $stmt = $db->prepare("SELECT id_produktu,id_uzivatele FROM oblibene_produkty WHERE id_produktu =(SELECT id FROM produkt WHERE nazev = :nazev) AND id_uzivatele = (SELECT id FROM uzivatel WHERE email = :email)");

    $stmt->execute(["email" => $_SESSION["user"],":nazev" => rawurldecode($_GET["nazev"])]);

    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    if (count($arr) > 0) {
        # code...
        $html = str_replace("[@oblibene]","obrazky/srdce_cervene_plne_ikona.svg",$html);
    } else {
        $html = str_replace("[@oblibene]","obrazky/srdce_cervene_prazdne_ikona.svg",$html);
    }
} else {
    $html = str_replace("[@oblibene]","obrazky/srdce_cervene_prazdne_ikona.svg",$html);
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
