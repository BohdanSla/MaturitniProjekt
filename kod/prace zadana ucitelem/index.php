<?php

declare(strict_types=1);

echo file_get_contents("index.html");

if(isset($_POST["odeslat"])) {
    $soubor = file_get_contents($_FILES["soubor"]["tmp_name"]);
    $soubor = array_map("json_decode", explode("\n",$soubor));
    echo "<table>";
    foreach ($soubor as $key => $radek) {
        $zaznam = explode(",",$radek);
        echo "<tr>";
        foreach ($zaznam as $key => $value) {
            echo "<td>  $value</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}