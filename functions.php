<?php

$file = 'dicionario_z.csv';

function printCsv($filename)
{

    if (( $handle = fopen( __DIR__ . '/csv/DicionarioFonetico/'.$filename, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
            for ($c=0; $c < $num; $c++) {
                echo $data[$c].", ";
            }
            echo "<br />\n";
        }
        fclose($handle);
    }
}

function checkLetter($str)
{
    $transLetters = array("á" => "a", "à" => "a", "ã" => "a", "â" => "a", "ä" => "a", "é" => "e", "è" => "e", "ẽ" => "e", "ê" => "e", "ë" => "e", "í" => "i", "ì" => "i", "ĩ" => "i", "î" => "i", "ï" => "i", "ó" => "o", "ò" => "o", "õ" => "o", "ô" => "o", "ö" => "o", "ú" => "u", "ù" => "u", "ũ" => "u", "û" => "u", "ü" => "u", "ç" => "c");
    //Passo tudo pra minúsculo e passo as acentuadas pra não acentuadas e cecidilha para c
    $str = strtolower($str);
    $str = strtr($str, $transLetters);
    
    if (ctype_alpha($str)) {
        return true;
    } else {
        return false;
    }
}

function setCanonic($filename)
{
    $cont = 0;
    if (( $handle = fopen( __DIR__ . '/csv/DicionarioFonetico/'.$filename, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
            if ($cont == 0)
            {
                $silabas = explode("·", $data[0]);
                echo $silabas[1];
            } 
            $cont++; 
        }
        fclose($handle);
    }
}

checkLetter("cAçárù");
echo "testando função: <br />\n";
setCanonic($file);

?>