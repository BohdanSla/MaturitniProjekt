<?php

declare(strict_types=1);

$html = file_get_contents("kod/html/vypis.html");

//neni konecne reseni pro sbirani hodnot z db
$db = new PDO("mysql:host=localhost;dbname=pro_sportovce;charset=utf8","root","");
$stmt = $db->prepare("SELECT 
MAX(GREATEST(produkt.cena,produkt.cena_ve_sleve)) AS nejvetsi_cena 
FROM produkt");

$stmt->execute();
$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($arr as $key => $value) {
    # code...
    $html = preg_replace("/\[@maximum\]/",strval($value["nejvetsi_cena"]),$html);
}

$stmt = $db->prepare("SELECT
obrazek.src,
produkt.nazev,
produkt.cena,
produkt.cena_ve_sleve,
GROUP_CONCAT(barva.nazev) AS barvy
FROM produkt
JOIN mnozstvi_produktu_urcite_barvy_a_velikosti ON mnozstvi_produktu_urcite_barvy_a_velikosti.id_produktu = produkt.id
JOIN barva ON barva.id = mnozstvi_produktu_urcite_barvy_a_velikosti.id_barvy
JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id
JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku
GROUP BY produkt.id");

$stmt->execute();

$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);


foreach ($arr as $key => $value) {
    # code...
    $src = "obrazky/" . $value["src"];
    $cenaVesleve = "";
    $odkaz = "produkt.php?nazev=" . $value["nazev"] ;

    if (isset($value["cena-ve-sleve"])) {
        # code...
        $cenaVesleve = "<p>" . strval($value["cena_ve_sleve"]) . "</p>";
    }
    
    $html = preg_replace("/\[@odkaz\]/",$odkaz,$html,1);
    $html = preg_replace("/\[@obrazek\]/",$src,$html,1);
    $html = preg_replace("/\[@nazev\]/",$value["nazev"],$html,1);
    $html = preg_replace("/\[@cena\]/",strval($value["cena"]),$html,1);
    $html = preg_replace("/\[@cena-ve-sleve\]/",$cenaVesleve,$html,1);
    $html = preg_replace("/\[@barvy\]/",$value["barvy"],$html,1);

}

echo $html;