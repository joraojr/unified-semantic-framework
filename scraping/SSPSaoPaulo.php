<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace scraping;

require_once __DIR__ . '/../simplehtmldom_1_8_1/simple_html_dom.php';
require_once __DIR__.'/../Controller/../scraping/Scraping.php';

require_once __DIR__.'/../scripts/vendor/autoload.php';

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use DOMDocument;
use DOMXPath;

class SSPSaoPaulo implements Scraping{

	private $nome;
    private $sexo;
	private $idade;
	private $altura;
	private $cor_olho;
	private $cor_cabelo;
	private $pele;
	private $imagem;
	private $cidade;
    private $estado;
    private $data_desaparecimento;
    private $data_nascimento;
    private $fonte;
    private $situacao;
    private $dados_adicionais;

    // Extrai o número da string
    public function getNumero($string){

		$tamanho = strlen($string);

		for ($i = 0; $i < $tamanho; $i++){
			if(is_numeric($string[$i]) && ($string[$i] != '0')){
				break;
			}	
		}

		$idade = substr($string, $i, 3);

		return $idade;

	}

    // Extrai somente as letras maiusculas da string
    public function getMaiusculas($string){

        $tamanho = strlen($string);

		for ($i = 0; $i < $tamanho; $i++){
			if(!preg_match("/[\.A-Z]/", $string[$i])){
				break;
			}	
		}
		$maiusculas = substr($string, 0, $i-1);

		return $maiusculas;
    }

