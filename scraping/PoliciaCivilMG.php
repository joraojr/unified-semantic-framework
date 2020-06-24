<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace scraping;

require_once __DIR__ . '/../simplehtmldom_1_8_1/simple_html_dom.php';
require_once __DIR__.'/../Controller/../scraping/Scraping.php';

class PoliciaCivilMG implements Scraping{

	private $nome;
	private $idade;
	private $sexo;
    private $data_nascimento;
	private $cidade;
    private $estado;
    private $imagem;
    private $local_desaparecimento;
    private $data_desaparecimento;
    private $fonte;
	private $situacao;
	private $idadeDesaparecimento;
	
	public function setCidadeEstado($local){
		$barra = strpos($local, '/');
		$this->cidade = substr($local, 0, $barra);
		$this->estado = substr($local, $barra + 1);
	}

	public function getIdade($idade){
		return substr($idade, 0, 2);
	}

	public function scraping(){

		$cont = 0;

		$urlBase ="https://desaparecidos.policiacivil.mg.gov.br";

        $html = file_get_html($urlBase."/desaparecido/album");
        
        foreach ($html->find('div.grid a') as $people) {
            
            $html_people = file_get_html($urlBase . $people->href);
            $data = array();

            $data['Fonte'] = $urlBase . $people->href;

            $srcImg = $html_people->find('div.col-md-4 div.col_esq a img', 0)->src;
            $data['Foto'] = $urlBase . $srcImg;
			
            foreach( $html_people->find('div.col-md-8 dl') as $dado){
				$titulos = $dado->find('dt');
				$infos = $dado->find('dd');
			}

			for($i = 0; $i <= array_key_last($titulos); $i++){
				$data[$titulos[$i]->plaintext] = $infos[$i]->plaintext;
			}
            
            $this->saveData($data);
            $name = $name = 'PoliciaCivilMG_'.$cont.'.json';
            $this->generateJson($name);
            $cont++;
            $this->clearAttributes();
        }
		
	}

	public function saveData($data){

		$this->fonte = $data["Fonte"];
		$this->imagem = $data["Foto"];
		$this->nome = $data["Nome do desaparecido:"];

		if(array_key_exists("Idade Atual",$data)){
			$this->idade = $this->getIdade($data["Idade Atual"]);
		}

		if(array_key_exists("Data de Nascimento",$data)){
			$this->data_nascimento = $data["Data de Nascimento"];
		}

		if(array_key_exists("Data do desaparecimento",$data)){
			$this->data_desaparecimento = $data["Data do desaparecimento"];
		}

		$this->setCidadeEstado($data["Município/Cidade do desaparecimento"]);
		
		if(array_key_exists("Idade na ocasião do desaparecimento", $data)){
			$this->idadeDesaparecimento = $data["Idade na ocasião do desaparecimento"]. " na ocasião.";
		}		
		
		$this->situacao = "Desaparecida";
		
	}

	// As some registers, data, doesnt have the same pattern or fields
	// this function is needed. Because when a previous register has an attribute and
	// the current one doesnt have, the previous attribute will be part of the current one
	public function clearAttributes(){

		$this->imagem = null;
		$this->fonte = null;
		$this->sexo = null;
		$this->data_nascimento = null;
		$this->data_desaparecimento = null;
		$this->local_desaparecimento = null;
		$this->idadeDesaparecimento = null;
		$this->situacao = null;
		$this->cidade = null;
		$this->estado = null;
		$this->nome = null;
		$this->idade = null;
	}

	public function generateJson($name)
    {
        $arr_json = array(
            'name' => 'PoliciaCivilMG',
            'attributes' => array(
                array('nome' => $this->nome),
				array('idade' => $this->idade),
				array('sexo' => $this->sexo),
				array('local_desaparecimento' => $this->local_desaparecimento),
				array('dt_nascimento' => $this->data_nascimento),
                array('dt_desaparecimento' => $this->data_desaparecimento),
                array('cidade' => $this->cidade),
                array('estado' => $this->estado),
                array('imagem' => $this->imagem),
                array('fonte' => $this->fonte),
                array('circunstancia_desaparecimento' => $this->idadeDesaparecimento),
                array('situacao' => $this->situacao),
            )
        );

        //format the data
        $formattedData = json_encode($arr_json);

        //set the filename
        $filename = $name;

        //open or create the file
        $handle = fopen( __DIR__ . '/../json/PoliciaCivilMG/'.$filename, 'w+');

        //write the data into the file
        fwrite($handle, $formattedData);

        //close the file
        fclose($handle);
    }
}