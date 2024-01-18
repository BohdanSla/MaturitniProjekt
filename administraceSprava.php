<?php

declare(strict_types=1);

session_start();


//každý form bude muset mít vlastní name!! (takže přes fory dát navíc čísla), jednotlivé inputy pak ne!!!!!!

spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

use Databaze as Db;

$db = new Db();

if(isset($_SESSION["username"])) {
  $html = file_get_contents("kod/html/administraceSprava.html");

  $db->beginTransaction();
  try {
      //! SELECT pro dany produkt
      //!SELECT pro vybrani vsech kategorie
      //!SELECT pro vybrani podkategorie a kategorie pro určení podkategorie
      //!SELECTY pro vybrani vsech znacek,sportu,materialu a barev pro vyber
      //!SELECT barvy,velikosti a mnozstvi pro dany produkt
      //!SELECt materialu pro dany produkt
      $sql = 'SELECT 
      produkt.nazev,
      obrazek.src,
      produkt.popis,
      produkt.cena,
      produkt.cena_ve_sleve,
      kategorie_produktu.kategorie,
      kategorie_produktu.podkategorie,
      znacka.nazev AS znacka,
      sport.nazev AS sport,
      (
        SELECT COUNT(DISTINCT obrazky_k_produktu.id_barvy) 
        FROM obrazky_k_produktu, barva 
        WHERE barva.id = obrazky_k_produktu.id_barvy 
        AND produkt.id = obrazky_k_produktu.id_produktu 
      ) AS pocet_barev
    FROM 
      produkt 
    JOIN 
      obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id 
    JOIN 
      obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku 
    JOIN 
      kategorie_produktu ON kategorie_produktu.id = produkt.id_kategorie_produktu 
    JOIN 
      znacka ON znacka.id = produkt.id_znacky 
    JOIN 
      sport ON sport.id = produkt.id_sportu 
    WHERE 
      obrazek.src LIKE "%main%" 
    ORDER BY 
      produkt.nazev ASC;
      SELECT DISTINCT kategorie FROM kategorie_produktu; 
      SELECT kategorie,podkategorie FROM kategorie_produktu;
      SELECT nazev FROM znacka; 
      SELECT nazev FROM sport;
      SELECT nazev FROM material;
      SELECT nazev FROM barva;
      SELECT produkt.nazev,velikost.nazev AS velikost, barva.nazev AS barva, mnozstvi.pocet AS pocet
      FROM produkt
      JOIN mnozstvi ON mnozstvi.id_produktu = produkt.id
      JOIN velikost ON velikost.id = mnozstvi.id_velikosti
      JOIN barva ON barva.id = mnozstvi.id_barvy
      ORDER BY barva.nazev ASC,velikost.nazev ASC;
      SELECT produkt.nazev,material.nazev AS material, materialy_produktu.procento_materialu AS procento
      FROM produkt 
      JOIN materialy_produktu ON materialy_produktu.id_produktu = produkt.id 
      JOIN material ON material.id = materialy_produktu.id_materialu
      ORDER BY produkt.nazev ASC;
      SELECT produkt.nazev,
      barva.nazev AS barva,
      obrazek.src AS obrazek
      FROM produkt
      JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id
      JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku
      JOIN barva ON barva.id = obrazky_k_produktu.id_barvy
      ORDER BY barva.nazev ASC;
      SELECT velikost.nazev FROM velikost ORDER BY velikost.nazev;
      SELECT DISTINCT kod,expirace,sport.nazev FROM slevovy_kod,sport JOIN slevovy_kod_sport ON slevovy_kod_sport.id_sportu = sport.id WHERE slevovy_kod.id = slevovy_kod_sport.id_slevoveho_kodu;
      SELECT DISTINCT kod,expirace,znacka.nazev FROM slevovy_kod,znacka JOIN slevovy_kod_znacka ON slevovy_kod_znacka.id_znacky = znacka.id WHERE slevovy_kod.id = slevovy_kod_znacka.id_slevoveho_kodu;';

      $moznosti = "";
      $stmt = $db->prepare($sql);
      $stmt->execute();
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($arr as $key => $value) {
          # code...

          $moznosti .= "<option value=\"" . $value["nazev"] . "\" data-popis=\"" . $value["popis"] . "\" data-cena=\"" . $value["cena"] . "\" data-cenaVeSleve=\"" . $value["cena_ve_sleve"] . "\" data-kategorie=\"" . $value["kategorie"] . "\" data-podkategorie=\"" . $value["podkategorie"] . "\" data-znacka=\"" . $value["znacka"] . "\" data-sport=\"" . $value["sport"] . "\" data-hlavniObrazek=\"obrazky/" . $value["src"] . "\" data-pocetBarev=\"" . $value["pocet_barev"] . "\">";
      }
      
      $html = preg_replace("/\[@navrhy-produkty\]/",$moznosti,$html);
      
      $moznosti = "";
      $stmt->nextRowset();
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($arr as $key => $value) {
          # code...
          $moznosti .= "<option value=\"" . $value["kategorie"] . "\">" . $value["kategorie"] . "</option>";
      }
      $html = preg_replace("/\[@kategorie-produkty\]/",$moznosti,$html);

      $moznosti = "";
      $stmt->nextRowset();
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($arr as $key => $value) {
          # code...
          $moznosti .= "<option value=\"" . $value["podkategorie"] . "\" data-kategorie=\"" . $value["kategorie"] . "\">" . $value["podkategorie"] . "</option>";
      }
      $html = preg_replace("/\[@podkategorie-produkty\]/",$moznosti,$html);

      $moznosti = "";
      $stmt->nextRowset();
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($arr as $key => $value) {
          # code...
          $moznosti .= "<option value=\"" . $value["nazev"] . "\">" . $value["nazev"] . "</option>";
      }
      $html = preg_replace("/\[@znacka-produkty\]/",$moznosti,$html);
      $html = preg_replace("/\[@znacka-kody\]/",$moznosti,$html);

      $moznosti = "";
      $stmt->nextRowset();
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($arr as $key => $value) {
          # code...
          $moznosti .= "<option value=\"" . $value["nazev"] . "\">" . $value["nazev"] . "</option>";
      }
      $html = preg_replace("/\[@sport-produkty\]/",$moznosti,$html);
      $html = preg_replace("/\[@sport-kody\]/",$moznosti,$html);

      $moznosti = "";
      $stmt->nextRowset();
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($arr as $key => $value) {
          # code...
          $moznosti .= "<input id='materialProduktu' type='checkbox' value=\"" . $value["nazev"] . "\" name='materialy[]' >" . $value["nazev"] . " ";
          $moznosti .= "<input id='procentoMaterialu' name='procento[]' type='number' min='0' max='100'>%<br>";
      }
      $html = preg_replace("/\[@materialy-produkty\]/",$moznosti,$html);
      
      $moznosti = "";
      $stmt->nextRowset();
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($arr as $key => $value) {
          # code...
          $moznosti .= "<input id='barvaProduktu' type='checkbox' value=\"" . $value["nazev"] . "\" name='barvy[]' >" . $value["nazev"] . "<br>";
      }
      $html = preg_replace("/\[@barvy-produkty\]/",$moznosti,$html);

      $moznosti = "";
      $stmt->nextRowset();
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($arr as $key => $value) {
          # code...
          $moznosti .= "<option value=\"" . $value["nazev"] . "\" data-velikost=\"" . $value["velikost"] . "\" data-barva=\"" . $value["barva"] . "\" data-pocet=\"" . $value["pocet"] . "\">";
      }
      $html = preg_replace("/\[@mnozstvi-produkty\]/",$moznosti,$html);

      $moznosti = "";
      $stmt->nextRowset();
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($arr as $key => $value) {
          # code...
          $moznosti .= "<option value=\"" . $value["nazev"] . "\" data-material=\"" . $value["material"] . "\" data-procento=\"" . $value["procento"] . "\">";
      }
      $html = preg_replace("/\[@dane-materialy-produktu\]/",$moznosti,$html);

      $moznosti = "";
      $stmt->nextRowset();
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($arr as $key => $value) {
          # code...
          $moznosti .= "<option value=\"" . $value["nazev"] . "\" data-barva=\"" . $value["barva"] . "\" data-obrazek=\"obrazky/" . $value["obrazek"] . "\">";
      }
      $html = preg_replace("/\[@dane-obrazky-produktu\]/",$moznosti,$html);

      $moznosti = "";
      $stmt->nextRowset();
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($arr as $key => $value) {
          # code...
          $moznosti .= "<option value=\"" . $value["nazev"] . "\">" . $value["nazev"] . "</option>";
      }
      $html = preg_replace("/\[@velikosti\]/",$moznosti,$html);
      
      $moznosti = "";
      $stmt->nextRowset();
      $kody = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($kody as $key => $value) {
        # code...
        $moznosti .= "<tr><td>" . $value["kod"] . "</td><td>" . $value["expirace"] . "</td><td>" . $value["nazev"] ."</td></tr>";
      }
      $html = str_replace("[@kody-sport]",$moznosti,$html);

      $moznosti = "";
      $stmt->nextRowset();
      $kody = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($kody as $key => $value) {
        # code...
        $moznosti .= "<tr><td>" . $value["kod"] . "</td><td>" . $value["expirace"] . "</td><td>" . $value["nazev"] ."</td></tr>";
      }
      $html = str_replace("[@kody-znacka]",$moznosti,$html);
      
      
    if(isset($_POST["aktualizovat"])) {
      if(isset($_POST["barvy"])) {
        foreach ($_POST["barvy"] as $key => $value) {
            # code..
            $barvaDoDatabaze = str_replace("-","",$value);
            if(isset($_POST[$value])) {

                $obrazky = "";
                $parametry = [":puvodniNazev" => $_POST["puvodniNazev"],":barva" => $value];

                foreach ($_POST[$value] as $key2 => $value2) {
                    # code...
                    $obrazky .= ":src$key2,";
                    $parametry[":src$key2"] = str_replace("obrazky/","",$value2);
                }
                $obrazky = rtrim($obrazky,",");

                $sql = "SELECT src,id FROM obrazek WHERE id IN (SELECT id_obrazku FROM obrazky_k_produktu WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev) AND id_barvy = (SELECT id FROM barva WHERE nazev = :barva)) AND src NOT IN ($obrazky) AND src NOT LIKE \"%main%\"";

                $stmt = $db->prepare($sql);

                $stmt->execute($parametry);

                $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($arr as $key3 => $value3) {
                    # code...
                    unlink("obrazky/" . $value3["src"]);

                    
                    $sql = "DELETE FROM obrazky_k_produktu WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev) AND id_barvy = (SELECT id FROM barva WHERE nazev = :barva) AND id_obrazku = :id";
                    
                    $stmt = $db->prepare($sql);
                    
                    $stmt->execute([":puvodniNazev" => $_POST["puvodniNazev"],":barva" => $value,":id" => $value3["id"]]);



                    $sql = "DELETE FROM obrazek WHERE id = :id";

                    $stmt = $db->prepare($sql);

                    $stmt->execute([":id" => $value3["id"]]);
                }
            } else {
                $sql = "SELECT src FROM obrazek WHERE id IN (SELECT id_obrazku FROM obrazky_k_produktu WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev) AND id_barvy = (SELECT id FROM barva WHERE nazev = :barva))";

                $stmt = $db->prepare($sql);

                $stmt->execute([":puvodniNazev" => $_POST["puvodniNazev"],":barva" => $value]);

                $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($arr as $key3 => $value3) {
                    # code...
                    unlink("obrazky/" . $value3["src"]);

                    
                    $sql = "DELETE FROM obrazky_k_produktu WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev) AND id_barvy = (SELECT id FROM barva WHERE nazev = :barva)";
                    
                    $stmt = $db->prepare($sql);
                    
                    $stmt->execute([":puvodniNazev" => $_POST["puvodniNazev"],":barva" => $value]);



                    $sql = "DELETE FROM obrazek WHERE src = :src";

                    $stmt = $db->prepare($sql);

                    $stmt->execute([":src" => $value3["src"]]);
                }

            }

            if(isset($_FILES[$value]["name"])) {
                foreach ($_FILES[$value]["name"] as $key2 => $value2) {
                    # code...
                    if(mb_strlen($_FILES[$value]["name"][$key2]) > 0) {
                        if(getimagesize($_FILES[$value]["tmp_name"][$key2])) {
                            
                            if(!file_exists("obrazky/" . $_FILES[$value]["name"][$key2])) {
        
                                $sql = "INSERT INTO obrazek (src) VALUES (:src);";

                                $stmt = $db->prepare($sql);

                                $stmt->execute([":src" =>$_FILES[$value]["name"][$key2]]);
        
                                move_uploaded_file($_FILES[$value]["tmp_name"][$key2],"obrazky/" . $_FILES[$value]["name"][$key2]);
                            }
        
                            $sql = "INSERT INTO obrazky_k_produktu (id_produktu,id_barvy,id_obrazku) VALUES ((SELECT id FROM produkt WHERE nazev = :puvodniNazev LIMIT 1),(SELECT id FROM barva WHERE nazev = :barva),(SELECT id FROM obrazek WHERE src = :src LIMIT 1))";
                            
                            $stmt = $db->prepare($sql);
                            
                            $stmt->execute([":puvodniNazev" => $_POST["puvodniNazev"],":barva" => $value, ":src" =>$_FILES[$value]["name"][$key2]]);
        
                        }
                    }
                  }
            }
        
            //! pridni novych velikosti k barve,produktu a poctu
        
            if(isset($_POST[$value . "Velikost"])) {
                $sql = "DELETE FROM mnozstvi WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev LIMIT 1) AND id_barvy = (SELECT id FROM barva WHERE nazev = :barva LIMIT 1)";
                
                $stmt = $db->prepare($sql);
                
                $stmt->execute([":puvodniNazev" => $_POST["puvodniNazev"],":barva" => $barvaDoDatabaze]);
                
                $sql = "INSERT INTO mnozstvi (id_produktu,id_barvy,id_velikosti,pocet) VALUES ((SELECT id FROM produkt WHERE nazev = :puvodniNazev LIMIT 1),(SELECT id FROM barva WHERE nazev = :barva LIMIT 1),(SELECT id FROM velikost WHERE nazev = :velikost LIMIT 1),:pocet)";
        
                $stmt = $db->prepare($sql);
        
                for ($i=0; $i < count($_POST[$value . "Velikost"]); $i++) { 
                    $stmt->execute([":barva" => $barvaDoDatabaze,":puvodniNazev" => $_POST["puvodniNazev"],":velikost" => $_POST[$value . "Velikost"][$i], ":pocet" => $_POST[$value . "Pocet"][$i]]);
                    # code...
                }
            }
        }
      }
      if (!isset($_FILES["hlavniObrazek"]["name"])) {
        # code...
        if(getimagesize($_FILES["hlavniObrazek"]["tmp_name"])) {
            $hlavniObrazek = "main-" . htmlspecialchars($_FILES["hlavniObrazek"]["name"]);
            $src =  "obrazky/" . $hlavniObrazek;
    
            if(!file_exists($src)) {
                move_uploaded_file($_FILES["hlavniObrazek"]["tmp_name"],$src);
                unlink(htmlspecialchars($_POST["puvodniHlavniObrazek"]));


                $sql = 'UPDATE obrazek JOIN obrazky_k_produktu ON obrazek.id = obrazky_k_produktu.id_obrazku SET src = REPLACE(src,"main","") WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = ":nazevProduktu" LIMIT 1)';

                $stmt->prepare($sql);

                $stmt->execute([":nazevProduktu" => htmlspecialchars($_POST["puvodniNazev"])]);


    
                $sql = "UPDATE obrazek SET src = :novyObrazek WHERE src = :puvodniObrazek";

                $stmt = $db->prepare($sql);

                $stmt->execute([":novyObrazek" => $_FILES["hlavniObrazek"]["name"],":puvodniObrazek" => basename(htmlspecialchars($_POST["puvodniHlavniObrazek"]))]);
            }
        }
      }
      if(isset($_POST["materialy"])) {
        $sql = "DELETE FROM materialy_produktu WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev)";

        $stmt = $db->prepare($sql);

        $stmt->execute([":puvodniNazev" => $_POST["puvodniNazev"]]);

        $sql = "INSERT INTO `materialy_produktu`(`id_produktu`, `id_materialu`, `procento_materialu`) VALUES ((SELECT id FROM produkt WHERE nazev = :nazev),(SELECT id FROM material WHERE nazev = :material),:procento)";

        $stmt = $db->prepare($sql);

        $procenta = array_filter($_POST["procento"],function($value) {
            return trim($value) !== "";
        });

        foreach ($_POST["materialy"] as $key => $value) {
            $stmt->execute([":nazev" => htmlspecialchars($_POST["puvodniNazev"]),
            ":material" => htmlspecialchars($value),
            ":procento" => $procenta[$key]]);
        }
        # code...
      }          
          $sql = 'UPDATE `produkt` SET `nazev`= :nazev,`popis`= :popis ,`cena`= :cena,`cena_ve_sleve`= :cenaVeSleve,`id_znacky`= (SELECT id FROM znacka WHERE nazev  = :znacka LIMIT 1),`id_sportu`= (SELECT id FROM sport where nazev = :sport LIMIT 1),`id_kategorie_produktu`= (SELECT id FROM kategorie_produktu where kategorie = :kategorie LIMIT 1) WHERE nazev = :puvodniNazev;
          ';

          $stmt = $db->prepare($sql);

          $cenaVeSleve = 0;

          if(isset($_POST["cenaVeSleve"])) {
            $cenaVeSleve = $_POST["cenaVeSleve"];
          } else {
            $cenaVeSleve = NULL;
          }

          $stmt->execute([":sport" => htmlspecialchars($_POST["sport"]),
          ":kategorie" => htmlspecialchars($_POST["kategorie"]),
          ":znacka" => htmlspecialchars($_POST["znacka"]),
          ":nazev" => htmlspecialchars($_POST["nazev"]),
          ":popis" => htmlspecialchars($_POST["popis"]),
          ":cena" => htmlspecialchars($_POST["cena"]),
          ":cenaVeSleve" => htmlspecialchars($cenaVeSleve),
          ":puvodniNazev" => $_POST["puvodniNazev"]]);

          header("Location: administraceSprava.php");
    }

    if(isset($_POST["odstranit"])) {
      $sql = "DELETE FROM obrazky_k_produktu WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev);
      DELETE FROM mnozstvi WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev);
      DELETE FROM zakoupene_produkty WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev);
      DELETE FROM materialy_produktu WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev);
      DELETE FROM produkty_v_objednavce WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev);
      DELETE FROM oblibene_produkty WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev);
      DELETE FROM recenze WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev);
      DELETE FROM produkt WHERE nazev = :puvodniNazev";

      $stmt = $db->prepare($sql);

      $stmt->execute([":puvodniNazev" => $_POST["puvodniNazev"]]);

      header("Location: administraceSprava.php");
    }

    if(isset($_POST["kody"])) {

      $kody = array_map(function($value){
        return $value["kod"];
      },$kody);

      if(!in_array($_POST["kodKategorie"],$kody)) {
        $kategorie = $_POST["kodKategorie"];
  
        $stmt = $db->prepare("INSERT INTO slevovy_kod (kod,expirace,sleva) VALUES (:kod,:expirace,:sleva)");
        
        $stmt->execute([":kod" => htmlspecialchars($_POST["kod"]),":expirace" => htmlspecialchars($_POST["datum"]),":sleva" => htmls]);
        
  
  
        $sql = "";
  
        if($_POST["kodKategorie"] == "sport") {
          $sql = "INSERT INTO slevovy_kod_" . $kategorie . " (id_slevoveho_kodu,id_sportu) VALUES ((SELECT id FROM slevovy_kod WHERE kod = :kod LIMIT 1),(SELECT id FROM sport WHERE nazev = :nazev LIMIT 1))";
        } else {
          $sql = "INSERT INTO slevovy_kod_" . $kategorie . " (id_slevoveho_kodu,id_znacky) VALUES ((SELECT id FROM slevovy_kod WHERE kod = :kod LIMIT 1),(SELECT id FROM znacka WHERE nazev = :nazev LIMIT 1))";
        }
  
        $stmt = $db->prepare($sql);
  
        $stmt->execute([":kod" => htmlspecialchars($_POST["kod"]),":nazev" => htmlspecialchars($_POST[$kategorie])]);
  
        header("Location: administraceSprava.php");
      }

    }




    $db->commit();
    } catch(Exception $e) {
      echo $e;
      $db->rollBack();
    }
} else {
  header("Location: administraceLogin.php");
}
    
echo $html;
