<?php

declare(strict_types=1);

session_start();


spl_autoload_register(fn($trida):int|bool => require_once "$trida.class.php" );

use Databaze as Db;

$db = new Db();



$html = file_get_contents("kod/html/kosik.html");

if (isset($_SESSION["user"])) {
    # code...
    $stmt = $db->prepare('SELECT produkt.nazev,
    barva.nazev AS barva,
    velikost.nazev AS velikost,
    COALESCE(cena_ve_sleve,cena) AS cena,
    mnozstvi,
    obrazek.src
    FROM `produkty_v_objednavce`
    JOIN produkt ON produkt.id = produkty_v_objednavce.id_produktu
    JOIN barva ON barva.id = produkty_v_objednavce.id_barvy
    JOIN velikost ON velikost.id = produkty_v_objednavce.id_velikosti
    JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id
    JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku
    WHERE id_objednavky = (SELECT id FROM objednavka WHERE jeObjednana = 0 AND id_uzivatele = (SELECT id FROM uzivatel WHERE email = :email)) AND obrazek.src LIKE "%main%";');

    $stmt->execute([":email" => $_SESSION["user"]]);

    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $produkty = "";
    $celkovaCena = 0;

    foreach ($arr as $key => $value) {
        # code...
        $src = "obrazky/" . $value["src"];

        $produkty .= '<div><section><img src="' . $src . '"><h2>' . $value["nazev"] .'</h2></section><section><div><p>Barva: ' . $value["barva"] . '</p><p>Velikost: ' . $value["velikost"] .'</p></div><b>' . $value["cena"] . ' Kč</b><form method="get">množství:<input type="number" name="mnozstvi" id="mnozstvi" min="0" max="5" value="' . $value["mnozstvi"] . '"><button name="odstranit" type="submit"><img src="obrazky/krizek_ikona.svg"></button></form></section></div>';

        $celkovaCena += intval($value["cena"]) * intval($value["mnozstvi"]);
    }

    $html = str_replace("[@celkovaCena]",strval($celkovaCena),$html);
    $html = str_replace("[@kosik]",$produkty,$html);
}


echo $html;