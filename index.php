<?php

declare(strict_types=1);

$html = file_get_contents("kod/stranky/index.html");

$db = new PDO("mysql:host=localhost;dbname=pro_sportovce;charset=utf8","root","");

$stmt = $db->prepare("SELECT produkt.nazev, produkt.popis, produkt.cena, produkt.hodnoceni_produktu, obrazek.obrazek_src
FROM produkt 
JOIN obrazky_k_produktu ON produkt.id = obrazky_k_produktu.id_produktu
JOIN obrazek ON obrazky_k_produktu.id_obrazku = obrazek.id
LIMIT 4");

$stmt->execute();
$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($arr as $key => $value) {
    # code...
    $zformulovanaCena = "";

    $cena = strval($value["cena"]);
    $delka = mb_strlen($cena);

    for ($i=$delka - 1; $i >= 0; $i--) { 
        # code...
        $zformulovanaCena = $cena[$i] . $zformulovanaCena;
        if (($delka - $i) % 3 == 0 && $i > 0) {
            $zformulovanaCena = " " . $zformulovanaCena;
        }
    }

    $src = "kod/obrazky/" . $value["obrazek_src"];
    $cena = $zformulovanaCena . " Kč";


    $html = preg_replace("/\[@doporuceny-produkt-obrazek]/",$src, $html,1);
    $html = preg_replace("/\[@nazev\]/",$value["nazev"], $html,1);
    $html = preg_replace("/\[@cena\]/",$cena, $html,1);
    $html = preg_replace("/\[@recenze\]/",strval($value["hodnoceni_produktu"]), $html,1);
    $html = preg_replace("/\[@popis\]/",strval($value["popis"]), $html,1);
}

echo $html;