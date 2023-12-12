<?php

declare(strict_types=1);

spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

use Databaze as Db;

$db = new Db();

$db->beginTransaction();


try {
    //code...
    if(isset($_POST["aktualizovat"])) {
        // !jeste doprozkoumat
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

        if(isset($_POST["barvy"])) {
            foreach ($_POST["barvy"] as $key => $value) {
                # code..
                echo "Barva:" . $value . "<br>";
                $barvaDoDatabaze = $value;
                $value = str_replace(" ","-",$value);

                if(isset($_FILES[$value]["name"])) {
                    foreach ($_FILES[$value]["name"] as $key2 => $value2) {
                        # code...
                        if(mb_strlen($_FILES[$value]["name"][$key2]) > 0) {
                            if(getimagesize($_FILES[$value]["tmp_name"][$key2])) {
                                if(mb_strlen($_FILES[$value]["name"][$key2]) > 0) {
                                    if(!file_exists('obrazky/' . $_FILES[$value]["name"][$key2])) {
                                        
                                        move_uploaded_file($_FILES[$value]["tmp_name"][$key2],'obrazky/' . $_FILES[$value]["name"][$key2]);
                                        
                                        $sql = "INSERT INTO obrazek (src) VALUES (:obrazek)";
                                        
                                        $stmt = $db->prepare($sql);
                                        
                                        $stmt->execute([":obrazek" => $_FILES[$value]["name"][$key2]]);
                                    }
                                    $sql = "REPLACE INTO obrazky_k_produktu (id_produktu,id_barvy,id_obrazku) VALUES ((SELECT id FROM produkt WHERE nazev = :puvodniNazev LIMIT 1),(SELECT id FROM barva WHERE nazev = :barva LIMIT 1),(SELECT id FROM obrazek WHERE src = :src LIMIT 1))";
                                    
                                    $stmt = $db->prepare($sql);
                                    
                                    $stmt->execute([":puvodniNazev" => htmlspecialchars($_POST["puvodniNazev"]),":barva" => $barvaDoDatabaze,":src" => $_FILES[$value]["name"][$key2]]);
                                    
                                    echo "Nový obrázek:" . $_FILES[$value]["name"][$key2] . "<br>";
                                }
                            }
                        
                        }
                    }
                }
                // ! odebrani zaznamu z tabulek obrazek, obrazky_k_produktu
                $obrazky = "";
                $i = 0;

                $parametry  = [
                    ":barva" => $barvaDoDatabaze,
                    ":puvodniNazev" => $_POST["puvodniNazev"]
                ];
                if(isset($_POST[$value])) {

                    foreach ($_POST[$value] as $key2 => $value2) {
                        # code...
                        $obrazky .= ":src$i,";
                        $i++;
                    }
                    $obrazky = rtrim($obrazky,",");
                    
                } else {
                    $obrazky = ":src$i";
                }

                $sql = "DELETE
                FROM obrazky_k_produktu
                WHERE id_barvy = (
                    SELECT id
                    FROM barva
                    WHERE nazev = :barva
                )
                    AND id_produktu = (
                    SELECT id
                    FROM produkt
                    WHERE nazev = :puvodniNazev
                )
                AND id_obrazku NOT IN (
                    SELECT id
                    FROM obrazek
                    WHERE src IN ($obrazky)
                )";
                $stmt = $db->prepare($sql);

                if (isset($_POST[$value])) {
                    $i = 0;
                    # code...
                    foreach ($_POST[$value] as $key2 => $value2) {
                        # code...
                        $parametry[":src$i"] = str_replace("obrazky/","",$value2);
                        $i++;
                    }
                } else {
                    # code...
                    $parametry[":src$i"] = "";
                }

                $sql = "SELECT src,id FROM obrazek WHERE id IN (SELECT id_obrazku FROM obrazky_k_produktu WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev) AND id_barvy = (SELECT id from barva WHERE nazev = :barva)) AND src NOT IN ($obrazky) AND src NOT LIKE \"%main%\"";


                $stmt = $db->prepare($sql);
                
                $stmt->execute($parametry);

                $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($arr as $key2 => $value2) {
                    # code...
                    unlink("obrazky/" . $value2["src"]);
                }

                $sql = "DELETE FROM obrazek WHERE id IN (SELECT id_obrazku FROM obrazky_k_produktu WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = :puvodniNazev) AND id_barvy = (SELECT id from barva WHERE nazev = :barva)) AND src NOT IN ($obrazky) AND src NOT LIKE \"%main%\"";


                $stmt = $db->prepare($sql);
                
                $stmt->execute($parametry);
                

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
    }
    $db->commit();
} catch (Exception $e) {
    //throw $th;
    echo $e;
    $db->rollBack();
}