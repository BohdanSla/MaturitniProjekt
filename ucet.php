<?php

session_start();

if(isset($_SESSION["user_id"])) {
    $html = file_get_contents("kod/html/ucet.html");
} else {
    $html = file_get_contents("kod/html/login.html");
}

echo $html;
