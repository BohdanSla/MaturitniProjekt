<?php

declare(strict_types=1);

spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

use Databaze as Db;

$html = file_get_contents("kod/html/registrace.html");

$zprava = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Db();

    $stmt = $db->prepare("SELECT id FROM uzivatel WHERE email = :email");
    $stmt->execute([":email" => $_POST["email"]]);
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($arr) < 1) {
        $emailRegex = "/^[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z]{2,4}$/";
        $telefonniCisloregex = "/(^(\+[0-9]{1,4} )?([0-9]{3} ){2}[0-9]{3}$)|(^(\+[0-9]{1,4})?[0-9]{9}$)/";
        $hesloRegex = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z\d\s])[A-Za-z\d\W\S]{8,}$/";
        # code...
        if(isset($_POST["jmeno"]) && isset($_POST["prijmeni"]) && isset($_POST["heslo"]) && isset($_POST["hesloZnovu"]) && isset($_POST["email"]) && isset($_POST["telefonniCislo"]) && isset($_POST["psc"]) && isset($_POST["ulice"]) && isset($_POST["mesto"])) {


            if(strcmp($_POST["heslo"],$_POST["hesloZnovu"]) == 0 && preg_match($telefonniCisloregex,$_POST["telefonniCislo"]) && preg_match($hesloRegex,$_POST["heslo"]) && preg_match($emailRegex,$_POST["email"])) {


                $stmt = $db->prepare("insert into uzivatel(heslo,jmeno,prijmeni,email,telefonni_cislo,psc,ulice,mesto,id_role,jeZaregistrovany) values(:heslo,:jmeno,:prijmeni,:email,:telefonni_cislo,:psc,:ulice,:mesto,:id_role,1)");
                
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
        } 
    } else {
        $zprava = "Zadaný email už existuje";
    }
}
$html = str_replace("[@zprava]",$zprava,$html);

echo $html;
