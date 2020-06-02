<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace scraping;

require_once __DIR__ . '/../simplehtmldom_1_8_1/simple_html_dom.php';
require_once __DIR__.'/../Controller/../scraping/Scraping.php';

class PoliciaCivilAM implements Scraping{

	private $nome;
	private $idade;
	private $sexo;
	private $local_desaparecimento;
    private $dt_nascimento;
	private $imagem;
	private $cidade;
    private $estado;
    private $dt_desaparecimento;
    private $fonte;
    private $detalhes;
    private $situacao;

	public function scraping(){

		$cont = 0;

		$url ="http://www.policiacivil.am.gov.br/desaparecidos/pag/";

        for ($i = 1; $i <= 36; $i++) {

            $page = $url.$i;
			$html = file_get_html($page);
			
            foreach ($html->find('div.grid-item a') as $people) {

				$html_people = file_get_html($people->href);
				
                $data = array();

                $data["Foto"] = $html_people->find('div.img-identifica img', 0)->src;
                $data["Fonte"] = $people->href;
                $data["Nome"] = trim($html_people->find('div.post-text h3', 0)->plaintext);

				foreach ($html_people->find('div.post-text p') as $metadata) {

					$rotulo = $metadata->find('b', 0)->plaintext;
					if($rotulo == "")
						break;

					if(strpos($metadata->plaintext,':')){

						$doisPontos = strpos($metadata->plaintext, ':');
						$dado = substr($metadata->plaintext, $doisPontos + 1);

					}

					$data[$rotulo] = trim($dado);
				}

                $detalhes = "";

                foreach ($html_people->find('div.post-text p[style=text-align: justify;]') as $metadata) {

                    $detalhes.= trim(($metadata->plaintext));
				
				}

                $data["Detalhes:"] = $detalhes;
				
				$this->saveData($data);

				$name = $name = 'PoliciaCivilAM_'.$cont.'.json';
				$this->generateJson($name);
				$cont++;
				$this->clearAttributes();
			
            }
        }        		
	}

	// Função para transformar ISO 8859-1 para UTF-8
	public function utf8ize($d) {
		if (is_array($d)) {
			foreach ($d as $k => $v) {
				$d[$k] = $this->utf8ize($v);
			}
		} else if (is_string ($d)) {
			return utf8_encode($d);
		}
		return $d;
	}

	public function saveData($data){

		$data = $this->utf8ize($data);

		$this->imagem = $data["Foto"];
		$this->fonte = $data["Fonte"];
		
		if(array_key_exists("Data de Nascimento:",$data)){
			$this->dt_nascimento = $data["Data de Nascimento:"];
		}

		if(array_key_exists("Desaparecimento:",$data)){
			$this->data_desaparecimento = $data["Desaparecimento:"];		
		}		
		
		if(array_key_exists("Local:",$data)){
			$this->local_desaparecimento = $data["Local:"];
		}
		
		if(array_key_exists("Detalhes:", $data)){
			$this->detalhes = $data["Detalhes:"];
		}		
		
		$this->situacao = "Desaparecida";
		$this->estado = "AM";
		$this->nome = $data["Nome"];
		
	}

	// As some registers, data, doesnt have the same pattern or fields
	// this function is needed. Because when a previous register has an attribute and
	// the current one doesnt have, the previous attribute will be part of the current one
	public function clearAttributes(){

		$this->imagem = null;
		$this->fonte = null;
		$this->sexo = null;
		$this->dt_nascimento = null;
		$this->data_desaparecimento = null;
		$this->local_desaparecimento = null;
		$this->situacao = null;
		$this->estado = null;
		$this->nome = null;
		$this->idade = null;
		$this->detalhes = null;
	}

	public function generateJson($name)
    {

        $arr_json = array(
            'name' => 'PoliciaCivilAM',
            'attributes' => array(
                array('nome' => $this->nome),
				array('idade' => $this->idade),
				array('sexo' => $this->sexo),
				array('local_desaparecimento' => $this->local_desaparecimento),
				array('dt_nascimento' => $this->dt_nascimento),
                array('dt_desaparecimento' => $this->data_desaparecimento),
                array('cidade' => $this->cidade),
                array('estado' => $this->estado),
                array('imagem' => $this->imagem),
                array('fonte' => $this->fonte),
                array('circunstancia_desaparecimento' => $this->detalhes),
                array('situacao' => $this->situacao),
            )
        );

		// var_dump($arr_json);

        //format the data
		$formattedData = json_encode($arr_json, JSON_UNESCAPED_UNICODE);
		
        //set the filename
        $filename = $name;

        //open or create the file
        $handle = fopen( __DIR__ . '/../json/PoliciaCivilAM/'.$filename, 'w+');

        //write the data into the file
        fwrite($handle, $formattedData);

        //close the file
        fclose($handle);
    }
}