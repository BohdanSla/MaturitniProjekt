<?php

session_start();

spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

use Databaze as Db;

$db = new Db();

if(isset($_SESSION["user"])) {
    $html = file_get_contents("kod/html/ucet.html");
} else {
    $html = file_get_contents("kod/html/login.html");


    if(isset($_POST["odeslat"])) {
        $stmt = $db->prepare("SELECT email,heslo FROM uzivatel WHERE email = :email");

        $stmt->execute([":email" => $_POST["email"]]);

        $uzivatel = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($uzivatel) {
            # code...
            if (password_verify($_POST["heslo"],$uzivatel["heslo"])) {
                $_SESSION["user"] = $uzivatel["email"];
                header("Location: ucet.php");
                # code...
            } else {

            }
        }
    }
}

echo $html;
