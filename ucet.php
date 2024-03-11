<?php

declare(strict_types=1);

session_start();


// Set the inactivity time of 15 minutes (900 seconds)
$inactivity_time = 15 * 60;

if(isset($_SESSION["user"])) {
    if (isset($_SESSION['last_timestamp']) && (time() - $_SESSION['last_timestamp']) > $inactivity_time) {
        //Redirect user to login page
        header("Location: odhlasit.php");
      }else{
        // Regenerate new session id and delete old one to prevent session fixation attack
        session_regenerate_id(true);
    
        // Update the last timestamp
        $_SESSION['last_timestamp'] = time();
    }
}

spl_autoload_register(fn(string $trida):int|bool  => require_once "$trida.class.php");

use Databaze as Db;
$db = new Db();

$zprava = '';
$admin = '';

if(isset($_SESSION["user"])) {

    $html = file_get_contents("kod/html/ucet.html");

    
    
    $stmt = $db->prepare('SELECT jmeno,prijmeni,email,telefonni_cislo,mesto,ulice,psc FROM uzivatel WHERE id = :id;
    SELECT produkt.nazev,produkt.id,obrazek.src FROM produkt JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku JOIN oblibene_produkty ON oblibene_produkty.id_produktu = produkt.id JOIN uzivatel ON uzivatel.id = oblibene_produkty.id_uzivatele WHERE obrazek.src LIKE "%main%" AND id_uzivatele = :id GROUP BY oblibene_produkty.id_produktu;
    SELECT produkty_v_objednavce.id_objednavky, produkt.nazev,barva.nazev AS barva, velikost.nazev AS velikost, COALESCE(cena_ve_sleve,cena) AS cena, pocet AS mnozstvi, obrazek.src,objednavka.sleva FROM produkty_v_objednavce JOIN produkt ON produkt.id = produkty_v_objednavce.id_produktu JOIN barva ON barva.id = produkty_v_objednavce.id_barvy JOIN velikost ON velikost.id = produkty_v_objednavce.id_velikosti JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku JOIN objednavka ON objednavka.id = produkty_v_objednavce.id_objednavky WHERE id_objednavky IN (SELECT id FROM objednavka WHERE jeObjednana = 1 AND id_uzivatele = :id) AND obrazek.src LIKE "%main%" ORDER BY produkty_v_objednavce.id_objednavky;');

    $stmt->execute([":id" => $_SESSION["user"]]);

    $arr = $stmt->fetch(PDO::FETCH_ASSOC);
    $email = $arr["email"];
    
    $html = str_replace("[@jmeno]",$arr["jmeno"],$html);
    $html = str_replace("[@prijmeni]",$arr["prijmeni"],$html);
    $html = str_replace("[@email]",$email,$html);
    $html = str_replace("[@telefonniCislo]",$arr["telefonni_cislo"],$html);
    $html = str_replace("[@mesto]",$arr["mesto"],$html);
    $html = str_replace("[@ulice]",$arr["ulice"],$html);
    $html = str_replace("[@psc]",$arr["psc"],$html);
    
    $stmt->nextRowSet();
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    
    if(count($arr) > 0) {
        $oblibene = "";
        $oblibenyObrazek = "";
        foreach ($arr as $key => $value) {
            # code...
            $src = "obrazky/" . $value["src"];    
            $oblibene .= '<div><section><img src="' . $src . '"><h3>' . $value["nazev"] . '</h3></section><a href="produkt.php?id=' . $value["id"]  .'">Podívat se</a></div>';
        }
        
        $html = str_replace("[@oblibene]",$oblibene,$html);
    } else {
        $html = str_replace("[@oblibene]","Nemáte tu žádné oblíbené produkty",$html);
    }
    
    $stmt->nextRowSet();
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($arr) > 0) {
        # code...
        $idObjednavky = array_values(array_unique(array_map(function($value) {
            return $value["id_objednavky"];
        },$arr)));

        $objednavky = '';
        for ($i=0; $i < count($idObjednavky); $i++) {
            # code...
            $objednavky .= '<div><h3>č. objednávky: ' . $idObjednavky[$i] .'</h3><table><tbody>';
            $celkovaCena = 0;

            $produkty = array_filter($arr,function($value) use ($idObjednavky,$i){
                return $value["id_objednavky"] == $idObjednavky[$i];
            });


            $y = 0;
            foreach ($produkty as $key => $value) { 
                # code...
                $src = "obrazky/" . $value["src"];

                $objednavky .= '<tr><td><img src="' . $src .'"></td><td><b>' . $value["nazev"] . '</b></td><td>' . $value["barva"] .' | '. $value["velikost"] .' | '. $value["mnozstvi"] .' ks</td><td><b>' .  ($value["mnozstvi"] * $value["cena"]) . ' Kč</b></td></tr>';
                $celkovaCena += $value["cena"] * $value["mnozstvi"];
                if(count($produkty) - $y == 1 && $value["sleva"] != NULL) {
                    $celkovaCena =  $celkovaCena - $value["sleva"];
                    $objednavky .= '<tr><td colspan=4><b style=color:green;display:flex;justify-content:flex-end;>Sleva: -' . $value["sleva"] .' Kč</b></td></tr>';
                }
                $y++;
            // ! dopsat slevový kód a celkovou cenu
        }
        $objednavky .= '<tr><td colspan=4><b>Celková cena: ' . $celkovaCena .' Kč</b></td></tr></tbody></table></div>';
        }
        $html = str_replace("[@objednavky]",$objednavky,$html);
    } else {
        $html = str_replace("[@objednavky]","<p>Zatím tu není žádné zakoupené zboží</p>",$html);
    }
    
    if(isset($_POST["odeslat"])) {
        
        $stmt = $db->prepare("SELECT id FROM uzivatel WHERE email = :email");
        $stmt->execute([":email" => $_POST["email"]]);
        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($arr) < 1)  {
            aktualizovatUdaje($db,$zprava);
        } else if (strcmp($email,$_POST["email"]) == 0) {
            aktualizovatUdaje($db,$zprava);
        } else {
            $zprava = "<b>Zadaný email už existuje</b>";
        }

        header("Location: ucet.php");
    }
    $html = str_replace("[@timeout]",'<script defer src="kod/js/timeout.js"></script>',$html);

    $stmt = $db->prepare("SELECT id_role FROM uzivatel WHERE id = :id");
    $stmt->execute([":id" => $_SESSION["user"]]);
    
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if($arr[0]["id_role"] == 1) {
        $admin = '<a href="administrace.php"><li><img src="obrazky/naradi_ikona.svg" alt="naradi_ikona">Administrace</li></a>';
    }
    
    
} else {
    $html = file_get_contents("kod/html/login.html");
    
    if(isset($_GET["zprava"])) {
        $zprava = '<p style="color:red;">Byli jste odhlášeni kvůli neaktivitě po dobu minut 30</p>';
    }
    
    if(isset($_POST["odeslat"])) {
        $stmt = $db->prepare("SELECT id,email,heslo FROM uzivatel WHERE email = :email");

        $stmt->execute([":email" => $_POST["email"]]);
        
        $uzivatel = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($uzivatel) {
            # code...
            if (password_verify($_POST["heslo"],$uzivatel["heslo"])) {
                $_SESSION["user"] = $uzivatel["id"];
                header("Location: ucet.php");
                # code...
            } else {
                $zprava = "<b>Špatně zadané heslo</b>";
            }
        } else {
            $zprava = "<b>zadaný email neexistuje</b>";
        }
    }
}

