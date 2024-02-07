<?php

declare(strict_types=1);

session_start();

$html = file_get_contents("kod/html/timeout.html");

echo $html;