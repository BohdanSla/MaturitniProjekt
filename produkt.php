<?php

declare(strict_types=1);

session_start();



spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

use Databaze as Db;



$html = file_get_contents("kod/html/produkt.html");

$db = new Db();

if(isset($_GET["nazev"])) {

    $stmt = $db->prepare('SELECT produkt.nazev,produkt.popis,produkt.cena,produkt.cena_ve_sleve,FORMAT(AVG(recenze.pocet_hvezd),1) AS hodnoceni_produktu,
    znacka.nazev AS znacka,sport.nazev AS sport,kategorie_produktu.podkategorie
    FROM produkt
    JOIN znacka ON znacka.id = produkt.id_znacky
    JOIN sport ON sport.id = produkt.id_sportu
    JOIN kategorie_produktu ON kategorie_produktu.id = produkt.id_kategorie_produktu
    JOIN recenze ON recenze.id_produktu = produkt.id
    WHERE produkt.nazev = :nazev
    GROUP BY produkt.id;
    SELECT barva.nazev,obrazek.src FROM obrazek JOIN obrazky_k_produktu ON obrazky_k_produktu.id_obrazku = obrazek.id JOIN produkt ON produkt.id = obrazky_k_produktu.id_produktu JOIN barva ON barva.id = obrazky_k_produktu.id_barvy WHERE produkt.nazev = :nazev ORDER BY barva.nazev;
    SELECT barva.nazev AS barva, velikost.nazev AS velikost, mnozstvi.pocet FROM mnozstvi JOIN barva ON barva.id = mnozstvi.id_barvy JOIN velikost ON velikost.id = mnozstvi.id_velikosti JOIN produkt ON produkt.id = mnozstvi.id_produktu WHERE mnozstvi.id_produktu = (SELECT id FROM produkt WHERE nazev = :nazev) ORDER BY velikost.nazev;
    SELECT uzivatel.jmeno AS jmeno,uzivatel.prijmeni, recenze.recenze, recenze.pocet_hvezd FROM recenze JOIN uzivatel ON uzivatel.id = recenze.id_uzivatele WHERE recenze.id_produktu = (SELECT id FROM produkt WHERE nazev = :nazev LIMIT 1) AND recenze.id_uzivatele = :id;
    SELECT uzivatel.jmeno AS jmeno,uzivatel.prijmeni, recenze.recenze, recenze.pocet_hvezd FROM recenze JOIN uzivatel ON uzivatel.id = recenze.id_uzivatele WHERE recenze.id_produktu = (SELECT id FROM produkt WHERE nazev = :nazev LIMIT 1) AND recenze.id_uzivatele != :id;
    SELECT material.nazev,materialy_produktu.procento_materialu
    FROM produkt 
    JOIN materialy_produktu ON materialy_produktu.id_produktu = produkt.id
    JOIN material ON material.id = materialy_produktu.id_materialu
    WHERE produkt.nazev = :nazev;');

    $uzivatel = $_SESSION["user"] ?? "";

    $stmt->execute([":nazev" => rawurldecode($_GET["nazev"]),":id" => $uzivatel]);

    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($arr) > 0) {
        # code...
        $cena = "<h2>" . zformulujCenu(strval($arr[0]["cena"])) . "</h2>";

        if (isset($arr[0]["cena_ve_sleve"])) {
            # code...
            $cena = "<b>Původní cena: <s>" . zformulujCenu(strval($arr[0]["cena"])) . "</s></b><h2>Akční cena: " . zformulujCenu(strval($arr[0]["cena_ve_sleve"])) . "</h2>";
        }

        $html = str_replace("[@nazev]",$arr[0]["nazev"],$html);
        $html = str_replace("[@cena]",$cena,$html);
        $html = str_replace("[@hodnoceni-produktu]",strval($arr[0]["hodnoceni_produktu"]),$html);
        $html = str_replace("[@popis]",$arr[0]["popis"],$html);
        $html = str_replace("[@kategorie]",$arr[0]["podkategorie"],$html);
        $html = str_replace("[@znacka]",$arr[0]["znacka"],$html);
        $html = str_replace("[@sport]",$arr[0]["sport"],$html);
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
    $tlacitko = '';

    if(count($arr) > 0) {
        foreach ($arr as $key => $value) {
            # code...
            $recenze .= '<div><div class="jmeno-hodnoceni"><h3>' . $value["jmeno"] . ' ' . $value["prijmeni"] .'</h3><div><b>' . $value["pocet_hvezd"] . '/5 </b><img src="obrazky/hvezda_ikona.svg"></div></div><p>' . $value["recenze"] .'</p><form method="post">[@tlacitko]</form></div>';
        }
    }  else {
        $tlacitko = '<b>Napsat recenzi</b>';
    }

    if(isset($_SESSION["user"])) {
        $recenze = str_replace("[@tlacitko]",$tlacitko,$recenze);
    }

    $html = str_replace("[@mojeRecenze]",$recenze,$html);



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

    if(count($arr) > 0) {
    
        $materialy .= "<tr><td>Materiály</td><td>";

        foreach ($arr as $key => $value) {
            # code...
            $materialy .= $value["nazev"] . ": " . $value["procento_materialu"] . " %";
        }

        $materialy .= "</td></tr>";

    }
    
    $html = str_replace("[@materialy]",$materialy,$html);


} else {
    $html .= "Něco je blbě...";
}

if(isset($_SESSION["user"])) {
    if(isset($_POST["odeslat"])) {
        $stmt = $db->prepare("SELECT jeObjednana FROM objednavka WHERE id_uzivatele = :id AND jeObjednana = 0");

        $stmt->execute([":id" => $_SESSION["user"]]);

        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($arr) < 1) {
            $stmt = $db->prepare("INSERT INTO objednavka (id_uzivatele,jeObjednana) VALUES (:id,:jeObjednana)");
    
            $stmt->execute([":id" => $_SESSION["user"],"jeObjednana" => 0]);
        }

        //!zkontrolovat
        $stmt = $db->prepare("SELECT pocet FROM mnozstvi WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :nazev)AND id_barvy =(SELECT id FROM barva WHERE nazev = :barva) AND id_velikosti = (SELECT id FROM velikost WHERE nazev = :velikost)");

        $stmt->execute([":velikost" => $_POST["velikost"],":barva" => $_POST["barva"],":nazev" => $_POST["puvodniNazev"]]);

        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($arr[0]["pocet"] > $_POST["mnozstvi"]) {
            $stmt = $db->prepare("INSERT INTO produkty_v_objednavce (id_produktu, id_objednavky, id_barvy, id_velikosti, pocet) VALUES ((SELECT id FROM produkt WHERE nazev = :nazev),(SELECT id FROM objednavka WHERE jeObjednana = 0),(SELECT id FROM barva WHERE nazev = :barva),(SELECT id FROM velikost WHERE nazev = :velikost),:mnozstvi)");
    
            $stmt->execute([":mnozstvi" => $_POST["mnozstvi"],":velikost" => $_POST["velikost"],":barva" => $_POST["barva"],":nazev" => $_POST["puvodniNazev"]]);
        }
    }

    if(isset($_POST["oblibene"])) {
        $stmt = $db->prepare("SELECT id_produktu,id_uzivatele FROM oblibene_produkty WHERE id_produktu =(SELECT id FROM produkt WHERE nazev = :nazev) AND id_uzivatele = :id");

        $stmt->execute([":id" => $_SESSION["user"],":nazev" => $_POST["puvodniNazev"]]);

        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($arr) > 0) {
            $stmt = $db->prepare("DELETE FROM oblibene_produkty WHERE id_produktu =(SELECT id FROM produkt WHERE nazev = :nazev) AND id_uzivatele = :id");
        } else {
            $stmt = $db->prepare("INSERT INTO oblibene_produkty (id_produktu,id_uzivatele) VALUES ((SELECT id FROM produkt WHERE nazev = :nazev),:id)");
        }
        
        $stmt->execute([":id" => $_SESSION["user"],":nazev" => $_POST["puvodniNazev"]]);
    }

    if(isset($_POST["napsat"])) {
        $stmt = $db->prepare("INSERT INTO recenze (recenze, pocet_hvezd, id_produktu, id_uzivatele) VALUES (:recenze, :pocetHvezd, (SELECT id FROM produkt WHERE nazev = :nazev), :id)");

        $stmt->execute([":recenze" => htmlspecialchars($_POST["recenze"]),":pocetHvezd" => htmlspecialchars($_POST["hvezdy"]),":nazev" => rawurldecode($_GET["nazev"]),":id" => $_SESSION["user"]]);

        header("Location: produkt.php?nazev=" . rawurldecode($_GET["nazev"]));
    }
    
    if(isset($_POST["odstranit"])) {
        $stmt = $db->prepare("DELETE FROM recenze WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :nazev) AND id_uzivatele = :id");

        $stmt->execute([":id" => $_SESSION["user"],":nazev" => rawurldecode($_GET["nazev"])]);

        header("Location: produkt.php?nazev=" . rawurldecode($_GET["nazev"]));
    }

    if(isset($_POST["upravit"])) {
        $stmt = $db->prepare("UPDATE recenze SET recenze = :recenze,pocet_hvezd = :pocetHvezd WHERE recenze.id_produktu = (SELECT id FROM produkt WHERE nazev = :nazev) AND recenze.id_uzivatele = :id");

        $stmt->execute([":recenze" => htmlspecialchars($_POST["recenze"]),":pocetHvezd" => htmlspecialchars($_POST["hvezdy"]),":nazev" => rawurldecode($_GET["nazev"]),":id" => $_SESSION["user"]]);

        header("Location: produkt.php?nazev=" . rawurldecode($_GET["nazev"]));
    }

    $stmt = $db->prepare("SELECT id_produktu,id_uzivatele FROM oblibene_produkty WHERE id_produktu =(SELECT id FROM produkt WHERE nazev = :nazev) AND id_uzivatele = :id");

    $stmt->execute([":id" => $_SESSION["user"],":nazev" => rawurldecode($_GET["nazev"])]);

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