$html = str_replace("[@zprava]",$zprava,$html);
$html = str_replace("[@admin]",$admin,$html);

echo $html;

function aktualizovatUdaje(&$db,&$zprava) {
    $emailRegex = "/^[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z]{2,4}$/";
    $telefonniCisloregex = "/(^(\+[0-9]{1,4} )?([0-9]{3} ){2}[0-9]{3}$)|(^(\+[0-9]{1,4})?[0-9]{9}$)/";
    # code...
    if(isset($_POST["jmeno"]) && isset($_POST["prijmeni"]) && isset($_POST["email"]) && isset($_POST["telefonniCislo"]) && isset($_POST["psc"]) && isset($_POST["ulice"]) && isset($_POST["mesto"])) {
        
        
        if(preg_match($telefonniCisloregex,$_POST["telefonniCislo"]) && preg_match($emailRegex,$_POST["email"])) {
            
            
            $stmt = $db->prepare("UPDATE uzivatel SET jmeno= :jmeno,prijmeni= :prijmeni,email= :email,telefonni_cislo= :telefonniCislo,psc= :psc,ulice= :ulice,mesto= :mesto WHERE id = :id");
    
            $stmt->execute([":jmeno" => htmlspecialchars($_POST["jmeno"]),":prijmeni" => htmlspecialchars($_POST["prijmeni"]),":email" => htmlspecialchars($_POST["email"]),":telefonniCislo" => htmlspecialchars($_POST["telefonniCislo"]),":psc" => htmlspecialchars($_POST["psc"]),":ulice" => htmlspecialchars($_POST["ulice"]),":mesto" => htmlspecialchars($_POST["mesto"]),":id" => $_SESSION["user"]]);
    
            $zprava = "<b style='color:green'>Změny provedeny!</b>";

        }
    } 
}
