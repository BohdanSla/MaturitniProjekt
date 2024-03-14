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




        $stranky = ["novyProdukt" => file_get_contents("kod/html/novyProdukt.html"),"editaceProduktu" => file_get_contents("kod/html/editace.html"),"vlastnostiProduktu" =>file_get_contents("kod/html/vlastnosti.html"),"slevoveKody" =>file_get_contents("kod/html/kody.html"),"uzivatele" => file_get_contents("kod/html/uzivatele.html")];

        if(isset($_GET["barva"])) {
            $html = str_replace("[@stranka]",file_get_contents("kod/html/barva.html"),$html);
            $html = str_replace("[@zpet]","administrace.php?stranka=editaceProduktu&produkt=" . $_GET["produkt"] ,$html);
            
            
            $stmt = $db->prepare("SELECT produkt.nazev FROM produkt WHERE produkt.id = :id");
            $stmt->execute([":id" => $_GET["produkt"]]);
            
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $html = str_replace("[@produkt]",$arr[0]["nazev"] . ", " . $_GET["barva"],$html);
            
            $stmt = $db->prepare("SELECT velikost.nazev 
            FROM velikost
            JOIN varianty ON varianty.id_velikosti = velikost.id
            JOIN produkt ON produkt.id = varianty.id_produktu
            JOIN barva ON barva.id = varianty.id_barvy
            WHERE produkt.id = :produkt AND barva.nazev = :barva;
            SELECT obrazek.src 
            FROM obrazek
            JOIN obrazky_k_produktu ON obrazky_k_produktu.id_obrazku = obrazek.id
            JOIN produkt ON produkt.id = obrazky_k_produktu.id_produktu
            JOIN barva ON barva.id = obrazky_k_produktu.id_barvy
            WHERE produkt.id = :produkt AND barva.nazev = :barva;
            SELECT DISTINCT velikost.nazev 
            FROM velikost
            JOIN varianty ON varianty.id_velikosti = velikost.id
            JOIN produkt ON produkt.id = varianty.id_produktu
            JOIN barva ON barva.id = varianty.id_barvy
            WHERE produkt.id != :produkt AND barva.nazev != :barva;");

            $stmt->execute([":produkt" => $_GET["produkt"],
                ":barva" => $_GET["barva"]     
            ]);
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $velikosti = '';
            foreach($arr as $key => $value) {
                $velikosti .= '<tr><td>' . $value['nazev'].  '</td><td><input type="submit" name="odstranitVelikost' . $key.'" value="odstranit velikost"></td><tr>';

                if(isset($_POST["odstranitVelikost" . $key])) {
                    $stmt = $db->prepare("DELETE FROM varianty WHERE id_produktu = :idProduktu AND id_barvy = (SELECT id FROM barva WHERE nazev = :barva LIMIT 1) AND id_velikosti = (SELECT id FROM velikost WHERE nazev = :velikost LIMIT 1)");

                    $stmt->execute([":idProduktu" => $_GET["produkt"],
                        ":barva" => $_GET["barva"],
                        ":velikost" => $value["nazev"]
                    ]);

                    header("Location: " . $_SERVER["REQUEST_URI"]);
                }

            }
            $html = str_replace("[@velikosti]",$velikosti,$html);


            $stmt->nextRowSet();

            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $obrazky = '';
            foreach($arr as $key => $value) {
                $obrazky .= '<tr><td><img src="obrazky/' . $value['src'].  '"></td><td><input type="submit" name="odstranitObrazek' . $key.'" value="odstranit obrázek"></td><tr>';

                if(isset($_POST["odstranitObrazek" . $key])) {
                    // $stmt = $db->prepare("SELECT id_obrazku FROM obrazky_k_produktu WHERE id_produktu = :idProduktu AND id_barvy = (SELECT id FROM barva WHERE nazev = :barva LIMIT 1)");

                    // $stmt->execute([":idProduktu" => $_GET["produkt"],
                    //     ":barva" => $_GET["barva"]]
                    // );
                    // $idObrazku = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // $placeholdery = "";
                    // $parametry = [
                    //     ":obrazek" => $value["src"]
                    // ];
                    // foreach ($idObrazku as $key2 => $value2) {
                    //     # code...
                    //     $placeholdery .= ":obrazek$key2,";
                    //     $parametry[":obrazek$key2"] = $value2["id_obrazku"];
                        
                    // }
                    // $placeholdery = rtrim($placeholdery,",");

                    //DELETE FROM obrazek WHERE id IN ($placeholdery)

                    $stmt = $db->prepare("DELETE FROM obrazky_k_produktu WHERE id_obrazku = (SELECT id FROM obrazek WHERE src = :obrazek LIMIT 1);");

                    $stmt->execute([":obrazek" => $value["src"]]);

                    header("Location: " . $_SERVER["REQUEST_URI"]);
                }
            }
            $html = str_replace("[@obrazky]",$obrazky,$html);
            
            
            
            $stmt->nextRowSet();
            
            $zbyvajiciVelikosti = '';
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($arr as $key => $value) {
                # code...
                $zbyvajiciVelikosti .= '<option value="' . $value["nazev"] .'">' . $value["nazev"] .'</option>';
            }
            $html = str_replace("[@zbyvajiciVelikosti]",$zbyvajiciVelikosti,$html);

            if(isset($_POST["pridatVelikost"])) {
                $stmt = $db->prepare("INSERT INTO varianty (id_produktu,id_barvy,id_velikosti) values (:idProduktu,(SELECT id FROM barva WHERE nazev = :barva LIMIT 1),(SELECT id FROM velikost WHERE nazev = :velikost))");

                $stmt->execute([":idProduktu" => $_GET["produkt"],
                    ":barva" => $_GET["barva"],
                    ":velikost" => htmlspecialchars($_POST["novaVelikost"])
                ]);

                header("Location: " . $_SERVER["REQUEST_URI"]);
            }

            if(isset($_POST["pridatObrazek"])) {
                $target_file = "obrazky/" . basename($_FILES["novyObrazek"]["name"]);
                
                if(getimagesize($_FILES["novyObrazek"]["tmp_name"])) {
                    if (!file_exists($target_file)) {
                        // ! vyřešit main obrazek!!!
                        move_uploaded_file($_FILES["novyObrazek"]["tmp_name"], $target_file);

                        $stmt = $db->prepare("INSERT INTO obrazek (src) VALUES (:src)");
                        $stmt->execute([":src" => $_FILES["novyObrazek"]["name"]]);
                        
                        $id = $db->lastInsertId();
                        
                        $stmt = $db->prepare("INSERT INTO obrazky_k_produktu (id_produktu,id_barvy,id_obrazku) VALUES (:idProduktu,(SELECT id FROM barva WHERE nazev = :nazev),:idObrazku)");

                        $stmt->execute([":idProduktu" => $_GET["produkt"],":nazev" => $_GET["barva"],":idObrazku" => $id]);

                    }
                } 

                header("Location: " . $_SERVER["REQUEST_URI"]);

            }
            
            
        } else {
            if(isset($_GET["stranka"])) {
                foreach ($stranky as $key => $value) {
                    if($key == $_GET["stranka"]) {
                        $html = str_replace("[@stranka]",$value,$html);
                        break;
                    }
                }
            } else {
                if(empty($_SERVER["QUERY_STRING"])) {
                    header("Location: administrace.php?stranka=novyProdukt");
                } else {
                    header("Location: administrace.php?" . $_SERVER["QUERY_STRING"]);
                }
            }
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
                    $vyhledaneProdukty .= '<a href="administrace.php?stranka=editaceProduktu&produkt=' . $value["id"] .'">' . $value["nazev"] .'</a>&nbsp;';
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
        $html = str_replace("[@vlastnostiMaterialy]",$nazvyMaterialu,$html);



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
                $stmt = $db->prepare("DELETE FROM kategorie WHERE nazev = :nazev LIMIT 1");
                $stmt->execute([":nazev" => $_POST["kategorieVlastnost"]]);
            }

            header("Location: administrace.php?stranka=vlastnostiProduktu");
        }
        
        // ? sport
        
        if(isset($_POST["sportOdstranit"])) {
            $stmt = $db->prepare("SELECT id FROM produkt WHERE id_sportu = (SELECT id FROM sport WHERE nazev = :nazev LIMIT 1)");
            $stmt->execute([":nazev" => $_POST["sportVlastnost"]]);
            
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if(count($arr) == 0) {
                $stmt = $db->prepare("DELETE FROM sport WHERE nazev = :nazev LIMIT 1");
                $stmt->execute([":nazev" => $_POST["sportVlastnost"]]);
            }
            header("Location: administrace.php?stranka=vlastnostiProduktu");
        }
        
        // ? znacka
        
        if(isset($_POST["znackaOdstranit"])) {
            $stmt = $db->prepare("SELECT id FROM produkt WHERE id_znacky = (SELECT id FROM znacka WHERE nazev = :nazev LIMIT 1)");
            $stmt->execute([":nazev" => $_POST["znackaVlastnost"]]);

            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if(count($arr) == 0) {
                $stmt = $db->prepare("DELETE FROM znacka WHERE nazev = :nazev LIMIT 1");
                $stmt->execute([":nazev" => $_POST["znackaVlastnost"]]);
            }
            header("Location: administrace.php?stranka=vlastnostiProduktu");
        }
        
        // ? material
        
        if(isset($_POST["materialOdstranit"])) {
            $stmt = $db->prepare("SELECT id FROM materialy_produktu WHERE id_materialu = (SELECT id FROM material WHERE nazev = :nazev LIMIT 1)");
            $stmt->execute([":nazev" => $_POST["materialVlastnost"]]);
            
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if(count($arr) == 0) {
                $stmt = $db->prepare("DELETE FROM material WHERE nazev = :nazev LIMIT 1");
                $stmt->execute([":nazev" => $_POST["materialVlastnost"]]);
            }
            header("Location: administrace.php?stranka=vlastnostiProduktu");
        }
        
        // ? velikost
        
        if(isset($_POST["velikostOdstranit"])) {
            $stmt = $db->prepare("SELECT id FROM varianty WHERE id_velikosti = (SELECT id FROM velikost WHERE nazev = :velikost LIMIT 1)");
            $stmt->execute([":nazev" => $_POST["velikostVlastnost"]]);
            
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if(count($arr) == 0) {
                $stmt = $db->prepare("DELETE FROM velikost WHERE nazev = :nazev LIMIT 1");
                $stmt->execute([":nazev" => $_POST["velikostVlastnost"]]);
            }
            header("Location: administrace.php?stranka=vlastnostiProduktu");
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
            header("Location: administrace.php?stranka=vlastnostiProduktu");
        }
        
        if(isset($_POST["novaVlastnost"])) {
            $sql = "INSERT IGNORE INTO ". htmlspecialchars($_POST["vlastnost"]) ." (nazev) VALUES (:novaVlastnost)";
            $stmt = $db->prepare($sql);
            $stmt->execute([":novaVlastnost" => htmlspecialchars($_POST["nazevVlastnosti"])]);

            header("Location: administrace.php?stranka=vlastnostiProduktu");
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
                
                header("Location: administrace.php?stranka=slevoveKody");
            }
        }
        $html = str_replace("[@slevoveKody]",$kody,$html);
        
        if(isset($_POST["vytvoritSlevovyKod"])) {
            $sql = "INSERT INTO slevovy_kod (kod,sleva,expirace,bylPouzit) VALUES (:slevovyKod,:sleva,:expirace,:bylPouzit);";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([":slevovyKod" => htmlspecialchars($_POST["slevovyKod"]),":sleva" => htmlspecialchars($_POST["slevaKodu"]),":expirace" => htmlspecialchars($_POST["expiraceKodu"]),":bylPouzit" => 0]);
            
            $moznost = '';
            
            if($_POST["moznost"] == "sport") {
                $sql = "INSERT INTO slevovy_kod_sport (id_slevoveho_kodu,id_sportu) VALUES (:idKodu,(SELECT id FROM sport WHERE nazev = :nazev LIMIT 1));";
                $moznost = $_POST["slevovyKodSport"];
            } else {
                $sql = "INSERT INTO slevovy_kod_znacka (id_slevoveho_kodu,id_znacky) VALUES (:idKodu,(SELECT id FROM znacka WHERE nazev = :nazev LIMIT 1));";
                $moznost = $_POST["slevovyKodZnacka"];
            }

            $stmt = $db->prepare($sql);
            $stmt->execute([":idKodu" => $db->lastInsertId(),":nazev" => htmlspecialchars($moznost)]);

            header("Location: administrace.php?stranka=slevoveKody");
        }
        
        // ! Uživatelé
        
        $stmt = $db->prepare("SELECT uzivatel.id, `jmeno`, `prijmeni`, `email`, `telefonni_cislo`, `psc`, `ulice`, `mesto`, role.nazev AS role FROM uzivatel JOIN role ON role.id = uzivatel.id_role WHERE jeZaregistrovany = 1 AND role.id != 1 ORDER BY uzivatel.id;
        SELECT recenze.id,recenze.recenze,recenze.pocet_hvezd,recenze.id_uzivatele,produkt.nazev FROM recenze JOIN produkt ON produkt.id = recenze.id_produktu JOIN uzivatel ON uzivatel.id = recenze.id_uzivatele ORDER BY uzivatel.id;");
        
        $stmt->execute();
        
        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->nextRowSet();
        $recenze = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $uzivatele = '';
        
        foreach ($arr as $key => $value) {
            
            $recenzeUzivatele = array_filter($recenze,function($value2) use ($value) {
                return $value2["id_uzivatele"] == $value["id"];
            });
            
            # code...
            $uzivatele .= '<tr><td>' . $value["id"]. '</td><td>' . $value["jmeno"]. '</td><td>' . $value["prijmeni"]. '</td><td>' . $value["email"]. '</td><td>' . $value["telefonni_cislo"]. '</td><td>' . $value["mesto"]. '</td><td>' . $value["ulice"]. '</td><td>' . $value["psc"]. '</td><td>' . $value["role"]. '</td><td><input type="submit" value="smazat účet" name="smazatUcet' . $key .'"></td></tr>';
            
            if(count($recenzeUzivatele) != 0) {

                $uzivatele .= "<tr><td colspan='10'><b>Recenze uživatele:</b></td></tr>";
                
                foreach ($recenzeUzivatele as $key2 => $value2) {
                    # code...
                    $uzivatele .= "<tr><td></td><td>" . $value2["nazev"]. "</td><td><div><img src='obrazky/hvezda_ikona.svg'>" . $value2["pocet_hvezd"] . "</div</td><td colspan='6'>" . $value2["recenze"] . "</td><td><input type='submit' name='odstranitRecenzi" . $key2 . "' value='Odstranit recezni'></td></tr>";
                    
                    if(isset($_POST["odstranitRecenzi" . $key2])) {
                        $stmt = $db->prepare("DELETE FROM recenze WHERE id = :id");
                        $stmt->execute([":id" => $value2["id"]]);
                        
                        
                        header("Location: administrace.php?stranka=uzivatele");
                    }
                }
            }
            
            
            // ! smazani uzivatele
            
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
                
                header("Location: administrace.php?stranka=uzivatele");
                
            }
        }
        $html = str_replace("[@uzivatele]",$uzivatele,$html);
        
        // ! produkt
        
        // ? smazání produktu
        
        
        if(isset($_GET["produkt"])) {

            $html = str_replace("[@znacky]",$nazvyZnacek,$html);
            $html = str_replace("[@kategorie]",$nazvyKategorii,$html);
            $html = str_replace("[@sporty]",$nazvySportu,$html);
            
            $stmt = $db->prepare("
            SELECT produkt.id, produkt.nazev, produkt.popis, produkt.cena, produkt.cena_ve_sleve, znacka.nazev AS znacka, sport.nazev AS sport, kategorie.nazev AS kategorie
            FROM produkt 
            JOIN kategorie ON kategorie.id = produkt.id_kategorie 
            JOIN sport ON sport.id = produkt.id_sportu 
            JOIN znacka ON znacka.id = produkt.id_znacky
            WHERE produkt.id = :id;
            SELECT material.nazev,materialy_produktu.procento_materialu 
            FROM material 
            JOIN materialy_produktu ON materialy_produktu.id_materialu = material.id 
            JOIN produkt ON produkt.id = materialy_produktu.id_produktu 
            WHERE produkt.id = :id;
            SELECT DISTINCT barva.nazev 
            FROM barva 
            JOIN varianty ON varianty.id_barvy = barva.id 
            JOIN produkt ON produkt.id = varianty.id_produktu 
            WHERE produkt.id = :id;");

            $stmt->execute([":id" => $_GET["produkt"]]);

            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt->nextRowSet();
            
            $materialyProduktu = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt->nextRowSet();
            
            $barvyProduktu = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            
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
            
            
            
            
            $vsechnyMaterialy = '';
            foreach ($materialyProduktu as $key => $value) {
                # code...
                $vsechnyMaterialy .= "<tr><td>".$value["nazev"]."</td><td><input type='number' name='procento". $key."' value='" . $value["procento_materialu"] ."' min='1' max='100'></td><td><input type='submit' name='odstranitMaterial" . $key. "' value='Odstranit materiál'></td><td><input type='submit' name='aktualizovatProcento" . $key ."' value='Aktualizovat procento materiálu'></td></tr>";

                // ! odstraneni materialu produktu

                if(isset($_POST["odstranitMaterial" . $key])) {
                    $stmt = $db->prepare("DELETE FROM materialy_produktu WHERE id_produktu = :idProduktu AND id_materialu = (SELECT id FROM material WHERE nazev = :nazev LIMIT 1)");

                    $stmt->execute([":idProduktu" => $arr[0]["id"],
                        ":nazev" => $value["nazev"]
                    ]);

                    header("Location: administrace.php?" . $_SERVER["QUERY_STRING"] );
                }

                // ! aktualizace materialu

                if(isset($_POST["aktualizovatProcento" . $key])) {
                    $stmt = $db->prepare("UPDATE materialy_produktu SET procento_materialu = :procento WHERE id_produktu = :idProduktu AND id_materialu = (SELECT id FROM material WHERE nazev = :nazev LIMIT 1)");

                    $stmt->execute([":idProduktu" => $arr[0]["id"],
                        ":nazev" => $value["nazev"],
                        ":procento" => htmlspecialchars($_POST["procento" . $key])
                    ]);

                    header("Location: administrace.php?" . $_SERVER["QUERY_STRING"] );
                }
            }
            $html = str_replace("[@materialy]",$vsechnyMaterialy,$html);
            
            $zbyvajiciMaterialy = [];
            
            $materialyProduktu = array_map(function($value) {
                return $value["nazev"];
            },$materialyProduktu);
            
            for ($i=0; $i < count($materialy); $i++) { 
                # code...
                if(!in_array($materialy[$i]["nazev"],$materialyProduktu)) {
                    $zbyvajiciMaterialy[] =$materialy[$i]["nazev"];
                }
            }
            
            $nazvyMaterialu = "";
            foreach ($zbyvajiciMaterialy as $key => $value) {
                # code...
                $nazvyMaterialu .= '<option value="' . $value . '">' . $value .'</option>';
            }
            $html = str_replace("[@zbyvajiciMaterialy]",$nazvyMaterialu,$html);
            
            
            
            $nazvyBarvy = '';
            foreach($barvyProduktu as $key => $value) {
                $nazvyBarvy .= '<tr><td>' . $value["nazev"] . '</td><td><input type="submit" value="odstranit barvu" name="odstranitBarvuProduktu' . $key. '"></td><td><a href="administrace.php?'. $_SERVER["QUERY_STRING"].'&barva=' . $value["nazev"] .'">Zobrazit obrázky a velikosti</a></td></tr>';

                if(isset($_POST["odstranitBarvuProduktu" . $key])) {
                    $stmt = $db->prepare("SELECT id_obrazku FROM obrazky_k_produktu WHERE id_produktu = :id AND id_barvy = (SELECT id FROM barva WHERE nazev = :nazev)");
                    
                    $stmt->execute([":id" => $arr[0]["id"],
                        ":nazev" => $value["nazev"]
                    ]);

                    $obrazky = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $placeholdery = "";
                    $parametry = [":id" => $arr[0]["id"],
                        ":nazev" => $value["nazev"]
                    ];
                    foreach ($obrazky as $key2 => $value2) {
                        # code...
                        $placeholdery .= ":obrazekId$key2,";
                        $parametry[":obrazekId$key2"] = $value2["id_obrazku"];
                        
                    }
                    $placeholdery = rtrim($placeholdery,",");



                    $sql = "DELETE FROM varianty 
                    WHERE id_produktu = :id 
                    AND id_barvy = (SELECT id FROM barva WHERE nazev = :nazev);
                    DELETE FROM obrazky_k_produktu
                    WHERE id_produktu = :id 
                    AND id_barvy = (SELECT id FROM barva WHERE nazev = :nazev);
                    DELETE FROM obrazek WHERE id IN ($placeholdery)";

                    $stmt = $db->prepare($sql);
                    $stmt->execute($parametry);

                    header("Location: administrace.php?" . $_SERVER["QUERY_STRING"] );
                }
            }
            $html = str_replace("[@barvyProduktu]",$nazvyBarvy,$html);

            $zbyvajiciBarvy = [];
            
            $barvyProduktu = array_map(function($value) {
                return $value["nazev"];
            },$barvyProduktu);
            
            for ($i=0; $i < count($barvy); $i++) { 
                # code...
                if(!in_array($barvy[$i]["nazev"],$barvyProduktu)) {
                    $zbyvajiciBarvy[] =$barvy[$i]["nazev"];
                }
            }

            $nazvyBarvy = "";
            foreach ($zbyvajiciBarvy as $key => $value) {
                # code...
                $nazvyBarvy .= '<option value="' . $value . '">' . $value .'</option>';
            }
            $html = str_replace("[@zbyvajiciBarvy]",$nazvyBarvy,$html);

            // ! aktualizace zakladnich udaju

            if(isset($_POST["aktualizovatZakladaniUdaje"])) {
                $stmt = $db->prepare("UPDATE produkt SET nazev = :nazev, popis = :popis, cena = :cena, cena_ve_sleve = :sleva, id_znacky = (SELECT id FROM znacka WHERE nazev = :znacka LIMIT 1), id_sportu = (SELECT id FROM sport WHERE nazev = :sport LIMIT 1),id_kategorie = (SELECT id FROM kategorie WHERE nazev = :kategorie) WHERE id = :id");

                $cenaVeSleve = htmlspecialchars($_POST["slevaProduktu"]) != '' ? htmlspecialchars($_POST["slevaProduktu"]) : null;

                $stmt->execute([
                    ":id" => $arr[0]["id"],
                    ":nazev" => htmlspecialchars($_POST["nazevProduktu"]),
                    ":popis" => htmlspecialchars($_POST["popisProduktu"]),
                    ":cena" => htmlspecialchars($_POST["cenaProduktu"]),
                    ":sleva" => $cenaVeSleve,
                    ":kategorie" => htmlspecialchars($_POST["kategorieProduktu"]),
                    ":sport" => htmlspecialchars($_POST["sportProduktu"]),
                    ":znacka" => htmlspecialchars($_POST["znackaProduktu"]),
                ]);
                
                header("Location: administrace.php?" . $_SERVER["QUERY_STRING"] );
            }
            
            // ! pridani noveho materialu

            if(isset($_POST["pridatMaterial"])) {
                $stmt = $db->prepare("INSERT INTO materialy_produktu (id_produktu,id_materialu,procento_materialu) VALUES (:id,(SELECT id FROM material WHERE nazev = :nazev LIMIT 1),:procento)");

                $stmt->execute([
                    ":id" => $arr[0]["id"],
                    ":nazev" => htmlspecialchars($_POST["novyMaterial"]),
                    ":procento" => htmlspecialchars($_POST["procentoNovehoMaterialiu"])
                ]);

                header("Location: administrace.php?" . $_SERVER["QUERY_STRING"] );
            }

            // ! pridani nove barvy

            if(isset($_POST["pridatBarvu"])) {
                // ? dodelat to tak, ze do db varianty se da idproduktu,idbarvy,0
                $stmt = $db->prepare("INSERT INTO varianty (id_produktu,id_barvy,id_velikosti) VALUES (:idProduktu,(SELECT id FROM barva WHERE nazev = :nazev LIMIT 1),:idVelikosti)");

                $stmt->execute([
                    ":idProduktu" => $arr[0]["id"],
                    ":nazev" => htmlspecialchars($_POST["novaBarva"]),
                    ":idVelikosti" => 0
                ]);
                
                header("Location: administrace.php?" . $_SERVER["QUERY_STRING"] );
            }

            
            // ! odstranění produktu
            
            if(isset($_POST["odstranitProdukt"])) {
                $stmt = $db->prepare("DELETE FROM produkt WHERE id = :id;
                DELETE FROM varianty WHERE id_produktu = :id;
                DELETE FROM obrazky_k_produktu WHERE id_produktu = :id;
                DELETE FROM materialy_produktu WHERE id_produktu = :id;
                DELETE FROM recezne WHERE id_produktu = :id;
                DELETE FROM oblibene_produkty WHERE id_produktu = :id;
                DELETE FROM zakoupene_produkty WHERE id_produktu = :id;
                DELETE FROM produkty_v_objednavce WHRE id_produktu = :id");
                $stmt->execute([":id" => $arr[0]["id"]]);
                
                header("Location: administrace.php?" . $_SERVER["QUERY_STRING"] );
            }
        }

        if(isset($_POST["pridatNovyProdukt"])) {
            $stmt = $db->prepare("INSERT INTO produkt (nazev,popis,cena,cena_ve_sleve,id_znacky,id_kategorie,id_sportu) VALUES (:nazev,:popis,:cena,:cenaVeSleve,(SELECT id FROM znacka WHERE nazev = :znacka LIMIT 1),(SELECT id FROM kategorie WHERE nazev = :kategorie LIMIT 1),(SELECT id FROM sport WHERE nazev = :sport LIMIT 1))");

            $cenaVeSleve = htmlspecialchars($_POST["slevaNovehoProduktu"]) != '' ? htmlspecialchars($_POST["slevaNovehoProduktu"]) : null;

            $stmt->execute([":nazev" => htmlspecialchars($_POST["nazevNovehoProduktu"]),
                ":popis" => htmlspecialchars($_POST["popisNovehoProduktu"]),
                ":cena" => htmlspecialchars($_POST["cenaNovehoProduktu"]),
                ":cenaVeSleve" => $cenaVeSleve,
                ":znacka" => htmlspecialchars($_POST["znackaNovehoProduktu"]),
                ":kategorie" => htmlspecialchars($_POST["kategorieNovehoProduktu"]),
                ":sport" => htmlspecialchars($_POST["sportNovehoProduktu"])
            ]);

            $id = $db->lastInsertId();

            if(isset($_POST["material"])) {
                for($i = 0;$i < count($_POST["material"]);$i++) {
                    $stmt = $db->prepare("INSERT INTO materialy_produktu (id_produktu,id_materialu,procento_materialu) VALUES (:id,(SELECT id FROM material WHERE nazev = :nazev LIMIT 1),:procento)");
    
                    $stmt->execute([":id" => $id,
                        ":nazev" => $_POST["material"][$i],
                        ":procento" => $_POST["procento"][$i],
                    ]);
                }
            }

            foreach($_POST["barva"] as $value) {
                foreach($_POST[$value . "Velikost"] as $value2) {
                    $stmt = $db->prepare("INSERT INTO varianty (id_produktu,id_barvy,id_velikosti) VALUES (:id,(SELECT id FROM barva WHERE nazev = :barva LIMIT 1),(SELECT id FROM velikost WHERE nazev = :velikost LIMIT 1))");

                    $stmt->execute([":id" => $id,
                        ":barva" => $value,
                        ":velikost" => $value2,
                    ]);
                }

                $obrazky = $_FILES[$value . "Obrazky"];

                foreach($obrazky["name"] as $key2 => $value2) {
                    $target_file = "obrazky/" . basename($obrazky["name"][$key2]);
                
                    if(getimagesize($obrazky["tmp_name"][$key2])) {
                        if (!file_exists($target_file)) {
                            // ! vyřešit main obrazek!!!
                            move_uploaded_file($obrazky["tmp_name"][$key2], $target_file);
    
                            $stmt = $db->prepare("INSERT INTO obrazek (src) VALUES (:src)");
                            $stmt->execute([":src" => $obrazky["name"][$key2]]);
                            
                            $idObrazku = $db->lastInsertId();
                            
                            $stmt = $db->prepare("INSERT INTO obrazky_k_produktu (id_produktu,id_barvy,id_obrazku) VALUES (:idProduktu,(SELECT id FROM barva WHERE nazev = :nazev),:idObrazku)");
    
                            $stmt->execute([":idProduktu" => $id,":nazev" => $value,":idObrazku" => $idObrazku]);
    
                        }
                    } 
                }
            }
        }
        
        echo $html;
    } else {
        header("Location: index.php");
    }
} else {
    header("Location: index.php");
}