	public function scraping(){

		// Definindo critérios para Selenium
		// start Chrome with 5 second timeout
		$host = 'http://localhost:4444/wd/hub'; // this is the default
		$capabilities = DesiredCapabilities::chrome();
		$driver = RemoteWebDriver::create($host, $capabilities, 5000);
		$url ="http://200.144.31.45/desaparecidos/";
		$driver->get($url);
		$driver->manage()->window()->setSize(new WebDriverDimension(1366, 768)); // É necessário aumentar o tamanho da janela pois o elemento de clique fica fora2
	
		// Espera pelo update no código fonte feito pelo Ajax
		$driver->wait(10,7500)->until(
			function () use ($driver) {
				$elements = $driver->findElements(WebDriverBy::xpath("//a[@class='NumeroPagina']"));
		
				return count($elements);
			},
			'Não foi possivel achar "PROXIMA"'
		);

		// Contador de registros
		$cont = 0;

		// Variavel para controle de paginação
        $proxima = $driver->findElement(WebDriverBy::id('proxima'));
        
        // Paginação
        while ($proxima) {

            // Pegando o DOM da página
			$dom = new DOMDocument();	
			$dom->loadHTML($driver->getPageSource());
            // Utilizando XPath para obter os elementos da página
            $xpath = new DOMXPath($dom);

            foreach($xpath->query("//div[@class='DivPanelDesaparecido']") as $registro){

                $data = array();

                // Os registros estão na mesma url
                $this->fonte = $url;

                $data['Imagem'] = $url.$registro->getElementsByTagName("img")[0]->getAttribute("src");

                // Pegando todos os dados de um registro
                $metadados = $registro->getElementsByTagName("span");

                // 0 Nome
                $data['Nome'] = $metadados[0]->nodeValue;
                // 1 Pai
                $data['Pai'] = $metadados[1]->nodeValue;
                // 3 Mae
                $data['Mae'] = $metadados[3]->nodeValue;
                // 5 Sexo
                $data['Sexo'] = $metadados[5]->nodeValue;
                // 7 Idade
                $data['Idade'] = explode(' ', $this->getNumero($metadados[7]->nodeValue))[0];
                // 9 Data Nascimento
                $data['DataNascimento'] = $metadados[9]->nodeValue;
                // 10 Data Desaparecimento
                $data['DataDesaparecimento'] = $metadados[10]->nodeValue;
                // 11 Naturalidade
                $data['Local'] = $metadados[11]->nodeValue;

                $data['Altura'] = $this->getNumero(explode(': ', $metadados[13]->nodeValue)[1]);
                $data['Olhos'] = $this->getMaiusculas(explode(': ', $metadados[15]->nodeValue)[1]);
                $data['Pele'] = $this->getMaiusculas(explode(': ', $metadados[16]->nodeValue)[1]);
                $data['Cabelo'] = explode(': ', $metadados[17]->nodeValue)[1];
                
                $this->saveData($data);
            
                $name = $name = 'SSPSaoPaulo'.$cont.'.json';
                $this->generateJson($name);
                $cont++;

            }

            $proxima = $driver->findElement(WebDriverBy::id('proxima'));
            $proxima->click();
            sleep(1);
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

        $this->imagem = $data["Imagem"];
        $this->nome = $data["Nome"];
        $this->idade = $data["Idade"];
        $this->data_nascimento = $data["DataNascimento"];
        $this->sexo = $data["Sexo"];
        $this->altura = $data["Altura"];
        $this->cor_olho = $data["Olhos"];
        $this->pele = $data["Pele"];
        $this->cor_cabelo = $data["Cabelo"];
		$this->data_desaparecimento = $data["DataDesaparecimento"];
                
        $bool = false;
        $bool_ = false;

        for($i=0;$i<strlen($data['Local']);$i++)
        {
            if($data['Local'][$i]=='-')
            {
                $bool = true;
                break;
            }
            else if($data['Local'][$i]=='/')
            {
                $bool_ = true;
                break;
            }
        }
        if($bool)
        {
            $local = explode('-',$data['Local']);
            $this->cidade = $local[0];
            $this->estado = $local[1];
        }
        else if($bool_)
        {
            $local = explode('/',$data['Local']);
            $this->cidade = $local[0];
            $this->estado = $local[1];
        }
        $this->situacao = "Desaparecida";
        $this->dados_adicionais = "Pai: " . $data['Pai'] . ", Mae: " . $data["Mae"];
		
	}

	// As some registers, data, doesnt have the same pattern or fields
	// this function is needed. Because when a previous register has an attribute and
	// the current one doesnt have, the previous attribute will be part of the current one
    public function clearAttributes(){

		$this->imagem = null;
		$this->fonte = null;
		$this->data_desaparecimento = null;
		$this->altura = null;
		$this->cor_olho = null;
		$this->cor_cabelo = null;
		$this->pele = null;
        $this->situacao = null;
        $this->cidade = null;
		$this->estado = null;
		$this->nome = null;
        $this->idade = null;
        $this->dados_adicionais = null;
        $this->sexo = null;
        $this->data_nascimento = null;
    }

	public function generateJson($name)
    {

        $arr_json = array(
            'name' => 'SSPSaoPaulo',
            'attributes' => array(
                array('nome' => $this->nome),
				array('idade' => $this->idade),
				array('altura' => $this->altura),
				array('cor_olho' => $this->cor_olho),
				array('cor_cabelo' => $this->cor_cabelo),
				array('pele' => $this->pele),
                array('dt_desaparecimento' => $this->data_desaparecimento),
                array('dt_nascimento' => $this->data_nascimento),
                array('cidade' => $this->cidade),
                array('estado' => $this->estado),
                array('imagem' => $this->imagem),
                array('fonte' => $this->fonte),
                array('situacao' => $this->situacao),
                array('dados_adicionais' => $this->dados_adicionais),
                array('sexo' => $this->sexo),
            )
        );

        //format the data
		$formattedData = json_encode($arr_json, JSON_UNESCAPED_UNICODE);
		
        //set the filename
        $filename = $name;

        //open or create the file
        $handle = fopen( __DIR__ . '/../json/SSPSaoPaulo/'.$filename, 'w+');

        //write the data into the file
        fwrite($handle, $formattedData);

        //close the file
        fclose($handle);
    }
}