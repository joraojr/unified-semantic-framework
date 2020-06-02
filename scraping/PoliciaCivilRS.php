<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace scraping;

require_once __DIR__ . '/../simplehtmldom_1_8_1/simple_html_dom.php';
require_once __DIR__.'/../Controller/../scraping/Scraping.php';

class PoliciaCivilRS implements Scraping{

	private $nome;
	private $idade;
	private $sexo;
	private $local_desaparecimento;
    private $data_nascimento;
	private $imagem;
	private $cidade;
    private $estado;
    private $data_desaparecimento;
    private $fonte;
    private $circunstancia_desaparecimento;
    private $situacao;

	public function scraping(){

		$cont = 0;

        $url ="https://www.pc.rs.gov.br/desaparecidos";
        $urlRequest = "https://www.pc.rs.gov.br/_service/desaparecidos/listhtml?&pagina=";

        for($i=1 ; file_get_html($urlRequest.$i); $i ++){

            $html = file_get_html($urlRequest.$i);

            foreach ($html->find('div.card-desaparecido a') as $people) {

				$urlPeople = str_replace(" ","%20", html_entity_decode($people->href));
				
				$html_people = file_get_html($url .'/'. $urlPeople);
				$data = array();

                $data['Fonte'] = $url . $urlPeople;

				$data['Foto'] = $url.$html_people->find('div.card-desaparecido figure img', 0)->src;

				$data['Nome'] =  html_entity_decode($html_people->find('div.card-desaparecido-info h3', 0)->plaintext);
				
				foreach ($html_people->find('div.card-desaparecido-info p') as $metadata) {
					$d = trim($metadata->plaintext);
					if ($d === "")
						continue;
					$d = explode(':', $d);
					if(array_key_exists(1, $d)){
						$data[$d[0]] = $d[1];
					}else{
						$data[$d[0]] = null;
					}
				}
				
				$this->saveData($data);

				$name = $name = 'PoliciaCivilRS_'.$cont.'.json';
				$this->generateJson($name);
				$cont++;
				$this->clearAttributes();
			}
		}
	}

	public function saveData($data){

		$this->imagem = $data["Foto"];
		$this->fonte = $data["Fonte"];
		
		$this->data_nascimento = $data["Nascimento"];
		$this->data_desaparecimento = $data["Desaparecimento"];
		$this->local_desaparecimento = $data["Local"];		
		
		$this->cidade = $data["Local"];

		$this->situacao = "Desaparecida";
		$this->nome = $data["Nome"];

	}

	// As some registers, data, doesnt have the same pattern or fields
	// this function is needed. Because when a previous register has an attribute and
	// the current one doesnt have, the previous attribute will be part of the current one
	public function clearAttributes(){

		$this->imagem = null;
		$this->fonte = null;
		$this->data_nascimento = null;
		$this->data_desaparecimento = null;
		$this->local_desaparecimento = null;
		$this->cidade = null;
		$this->situacao = null;
		$this->nome = null;
	}

	public function generateJson($name)
    {
        $arr_json = array(
            'name' => 'PoliciaCivilRS',
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
                array('situacao' => $this->situacao),
            )
        );

        //format the data
        $formattedData = json_encode($arr_json);

        //set the filename
        $filename = $name;

        //open or create the file
        $handle = fopen( __DIR__ . '/../json/PoliciaCivilRS/'.$filename, 'w+');

        //write the data into the file
        fwrite($handle, $formattedData);

        //close the file
        fclose($handle);
    }
}