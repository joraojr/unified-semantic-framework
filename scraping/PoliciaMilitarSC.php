<?php

namespace scraping;

require_once __DIR__ . '/../simplehtmldom_1_8_1/simple_html_dom.php';

require_once __DIR__.'/../Controller/../scraping/Scraping.php';

class PoliciaMilitarSC implements Scraping
{
    private $nome;
    private $data_nascimento;
    private $imagem;
    private $cidade;
    private $estado;
    private $data_desaparecimento;
    private $fonte;
    private $circunstancia_desaparecimento;
    private $situacao;


    public function scraping()
    {
        $cont = 0;

        $urlBase = "https://www.pm.sc.gov.br/sos-desaparecidos/default/index?SosDesaparecidosSearch[sos_status_desaparecido_id]=";

        for($situacao = 1; $situacao <= 2; $situacao++){

            $pageLimit = 0;

            $urlSituacao = $urlBase . $situacao;

            if($situacao == 1){
                $pageLimit = 6;
            }else{
                $pageLimit = 7;
            }

            for ($i = 1; $i <= $pageLimit; $i++) {

                $htmlPage = file_get_html($urlSituacao. '&page=' . $i);
    
                // pega a tabela de desaparecidos da página
                $table = $htmlPage->getElementsByTagName('tbody');
                // $rowData = array(); // array para os dados da tabela
    
                foreach ($table->find('tr') as $row){
    
                    $register = array(); // array auxiliar para pegar o registro de desaparecido
    
                    foreach($row->find('td') as $cell){
    
                        if( $cell->find('img') ){ 
    
                            $img = $cell->find('img');
                            $register[] = $img[0]->src;
    
                        }else if( $cell->find('a[title="Ver detalhes"]') ){
    
                            $a = $cell->find('a[title="Ver detalhes"]');
                            $fonte = "https://www.pm.sc.gov.br".$a[0]->href;
                            $register[] = $fonte;
    
                            $desaparecidoPage = file_get_html("https://www.pm.sc.gov.br".$a[0]->href);
    
                            $idade = $desaparecidoPage->find('li')[31]->plaintext; //idade
    
                            $register[] = $idade;
    
                        }else{
                            $register[] = $cell->plaintext;
                        }
    
                    }
    
                    $this->saveRegisterDesaparecido($register);
    
                    $name = $name = 'PoliciaMilitarSC_'.$cont.'.json';
                    $this->generateJson($name);
                    $cont++;
    
                }
    
            }
        }
        echo "<h4>Scraping Realizado</h4>";
    }

    public function saveRegisterDesaparecido($registro){

        $this->imagem = $registro[0];
        $this->nome = $registro[1];

        if($registro[2] == 'Não informado'){
            $this->cidade = 'nao informado';
            $this->estado = 'nao informado';

        }else{
            $separador = strpos($registro[2], '/');
            $this->cidade = substr($registro[2], 0, $separador);
            $this->estado = substr($registro[2], $separador+1);
        }

        $this->data_desaparecimento = $registro[3];

        $this->situacao = $registro[4];

        $this->fonte = $registro[5];

        if(preg_match('~[0-9]+~', $registro[6])){

            $idade = substr($registro[6], 7, -5);
            $this->idade = $idade;
        }
    }

    public function getInformationDesaparecidos($dados)
    {
        $information = explode(":", $dados);

        if (strtolower($information[0]) == "nome") {
            $this->sexo = $information[1];
        }

        if (strtolower($information[0]) == "data de nascimento") {
            $this->data_nascimento = $information[1];
        }

        if (strtolower($information[0]) == "desaparecido em") {
            $this->data_desaparecimento = $information[1];
        }

        if (strtolower($information[0]) == "estado onde reside") {
            $this->estado = $information[1];
        }

        if (strtolower($information[0]) == "cidade onde reside") {
            $this->cidade = $information[1];
        }

        if (strtolower($information[0]) == "descrição dos fatos") {
            $this->circunstancia_desaparecimento = $information[1];
        }
    }

    public function generateJson($name)
    {
        $arr_json = array(
            'name' => 'PoliciaMilitarSC',
            'attributes' => array(
                array('nome' => $this->nome),
                array('dt_nascimento' => $this->data_nascimento),
                array('dt_desaparecimento' => $this->data_desaparecimento),
                array('cidade' => $this->cidade),
                array('estado' => $this->estado),
                array('imagem' => $this->imagem),
                array('fonte' => $this->fonte),
                array('circunstancia_desaparecimento' => $this->circunstancia_desaparecimento),
                array('situacao' => $this->situacao),
            )
        );

        //format the data
        $formattedData = json_encode($arr_json);

        //set the filename
        $filename = $name;

        //open or create the file
        $handle = fopen( __DIR__ . '/../json/PoliciaMilitarSC/'.$filename, 'w+');

        //write the data into the file
        fwrite($handle, $formattedData);

        //close the file
        fclose($handle);
    }
}