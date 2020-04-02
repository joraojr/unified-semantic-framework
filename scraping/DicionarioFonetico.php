<?php

namespace scraping;

use ArrayObject;

require_once __DIR__ . '/../simplehtmldom_1_8_1/simple_html_dom.php';

require_once __DIR__.'/../Controller/../scraping/Scraping.php';

class DicionarioFonetico implements Scraping
{
    private $letras = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
    public $dicionario = array();
    //public $linha = array(); //linha do dicionario, criada com o propósito de deixar num formato json
    //Se for necessario a variavel da linha, provavelmente a atribuição deva ser feita assim:
    /*reset($this->linha); 
    array_push($this->linha, array('Palavra' => $palavrasPag[$j]->plaintext));
    array_push($this->linha, array('Categoria' => $categoria[$indexCategoria]));
    array_push($this->linha, array('Fonetica' => $foneticasPag[$j]->plaintext));
    array_push($this->dicionario, $this->linha);*/
    public function scraping()
    {

        $urlBase = "http://www.portaldalinguaportuguesa.org/index.php?action=fonetica&region=spx&act=list&letter=";
        //$letraAtual = $this->$letras[23];
        $numeroAtual = 0;
        //$htmlPagina = file_get_html($urlBase.$letraAtual."&start=".$numeroAtual);
        //echo $palavrasPag[0];
        
        //Tenho que usar o getElementByTagName para percorrer por todos os tds da tabela

        //Exemplo: para a letra X tem 77 resultados, cada página vai de 20 em 20, o que dá 4 páginas:
        //foreach($this->letras as $letra)

        for($letra = 0; $letra < 1; $letra++)
        {
            $i=0;
            $flag = true;
            $cont = 0;
            while($flag) {
                $numeroAtual = $i*20;
                $htmlPagina = file_get_html($urlBase.$this->letras[$letra]."&start=".$numeroAtual);
                $palavrasPag = $htmlPagina->find('td[title="Palavra"]');
                $foneticasPag = $htmlPagina->find('td[title="Fonética"]');
                $tabelaPag = $htmlPagina->find('table[id="rollovertable"]');
                $categoria = $tabelaPag[0]->getElementsByTagName('td');
                $indexCategoria = 1;
                for($j = 0; $j < 1; $j++)
                {
                    if (is_null($palavrasPag[$j]))
                    {
                        $flag = false;
                        break 2;
                    }
                    //echo ($palavrasPag[$j]->plaintext).' -> '.($categoria[$indexCategoria]).' -> '.($foneticasPag[$j]->plaintext).'<br>';
                    array_push($this->dicionario, array('Palavra' => strip_tags(html_entity_decode($palavrasPag[$j]->plaintext)), 'Categoria' => strip_tags(html_entity_decode($categoria[$indexCategoria])), 'Fonetica' => strip_tags(html_entity_decode($foneticasPag[$j]->plaintext))));
                    //print_r($this->dicionario[$cont]);
                    //$cont++;
                    $indexCategoria = $indexCategoria + 3;
                }   
                /*if($i === 1)
                {
                    $flag=false; z4Xt4V9pDBaG
                }*/
                
                $i++;
            }
            $this->generateJson("dicionario_".$this->letras[$letra]);
        }
        //$this->generateJson("dicionario");
        //Testando pra ver se o array_push funcionou
        echo ($this->dicionario[10]['Palavra']).' -> '.($this->dicionario[10]['Categoria']).' -> '.($this->dicionario[10]['Fonetica']);
        echo "<h4>Scraping Realizado</h4>";
        //unset($this->linha);
        //unset($this->dicionario);
        
    }

    public function generateJson($name)
    {
        /*
            array('nome' => $this->nome),
            array('dt_nascimento' => $this->dt_nascimento),
            array('dt_desaparecimento' => $this->dt_desaparecimento),
            array('cidade' => $this->cidade),
            array('estado' => $this->estado),
            array('imagem' => $this->imagem),
            array('fonte' => $this->fonte),
            array('circunstancia_desaparecimento' => $this->circunstancia_desaparecimento),
            array('situacao' => $this->situacao),
        
        $arr_json = array(
            'name' => 'DicionarioFonetico',
            'dicionario' => $this->dicionario
        );
        */
        //format the data
        //$formattedData = json_encode($this->dicionario);

        //set the filename
        $filename = $name.'.csv';

        //open or create the file
        $handle = fopen( __DIR__ . '/../csv/DicionarioFonetico/'.$filename, 'w+');

        foreach($this->dicionario as $linha)
        {
            //write the data into the file
            $linha['Categoria'] = trim(preg_replace('/\t+/', '', $linha['Categoria']));
            //Em txt:
            //$escrita = ($linha['Palavra']).','.($linha['Categoria']).','.($linha['Fonetica']).PHP_EOL;
            //fwrite($handle, $escrita);
            fputcsv($handle, array_values($linha));

        }
        //write the data into the file
        //fwrite($handle, $formattedData);

        //close the file
        fclose($handle);
    }
}

?>