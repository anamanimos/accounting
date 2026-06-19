<?php
if (file_exists('wa.txt')) {
    echo "<pre>";
    // read last 100 lines
    $file = file('wa.txt');
    for ($i = max(0, count($file)-100); $i < count($file); $i++) {
        echo htmlspecialchars($file[$i]);
    }
    echo "</pre>";
} else {
    echo "wa.txt not found!";
}
