<?php

declare(strict_types=1);

$html = file_get_contents("kod/index.html");


while (mb_strpos($html,"[@recenze]") != false) {
    # code...
    $html = preg_replace("/\[@recenze\]/",strval(random_int(2,12)), $html,1);
}

echo $html;