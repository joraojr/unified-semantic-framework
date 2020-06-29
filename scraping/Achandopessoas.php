<?php

namespace scraping;

require_once __DIR__ . '/../simplehtmldom_1_8_1/simple_html_dom.php';
require_once __DIR__ . '/../Controller/../scraping/Scraping.php';

class Achandopessoas implements Scraping
{
    private $nome;
    private $cidade;
    private $fonte;
    private $situacao;
    private $mais_caracteristcas;
    private $imagem;
    private $local_desaparecimento;
    private $dados_adicionais;
    private $cont;

    public function clearAttributes()
    {

        $this->imagem = null;
        $this->fonte = null;
        $this->situacao = null;
        $this->cidade = null;
        $this->nome = null;
        $this->local_desaparecimento = null;
        $this->dados_adicionais = null;
        $this->mais_caracteristicas = null;
    }

    public function scraping()
    {
        $this->cont = 0;
        for ($i = 1; $i <= 62; $i++) {
            $url = "http://achandopessoas.com.br/pessoas/page/$i/";
            $html = file_get_html($url);
            foreach ($html->find('div.image-fade_in_back a') as $people) {
                $this->clearAttributes();
                $data = array();
                $html_people = file_get_html($people->href);
                $this->fonte = $people->href;
                $this->situacao = "desaparecido";
                foreach ($people->find('img') as $foto) {
                    $this->imagem = $foto->src;
                }
                foreach ($html_people->find('h1') as $nome) {
                    $nome = explode('>', $nome);
                    $nome = explode('<', $nome[1]);
                    $nome = str_replace("\t", '', $nome[0]);
                    $tamanho = strlen($nome);
                    $tamanho = $tamanho - 1;
                    for ($k = 1; $k <= $tamanho; $k++) {
                        $j = $k - 1;
                        $nome[$j] = $nome[$k];
                    }
                    $this->nome = $nome;
                }
                $data = null;
                foreach ($html_people->find('div.product-short-description p') as $metadata) {
                    $informacao = explode('>', $metadata);
                    $tamanho = count($informacao);
                    for ($k = 1; $k < $tamanho - 1; $k++) {
                        $auxiliar = explode('<', $informacao[$k]);
                        $auxiliar1 = explode(':', $auxiliar[0]);
                        $auxiliar2 = str_replace(" ", '', $auxiliar1[0]);
                        if ($auxiliar2 == 'UltimaCidadequefoivisto') {
                            $this->cidade = $auxiliar1[1];
                            $this->local_desaparecimento = $auxiliar1[1];
                        } else if ($auxiliar2 == 'Descrição') {
                            $this->mais_caracteristicas = $auxiliar1[1];
                        } else {
                            $data .= " " . $auxiliar[0];
                        }
                    }
                    /*
                    $data['Descricao'] = $descricao[0];
                    $cidade = explode('<', $informacao[2]);
                    $cidade = explode(':', $cidade[0]);
                    $data['Cidade'] = $cidade[1];*/
                }
                $this->dados_adicionais = $data;
                $this->generateJson();
                $this->cont++;
            }
            echo("Terminada pagina: ");
            echo($i);
            echo("\n");
        }
    }

    public function generateJson($name = '')
    {
        $arr_json = array(
            'name' => 'achandopessoas',
            'attributes' => array(
                array('nome' => $this->nome),
                array('cidade' => $this->cidade),
                array('imagem' => $this->imagem),
                array('fonte' => $this->fonte),
                array('situacao' => $this->situacao),
                array('mais_caracteristicas' => $this->mais_caracteristcas),
                array('dados_adicionais' => $this->dados_adicionais),
                array('local_desaparecimento' => $this->local_desaparecimento),
            )
        );

        //format the data
        $formattedData = json_encode($arr_json);

        //set the filename
        $filename = 'achandopessoas_' . $this->cont . '.json';

        //open or create the file
        $handle = fopen(__DIR__ . '/../json/achandopessoas/' . $filename, 'w+');

        //write the data into the file
        fwrite($handle, $formattedData);

        //close the file
        fclose($handle);
    }
}