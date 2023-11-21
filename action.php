<?php

declare(strict_types=1);

$html = file_get_contents("vypis.html");

if(isset($_GET["sport"])) {
    $sport = $_GET["sport"];
    $html = preg_replace("/daw/",$sport,$html,1);
}

echo $html;