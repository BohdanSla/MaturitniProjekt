<?php

declare(strict_types=1);

spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

use Databaze as Db;

$db = new Db();



$html = file_get_contents("kod/html/index.html");

$stmt = $db->prepare("SELECT produkt.nazev, produkt.popis, COALESCE(produkt.cena_ve_sleve,produkt.cena) AS cena, produkt.hodnoceni_produktu, obrazek.src
FROM produkt 
JOIN obrazky_k_produktu ON produkt.id = obrazky_k_produktu.id_produktu
JOIN obrazek ON obrazky_k_produktu.id_obrazku = obrazek.id
WHERE obrazek.src LIKE '%main%'
AND hodnoceni_produktu IS NOT NULL
ORDER BY hodnoceni_produktu DESC
LIMIT 4");

$stmt->execute();
$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($arr as $key => $value) {
    # code...

    $src = "obrazky/" . $value["src"];
    $odkaz = "produkt.php?nazev=" . $value["nazev"];

    $html = preg_replace("/\[@doporuceny-produkt-obrazek\]/",$src, $html,1);
    $html = preg_replace("/\[@doporuceny-produkt-odkaz\]/",$odkaz , $html,1);
    $html = preg_replace("/\[@nazev\]/",$value["nazev"], $html,1);
    $html = preg_replace("/\[@cena\]/",zformulujCenu(strval($value["cena"])), $html,1);
    $html = preg_replace("/\[@recenze\]/",strval($value["hodnoceni_produktu"]), $html,1);
    $html = preg_replace("/\[@popis\]/",strval($value["popis"]), $html,1);
}

// musim dat AS protoze php ma problem se stejnymi nazvy sloupce!!!
$stmt = $db->prepare("SELECT produkt.nazev, znacka.nazev as znacka, produkt.cena, produkt.cena_ve_sleve, obrazek.src 
FROM produkt, znacka, obrazek 
JOIN obrazky_k_produktu ON obrazky_k_produktu.id_obrazku = obrazek.id 
WHERE znacka.id = produkt.id_znacky 
AND produkt.id = obrazky_k_produktu.id_produktu 
AND produkt.cena_ve_sleve IS NOT NULL
AND obrazek.src LIKE '%main%';");

$stmt->execute();
$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($arr as $key => $value) {
    # code...

    $src = "obrazky/" . $value["src"];
    $odkaz = "produkt.php?nazev=" . $value["nazev"];

    $html = preg_replace("/\[@produkt-ve-sleve-obrazek]/",$src, $html,1);
    $html = preg_replace("/\[@produkt-ve-sleve-odkaz]/",$odkaz, $html,1);
    $html = preg_replace("/\[@produkt-ve-sleve-nazev\]/",$value["nazev"], $html,1);
    $html = preg_replace("/\[@produkt-ve-sleve-znacka\]/",strval($value["znacka"]), $html,1);
    $html = preg_replace("/\[@produkt-ve-sleve-cena\]/",zformulujCenu(strval($value["cena"])), $html,1);
    $html = preg_replace("/\[@produkt-ve-sleve-cena_ve_sleve\]/",zformulujCenu(strval($value["cena_ve_sleve"])), $html,1);
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