<?php

$row = 1;
$filename = 'dicionario_x.csv';
if (( $handle = fopen( __DIR__ . '/json/DicionarioFonetico/'.$filename, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        echo "$num campos na linha $row: <br />\n";
        $row++;
        for ($c=0; $c < $num; $c++) {
            echo $data[$c] . "<br />\n";
        }
    }
    fclose($handle);
}
?>