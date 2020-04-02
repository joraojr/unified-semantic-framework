<?php

$file = 'dicionario_x.csv';

function printCsv($filename)
{

    $row = 1;
    if (( $handle = fopen( __DIR__ . '/csv/DicionarioFonetico/'.$filename, "r")) !== FALSE) {
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
}

printCsv($file);

?>