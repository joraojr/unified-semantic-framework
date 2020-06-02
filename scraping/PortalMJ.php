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

class PortalMJ implements Scraping{

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
    private $data_localizacao;

    public function sendPeople($driver, $place){

        $this->cidade = $this->local_desaparecimento = $place;
        $this->estado = $driver->findElement(WebDriverBy::xpath("//span[@id='lblEstado']"))->getText();
        $driver->switchTo()->window($driver->getWindowHandles()[1]);
        $this->fonte = $driver->getCurrentURL();
        $this->imagem = $driver->findElement(WebDriverBy::xpath("//img"))->getAttribute("src");
        $this->nome = $driver->findElement(WebDriverBy::xpath("//span[@id='lblNome']"))->getText();
        $this->data_nascimento = $driver->findElement(WebDriverBy::xpath("//span[@id='lblDataNascimento']"))->getText();
        $this->data_desaparecimento = $driver->findElement(WebDriverBy::xpath("//span[@id='lblDataDesaparecimento']"))->getText();
        $this->altura = $driver->findElement(WebDriverBy::xpath("//span[@id='lblAltura']"))->getText();
        $this->peso = $driver->findElement(WebDriverBy::xpath("//span[@id='lblPeso']"))->getText();
        $this->cor_olho = $driver->findElement(WebDriverBy::xpath("//span[@id='lblCorOlhos']"))->getText();
        $this->cor_cabelo = $driver->findElement(WebDriverBy::xpath("//span[@id='lblCorOlhos']"))->getText();
        $this->pele = $driver->findElement(WebDriverBy::xpath("//span[@id='lblRaca']"))->getText();
        $this->circunstancia_desaparecimento = $driver->findElement(WebDriverBy::xpath("//span[@id='lblObservacao']"))->getText();
        $this->data_localizacao = $driver->findElement(WebDriverBy::xpath("//span[@id='lblDataLocalizacao']"))->getText();
        $this->dados_adicionais = "Contato: Nome: " . $driver->findElement(WebDriverBy::xpath("//span[@id='lblOrgao']"))->getText() .
            " Telefone: " . $driver->findElement(WebDriverBy::xpath("//span[@id='lblOrgao']"))->getText() .
            " E-mail: " . $driver->findElement(WebDriverBy::xpath("//span[@id='lblEmail']"))->getText();
    
        switch ($driver->findElement(WebDriverBy::xpath("//span[@id='lblSexo']"))->getText()) {
            case 'F':
                $this->sexo = "Feminino";
                break;
            case 'M':
                $this->sexo = "Masculino";
                break;
            default:
                break;
        };
        
        $driver->switchTo()->window($driver->getWindowHandles()[0]);
    
    }

	public function scraping(){

        // start Chrome with 5 second timeout
        $host = 'http://localhost:4444/wd/hub'; // this is the default
        $capabilities = DesiredCapabilities::chrome();
        $driver = RemoteWebDriver::create($host, $capabilities, 5000);

        $cont = 0;

        //$estates = array("AC", "AL", "AM", "AP", "BA", "CE", "DF", "ES", "GO", "MA", "MT", "MS", "MG", "PA", "PB", "PR", "PE", "PI", "RJ", "RN", "RO", "RS", "RR", "SC", "SE", "SP", "TO");
        $estates = array("SE", "SP", "TO");

        foreach ($estates as $uf) {

            $driver->get("http://portal.mj.gov.br/Desaparecidos/frmListaDesaparecidos.aspx?uf=" . strtolower($uf));

            $pages = $driver->findElements(WebDriverBy::xpath("//a[contains(@href,'doPostBack')]"));
            $x = count($pages);

            $link = $driver->findElements(WebDriverBy::xpath("//span[contains(@id, 'Label2')]"));
            $places = $driver->findElements(WebDriverBy::xpath("//span[contains(@id, 'Label9')]"));
            $k = 0;
            foreach ($link as $l) {
                $l->click();
                $this->sendPeople($driver, $places[$k]->getText());

                $name = $name = 'PortalMJ_'.$cont.'.json';
                $this->generateJson($name);
                $cont++;
                $this->clearAttributes();

                $k++;
            }

            for ($i = 0; $i < $x; $i++) {
                print_r($i);
                if ($i == ($x - 1) && $pages[$i]->getText() == "...") {
                    $pages[$i]->click();
                    $pages = $driver->findElements(WebDriverBy::xpath("//a[contains(@href,'doPostBack')]"));
                    $i = $x - intval($pages[count($pages) - 1]->getText()) + count($pages) + 1;
                } else {
                    $pages[$i]->click();
                    $pages = $driver->findElements(WebDriverBy::xpath("//a[contains(@href,'doPostBack')]"));

                }
                $link = $driver->findElements(WebDriverBy::xpath("//span[contains(@id, 'Label2')]"));
                $places = $driver->findElements(WebDriverBy::xpath("//span[contains(@id, 'Label9')]"));
                $k = 0;
                foreach ($link as $l) {
                    $l->click();
                    $this->sendPeople($driver, $places[$k]->getText());

                    $name = $name = 'PortalMJ_'.$cont.'.json';
                    $this->generateJson($name);
                    $cont++;
                    $this->clearAttributes();

                    $k++;
                }
            
            }

        }

        $driver->quit();

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
            'name' => 'PortalMJ',
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
        $handle = fopen( __DIR__ . '/../json/PortalMJ/'.$filename, 'w+');

        //write the data into the file
        fwrite($handle, $formattedData);

        //close the file
        fclose($handle);
    }
}