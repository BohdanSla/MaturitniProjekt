<?php

declare(strict_types=1);

$html = file_get_contents("kod/html/administraceSprava.html");
$js = file_get_contents("kod/js/administraceSprava.js");

//každý form bude muset mít vlastní name!! (takže přes fory dát navíc čísla), jednotlivé inputy pak ne!!!!!!

    $db = new PDO("mysql:host=localhost;dbname=pro_sportovce;charset=utf8","root","");

    //! SELECT pro dany produkt
    //!SELECT pro vybrani vsech kategorie
    //!SELECT pro vybrani podkategorie a kategorie pro určení podkategorie
    //!SELECTY pro vybrani vsech znacek,sportu,materialu a barev pro vyber
     //!SELECT barvy,velikosti a mnozstvi pro dany produkt
     //!SELECt materialu pro dany produkt
    $sql = "SELECT produkt.nazev, obrazek.src,produkt.popis,produkt.cena,produkt.cena_ve_sleve,kategorie_produktu.kategorie, kategorie_produktu.podkategorie, 
    znacka.nazev AS znacka, 
    sport.nazev AS sport 
    FROM produkt 
    JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id 
    JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku 
    JOIN kategorie_produktu ON kategorie_produktu.id = produkt.id_kategorie_produktu 
    JOIN znacka ON znacka.id = produkt.id_znacky 
    JOIN sport ON sport.id = produkt.id_sportu 
    WHERE obrazek.src LIKE '%main%' 
    ORDER BY produkt.nazev ASC;
    SELECT DISTINCT kategorie FROM kategorie_produktu; 
    SELECT kategorie,podkategorie FROM kategorie_produktu;
    SELECT nazev FROM znacka; 
    SELECT nazev FROM sport;
    SELECT nazev FROM material;
    SELECT nazev FROM barva;
    SELECT produkt.nazev,velikost.nazev AS velikost, barva.nazev AS barva, mnozstvi_produktu_urcite_barvy_a_velikosti.pocet AS pocet
    FROM produkt
    JOIN mnozstvi_produktu_urcite_barvy_a_velikosti ON mnozstvi_produktu_urcite_barvy_a_velikosti.id_produktu = produkt.id
    JOIN velikost ON velikost.id = mnozstvi_produktu_urcite_barvy_a_velikosti.id_velikosti
    JOIN barva ON barva.id = mnozstvi_produktu_urcite_barvy_a_velikosti.id_barvy
    ORDER BY produkt.nazev ASC;
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
    ORDER BY produkt.nazev ASC";

    $moznosti = "";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($arr as $key => $value) {
        # code...

        $moznosti .= "<option value=\"" . $value["nazev"] . "\" data-popis=\"" . $value["popis"] . "\" data-cena=\"" . $value["cena"] . "\" data-cenaVeSleve=\"" . $value["cena_ve_sleve"] . "\" data-kategorie=\"" . $value["kategorie"] . "\" data-podkategorie=\"" . $value["podkategorie"] . "\" data-znacka=\"" . $value["znacka"] . "\" data-sport=\"" . $value["sport"] . "\" data-hlavniObrazek=\"obrazky/" . $value["src"] . "\">";
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

    $moznosti = "";
    $stmt->nextRowset();
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($arr as $key => $value) {
        # code...
        $moznosti .= "<option value=\"" . $value["nazev"] . "\">" . $value["nazev"] . "</option>";
    }
    $html = preg_replace("/\[@sport-produkty\]/",$moznosti,$html);

    $moznosti = "";
    $stmt->nextRowset();
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($arr as $key => $value) {
        # code...
        $moznosti .= "<input id='materialProduktu' type='checkbox' value=\"" . $value["nazev"] . "\" name='materialy[]' >" . $value["nazev"] . " ";
        $moznosti .= "<input id='procentoMaterialu' type='number' min='0' max='100'>%<br>";
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
    
    
    echo $html;
