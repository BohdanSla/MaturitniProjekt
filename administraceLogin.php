<?php

declare(strict_types=1);

$html = file_get_contents("kod/html/administraceLogin.html");


if (isset($_SESSION["user_id"])) {
    # code...
    unset($_SESSION["userd_id"]);
    session_regenerate_id();
}

$db = new PDO("mysql:host=localhost;dbname=pro_sportovce;charset=utf8","root","");

$administrator = "administrator";

if (isset($_POST["odeslat"])) {
    # code...
    if (strcmp($_POST["jmeno"],$administrator) == 0) {
        # code...
        
        $stmt = $db->prepare("SELECT heslo FROM uzivatel WHERE jmeno = :jmeno");
        $stmt->execute([":jmeno" => $administrator]);

        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($arr) > 0) {
            # code...
            if (password_verify($_POST["heslo"],$arr[0]["heslo"])) {
                # code...
                $_SESSION["username"] = $login;
                echo "User: $login";

                header("Location: administraceSprava.php");
            } else {
                //predelat
                echo "špatné heslo";
            }
        } else {
            //predelat
            echo "uzivatel neexistuje";
        }

    } else {
        //predelat jeste
        echo "spatne zadane jmeno";
    }

}



echo $html;