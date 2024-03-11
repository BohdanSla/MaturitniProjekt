<?php

declare(strict_types=1);

session_start();


spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");
use Databaze as Db;

$db = new Db();




$inactivity_time = 15 * 60;



$stmt = $db->prepare("SELECT id_role FROM uzivatel WHERE id = :id");
$stmt->execute([":id" => $_SESSION["user"]]);

$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(isset($_SESSION["user"])) {

    if($arr[0]["id_role"] == 1) {

        $html = file_get_contents("kod/html/administrace.html");

        if (isset($_SESSION['last_timestamp']) && (time() - $_SESSION['last_timestamp']) > $inactivity_time) {
            //Redirect user to login page
            header("Location: odhlasit.php");
        }else{
            // Regenerate new session id and delete old one to prevent session fixation attack
            session_regenerate_id(true);
            
            // Update the last timestamp
            $_SESSION['last_timestamp'] = time();
        }

        $timeout = '<script defer src="kod/js/timeout.js"></script>';
        $admin = '<a href="administrace.php"><li><img src="obrazky/naradi_ikona.svg" alt="naradi_ikona">Administrace</li></a>';

        $html = str_replace("[@admin]",$admin,$html);
        $html = str_replace("[@timeout]",$timeout,$html);




        $stranky = ["novyProdukt" => 1,"editaceProduktu" => 2,"vlastnostiProduktu" => 3,"slevoveKody" => 4,"uzivatele" => 5];

        if(isset($_GET["stranka"])) {
            foreach ($stranky as $key => $value) {
                if($key == $_GET["stranka"]) {
                }
            }
        } else {
            header("Location: administrace.php?stranka=novyProdukt");
        }

        $vyhledaneProdukty = '';

        if(isset($_GET["odeslat"])) {
            
            $stmt = $db->prepare('SELECT produkt.id, produkt.nazev
            FROM produkt
            WHERE nazev LIKE :nazev');
            $stmt->execute([":nazev" =>  '%' . $_GET["hledat"] . '%']);
            
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $vyhledaneProdukty = '';

            if(count($arr) > 0) {
                $vyhledaneProdukty = "<h2>Hledanný výraz: " . $_GET["hledat"] . "</h2><div>";
                foreach ($arr as $key => $value) {
                    # code...
                    $vyhledaneProdukty .= '<a href="administrace.php?produkt=' . $value["id"] .'">' . $value["nazev"] .'</a>';
                }
                $vyhledaneProdukty .= "</div>";

            } else {
                $vyhledaneProdukty = "<p>Nenašel se žádný produkt s názvem <b>" . $_GET["hledat"] . "</b></p>";
            }
        }

        $html = str_replace("[@produkty]",$vyhledaneProdukty,$html);


        $stmt = $db->prepare('SELECT nazev FROM znacka;
            SELECT nazev FROM sport;
            SELECT nazev FROM kategorie;
            SELECT nazev FROM material;
            SELECT nazev FROM barva;
            SELECT nazev FROM velikost;
            SELECT slevovy_kod.id, slevovy_kod.kod, slevovy_kod.sleva,slevovy_kod.expirace,slevovy_kod.bylPouzit, COALESCE(znacka.nazev,sport.nazev) AS "znacka/sport" FROM slevovy_kod LEFT JOIN slevovy_kod_znacka ON slevovy_kod_znacka.id_slevoveho_kodu = slevovy_kod.id LEFT JOIN znacka ON znacka.id = slevovy_kod_znacka.id_znacky LEFT JOIN slevovy_kod_sport ON slevovy_kod_sport.id_slevoveho_kodu = slevovy_kod.id LEFT JOIN sport ON sport.id = slevovy_kod_sport.id_sportu;');
        $stmt->execute();

        $znacky = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $nazvyZnacek = "";
        foreach ($znacky as $key => $value) {
            # code...
            $nazvyZnacek .= '<option value="' . $value["nazev"] . '">' . $value["nazev"] .'</option>';
        }
        $html = str_replace("[@vlastnostiZnacky]",$nazvyZnacek,$html);



        $stmt->nextRowSet();

        $sporty = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $nazvySportu = "";
        foreach ($sporty as $key => $value) {
            # code...
            $nazvySportu .= '<option value="' . $value["nazev"] . '">' . $value["nazev"] .'</option>';
        }
        $html = str_replace("[@vlastnostiSporty]",$nazvySportu,$html);
       
        

        $stmt->nextRowSet();

        $kategorie = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $nazvyKategorii = "";
        foreach ($kategorie as $key => $value) {
            # code...
            $nazvyKategorii .= '<option value="' . $value["nazev"] . '">' . $value["nazev"] .'</option>';
        }
        $html = str_replace("[@vlastnostiKategorie]",$nazvyKategorii,$html);



        $stmt->nextRowSet();

        $materialy = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $nazvyMaterialu = "";
        foreach ($materialy as $key => $value) {
            # code...
            $nazvyMaterialu .= '<option value="' . $value["nazev"] . '">' . $value["nazev"] .'</option>';
        }
        $html = str_replace("[@vlastnostiMaterialy]",$nazvyKategorii,$html);



        $stmt->nextRowSet();

        $barvy = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $nazvyBarvy = "";
        foreach ($barvy as $key => $value) {
            # code...
            $nazvyBarvy .= '<option value="' . $value["nazev"] . '">' . $value["nazev"] .'</option>';
        }
        $html = str_replace("[@vlastnostiBarvy]",$nazvyBarvy,$html);



        $stmt->nextRowSet();

        $velikosti = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $nazvyVelikosti = "";
        foreach ($velikosti as $key => $value) {
            # code...
            $nazvyVelikosti .= '<option value="' . $value["nazev"] . '">' . $value["nazev"] .'</option>';
        }
        $html = str_replace("[@vlastnostiVelikosti]",$nazvyVelikosti,$html);

        // ! Vlastnosti produktu
        // ? kategorie

        if(isset($_POST["kategorieOdstranit"])) {
            $stmt = $db->prepare("SELECT id FROM produkt WHERE id_kategorie = (SELECT id FROM kategorie WHERE nazev = :nazev LIMIT 1)");
            $stmt->execute([":nazev" => $_POST["kategorieVlastnost"]]);

            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(count($arr) == 0) {
                $stmt = $db->prepare("DELETE FROM kategorie WHERE nazev = :nazev LIMIT 1)");
                $stmt->execute([":nazev" => $_POST["kategorieVlastnost"]]);
            }
        }

         // ? sport

         if(isset($_POST["sportOdstranit"])) {
            $stmt = $db->prepare("SELECT id FROM produkt WHERE id_sportu = (SELECT id FROM sport WHERE nazev = :nazev LIMIT 1)");
            $stmt->execute([":nazev" => $_POST["sportVlastnost"]]);

            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(count($arr) == 0) {
                $stmt = $db->prepare("DELETE FROM sport WHERE nazev = :nazev LIMIT 1)");
                $stmt->execute([":nazev" => $_POST["sportVlastnost"]]);
            }
        }

         // ? znacka

         if(isset($_POST["znackaOdstranit"])) {
            $stmt = $db->prepare("SELECT id FROM produkt WHERE id_znacky = (SELECT id FROM znacka WHERE nazev = :nazev LIMIT 1)");
            $stmt->execute([":nazev" => $_POST["znackaVlastnost"]]);

            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(count($arr) == 0) {
                $stmt = $db->prepare("DELETE FROM znacka WHERE nazev = :nazev LIMIT 1)");
                $stmt->execute([":nazev" => $_POST["znackaVlastnost"]]);
            }
        }

         // ? material

         if(isset($_POST["materialOdstranit"])) {
            $stmt = $db->prepare("SELECT id FROM materialy_produktu WHERE id_materialu = (SELECT id FROM material WHERE nazev = :nazev LIMIT 1)");
            $stmt->execute([":nazev" => $_POST["materialVlastnost"]]);

            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(count($arr) == 0) {
                $stmt = $db->prepare("DELETE FROM material WHERE nazev = :nazev LIMIT 1)");
                $stmt->execute([":nazev" => $_POST["materialVlastnost"]]);
            }
        }

         // ? velikost

         if(isset($_POST["velikostOdstranit"])) {
            $stmt = $db->prepare("SELECT id FROM varianty WHERE id_velikosti = (SELECT id FROM velikost WHERE nazev = :velikost LIMIT 1)");
            $stmt->execute([":nazev" => $_POST["velikostVlastnost"]]);

            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(count($arr) == 0) {
                $stmt = $db->prepare("DELETE FROM velikost WHERE nazev = :nazev LIMIT 1)");
                $stmt->execute([":nazev" => $_POST["velikostVlastnost"]]);
            }
        }

         // ? barva

         if(isset($_POST["barvaOdstranit"])) {
            $stmt = $db->prepare("SELECT id FROM varianty WHERE id_barvy = (SELECT id FROM barva WHERE nazev = :nazev LIMIT 1);
            SELECT id FROM obrazky_k_produktu WHERE id_barvy = (SELECT id FROM barva WHERE nazev = :nazev LIMIT 1");
            $stmt->execute([":nazev" => $_POST["barvaVlastnost"]]);

            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt->nextRowSet();

            $obrazky = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(count($arr) == 0) {
                if(count($obrazky) == 0) {
                    $stmt = $db->prepare("DELETE FROM barva WHERE nazev = :nazev LIMIT 1)");
                    $stmt->execute([":nazev" => $_POST["barvaVlastnost"]]);
                } 
            }
        }


        //! slevové kódy

        $stmt->nextRowSet();

        $kodyArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $kody = "";
        foreach ($kodyArr as $key => $value) {
            # code...
            $bylPouzit = $value["bylPouzit"] == 1 ? "Ano" : "Ne";

            $datum = new DateTime($value["expirace"]);
            $kody .= '<tr><td>' . $value["id"] .'</td><td>' . $value["kod"] .'</td><td>' . $value["sleva"] . ' Kč</td><td>' . $datum->format("j. n. Y") . '</td><td>' . $value["znacka/sport"] .'</td><td>' . $bylPouzit .'</td><td><input type="submit" name="slevovyKod' . $key.'" value="Odebrat kód"></td></tr>';

            if(isset($_POST["slevovyKod" . $key])) {
                $stmt = $db->prepare("DELETE FROM slevovy_kod WHERE id = :id;
                DELETE FROM slevovy_kod_znacka WHERE id_slevoveho_kodu = :id;
                DELETE FROM slevovy_kod_sport WHERE id_slevoveho_kodu = :id;");

                $stmt->execute([":id" => $value["id"]]);

                header("Location: administrace.php");
            }
        }
        $html = str_replace("[@slevoveKody]",$kody,$html);



        // ! Uživatelé

        $stmt = $db->prepare("SELECT uzivatel.id, `jmeno`, `prijmeni`, `email`, `telefonni_cislo`, `psc`, `ulice`, `mesto`, role.nazev AS role FROM uzivatel JOIN role ON role.id = uzivatel.id_role WHERE jeZaregistrovany = 1 AND role.id != 1;");

        $stmt->execute();

        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $uzivatele = '';

        foreach ($arr as $key => $value) {
            # code...
            $uzivatele .= '<tr><td>' . $value["id"]. '</td><td>' . $value["jmeno"]. '</td><td>' . $value["prijmeni"]. '</td><td>' . $value["email"]. '</td><td>' . $value["telefonni_cislo"]. '</td><td>' . $value["mesto"]. '</td><td>' . $value["ulice"]. '</td><td>' . $value["psc"]. '</td><td>' . $value["role"]. '</td><td><input type="submit" value="smazat účet" name="smazatUcet' . $key .'"></td></tr>';

            if(isset($_POST["smazatUcet" . $key])) {
                $stmt = $db->prepare("SELECT id FROM objednavka WHERE id_uzivatele = :id");
                $stmt->execute([":id" => $value["id"]]);
                $idObjednavek = $stmt->fetchAll(PDO::FETCH_ASSOC);


                $placeholdery = "";
                $parametry = [":id" => $value["id"]];
                foreach ($idObjednavek as $key2 => $value2) {
                    # code...
                    $placeholdery .= ":objednavka$key2,";
                    $parametry[":objednavka$key2"] = $value2["id"];
                    
                }
                $placeholdery = rtrim($placeholdery,",");

                $sql = "DELETE FROM uzivatel WHERE id = :id;
                DELETE FROM objednavka WHERE id_uzivatele = :id;
                DELETE FROM oblibene_produkty WHERE id_uzivatele = :id;
                DELETE FROM zakoupene_produkty WHERE id_uzivatele = :id;
                DELETE FROM recenze WHERE id_uzivatele = :id;
                DELETE FROM produkty_v_objednavce WHERE id_objednavky IN ($placeholdery)";
                $stmt = $db->prepare($sql);
                $stmt->execute($parametry);

                header("Location: administrace.php");

            }
        }
        $html = str_replace("[@uzivatele]",$uzivatele,$html);

        // ! produkt

        // ? smazání produktu

        if(isset($_POST["smazatProdukt"])) {

        }


        if(isset($_GET["produkt"])) {

            $html = str_replace("[@znacky]",$nazvyZnacek,$html);
            $html = str_replace("[@kategorie]",$nazvyKategorii,$html);
            $html = str_replace("[@sporty]",$nazvySportu,$html);

            $stmt = $db->prepare("
            SELECT produkt.id, produkt.nazev, produkt.popis, produkt.cena, produkt.cena_ve_sleve, znacka.nazev AS znacka, sport.nazev AS sport, kategorie.nazev AS kategorie, obrazek.src
            FROM produkt 
            JOIN kategorie ON kategorie.id = produkt.id_kategorie 
            JOIN sport ON sport.id = produkt.id_sportu 
            JOIN znacka ON znacka.id = produkt.id_znacky
            JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id
            JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku
            WHERE produkt.id = :id
            AND obrazek.src LIKE '%main%';");

            $stmt->execute([":id" => $_GET["produkt"]]);


            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $html = str_replace("[@nazevProduktu]",$arr[0]["nazev"],$html);
            $html = str_replace("[@popisProduktu]",$arr[0]["popis"],$html);
            $html = str_replace("[@cenaProduktu]",strval($arr[0]["cena"]),$html);

            $sleva = '';

            if(isset($arr[0]["sleva"])) {
                $sleva = strval($arr[0]["sleva"]);
            }
            $html = str_replace("[@slevaProduktu]",$sleva,$html);
            $html = str_replace("[@znackaProduktu]",$arr[0]["znacka"],$html);
            $html = str_replace("[@sportProduktu]",$arr[0]["sport"],$html);
            $html = str_replace("[@kategorieProduktu]",$arr[0]["kategorie"],$html);
            $html = str_replace("[@hlavniObrazekProduktu]","obrazky/" . $arr[0]["src"],$html);
        }


        echo $html;
    } else {
        header("Location: index.php");
    }
} else {
    header("Location: index.php");
}

