<?php

declare(strict_types=1);

session_start();




spl_autoload_register(fn($trida) => require_once "$trida.class.php");


use Databaze as Db;

$db = new Db();

$html = file_get_contents("kod/html/shrnuti.html");


if(isset($_SESSION["user"])) {

    $stmt = $db->prepare("SELECT sleva FROM slevovy_kod WHERE id = (SELECT id_slevoveho_kodu FROM objednavka WHERE id_uzivatele = :id AND jeObjednana = 0);
    SELECT produkt.nazev,COALESCE(produkt.cena_ve_sleve,produkt.cena) AS cena,obrazek.src,barva.nazev AS barva,velikost.nazev AS velikost,produkty_v_objednavce.pocet FROM produkt JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku JOIN produkty_v_objednavce ON produkty_v_objednavce.id_produktu = produkt.id JOIN barva ON barva.id = produkty_v_objednavce.id_barvy JOIN velikost ON velikost.id = produkty_v_objednavce.id_velikosti WHERE obrazek.src LIKE '%main%' AND produkty_v_objednavce.id_objednavky = (SELECT id FROM objednavka WHERE jeObjednana = 0 AND id_uzivatele = :id);
    SELECT jmeno, prijmeni, email, telefonni_cislo, psc, ulice, mesto FROM uzivatel WHERE id = :id");

    $stmt->execute([":id" => $_SESSION["user"]]);

    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt->nextRowset(); 
    $produkty = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $objednavka = '';
    $nazvyProduktu = '';
    $zpravaSleva = '<p>Není použit žádný slevový kód</p>';
    
    if(count($produkty) > 0) {
        $celkovaCena = 0;
        foreach ($produkty as $key => $value) {
            # code...
            $nazvyProduktu = $nazvyProduktu . $value["nazev"] .  ",";
            $value["src"] = "obrazky/" . $value["src"];
            $value["cena"] = $value["cena"] * $value["pocet"];
            $celkovaCena += $value["cena"];

            
            $objednavka .= '<tr><td><div><img src="' . $value["src"] . '"></div></td><td><b>' . $value["nazev"] . "</b></td><td>" . $value["barva"] . " | " . $value["velikost"] . " | " . $value["pocet"] . " ks</td><td><b>" . zformulujCenu(strval($value["cena"])) ."</b></td></tr>";
        }
        if(count($arr) > 0) {
            $celkovaCena -= $arr[0]["sleva"];
            $zpravaSleva = '<p style="color:green;">Sleva uplatněna!</p>';
            $objednavka .= "<tr><td colspan='4'><b style='display:flex;justify-content:flex-end;color:green'>Slevový kód: -". $arr[0]["sleva"] ." Kč</b></td></tr>";
        }
        $objednavka .= "<tr><td colspan='4'><b> Celková cena: " . zformulujCenu(strval($celkovaCena)) . "</b></td></tr>";
        $nazvyProduktu = rtrim($nazvyProduktu,",");
    }
    $html = str_replace("[@zbozi]",$objednavka,$html);
    $html = str_replace("[@sleva]",$zpravaSleva,$html);


    $stmt->nextRowset();
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $objednavka = '';
    
    if(count($arr) > 0) {
        foreach($arr as $key => $value) {
            $objednavka = "<tr><td>" . $value["jmeno"] . " " . $value["prijmeni"] . "</td></tr><tr><td>" . $value["email"] . ", " . $value["telefonni_cislo"] . "</td></tr><tr><td>" . $value["mesto"] .", " . $value["ulice"] . ", " . $value["psc"] . " </td></tr>";
        }
    }
    $html = str_replace("[@udaje]",$objednavka,$html);

    
    if(isset($_POST["uplatnit"])) {

        $nazvyProduktu = explode(",", $nazvyProduktu);
        $values = [":kod" => $_POST["kod"]];

        // Create named placeholders for each element in $nazvyProduktu
        $placeholdersArray = array_map(function ($key) {
            return ":nazev$key";
        }, array_keys($nazvyProduktu));
        $placeholders = implode(',', $placeholdersArray);

        $sql = "SELECT id_slevoveho_kodu, id_sportu AS kod FROM slevovy_kod_sport JOIN slevovy_kod ON slevovy_kod.id = slevovy_kod_sport.id_slevoveho_kodu WHERE id_sportu IN (SELECT id_sportu FROM produkt WHERE nazev IN ($placeholders)) AND kod = :kod AND expirace > CURRENT_DATE() AND bylPouzit = 0 UNION SELECT id_slevoveho_kodu, id_znacky FROM slevovy_kod_znacka JOIN slevovy_kod ON slevovy_kod.id = slevovy_kod_znacka.id_slevoveho_kodu WHERE id_znacky IN (SELECT id_znacky FROM produkt WHERE nazev IN ($placeholders)) AND kod = :kod AND expirace > CURRENT_DATE() AND bylPouzit = 0;";

        $stmt = $db->prepare($sql);
        foreach ($nazvyProduktu as $key => $value) {
            $stmt->bindParam(":nazev$key", $value);
        }

        $stmt->bindParam(":kod", $_POST["kod"]);
        $stmt->execute();

        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($arr) > 0) {
            $stmt = $db->prepare("UPDATE slevovy_kod SET bylPouzit = 1 WHERE kod = :kod;
            UPDATE objednavka SET id_slevoveho_kodu = (SELECT id FROM slevovy_kod WHERE kod = :kod) WHERE id_uzivatele = :id AND jeObjednana = 0");
            
            $stmt->execute([":kod" => $_POST["kod"],":id" => $_SESSION["user"]]);
        }
        header("Location: shrnuti.php");
    }
    
    if(isset($_POST["odeslat"])) {
        
        $nazvyProduktu = explode(",",$nazvyProduktu);
        $placeholdery = '';
        $values = [":id" => $_SESSION["user"]];

        for ($i=0; $i < count($nazvyProduktu); $i++) { 
            # code...
            $placeholdery .= "(:id,(SELECT id FROM produkt WHERE nazev = :nazev$i)),";
            $values[":nazev$i"] = $nazvyProduktu[$i];
        }
        $placeholdery = rtrim($placeholdery,",");
        

        $stmt = $db->prepare("SELECT id FROM objednavka WHERE jeObjednana = 0 AND id_uzivatele = :id");
        $stmt->execute([":id" => $_SESSION["user"]]);
        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //! poupravit maybe??????????????????????????
        file_put_contents("temp.txt","cisloObjednavky:".$arr[0]["id"]);

        // ! pokud chci mit neduplicitni radky v tabulce pouziju CONSTRAINT na tabulku a hodnoty a v INSERTU dam IGNORE
        $stmt = $db->prepare("INSERT IGNORE INTO zakoupene_produkty (id_uzivatele,id_produktu) VALUES $placeholdery;UPDATE objednavka SET jeObjednana = 1 WHERE id_uzivatele = :id AND jeObjednana = 0");
        $stmt->execute($values);
        

        header("Location: Objednavka.php");
    }
} 

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

echo $html;