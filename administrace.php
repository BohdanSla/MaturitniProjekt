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


        $stmt = $db->prepare("SELECT nazev FROM znacka;
            SELECT nazev FROM sport;
            SELECT kategorie FROM kategorie_produktu;
            SELECT nazev FROM material;
            SELECT nazev FROM barva;
            SELECT nazev FROM velikost;
        ");
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
            $nazvyKategorii .= '<option value="' . $value["kategorie"] . '">' . $value["kategorie"] .'</option>';
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

        $stmt = $db->prepare("SELECT uzivatel.id, `jmeno`, `prijmeni`, `email`, `telefonni_cislo`, `psc`, `ulice`, `mesto`, role.nazev AS role FROM uzivatel JOIN role ON role.id = uzivatel.id_role WHERE jeZaregistrovany = 1 AND role.id != 1;");

        $stmt->execute();

        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $uzivatele = '';

        foreach ($arr as $key => $value) {
            # code...
            $uzivatele .= '<tr><td>' . $value["id"]. '</td><td>' . $value["jmeno"]. '</td><td>' . $value["prijmeni"]. '</td><td>' . $value["email"]. '</td><td>' . $value["telefonni_cislo"]. '</td><td>' . $value["mesto"]. '</td><td>' . $value["ulice"]. '</td><td>' . $value["psc"]. '</td><td>' . $value["role"]. '</td></tr>';
        }
        $html = str_replace("[@uzivatele]",$uzivatele,$html);


        if(isset($_GET["produkt"])) {

            $html = str_replace("[@znacky]",$nazvyZnacek,$html);
            $html = str_replace("[@kategorie]",$nazvyKategorii,$html);
            $html = str_replace("[@sporty]",$nazvySportu,$html);

            $stmt = $db->prepare("
            SELECT produkt.id, produkt.nazev, produkt.popis, produkt.cena, produkt.cena_ve_sleve, znacka.nazev AS znacka, sport.nazev AS sport, kategorie_produktu.kategorie AS kategorie, obrazek.src
            FROM produkt 
            JOIN kategorie_produktu ON kategorie_produktu.id = produkt.id_kategorie_produktu 
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
    }else {
        header("Location: index.php");
    }
} else {
    header("Location: index.php");
}

