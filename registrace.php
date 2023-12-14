<?php

declare(strict_types=1);

spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

use Databaze as Db;

$html = file_get_contents("kod/html/registrace.html");


if (isset($_POST["odeslat"])) {
    $db = new Db();
    # code...
    
    $stmt = $db->prepare("insert into uzivatel(heslo,jmeno,prijmeni,email,telefonni_cislo,psc,ulice,mesto,id_role) values(:heslo,:jmeno,:prijmeni,:email,:telefonni_cislo,:psc,:ulice,:mesto,:id_role)");
    
    $stmt->execute([
    ":heslo" => password_hash($_POST["heslo"],PASSWORD_BCRYPT),
    ":jmeno" => htmlspecialchars($_POST["jmeno"]),
    ":prijmeni" => htmlspecialchars($_POST["prijmeni"]),
    ":email" => htmlspecialchars($_POST["email"]),
    ":telefonni_cislo" => htmlspecialchars($_POST["telefonniCislo"]),
    ":psc" => htmlspecialchars($_POST["psc"]),
    ":ulice" => htmlspecialchars($_POST["ulice"]),
    ":mesto" => htmlspecialchars($_POST["mesto"]),
    ":id_role" => 3
    ]);

    header("Location: ucet.php");
}

echo $html;
