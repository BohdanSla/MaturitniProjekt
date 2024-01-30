<?php

declare(strict_types=1);

$html = file_get_contents("kod/html/vypis.html");


spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

use Databaze as Db;

$db = new Db();

$sql = '
SELECT nazev FROM znacka ORDER BY nazev ASC;
SELECT nazev FROM sport ORDER BY nazev ASC;
SELECT nazev FROM velikost ORDER BY nazev ASC;
SELECT nazev FROM barva ORDER BY nazev ASC;
SELECT podkategorie AS nazev FROM kategorie_produktu ORDER BY podkategorie ASC;';

$stmt = $db->prepare($sql);

$stmt->execute();
vypisFiltry("znacka",$arr,$stmt,$html);

$stmt->nextRowSet();
vypisFiltry("sport",$arr,$stmt,$html);

$stmt->nextRowSet();
vypisFiltry("velikost",$arr,$stmt,$html);

$stmt->nextRowSet();
vypisFiltry("barva",$arr,$stmt,$html);

$stmt->nextRowSet();
vypisFiltry("kategorie",$arr,$stmt,$html);





$stmt = $db->prepare("SELECT MAX(COALESCE(produkt.cena_ve_sleve,produkt.cena)) AS nejvetsi_cena FROM produkt;");

$stmt->execute();
$nejvetsiCena = $stmt->fetch(PDO::FETCH_ASSOC);

$html = preg_replace("/\[@maximum\]/",strval($nejvetsiCena["nejvetsi_cena"]),$html);

$sql = 'SELECT DISTINCT obrazek.src,produkt.id, produkt.nazev, produkt.cena, produkt.cena_ve_sleve, sport.nazev AS sport 
FROM produkt 
JOIN kategorie_produktu ON kategorie_produktu.id = produkt.id_kategorie_produktu 
JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id
JOIN znacka ON znacka.id = produkt.id_znacky
JOIN sport ON sport.id = produkt.id_sportu
JOIN mnozstvi ON mnozstvi.id_produktu = produkt.id
JOIN velikost ON velikost.id = mnozstvi.id_velikosti
JOIN barva ON barva.id = mnozstvi.id_barvy
JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku WHERE obrazek.src LIKE "%main%"';

$filtry = "";
$nazvy = ["sport","znacka","velikost","barva"];
$parametry = [];
$nejmensiCena = "";
$nejvetsiCena = "";

if (isset($_GET["filtrovat"])) {
    # code...
    if(isset($_GET["nejmensiCena"])) {
        if($_GET["nejmensiCena"] != "") {
            $filtry .= " AND COALESCE(produkt.cena_ve_sleve,produkt.cena) >= :nejmensiCena";
            $parametry[":nejmensiCena"] = $_GET["nejmensiCena"];
            $nejmensiCena = $_GET["nejmensiCena"];
        }
    }
    if(isset($_GET["nejvetsiCena"])) {
        if($_GET["nejvetsiCena"] != "") {
            $filtry .= " AND COALESCE(produkt.cena_ve_sleve,produkt.cena) <= :nejvetsiCena";
            $parametry[":nejvetsiCena"] = $_GET["nejvetsiCena"];
            $nejvetsiCena = $_GET["nejvetsiCena"];
        }
    }

    
    if(isset($_GET["kategorie"])) {

        $placeholdery = "";
        foreach ($_GET["kategorie"] as $key2 => $value2) {
            # code...
            $placeholdery .= ":kategorie$key2,";
            $parametry[":kategorie$key2"] = $value2;

            $html = str_replace('filtr="[@' . $value2 . ']"',"checked",$html);
        }
        
        $placeholdery = rtrim($placeholdery,",");

        $filtry .= " AND kategorie_produktu.podkategorie IN ($placeholdery)";

    }

    foreach ($nazvy as $key => $value) {
        # code...
        if (isset($_GET[$value])) {
            # code...
            $placeholdery = "";
            foreach ($_GET[$value] as $key2 => $value2) {
                # code...
                $placeholdery .= ":$value$key2,";
                $parametry[":$value$key2"] = $value2;

                $html = str_replace('filtr="[@' . $value2 . ']"',"checked",$html);
            }
            
            $placeholdery = rtrim($placeholdery,",");

            $filtry .= " AND $value.nazev IN ($placeholdery)";
        }
    }
    $sql .= $filtry;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($parametry);
} else if(isset($_GET["odeslat"])) {
    if(trim($_GET["hledat"]) != "") {
        $sql .= " AND produkt.nazev LIKE :nazev";
        $stmt = $db->prepare($sql);
        $stmt->execute([":nazev" => '%' . $_GET["hledat"] . '%']);
    } else if (trim($_GET["odeslat"]) == "") {
        $stmt = $db->prepare($sql);
        $stmt->execute();
    }
} else {
    $stmt = $db->prepare($sql);
    $stmt->execute();
}

if($nejmensiCena > $nejvetsiCena) {
    $nejvetsiCena = $nejmensiCena;
}

$html = str_replace("[@nejmensiCena]",$nejmensiCena,$html);
$html = str_replace("[@nejvetsiCena]",$nejvetsiCena,$html);


$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

$produkty = "";

foreach ($arr as $key => $value) {


    $src = "obrazky/" . $value["src"];
    $odkaz = "produkt.php?id=" . $value["id"];
    $cenaVesleve = "";
    $maCenuVeSleve = "";
    
    $produkty .= '<a [@ma-cenu-ve-sleve] href="' . $odkaz . '"><img src="' . $src .'"><div class="parametry"><h2>' . $value["nazev"] . '</h2><b id="sport">' . $value["sport"] . '</b><b id="cena">' . $value["cena"] . ' Kč</b> [@cena-ve-sleve] </div></a>';
    
    if ($value["cena_ve_sleve"] != null) {
        # code...
        $cenaVesleve = "<b id=\"cenaVeSleve\">" . $value["cena_ve_sleve"] . " Kč</b>";
        $maCenuVeSleve = 'class="maCenuVeSleve"';
    }
    $produkty = preg_replace("/\[@cena-ve-sleve\]/",$cenaVesleve,$produkty,1);
    $produkty = preg_replace("/\[@ma-cenu-ve-sleve\]/",$maCenuVeSleve,$produkty,1);
}

$html = str_replace("[@produkty]",$produkty,$html);

function vypisFiltry(String $typFiltru,&$arr,&$stmt,&$html) {
    $filtry = "";

    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($arr as $key => $value) {
        # code...
        $filtr = $value["nazev"];
        $filtry .= '<div><input type="checkbox" value="' . $filtr . '" name="' . $typFiltru . '[]" id="' . $filtr .'" filtr="[@' . $filtr .  ']"><label for="' . $filtr .'">' . $filtr . '</label></div>';
    }

    $html = str_replace("[@" . $typFiltru ."]",$filtry,$html);
}

$html = preg_replace('/filtr="\[@[a-zA-Z0-9\s]+\]"/',"",$html);


echo $html;