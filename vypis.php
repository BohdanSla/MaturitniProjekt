<?php

declare(strict_types=1);

session_start();

$html = file_get_contents("kod/html/vypis.html");


spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

use Databaze as Db;

if(empty($_GET)) {
    header("Location: vypis.php?stranka=1");
} else if (!isset($_GET["stranka"])){
    header("Location: " . $_SERVER['REQUEST_URI'] . "&stranka=1");
}

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

$sql = '
SELECT nazev FROM znacka ORDER BY nazev ASC;
SELECT nazev FROM sport ORDER BY nazev ASC;
SELECT nazev FROM velikost ORDER BY nazev ASC;
SELECT nazev FROM barva ORDER BY nazev ASC;
SELECT kategorie AS nazev FROM kategorie_produktu ORDER BY kategorie ASC;';

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

$offset = 0;
if(isset($_GET["stranka"])) {
    $offset = 12 * (intval($_GET["stranka"]) - 1);
}


$sql = 'SELECT DISTINCT obrazek.src,produkt.id, produkt.nazev, produkt.cena, produkt.cena_ve_sleve, sport.nazev AS sport 
FROM produkt 
JOIN kategorie_produktu ON kategorie_produktu.id = produkt.id_kategorie_produktu 
JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id
JOIN znacka ON znacka.id = produkt.id_znacky
JOIN sport ON sport.id = produkt.id_sportu
JOIN mnozstvi ON mnozstvi.id_produktu = produkt.id
JOIN velikost ON velikost.id = mnozstvi.id_velikosti
JOIN barva ON barva.id = mnozstvi.id_barvy
JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku 
WHERE obrazek.src 
LIKE "%main%"';

$filtry = "";
$nazvy = ["sport","znacka","velikost","barva"];
$parametry = [];
$nejmensiCena = "";
$nejvetsiCena = "";
$nazevHledanehoProduktu = "";


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
        
        $filtry .= " AND kategorie_produktu.kategorie IN ($placeholdery)";

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
    $sql .= $filtry . " LIMIT 12 OFFSET :offset";

    $stmt = $db->prepare($sql);

    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    foreach ($parametry as $key => $value) {
        # code...
        if(str_contains($key,"Cena")) { 
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }


    $stmt->execute();
} else if(isset($_GET["odeslat"])) {
    if(trim($_GET["hledat"]) != "") {
        $nazevHledanehoProduktu = " AND produkt.nazev LIKE :nazev";
        $sql .=  $nazevHledanehoProduktu . " LIMIT 12 OFFSET :offset";
        $stmt = $db->prepare($sql);
        $parametry[":nazev"] = '%' . $_GET["hledat"] . '%';

        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':nazev', '%' . $_GET["hledat"] . '%', PDO::PARAM_STR);

        $stmt->execute();
    } else if (trim($_GET["odeslat"]) == "") {
        $sql .= " LIMIT 12 OFFSET :offset";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    }
} else {
    $sql .= " LIMIT 12 OFFSET :offset";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
}

$html = str_replace("[@nejmensiCena]",$nejmensiCena,$html);
$html = str_replace("[@nejvetsiCena]",$nejvetsiCena,$html);

$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = 'SELECT CEIL(count(DISTINCT produkt.id) / 12) AS "pocet" FROM produkt JOIN kategorie_produktu ON kategorie_produktu.id = produkt.id_kategorie_produktu JOIN znacka ON znacka.id = produkt.id_znacky JOIN sport ON sport.id = produkt.id_sportu JOIN mnozstvi ON mnozstvi.id_produktu = produkt.id JOIN velikost ON velikost.id = mnozstvi.id_velikosti JOIN barva ON barva.id = mnozstvi.id_barvy WHERE 1 = 1' . $filtry . $nazevHledanehoProduktu;


$stmt = $db->prepare($sql);
$stmt->execute($parametry);

$pocet = $stmt->fetchAll(PDO::FETCH_ASSOC);

$url = preg_replace("/[&?]stranka=\d+/","",$_SERVER['REQUEST_URI']);

$nasledujici = "";
$predchozi = "";
$stranka = "";
if($pocet[0]["pocet"] != 0) {
    if( $_GET["stranka"] + 1 <= $pocet[0]["pocet"]) {
        if(strcmp($url,"/vypis.php") == 0) {
            $nasledujici = "<a href='" . $url . "?stranka=" . $_GET["stranka"] + 1 ."'>></a>";
        } else {
            $nasledujici = "<a href='" . $url . "&stranka=" . $_GET["stranka"] + 1 ."'>></a>";
        }
    } 
    if($_GET["stranka"] - 1 > 0) {
        if(strcmp($url,"/vypis.php") == 0) {
            $predchozi = "<a href='" . $url . "?stranka=" . $_GET["stranka"] - 1 ."'><</a>";
        } else {
            $predchozi = "<a href='" . $url . "&stranka=" . $_GET["stranka"] - 1 ."'><</a>";
        }
    } 
    $stranka = "<form>$predchozi<b>" . $_GET["stranka"] ."</b>$nasledujici<p> z " . $pocet[0]["pocet"] ." stránek</p></form>";
}

$html = str_replace("[@stranka]",$stranka,$html);


$produkty = "";

if(count($arr) > 0) {
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
} else {
    $produkty = "<p>Bohužel se nenašly žádné výsledky.</p>";
}

$timeout = '';

if(isset($_SESSION["user"])) {
    $timeout = '<script defer src="kod/js/timeout.js"></script>';
}
$nazevHledanehoProduktu = "";
if(isset($_GET["odeslat"])) {
    $nazevHledanehoProduktu = trim($_GET["hledat"]) != "" ? "<h4>Výsledky obsahující: " . $_GET["hledat"] ."</h4><a href='vypis.php'>Zrušit Vyhledávání</a>" : "";
}

$html = str_replace("[@vysledek]",$nazevHledanehoProduktu,$html);


$html = str_replace("[@timeout]",$timeout,$html);
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

$html = preg_replace('/filtr="\[@[a-žA-Ž0-9\s]+\]"/',"",$html);


echo $html;