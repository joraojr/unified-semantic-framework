<?php

namespace scraping;

require_once __DIR__ .'/../simplehtmldom_1_8_1/simple_html_dom.php';
require_once __DIR__.'/../Controller/../scraping/Scraping.php';

class Disquedenuncia implements Scraping
{
    private $nome;
	private $idade;
	private $imagem;
	private $cidade;
    private $estado;
    private $data_desaparecimento;
    private $fonte;
    private $situacao;
    private $cont;

    public function clearAttributes(){

	    $this->imagem = null;
	    $this->fonte = null;
	    $this->data_desaparecimento = null;
	    $this->estado = null;
	    $this->nome = null;
        $this->idade = null;
        $this->cidade = null;
        $this->situacao = null;
    }

    public function dataToPerson($data)
    {
        $this->fonte = $data["Fonte"];
        $this->imagem = $data["Foto"];
        $this->nome = $data["Nome"];
        $this->idade = $data["Idade"];
        $this->data_desaparecimento = str_replace("\t",'', $data["Desaparecimento"]);
        $this->local_desaparecimento = $data["Bairro"];
        $this->cidade = $data["Cidade"];
        $this->estado = $data["Estado"];
        $this->situacao = "Desaparecida";
    }
    public function scraping()
    {
        $this->cont=0;
        for ($i = 1; $i <= 29; $i ++) {
            $url = "http://disquedenuncia.com/desaparecidos/page/$i/";
            $html = file_get_html($url);
            //echo($html);
            foreach($html->find('div.individuo a') as $people){
                $this->clearAttributes();
                $vezes=0;
                $data = array();
                $html_people = file_get_html($people->href);
                $data['Fonte'] = $people->href;
                foreach($html_people->find('div["desaparecido-single"] img') as $foto)
                {
                    $data['Foto'] = $foto->src;
                }
                foreach( $html_people->find('div["desaparecido-single-detalhes"] h3') as $nome)
                {
                    $nome= explode('>',$nome);
                    $nome= explode('<',$nome[1]);
                    $data['Nome'] = $nome[0];
                }

                foreach($html_people->find('div["desaparecido-single-detalhes"] p') as $metadata){
                    if($vezes>1 && $vezes<7)
                    {
                        $metadata = explode('>',$metadata);
                        $metadata = explode('<',$metadata[1]);
                        $metadata = explode(':', $metadata[0]);
                        $data[$metadata[0]] = $metadata[1];
                    }
                    $vezes++;
                }
                //var_dump($data);
                $this->dataToPerson($data);
                $this->generateJson();
                $this->cont++;
            }
            echo("Pagina ");
            echo($i);
            echo("terminada");
            echo '\n';
        }
    }

    public function generateJson($name='')
    {
        $arr_json = array(
            'name' => 'disquedenuncia',
            'attributes' => array(
                array('nome' => $this->nome),
				array('idade' => $this->idade),
                array('dt_desaparecimento' => $this->data_desaparecimento),
                array('cidade' => $this->cidade),
                array('estado' => $this->estado),
                array('imagem' => $this->imagem),
                array('fonte' => $this->fonte),
                array('situacao' => $this->situacao),
            )
        );

        //format the data
        $formattedData = json_encode($arr_json);

        //set the filename
        $filename = 'disquedenuncia_' . $this->cont . '.json';

        //open or create the file
        $handle = fopen( __DIR__ . '/../json/disquedenuncia/'.$filename, 'w+');

        //write the data into the file
        fwrite($handle, $formattedData);

        //close the file
        fclose($handle);
    }
}