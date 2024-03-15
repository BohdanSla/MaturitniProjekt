<?php

declare(strict_types=1);

session_start();


spl_autoload_register(fn($trida):int|bool => require_once "$trida.class.php" );

use Databaze as Db;

// Set the inactivity time of 15 minutes (900 seconds)
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

$timeout = '';
$admin = '';

$html = file_get_contents("kod/html/kosik.html");


if (isset($_SESSION["user"])) {

    $timeout = '<script defer src="kod/js/timeout.js"></script>';

    $stmt = $db->prepare("SELECT id_role FROM uzivatel WHERE id = :id");
    $stmt->execute([":id" => $_SESSION["user"]]);

    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if($arr[0]["id_role"] == 1) {
        $admin = '<a href="administrace.php"><li><img src="obrazky/naradi_ikona.svg" alt="naradi_ikona">Administrace</li></a>';
    }
    
    # code...
    $stmt = $db->prepare('SELECT produkt.nazev,
    barva.nazev AS barva,
    velikost.nazev AS velikost,
    COALESCE(cena_ve_sleve,cena) AS cena,
    pocet AS mnozstvi,
    obrazek.src
    FROM `produkty_v_objednavce`
    JOIN produkt ON produkt.id = produkty_v_objednavce.id_produktu
    JOIN barva ON barva.id = produkty_v_objednavce.id_barvy
    JOIN velikost ON velikost.id = produkty_v_objednavce.id_velikosti
    JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id
    JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku
    WHERE id_objednavky = (SELECT id FROM objednavka WHERE jeObjednana = 0 AND id_uzivatele = :id) 
    GROUP BY produkt.id
    ORDER BY produkt.nazev;');

    $stmt->execute([":id" => $_SESSION["user"]]);

    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $produkty = "";

    if(count($arr) > 0) {
        foreach ($arr as $key => $value) {
            # code...
            $src = "obrazky/" . $value["src"];
    
            $produkty .= '<div><section><img src="' . $src . '"><h2>' . $value["nazev"] .'</h2></section><section><div><p>Barva: ' . $value["barva"] . '</p><p>Velikost: ' . $value["velikost"] .'</p></div><b>' . $value["cena"] . ' Kč</b><form method="post">množství:<input type="number" form="pokracovat" name="mnozstvi' . $key .'" id="mnozstvi" min="1" max="5" value="' . $value["mnozstvi"] . '"><button name="odstranit' . $key .'" type="submit"><img src="obrazky/krizek_ikona.svg"></button></form></section></div>';

            if(isset($_POST["odstranit" . $key])) {
                $stmt = $db->prepare("DELETE FROM produkty_v_objednavce WHERE id_objednavky = (SELECT id FROM objednavka WHERE jeObjednana = 0 AND id_uzivatele = :id LIMIT 1) AND id_produktu = (SELECT id FROM produkt WHERE nazev = :nazev LIMIT 1) AND id_barvy = (SELECT id FROM barva WHERE nazev = :barva LIMIT 1) AND id_velikosti = (SELECT id FROM velikost WHERE nazev = :velikost LIMIT 1)");

                $stmt->execute([":id" => $_SESSION["user"],":nazev" => $arr[$key]["nazev"],":barva" => $arr[$key]["barva"],":velikost" => $arr[$key]["velikost"]]);
                

                header("Location: kosik.php");
            }
        }
        $produkty .= '<form method="post" id="pokracovat"><input type="submit" value="Zrušit objednávku" name="zrusit"><input type="submit" value="Pokračovat k údajům" name="odeslat"></form>';
    } else {
        $produkty = "Zatím jste si nevybrali žádné zboží";
    }

    $html = str_replace("[@kosik]",$produkty,$html);


    if(isset($_POST["zrusit"])) {
        $stmt = $db->prepare("SELECT id FROM objednavka WHERE id_uzivatele = :id AND jeObjednana = 0;DELETE FROM objednavka WHERE jeObjednana = 0 AND id_uzivatele = :id");

        $stmt->execute([":id" => $_SESSION["user"]]);

        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $idObjednavky = $arr[0]["id"];

        $stmt = $db->prepare("DELETE FROM produkty_v_objednavce WHERE id_objednavky = :idObjednavky");

        $stmt->execute([":idObjednavky" => $idObjednavky]);

        header("Location: kosik.php");
    }

    if(isset($_POST["odeslat"])) {
        foreach ($arr as $key => $value) {
            # code...

            if($_POST["mnozstvi" . $key] < 6 && $_POST["mnozstvi" . $key] > 0) {
                $stmt = $db->prepare("UPDATE produkty_v_objednavce SET pocet = :pocet WHERE id_objednavky = (SELECT id FROM objednavka WHERE jeObjednana = 0 AND id_uzivatele = :id LIMIT 1) AND id_produktu = (SELECT id FROM produkt WHERE nazev = :nazev LIMIT 1) AND id_barvy = (SELECT id FROM barva WHERE nazev = :barva LIMIT 1) AND id_velikosti = (SELECT id FROM velikost WHERE nazev = :velikost LIMIT 1)");
    
    
                $stmt->execute([":id" => $_SESSION["user"],":nazev" => $value["nazev"],":barva" => $value["barva"],":velikost" => $value["velikost"],":pocet" => $_POST["mnozstvi" . $key]]);
    
                header("Location: udaje.php");
            } else {
                header("Location: kosik.php");
            }

        }
    }
} else {
    $produkty = "";
    if(isset($_SESSION["kosik"]) && count($_SESSION["kosik"]) > 0) {
        $id = [];
        $idPlaceholdery = "";

        foreach ($_SESSION["kosik"] as $key => $value) {
            # code..
            $idProduktu = explode(";",$value)[0];
            if(!in_array($idProduktu,$id)) {
                $idPlaceholdery .= ":id$key" . ",";
                $id[":id$key"] = $idProduktu; 
            }
        }
        $idPlaceholdery = rtrim($idPlaceholdery,",");

        $stmt = $db->prepare("SELECT produkt.nazev,produkt.id, COALESCE(cena_ve_sleve,cena) AS cena, obrazek.src 
        FROM produkt 
        JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id 
        JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku 
        GROUP BY produkt.id
        AND produkt.id IN ($idPlaceholdery) ORDER BY produkt.nazev;");

        $stmt->execute($id);

        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($arr) > 0) {
            foreach ($arr as $key => $value) {
                # code...
                foreach($_SESSION["kosik"] as $key2 => $value2) {
                    $informace = explode(";",$value2);
                    if($value["id"] == $informace[0]) {

                        $src = "obrazky/" . $value["src"];

                        $produkty .= '<div><section><img src="' . $src . '"><h2>' . $value["nazev"] .'</h2></section><section><div><p>Barva: ' . $informace[1] . '</p><p>Velikost: ' . $informace[2] .'</p></div><b>' . $value["cena"] . ' Kč</b><form method="post">množství:<input type="number" form="pokracovat" name="mnozstvi' . $key .$key2 .'" id="mnozstvi" min="1" max="5" value="' . $informace[3] . '"><button name="odstranit' . $key . $key2 .'" type="submit"><img src="obrazky/krizek_ikona.svg"></button></form></section></div>';
                        
                        if(isset($_POST["odstranit" . $key . $key2])) {

                            $index = array_search($value["id"] . ";" . $informace[1] .";". $informace[2] . ";". $informace[3],$_SESSION["kosik"]);

                            unset($_SESSION["kosik"][$index]);
                            
                            header("Location: kosik.php");
                        }
                        if(isset($_POST["odeslat"])) {
                            $informace = explode(";",$value2);
                            $_SESSION["kosik"][$key2] = $informace[0] . ";" . $informace[1] . ";" . $informace[2] . ";" . $_POST["mnozstvi" . $key . $key2];
                            
                            header("Location: udaje.php");
                        }
                    }
                }
            }
            $produkty .= '<form method="post" id="pokracovat"><input type="submit" value="Zrušit objednávku" name="zrusit"><input type="submit" value="Pokračovat k údajům" name="odeslat"></form>';
        }
    } else {
        $produkty = "Zatím jste si nevybrali žádné zboží";
    }
    $html = str_replace("[@kosik]",$produkty,$html);
    
    if(isset($_POST["zrusit"])) {
        unset($_SESSION["kosik"]);
        header("Location: kosik.php");
    }
}

$html = str_replace("[@timeout]",$timeout,$html);
$html = str_replace("[@admin]",$admin,$html);

echo $html;