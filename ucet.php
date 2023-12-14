<?php

session_start();

if(isset($_SESSION["username"])) {
    $html = file_get_contents("kod/html/ucet.html");

} else {
    $html = file_get_contents("kod/html/login.html");


    if(isset($_POST["odeslat"])) {

    }
}

echo $html;
