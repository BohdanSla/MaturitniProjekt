<?php

declare(strict_types=1);

$html = file_get_contents("kod/html/vypis.html");


spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

use Databaze as Db;

$db = new Db();

$stmt = $db->prepare("SELECT 
MAX(GREATEST(produkt.cena,produkt.cena_ve_sleve)) AS nejvetsi_cena 
FROM produkt");

$stmt->execute();
$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($arr as $key => $value) {
    # code...
    $html = preg_replace("/\[@maximum\]/",strval($value["nejvetsi_cena"]),$html);
}

$stmt = $db->prepare('SELECT obrazek.src, produkt.nazev, produkt.cena, produkt.cena_ve_sleve, kategorie_produktu.podkategorie FROM produkt JOIN kategorie_produktu ON kategorie_produktu.id = produkt.id_kategorie_produktu JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku WHERE obrazek.src LIKE "%main%";');

$stmt->execute();

$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

$produkty = "";

foreach ($arr as $key => $value) {


    $src = "obrazky/" . $value["src"];
    $odkaz = "produkt.php?nazev=" . urlencode($value["nazev"]);
    $cenaVesleve = "";
    $maCenuVeSleve = "";

    $produkty .= '<a [@ma-cenu-ve-sleve] href="' . $odkaz . '"><img src="' . $src .'"><div class="parametry"><h2>' . $value["nazev"] . '</h2><b id="podkategorie">' . $value["podkategorie"] . '</b><b id="cena">' . $value["cena"] . '</b> [@cena-ve-sleve] </div></a>';
    
    if ($value["cena_ve_sleve"] != null) {
        # code...
        $cenaVesleve = "<b id=\"cenaVeSleve\">" . $value["cena_ve_sleve"] . "</b>";
        $maCenuVeSleve = 'class="maCenuVeSleve"';
    }
    $produkty = preg_replace("/\[@cena-ve-sleve\]/",$cenaVesleve,$produkty,1);
    $produkty = preg_replace("/\[@ma-cenu-ve-sleve\]/",$maCenuVeSleve,$produkty,1);
}

$html = preg_replace("/\[@produkty\]/",$produkty,$html,1);

echo $html;