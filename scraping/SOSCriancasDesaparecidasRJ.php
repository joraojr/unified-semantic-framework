<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace scraping;

require_once __DIR__ . '/../simplehtmldom_1_8_1/simple_html_dom.php';
require_once __DIR__.'/../Controller/../scraping/Scraping.php';

class SOSCriancasDesaparecidasRJ implements Scraping{

	private $nome;
	private $idade;
	private $sexo;
	private $cor_olho;
    private $cor_cabelo;
	private $cor_pele;
    private $data_nascimento;
	private $imagem;
    private $estado;
    private $data_desaparecimento;
    private $fonte;
    private $situacao;
    private $descricao_fisica;
    private $dados_adicionais;

    // Função para a requisição HTTP para POST
    public function getPage($id,$type) {
        date_default_timezone_set('America/Sao_Paulo');
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
        $headers = array(
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Accept-Encoding' =>'gzip, deflate',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'Connection' => 'keep-alive',
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'Host' => 'www.soscriancasdesaparecidas.rj.gov.br',
            'Origin' => 'http://www.soscriancasdesaparecidas.rj.gov.br',
            'Referer' => 'http://www.soscriancasdesaparecidas.rj.gov.br/consulta_publica/consulta_publica.php',
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36',
            'X-Requested-With' => 'XMLHttpRequest'
        );
        if($type == 1) {
            $postData = array(
                'nome' => "",
                'idadeInicial' => "",
                'idadeFinal' => "",
                'sexo' => "",
                'pele' => "",
                'corOlhos' => "",
                'tipoCabelo' => "",
                'corCabelo' => "",
                'paginaAtual' => "$id",
                'situacao' => "1",//desaparecido
                'ordem' => "1"
            );
    
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://www.soscriancasdesaparecidas.rj.gov.br/consulta_publica/corpo_consulta_publica.php");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }else{
            $idnum = "?idnum=$id";
    
            $ch = curl_init();
    
            curl_setopt($ch, CURLOPT_URL, 'http://www.soscriancasdesaparecidas.rj.gov.br/consulta_publica/dados_consultapublica.php' . $idnum);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        }
    
        $page = curl_exec($ch);
    
        curl_close($ch);
    
        return $page;
    }	

	public function scraping(){

        $cont = 0;

        $total = json_decode($this->getPage(1,1))->total;

        for ($i=1; $i <= ceil($total/12); $i++){

            $json = json_decode($this->getPage($i,1))->dados;

            foreach ($json as $people){

                $html = str_get_html($this->getPage($people->IDNUM,2));
                $data = trim($html->find('div.col-lg-6',0)->plaintext);
                $data = array_filter(explode("  			",$data));

                $data = str_replace("\r", '', $data);
                $data = str_replace("\t", '', $data);
                $data = str_replace("\n", '', $data);

                $img = $html->find('p[align=center] img[oncontextmenu=return false]',0)->src;

				$this->saveData($people->IDNUM, $img, $data);

				$name = $name = 'SOSCriancasDesaparecidasRJ_'.$cont.'.json';
				$this->generateJson($name);
				$cont++;
				$this->clearAttributes();             

            }
        }
	}

	public function saveData($idnum, $img, $data){

        $this->fonte = "http://www.soscriancasdesaparecidas.rj.gov.br/consulta_publica/dados_consultapublica.php?idnum=$idnum";
        $this->imagem = "http://www.soscriancasdesaparecidas.rj.gov.br/". $img;
        $this->nome = str_replace('&nbsp;              ',"",$data[0]);
        $this->dados_adicionais = str_replace('</br>',"",$data[1]." ".$data[2]." ".$data[3]);
        $this->data_nascimento = str_replace('</br>',"",str_replace('Data de Nascimento: ',"",$data[4]));
        $this->data_desaparecimento = str_replace('</br>',"",str_replace('Data de Desaparecimento: ',"",$data[5]));
        $this->cor_pele = str_replace('</br>',"",str_replace('Cor da Pele:',"",$data[9]));
        $this->cor_cabelo = str_replace('</br>',"",str_replace('Cor do Cabelo:',"",$data[11]));
        $this->cor_olho = str_replace('</br>',"",str_replace('Cor dos Olhos:',"",$data[12]));
        $this->descricao_fisica = str_replace('</br>',"",$data[14]);
        $this->estado = "rj";
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
		$this->cor_olho = null;
		$this->cor_cabelo = null;
		$this->cor_pele = null;
		$this->situacao = null;
		$this->estado = null;
		$this->nome = null;
        $this->idade = null;
        $this->descricao_fisica = null;
		$this->dados_adicionais = null;
	}

	public function generateJson($name)
    {
        $arr_json = array(
            'name' => 'SOSCriancasDesaparecidasRJ',
            'attributes' => array(
                array('nome' => $this->nome),
				array('idade' => $this->idade),
				array('sexo' => $this->sexo),
				array('cor_olho' => $this->cor_olho),
				array('cor_cabelo' => $this->cor_cabelo),
				array('pele' => $this->cor_pele),
				array('dt_nascimento' => $this->data_nascimento),
                array('dt_desaparecimento' => $this->data_desaparecimento),
                array('estado' => $this->estado),
                array('imagem' => $this->imagem),
                array('fonte' => $this->fonte),
                array('dados_adicionais' => $this->dados_adicionais),
                array('mais_caracteristicas' => $this->descricao_fisica),
                array('situacao' => $this->situacao),
                
            )
        );

        //format the data
        $formattedData = json_encode($arr_json);

        //set the filename
        $filename = $name;

        //open or create the file
        $handle = fopen( __DIR__ . '/../json/SOSCriancasDesaparecidasRJ/'.$filename, 'w+');

        //write the data into the file
        fwrite($handle, $formattedData);

        //close the file
        fclose($handle);
    }
}