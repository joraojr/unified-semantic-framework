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
use DOMDocument;
use DOMXPath;

class PoliciaCivilSC implements Scraping{

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

    // Separa o local em três informações:  bairro; cidade e estado
    public function separarLocal($string){

		return explode(' - ', $string);
    }

    public function acessarDesaparecidosSemFotos($driver){

        $filtrosToggler = $driver->findElement(WebDriverBy::xpath("//a[@id='form:filtros_toggler']"));
        $filtrosToggler->click();

        // $checkboxFotos = $driver->findElement(WebDriverBy::xpath("//div[@class='ui-chkbox-box ui-widget ui-corner-all ui-state-default ui-state-active']"));
        $checkboxFotos = $driver->findElement(WebDriverBy::xpath("//div[@id='form:comfotos']"));
        $checkboxFotos->click();

        sleep(1);

        $botaoFiltrar = $driver->findElements(WebDriverBy::xpath("//span[@class='ui-button-text ui-c']"));
        $botaoFiltrar[3]->click();

        $driver->navigate()->refresh();
    }

	public function scraping(){

		// Definindo critérios para Selenium
		// start Chrome with 5 second timeout
		$host = 'http://localhost:4444/wd/hub'; // this is the default
		$capabilities = DesiredCapabilities::chrome();
		$driver = RemoteWebDriver::create($host, $capabilities, 5000);
		$url = "http://desaparecidos.pc.sc.gov.br/desaparecidosSite/";
		$driver->get($url);
    
        $this->acessarDesaparecidosSemFotos($driver);

		// Contador de registros
		$cont = 0;

		// Variavel para controle de paginação
        $proxima = $driver->findElement(WebDriverBy::xpath("//span[@class='ui-paginator-next ui-state-default ui-corner-all']"));
        
        // Paginação
        while ($proxima) {

            foreach($driver->findElements(WebDriverBy::xpath("//a[@style='color:red;font-size: 12px;font-weight: bold;']")) as $linkPessoa){

                $linkPessoa->click();
                sleep(1);

                // Pegando o DOM da página
		    	$dom = new DOMDocument();	
                $dom->loadHTML($driver->getPageSource());
                // Utilizando XPath para obter os elementos da página
                $xpath = new DOMXPath($dom);

                $data = array();

                $metadados = $xpath->query("//div[@id='form:j_idt69_content']")[0]->getElementsByTagName("tr");
                
                // ! 0 Idade
                $data['Idade'] = explode(':', $metadados[0]->nodeValue)[1];
                // ! 1 Local no formato: 0 Bairro - 1 Cidade - 2 Estado
                $local = $this->separarLocal(explode(':', $metadados[1]->nodeValue)[1]);
                $data['Cidade'] = $local[1];
                $data['Estado'] = $local[2];
                // ! 2 Nome
                $data['Nome'] = explode(':', $metadados[2]->nodeValue)[1];
                // ! 3 Nome da Mãe
                $data['Mae'] = explode(':', $metadados[3]->nodeValue)[1];
                // ! 4 Ano Nascimento
                $data['AnoNascimento'] = explode(':', $metadados[4]->nodeValue)[1];
                // ! 5 Data desaparecimento
                $data['DataDesaparecimento'] = explode(':', $metadados[5]->nodeValue)[1];
                // ! 6 Telefone para contato
                $data['Contato'] = explode(':', $metadados[6]->nodeValue)[1];

                $this->saveData($data);
            
                $name = $name = 'PoliciaCivilSC'.$cont.'.json';
                $this->generateJson($name);
                $cont++;

                // Delay para poder fechar o popup
                sleep(1);
                $fecharPopUp = $driver->findElements(WebDriverBy::xpath("//span[@class='ui-icon ui-icon-closethick']"));
                $fecharPopUp[1]->click();

            }

            $proxima->click();
            sleep(1);

        }

	}	

	public function saveData($data){
        
        // Os registros não possuem paginas especificas
        $this->fonte = "http://desaparecidos.pc.sc.gov.br/desaparecidosSite/";

        // $this->imagem = $data["Imagem"];
        $this->nome = $data["Nome"];
        $this->idade = $data["Idade"];

        //É possível extrair o ano de nascimento mas não o dia e mês, portanto irei colocar como 00/00/ANO
        $this->data_nascimento = "00/00/".$data["AnoNascimento"];
		$this->data_desaparecimento = $data["DataDesaparecimento"];

        $this->cidade = $data["Cidade"];
        $this->estado = $data["Estado"];

        $this->situacao = "Desaparecida";
        $this->dados_adicionais = "Mae: " . $data["Mae"] . " Telefone para contato: " . $data["Contato"];
		
	}

	// As some registers, data, doesnt have the same pattern or fields
	// this function is needed. Because when a previous register has an attribute and
	// the current one doesnt have, the previous attribute will be part of the current one
    public function clearAttributes(){

		$this->imagem = null;
		$this->fonte = null;
		$this->data_desaparecimento = null;
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
            'name' => 'PoliciaCivilSC',
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
        $handle = fopen( __DIR__ . '/../json/PoliciaCivilSC/'.$filename, 'w+');

        //write the data into the file
        fwrite($handle, $formattedData);

        //close the file
        fclose($handle);
    }
}