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

function checkVowel($str)
{
    $transLetters = array("á" => "a", "à" => "a", "ã" => "a", "â" => "a", "ä" => "a", "é" => "e", "è" => "e", "ẽ" => "e", "ê" => "e", "ë" => "e", "í" => "i", "ì" => "i", "ĩ" => "i", "î" => "i", "ï" => "i", "ó" => "o", "ò" => "o", "õ" => "o", "ô" => "o", "ö" => "o", "ú" => "u", "ù" => "u", "ũ" => "u", "û" => "u", "ü" => "u", "ç" => "c");
    $vowels = array("a", "e", "i", "o", "u");
    //Passo tudo pra minúsculo e passo as acentuadas pra não acentuadas e cecidilha para c
    $str = strtolower($str);
    $str = strtr($str, $transLetters);
    
    if (in_array($str, $vowels)) {
        return true;
    } else {
        return false;
    }
    /*if (ctype_alpha($str)) {
        return true;
    } else {
        return false;
    }*/
}

function accented_to_normal($str)
{
    $transLetters = array("á" => "a", "à" => "a", "ã" => "a", "â" => "a", "ä" => "a", "é" => "e", "è" => "e", "ẽ" => "e", "ê" => "e", "ë" => "e", "í" => "i", "ì" => "i", "ĩ" => "i", "î" => "i", "ï" => "i", "ó" => "o", "ò" => "o", "õ" => "o", "ô" => "o", "ö" => "o", "ú" => "u", "ù" => "u", "ũ" => "u", "û" => "u", "ü" => "u", "ç" => "c");
    $str = strtolower($str);
    $str = strtr($str, $transLetters);
    return $str;
}

function howManySyllables($filename)
{
    if (( $handle = fopen( __DIR__ . '/csv/DicionarioFonetico/'.$filename, "r")) !== FALSE) 
    {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
        {
            $silabas = explode("·", $data[0]);
            $num = count($silabas);
            echo "Quantidade de sílabas: ".$num."<br />\n";   
        }
        fclose($handle);
    }
}

function setCanonic($filename)
{
    $cont = 0;
    if (( $handle = fopen( __DIR__ . '/csv/DicionarioFonetico/'.$filename, "r")) !== FALSE) 
    {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
        {
            if ($cont === 2)
            {
                $silabas = explode("·", $data[0]);
                $num = count($silabas);
                echo $num."<br />\n";
                $contCanonic = 0;
                for ($i = 0; $i < $num; $i++)
                {
                    $silaba = accented_to_normal($silabas[$i]);
                    echo $silaba."<br />\n";
                    $silaba = str_split($silaba);
                    $qtdLetters = count($silaba);
                    echo "Quantidade de letras: ".$qtdLetters."<br />\n";
                    if($qtdLetters === 2)
                    {
                        if(checkVowel($silaba[0]) === false)
                        {
                            if(checkVowel($silaba[1]) === true)
                            {
                                $contCanonic++;
                            }    
                        }
                    }
                }
                if($contCanonic === $num)
                {
                    echo $data[0]." é canônica";
                }
                else
                {
                    echo $data[0]." não é canônica";
                }
            } 
            $cont++; 
        }
        fclose($handle);
    }
}

//checkVowel("A");
echo "testando função: <br />\n";
howManySyllables($file);

?>