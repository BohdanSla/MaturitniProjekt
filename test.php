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
        // if (!isset($_FILES["hlavniObrazek"]["name"])) {
        //     # code...
        //     if(getimagesize($_FILES["hlavniObrazek"]["tmp_name"])) {
        //         $hlavniObrazek = "main-" . htmlspecialchars($_FILES["hlavniObrazek"]["name"]);
        //         $src =  "obrazky/" . $hlavniObrazek;
        
        //         if(!file_exists($src)) {
        //             move_uploaded_file($_FILES["hlavniObrazek"]["tmp_name"],$src);
        //             unlink(htmlspecialchars($_POST["puvodniHlavniObrazek"]));
    
    
        //             $sql = 'UPDATE obrazek JOIN obrazky_k_produktu ON obrazek.id = obrazky_k_produktu.id_obrazku SET src = REPLACE(src,"main","") WHERE id_produktu = (SELECT id FROM produkt WHERE nazev = ":nazevProduktu" LIMIT 1)';
    
        //             $stmt->prepare($sql);
    
        //             $stmt->execute([":nazevProduktu" => htmlspecialchars($_POST["puvodniNazev"])]);
    
    
        
        //             $sql = "UPDATE obrazek SET src = :novyObrazek WHERE src = :puvodniObrazek";
    
        //             $stmt = $db->prepare($sql);
    
        //             $stmt->execute([":novyObrazek" => $_FILES["hlavniObrazek"]["name"],":puvodniObrazek" => basename(htmlspecialchars($_POST["puvodniHlavniObrazek"]))]);
        //         }
        //     }
        // }

        if(isset($_POST["barvy"])) {
            
            
            foreach ($_POST["barvy"] as $key => $value) {
                // $sql = "SELECT id_obrazku FROM obrazky_k_produktu WHERE id_barvy = (SELECT id FROM barva WHERE nazev = :nazevBarvy) AND id_produktu = (SELECT id FROM produkt WHERE nazev = :nazevProduktu);";
    
                // $stmt = $db->prepare($sql);
    
                // $stmt->execute([":nazevProduktu" => htmlspecialchars($_POST["puvodniNazev"]),":nazevBarvy" => $value]);
                # code...
                foreach ($_POST[$value] as $key2 => $value2) {
                    # code...
                    echo $value2 . "<br>";
                }
                foreach ($_FILES[$value]["name"] as $key2 => $value2) {
                    # code...
                    echo $value2 . "<br>";
                }
                for ($i=0; $i < count($_POST[$value . "-velikost"]); $i++) { 
                    # code...
                    echo 'Velikost: ' . $_POST[$value . "-velikost"][$i] . ' Skladem: ' . $_POST[$value . "-pocet"][$i] . "ks <br>";
                }
            }
        }
    }
    $db->commit();
} catch (Exception $e) {
    //throw $th;
    $db->rollBack();
}