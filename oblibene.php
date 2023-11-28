<?php


if(isset($_SESSION["user-id"])) {
    $html = file_get_contents("kod/html/oblibeneProdukty.html");
} else {
    $html = file_get_contents("kod/html/login.html");
    $html .= "<h5>Musíš se přihlásit, abys viděl/a svoje oblíbenné produkty</h5>";
}

echo $html;
