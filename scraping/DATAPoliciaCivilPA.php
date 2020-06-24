<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace scraping;

require_once __DIR__ . '/../simplehtmldom_1_8_1/simple_html_dom.php';
require_once __DIR__.'/../Controller/../scraping/Scraping.php';

class DATAPoliciaCivilPA implements Scraping{

	private $nome;
	private $idade;
	private $sexo;
	private $local_desaparecimento;
	private $altura;
	private $peso;
	private $cor_olho;
	private $cor_cabelo;
	private $cor_pele;
    private $data_nascimento;
	private $imagem;
	private $cidade;
    private $estado;
    private $data_desaparecimento;
    private $fonte;
    private $circunstancia_desaparecimento;
    private $situacao;

	public function getIdade($string){

		$tamanho = strlen($string);

		for ($i = 0; $i < $tamanho; $i++){
			if(is_numeric($string[$i]) && ($string[$i] != '0')){
				break;
			}	
		}

		$idade = substr($string, $i, 2);

		return $idade;

	}

	public function scraping(){

		$cont = 0;

		$url ="http://data.policiacivil.pa.gov.br";

		for($i=0 ; $i <= 5; $i ++){

			$html = file_get_html("http://data.policiacivil.pa.gov.br/?q=listadesaparecidos&page=$i");
			
			foreach ($html->find('table tbody tr td div span a') as $people) {
				
				$html_people = file_get_html($url . $people->href);
				$data = array();

				$data['Fonte'] = $url . $people->href;
				$data['Foto'] = $html_people->find('div.field-items a img', 0)->src;
				
				$nomeBruto =  $html_people->find('h1.title', 0)->plaintext;
				$data["Idade"] = 'Não informado';

				if(preg_match('~[0-9]+~', $nomeBruto)){
					if(strpos($nomeBruto,',')){
						$virgula = strpos($nomeBruto, ',');
						$nome = substr($nomeBruto, 0, $virgula);
					}else{
						$hifen = strpos($nomeBruto, '-');
						$nome = substr($nomeBruto, 0, $hifen);
					}
					$data["Idade"] = $this->getIdade($nomeBruto);
				}else{
					$nome = $nomeBruto;
				}

				$data['Nome'] = $nome;
				
				foreach ($html_people->find('div.field-item') as $metadata) {
					$d = trim($metadata->plaintext);
					if ($d === "")
						continue;
					$d = explode('&nbsp;', $d);
					if (count($d) == 1)
						$data['Circunstância do desaparecimento:'] = $d[0];
					else
						$data[$d[0]] = $d[1];
				}
				
				$this->saveData($data);

				$name = $name = 'DATAPoliciaCivilPA_'.$cont.'.json';
				$this->generateJson($name);
				$cont++;
				$this->clearAttributes();
			}
		}
	}

	public function saveData($data){

		$this->imagem = $data["Foto"];
		$this->fonte = $data["Fonte"];
		$this->sexo = $data["Sexo:"];
		
		if(array_key_exists("Data de nascimento:",$data)){
			$this->data_nascimento = $data["Data de nascimento:"];
		}

		$this->data_desaparecimento = $data["Data do desaparecimento:"];
		
		$this->local_desaparecimento = $data["Local do desaparecimento:"];
		
		if(array_key_exists("Altura:", $data)){
			$this->altura = $data["Altura:"];
		}
		if(array_key_exists("Peso:", $data)){
			$this->peso = $data["Peso:"];
		}
		if(array_key_exists("Cor do olhos:", $data)){
			$this->cor_olho = $data["Cor do olhos:"];
		}
		if(array_key_exists("Cor do cabelo:", $data)){
			$this->cor_cabelo = $data["Cor do cabelo:"];
		}
		if(array_key_exists("Raça:", $data)){
			$this->cor_pele = $data["Raça:"];
		}
		if(array_key_exists("Circunstância do desaparecimento:", $data)){
			$this->circunstancia_desaparecimento = $data["Circunstância do desaparecimento:"];
		}		
		
		$this->situacao = "Desaparecida";
		$this->estado = "PA";
		$this->nome = $data["Nome"];
		$this->idade = $data["Idade"];

		if(array_key_exists("Nome do Contato:", $data)){
			$this->dados_adicionais = "Nome do Contato: " . trim($data["Nome do Contato:"]) . " Telefone para contato: ".
			trim($data["Telefone para contato:"]) . " E-mail para contato: ". trim($data["E-mail para contato:"]) ;
		}
		
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
		$this->altura = null;
		$this->peso = null;
		$this->cor_olho = null;
		$this->cor_cabelo = null;
		$this->cor_pele = null;
		$this->circunstancia_desaparecimento = null;
		$this->situacao = null;
		$this->estado = null;
		$this->nome = null;
		$this->idade = null;
		$this->dados_adicionais = null;
	}

	public function generateJson($name)
    {
        $arr_json = array(
            'name' => 'DATAPoliciaCivilPA',
            'attributes' => array(
                array('nome' => $this->nome),
				array('idade' => $this->idade),
				array('sexo' => $this->sexo),
				array('local_desaparecimento' => $this->local_desaparecimento),
				array('altura' => $this->altura),
				array('peso' => $this->peso),
				array('cor_olho' => $this->cor_olho),
				array('cor_cabelo' => $this->cor_cabelo),
				array('pele' => $this->cor_pele),
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
        $handle = fopen( __DIR__ . '/../json/DATAPoliciaCivilPA/'.$filename, 'w+');

        //write the data into the file
        fwrite($handle, $formattedData);

        //close the file
        fclose($handle);
    }
}