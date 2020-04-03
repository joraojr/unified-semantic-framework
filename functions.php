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

//split com caracter especial
function str_split_unicode($str, $l = 0) {
    if ($l > 0) {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
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

function howManySyllables($str)
{
    $num = count($str);
    return $num;
}


function setNumberSyllables($filename)
{
    if (( $handle = fopen( __DIR__ . '/csv/DicionarioFonetico/'.$filename, "r")) !== FALSE) 
    {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
        {
            echo "Palavra: ".$data[0]."<br />\n";
            $syllables = explode("·", $data[0]);
            $num = howManySyllables($syllables);
            echo "Quantidade de sílabas: ".$num."<br />\n";
        }
        fclose($handle);
    }
}

function syllabicKind($str)
{
    $numSyllables = howManySyllables($str);
    if($numSyllables === 1)
    {
        return "monossílaba";
    }
    elseif ($numSyllables === 2) 
    {
        return "dissílaba";
    }
    elseif ($numSyllables === 3) 
    {
        return "trissílaba";
    }
    elseif ($numSyllables >= 4) 
    {
        return "polissílaba";
    }
}

function setSyllabicKind($filename)
{
    if (( $handle = fopen( __DIR__ . '/csv/DicionarioFonetico/'.$filename, "r")) !== FALSE) 
    {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
        {
            $syllables = explode("·", $data[0]);
            $kind = syllabicKind($syllables);
            echo "Tipo silábico da palavra: ".$kind."<br />\n";
        }
        fclose($handle);
    }
}

function checkBeginH($str)
{
    if($str[0][0] === 'h')
    {
        return true;
    }
    else
    {
        return false;
    }
}

function setBeginH($filename)
{
    if (( $handle = fopen( __DIR__ . '/csv/DicionarioFonetico/'.$filename, "r")) !== FALSE) 
    {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
        {
            $syllables = explode("·", $data[0]);
            $begin = checkBeginH($syllables);
            if($begin == true)
            {
                echo "Começa com H <br />\n";
            }
            else
            {
                echo "Não começa com H <br />\n";
            }
        }
        fclose($handle);
    }
}

function haveCCedilha($str)
{
    $limit = count($str);
    $bool = false;
    for($i = 0; $i < $limit; $i++)
    {
        $letters = str_split_unicode($str[$i]);
        //$size = count($letters);
        if((in_array("ç", $letters))) 
        {
            $bool = true;
        }
        /*for($j = 0; $j < $size; $j++)
        {
            if((in_array('ç', $letters))) 
            {
                $bool = true;
            }
        } */
        
    }
    return $bool;
}

function setCCedilha($filename)
{
    if (( $handle = fopen( __DIR__ . '/csv/DicionarioFonetico/'.$filename, "r")) !== FALSE) 
    {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
        {
            $syllables = explode("·", $data[0]);
            $bool = haveCCedilha($syllables);
            if($bool == true)
            {
                echo "Tem Ç <br />\n";
            }
            else
            {
                echo "Não tem Ç <br />\n";
            }
        }
        fclose($handle);
    }
}

function whatSyllableTonic($str)
{
    $limit = count($str);
    $syl = 0;
    $tonic = 0;
    $found = false;
    $occored = false;
    for($i = 0; $i < $limit; $i++)
    {
        //Verifica se é o caso de terem dois fonemas e já ter passado por um, se for, para de contar
        if((in_array(" ", $letters)))
        {
            $occored = true;
        }
        $letters = str_split_unicode($str[$i]);
        //$size = count($letters);
        if((in_array("ˈ", $letters))) 
        {
            $found = true;
            $cont++;
        }
         
        if($found === true)
        {
            if(!$occored)
            {
                $tonic++;
            }
            
        }
    }
    return $tonic;
}

function setTonic($filename)
{
    if (( $handle = fopen( __DIR__ . '/csv/DicionarioFonetico/'.$filename, "r")) !== FALSE) 
    {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
        {
            $syllables = explode(".", $data[2]);
            $tonic = whatSyllableTonic($syllables);
            echo "Fonema: ".$data[2]."<br />\n";
            if($tonic === 3)
            {
                echo "Classificação tônica: Proparoxítona <br />\n";
            }
            elseif ($tonic === 2)
            {
                echo "Classificação tônica: Paroxítona <br />\n";
            }
            elseif ($tonic === 1)
            {
                echo "Classificação tônica: Oxítona <br />\n";
            }
            elseif ($tonic === 0)
            {
                echo "Não há fonema <br />\n";
            }
            elseif ($tonic > 3)
            {
                echo "Classificação tônica: Proparoxítona <br />\n";
            }
        }
        fclose($handle);
    }
}

function sayNoTonic($filename)
{
    if (( $handle = fopen( __DIR__ . '/csv/DicionarioFonetico/'.$filename, "r")) !== FALSE) 
    {
        $row = 1;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
        {
            $syllables = explode(".", $data[2]);
            $tonic = whatSyllableTonic($syllables);
            if ($tonic === 0)
            {
                echo "Palavra: ".$data[0]."<br />\n";
                echo "Não há fonema  na linha ".$row."<br />\n";
            }
            $row++;
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
                $syllables = explode("·", $data[0]);
                $num = count($syllables);
                echo $num."<br />\n";
                $contCanonic = 0;
                for ($i = 0; $i < $num; $i++)
                {
                    $syllable = accented_to_normal($syllables[$i]);
                    echo $syllable."<br />\n";
                    $syllable = str_split($syllable);
                    $qtdLetters = count($syllable);
                    echo "Quantidade de letras: ".$qtdLetters."<br />\n";
                    if($qtdLetters === 2)
                    {
                        if(checkVowel($syllable[0]) === false)
                        {
                            if(checkVowel($syllable[1]) === true)
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

echo "testando função: <br />\n";

/*
$test = array("za", "bˈũ", "bə");
$pos = whatSyllableTonic($test);

echo "Posicao da sílaba tônica: ".($pos+1);
*/
setTonic($file);


?>