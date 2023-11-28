<?php

declare(strict_types=1);

$html = file_get_contents("kod/html/registrace.html");


if (isset($_POST["odeslat"])) {
    $db = new PDO("mysql:host=localhost;dbname=pro_sportovce;charset=utf8","root","");
    # code...
    
    $stmt = $db->prepare("insert into uzivatel(heslo,jmeno,prijmeni,email,telefonni_cislo,psc,ulice,mesto) values(:heslo,:jmeno,:prijmeni,:email,:telefonni_cislo,:psc,:ulice,:mesto)");
    
    $stmt->execute([
    ":heslo" => password_hash($_POST["heslo"],PASSWORD_BCRYPT),
    ":jmeno" => htmlspecialchars($_POST["jmeno"]),
    ":prijmeni" => htmlspecialchars($_POST["prijmeni"]),
    ":email" => htmlspecialchars($_POST["email"]),
    ":telefonni_cislo" => htmlspecialchars($_POST["telefonniCislo"]),
    ":psc" => htmlspecialchars($_POST["psc"]),
    ":ulice" => htmlspecialchars($_POST["ulice"]),
    ":mesto" => htmlspecialchars($_POST["mesto"]),
    ]);

    header("Location: ucet.php");
}

echo $html;
