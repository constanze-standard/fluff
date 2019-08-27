<?php

$fd = fopen('php://output', 'w');

for ($a = 0; $a < 1; $a++) {
    // file_put_contents('php://output', $a);
    fwrite($fd, $a);
    flush();
    ob_flush();
}
ob_end_clean();
fclose($fd);