<?php

declare(strict_types=1);

$html = file_get_contents("kod/html/administraceSprava.html");

//bude muset zde to celý vyrenderovat
//každý form bude muset mít vlastní name!! (takže přes fory dát navíc čísla), jednotlivé inputy pak ne!!!!!!
echo $html;