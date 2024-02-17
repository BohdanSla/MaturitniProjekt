<?php

declare(strict_types=1);

session_start();



spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

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


$html = file_get_contents("kod/html/produkt.html");
$zprava = '';
$timeout = '';

$db = new Db();

if(isset($_GET["id"])) {

    $idProduktu = $_GET["id"];

    $stmt = $db->prepare('SELECT produkt.nazev,produkt.popis,produkt.cena,produkt.cena_ve_sleve,FORMAT(AVG(recenze.pocet_hvezd),1) AS hodnoceni_produktu,
    znacka.nazev AS znacka,sport.nazev AS sport,kategorie_produktu.kategorie
    FROM produkt
    JOIN znacka ON znacka.id = produkt.id_znacky
    JOIN sport ON sport.id = produkt.id_sportu
    JOIN kategorie_produktu ON kategorie_produktu.id = produkt.id_kategorie_produktu
    LEFT JOIN recenze ON recenze.id_produktu = produkt.id
    WHERE produkt.id = :idProduktu;
    SELECT barva.nazev,obrazek.src FROM obrazek JOIN obrazky_k_produktu ON obrazky_k_produktu.id_obrazku = obrazek.id JOIN produkt ON produkt.id = obrazky_k_produktu.id_produktu JOIN barva ON barva.id = obrazky_k_produktu.id_barvy WHERE produkt.id = :idProduktu ORDER BY barva.nazev;
    SELECT barva.nazev AS barva, velikost.nazev AS velikost, mnozstvi.pocet FROM mnozstvi JOIN barva ON barva.id = mnozstvi.id_barvy JOIN velikost ON velikost.id = mnozstvi.id_velikosti JOIN produkt ON produkt.id = mnozstvi.id_produktu WHERE mnozstvi.id_produktu = :idProduktu ORDER BY velikost.nazev;
    SELECT * FROM zakoupene_produkty WHERE id_produktu = :idProduktu AND id_uzivatele = :id;
    SELECT uzivatel.jmeno AS jmeno,uzivatel.prijmeni, recenze.recenze, recenze.pocet_hvezd FROM recenze JOIN uzivatel ON uzivatel.id = recenze.id_uzivatele WHERE recenze.id_produktu = :idProduktu AND recenze.id_uzivatele = :id;
    SELECT uzivatel.jmeno AS jmeno,uzivatel.prijmeni, recenze.recenze, recenze.pocet_hvezd FROM recenze JOIN uzivatel ON uzivatel.id = recenze.id_uzivatele WHERE recenze.id_produktu = :idProduktu AND recenze.id_uzivatele != :id;
    SELECT material.nazev,materialy_produktu.procento_materialu
    FROM produkt 
    JOIN materialy_produktu ON materialy_produktu.id_produktu = produkt.id
    JOIN material ON material.id = materialy_produktu.id_materialu
    WHERE produkt.id = :idProduktu;');

    $uzivatel = $_SESSION["user"] ?? "";

    $stmt->execute([":idProduktu" => $idProduktu,":id" => $uzivatel]);

    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($arr[0]["nazev"] != NULL) {
        # code...
        $cena = "<h2>" . zformulujCenu(strval($arr[0]["cena"])) . "</h2>";
        $hodnoceniProduktu = $arr[0]["hodnoceni_produktu"] ?? "nehodnocen";

        if (isset($arr[0]["cena_ve_sleve"])) {
            # code...
            $cena = "<b>Původní cena: <s>" . zformulujCenu(strval($arr[0]["cena"])) . "</s></b><h2>Akční cena: " . zformulujCenu(strval($arr[0]["cena_ve_sleve"])) . "</h2>";
        }

        $html = str_replace("[@nazev]",$arr[0]["nazev"],$html);
        $html = str_replace("[@cena]",$cena,$html);
        $html = str_replace("[@hodnoceni-produktu]",$hodnoceniProduktu,$html);
        $html = str_replace("[@popis]",$arr[0]["popis"],$html);
        $html = str_replace("[@kategorie]",$arr[0]["kategorie"],$html);
        $html = str_replace("[@znacka]",$arr[0]["znacka"],$html);
        $html = str_replace("[@sport]",$arr[0]["sport"],$html);
    } else {
        $html = "hledaný produkt nebyl nalezen...";
    }



    $stmt->nextRowset();
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($arr) > 0) {

        $obrazky = "";

        foreach ($arr as $key => $value) {

            # code...
            $src  = "obrazky/" . $value["src"];
            $obrazky .= '<option value="obrazky/' . $value["src"] . '" data-barva="' . $value["nazev"] . '"></option>';
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
    $mojeRecenze = [];
    
    
    if(count($arr) > 0) {
        $stmt->nextRowset();
        $mojeRecenze = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        
        if(count($mojeRecenze) > 0) {
            foreach ($mojeRecenze as $key => $value) {
                # code...
                $recenze .= '<div><div class="jmeno-hodnoceni"><h3>' . $value["jmeno"] . ' ' . $value["prijmeni"] .'</h3><div><b>' . $value["pocet_hvezd"] . '/5 </b><img src="obrazky/hvezda_ikona.svg"></div></div><p>' . $value["recenze"] .'</p><form method="post"><b>Upravit recenzi</b></form></div>';
            }
        } else {
            if(isset($_SESSION["user"])) {
                $recenze = '<p>Napsat recenzi</p>';
            } 
        }
    } else {
        $stmt->nextRowset();
        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(isset($_SESSION["user"])) {
            $recenze = '<h5>Abyste mohli napsat recenzi, musíte si nejdřív zakoupit</h5>';
        } else {
            $recenze = '<h5>Abyste mohli napsat recenzi, musíte se přihlásit a koupit produkt</h5>';
        }

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
    }

    if(count($mojeRecenze) < 1 && count($arr) < 1) {
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
            $materialy .= $value["nazev"] . ": " . $value["procento_materialu"] . " %, ";
        }

        $materialy = rtrim($materialy,", ");
        $materialy .= "</td></tr>";

    }
    
    $html = str_replace("[@materialy]",$materialy,$html);



    
    if(isset($_SESSION["user"])) {

        $timeout = '<script defer src="kod/js/timeout.js"></script>';

        if(isset($_POST["odeslat"])) {

            if($_POST["mnozstvi"] > 0 && $_POST["mnozstvi"] < 6) {

                $stmt = $db->prepare("SELECT pocet FROM produkty_v_objednavce WHERE id_produktu = :idProduktu AND id_barvy =(SELECT id FROM barva WHERE nazev = :barva LIMIT 1) AND id_velikosti = (SELECT id FROM velikost WHERE nazev = :velikost LIMIT 1)AND id_objednavky = (SELECT id FROM objednavka WHERE id_uzivatele = :id AND jeObjednana = 0)");
                $stmt->execute([":velikost" => $_POST["velikost"],":barva" => $_POST["barva"],":idProduktu" => $idProduktu,":id" => $uzivatel]);
    
                $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $pocet = 0;
    
                if(count($arr) > 0) {
                    $pocet = $arr[0]["pocet"];
                }
    
    
                //!zkontrolovat
                $stmt = $db->prepare("SELECT pocet FROM mnozstvi WHERE id_produktu = :idProduktu AND id_barvy =(SELECT id FROM barva WHERE nazev = :barva LIMIT 1) AND id_velikosti = (SELECT id FROM velikost WHERE nazev = :velikost LIMIT 1)");
        
                $stmt->execute([":velikost" => $_POST["velikost"],":barva" => $_POST["barva"],":idProduktu" => $idProduktu]);
        
                $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                $dostupnyPocet = $arr[0]["pocet"];
        
                if($dostupnyPocet >= (intval($_POST["mnozstvi"]) + $pocet)) {
                    $stmt = $db->prepare("SELECT id FROM objednavka WHERE id_uzivatele = :id AND jeObjednana = 0");
        
                    $stmt->execute([":id" => $_SESSION["user"]]);
            
                    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                    if(count($arr) < 1) {
                        $stmt = $db->prepare("INSERT INTO objednavka (id_uzivatele,jeObjednana) VALUES (:id,:jeObjednana)");
                
                        $stmt->execute([":id" => $_SESSION["user"],":jeObjednana" => 0]);
                    }
    
                    $values = [":mnozstvi" => (intval($_POST["mnozstvi"]) + $pocet),":velikost" => $_POST["velikost"],":barva" => $_POST["barva"],":idProduktu" => $idProduktu,":id" => $_SESSION["user"]];
    
                    if($pocet == 0) {
                        $stmt = $db->prepare("INSERT INTO produkty_v_objednavce (id_produktu, id_objednavky, id_barvy, id_velikosti, pocet) VALUES (:idProduktu,(SELECT id FROM objednavka WHERE jeObjednana = 0 AND id_uzivatele = :id LIMIT 1),(SELECT id FROM barva WHERE nazev = :barva LIMIT 1),(SELECT id FROM velikost WHERE nazev = :velikost LIMIT 1),:mnozstvi)");
                        
                    } else {
                        $stmt = $db->prepare("UPDATE produkty_v_objednavce SET pocet = :mnozstvi WHERE id_produktu = :idProduktu AND id_objednavky = (SELECT id FROM objednavka WHERE jeObjednana = 0 AND id_uzivatele = :id LIMIT 1) AND id_barvy = (SELECT id FROM barva WHERE nazev = :barva LIMIT 1) AND id_velikosti = (SELECT id FROM velikost WHERE nazev = :velikost LIMIT 1)");
                    }       
                    $stmt->execute($values);
    
                } 
                header("Location: produkt.php?id=" . $idProduktu);
            }
            
        }
        
        if(isset($_POST["oblibene"])) {
            $stmt = $db->prepare("SELECT id_produktu,id_uzivatele FROM oblibene_produkty WHERE id_produktu = :idProduktu AND id_uzivatele = :id");
    
            $stmt->execute([":id" => $_SESSION["user"],":idProduktu" => $idProduktu]);
            
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if(count($arr) > 0) {
                $stmt = $db->prepare("DELETE FROM oblibene_produkty WHERE id_produktu = :idProduktu AND id_uzivatele = :id");
            } else {
                $stmt = $db->prepare("INSERT INTO oblibene_produkty (id_produktu,id_uzivatele) VALUES (:idProduktu,:id)");
            }
            
            $stmt->execute([":id" => $_SESSION["user"],":idProduktu" => $idProduktu]);
        }
    
        if(isset($_POST["napsat"])) {
            $stmt = $db->prepare("INSERT INTO recenze (recenze, pocet_hvezd, id_produktu, id_uzivatele) VALUES (:recenze, :pocetHvezd, :idProduktu, :id)");
    
            $stmt->execute([":recenze" => htmlspecialchars($_POST["recenze"]),":pocetHvezd" => htmlspecialchars($_POST["hvezdy"]),":idProduktu" => $idProduktu, ":id" => $_SESSION["user"]]);
            
            header("Location: produkt.php?id=" . $idProduktu);
        }
        
        if(isset($_POST["odstranit"])) {
            $stmt = $db->prepare("DELETE FROM recenze WHERE id_produktu = :idProduktu AND id_uzivatele = :id");
            
            $stmt->execute([":id" => $_SESSION["user"],":idProduktu" => $idProduktu]);
            
            header("Location: produkt.php?id=" . $idProduktu);
        }
    
        if(isset($_POST["upravit"])) {
            $stmt = $db->prepare("UPDATE recenze SET recenze = :recenze,pocet_hvezd = :pocetHvezd WHERE recenze.id_produktu = :idProduktu AND recenze.id_uzivatele = :id");
    
            $stmt->execute([":recenze" => htmlspecialchars($_POST["recenze"]),":pocetHvezd" => htmlspecialchars($_POST["hvezdy"]),":idProduktu" => $idProduktu,":id" => $_SESSION["user"]]);
            
            header("Location: produkt.php?id=" . $idProduktu);
        }
        
        $stmt = $db->prepare("SELECT id_produktu,id_uzivatele FROM oblibene_produkty WHERE id_produktu = :idProduktu AND id_uzivatele = :id");
        
        $stmt->execute([":id" => $_SESSION["user"],":idProduktu" => $idProduktu]);
        
        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        
        if (count($arr) > 0) {
            # code...
            $html = str_replace("[@oblibene]","obrazky/srdce_cervene_plne_ikona.svg",$html);
        } else {
            $html = str_replace("[@oblibene]","obrazky/srdce_cervene_prazdne_ikona.svg",$html);
        }
    } else {
        
        if(isset($_POST["odeslat"])) {
            if(isset($_POST["barva"]) && isset($_POST["velikost"]) && $_POST["mnozstvi"] != "") {

                if(!isset($_SESSION["kosik"])) {
                    $_SESSION["kosik"] = [];
                }
                
                $jeVKosiku = false;
                
                $str = "$idProduktu;" . $_POST["barva"] .";". $_POST["velikost"];
                $strRegex = str_replace("/","\/",$str);
                
                foreach ($_SESSION["kosik"] as $key => $value) {
                    # code...
                    if(preg_match("/$strRegex;[1-5]/",$value)) {
                        $informace = explode(";",$value);
                        if(intval($informace[3]) + $_POST["mnozstvi"] < 6 && $_POST["mnozstvi"] > 0 && $_POST["mnozstvi"] < 6) {
                            $_SESSION["kosik"][$key] = $strRegex . ";" . (intval($informace[3]) + $_POST["mnozstvi"]);
                            $jeVKosiku = true;
                            break;
                        }
                        if((intval($informace[3]) + $_POST["mnozstvi"]) > 5) {
                            $jeVKosiku = true;
                        }
                    }
                }
                if(!$jeVKosiku) {
                    $_SESSION["kosik"][] = "$idProduktu;" . $_POST["barva"] .";". $_POST["velikost"] . ";". $_POST["mnozstvi"];
                }
                header("Location: produkt.php?id=" . $idProduktu);
            }
            
        }

        
        $html = str_replace("[@oblibene]","obrazky/srdce_cervene_prazdne_ikona.svg",$html);
    }
    
} else {
    $html .= "Něco je blbě...";
}

$html = str_replace("[@zprava]",$zprava,$html);

$html = str_replace("[@timeout]",$timeout,$html);

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
