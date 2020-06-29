<?php

namespace scraping;

require_once __DIR__ .'/../simplehtmldom_1_8_1/simple_html_dom.php';
require_once __DIR__.'/../Controller/../scraping/Scraping.php';

class Desaparecidos_PR implements Scraping
{
    private $nome;
	private $idade;
	private $local_desaparecimento;
	private $altura;
	private $peso;
	private $cor_olho;
	private $cor_cabelo;
	private $pele;
	private $imagem;
	private $cidade;
    private $estado;
    private $data_desaparecimento;
    private $fonte;
    private $circunstancia_desaparecimento;
    private $situacao;
    private $mais_caracteristcas;
    private $dados_adicionais;
    private $cont;
    private $boletim_ocorrencia;

    public function getPage($id,$bool){
        date_default_timezone_set('America/Sao_Paulo');
            ini_set("display_errors", 1);
            error_reporting(E_ALL);
            $postData = array(
                'codigoDesaparecido' => '',
                'menor' => "$bool",
                'nome' => '',
                'municipio' => '',
                'ordenacao' => 3,
                'button' => 'Consultar',
                '_' => ''
                );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.desaparecidos.pr.gov.br/desaparecidos/desaparecidos.do?action=iniciarProcesso&indice=$id&m=$bool"); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $page = curl_exec($ch); 
            curl_close($ch);
            return $page;
    }


    //http://www.desaparecidos.pr.gov.br/desaparecidos/desaparecidos.do?action=iniciarProcesso&m=false

    public function getStr($begin, $end, $x)
    {
        $str ="";
        for($i = 0 ; $i < count_chars($x) ; $i ++){
            if($x[$i] == $begin){
                $i++;
                while($x[$i] != $end){
                    $str.= $x[$i];
                    $i++;
                }
                break;
            }
        }
        return $str;
    }
    public function dataToPerson($data){

    // Tem mais dado pra colher !!

        foreach (array_keys($data) as $key)
        if(strpos($key,"B.O.U:") == true)
            $data["B.O.U:"] = $data[$key];

        $this->imagem = $data["Foto"];
        $this->fonte = $data["Fonte"];
        $this->nome = $data["Nome Completo:"];
        $this->data_desaparecimento = $data["Data (desaparecimento):"];
        $this->idade = $data["Idade (atual):"];
        $auxiliar = explode('-',$data["Local do desaparecimento:"]);
        $this->local_desaparecimento = $auxiliar[0] . "-" . $auxiliar[1];
        $auxiliar = explode("/",$auxiliar[2]);
        $this->cidade = $auxiliar[0];
        $this->cor_cabelo = $data["Cor dos cabelos:"];
        $this->cor_olho = $data["Olhos:"];
        $this->altura =$data["Altura Estimada:"];
        $this->peso =$data["Peso Estimado:"];
        $this->mais_caracteristicas = "Barba: " . $data["Barba::"] . ", Deficiencia fisica: " . $data["Defici&ecirc;ncia F&iacute;sica:"] .
            ", Trajava na ocasiao: " . $data["Trajava na ocasi&atilde;o:"] . ", Tipo do cabelo: " . $data["Tipo dos Cabelos:"];
        $this->dados_adicionais = "Informacoes: ". $data["Informa&ccedil;&otilde;es:"]
            . ", Idade que tinha quando desapareceu: " . $data["Idade (desaparecimento):"];
        $this->pele = $data["C&uacute;tis:"];
        $this->estado = "PR";
        $this->situacao = "Desaparecida";
        $this->boletim_ocorrencia = $data['Número B.O.U:'] ;

        //var_dump($p);

        //montar caraceteristicas e dados adicionais
    }

    public function clearAttributes(){

		$this->imagem = null;
		$this->fonte = null;
		$this->data_desaparecimento = null;
		$this->local_desaparecimento = null;
		$this->altura = null;
		$this->peso = null;
		$this->cor_olho = null;
		$this->cor_cabelo = null;
		$this->pele = null;
        $this->situacao = null;
        $this->cidade = null;
		$this->estado = null;
		$this->nome = null;
        $this->idade = null;
        $this->mais_caracteristicas = null;
        $this->dados_adicionais = null;
        $this->boletim_ocorrencia = null;
	}


    public function scraping()
    {
        $this->cont=0;
        $this->clearAttributes();
        for($i = 1; $i <= 3 ; $i ++){
            $html = str_get_html($this->getPage($i,"true"));
            foreach ( $html->find('tr[onmouseover] div a[onclick]') as $id) {
                $this->clearAttributes();
                gettype($id->onclick);
                $source = 'http://www.desaparecidos.pr.gov.br/desaparecidos/desaparecidos.do?action=detalhesDesaparecido&c='. $this->getStr('(',')',$id->onclick);
                $html_people = file_get_html($source);
                $data = array();
                $img = $html_people->find('span.form_value img', 0)->src;
                $img = str_replace((';'. $this->getStr(";","?",$img)),"", $img);
                $data["Foto"] = "http://www.desaparecidos.pr.gov.br".$img;
                $data["Fonte"] = $source;
                foreach ($html_people->find('table.form_tabela') as $metadata) {
                    foreach($metadata->find('tr') as $tr){
                        $j=0;
                        $k=0;
                        foreach($tr->find('td.form_label') as $td_label){
                            $td_label = explode('>',$td_label);
                            $td_label = $td_label[1];
                            $td_label = explode('<',$td_label);
                            $auxiliar1[$j] = $td_label[0];
                            $j++;
                        }   
                        foreach($tr->find('td.form_value') as $td_value){
                            $td_value = explode('>',$td_value);
                            $td_value = $td_value[1];
                            $td_value = explode('<',$td_value);
                            $data[$auxiliar1[$k]] = $td_value[0];
                            if($td_value[0]==NULL)
                                $data[$auxiliar1[$k]] = "SEM INFORMACAO";
                            $k++;

                        }
                    }
                }
                $this->dataToPerson($data);
                $this->generateJson();
                $this->cont++; 
            }    
            
        }

        for($i = 1; $i <= 133 ; $i ++){
            $html = str_get_html($this->getPage($i,"false"));
            foreach ( $html->find('tr[onmouseover] div a[onclick]') as $id) {
                $this->clearAttributes();
                $source = 'http://www.desaparecidos.pr.gov.br/desaparecidos/desaparecidos.do?action=detalhesDesaparecido&c='. $this->getStr('(',')',$id->onclick);
                $html_people = file_get_html($source);
                $data = array();
                $img = $html_people->find('span.form_value img', 0)->src;
                $img = str_replace((';'. $this->getStr(";","?",$img)),"", $img);
                $data["Foto"] = "http://www.desaparecidos.pr.gov.br".$img;
                $data["Fonte"] = $source;
                foreach ($html_people->find('table.form_tabela') as $metadata) {
                    foreach($metadata->find('tr') as $tr){
                        $j=0;
                        $k=0;
                        foreach($tr->find('td.form_label') as $td_label){
                            $td_label = explode('>',$td_label);
                            $td_label = $td_label[1];
                            $td_label = explode('<',$td_label);
                            $auxiliar1[$j] = $td_label[0];
                            $j++;
                        }   
                        foreach($tr->find('td.form_value') as $td_value){
                            $td_value = explode('>',$td_value);
                            $td_value = $td_value[1];
                            $td_value = explode('<',$td_value);
                            $data[$auxiliar1[$k]] = $td_value[0];
                            if($td_value[0]==NULL)
                                $data[$auxiliar1[$k]] = "SEM INFORMACAO";
                            $k++;

                        }
                    }
                }
                $this->dataToPerson($data);
                $this->generateJson();
                $this->cont++;
            }
        }
    }

    public function generateJson($name='')
    {
        $arr_json = array(
            'name' => 'Desaparecidos_PR',
            'attributes' => array(
                array('nome' => $this->nome),
				array('idade' => $this->idade),
				array('local_desaparecimento' => $this->local_desaparecimento),
				array('altura' => $this->altura),
				array('peso' => $this->peso),
				array('cor_olho' => $this->cor_olho),
				array('cor_cabelo' => $this->cor_cabelo),
				array('pele' => $this->pele),
                array('dt_desaparecimento' => $this->data_desaparecimento),
                array('cidade' => $this->cidade),
                array('estado' => $this->estado),
                array('imagem' => $this->imagem),
                array('fonte' => $this->fonte),
                array('situacao' => $this->situacao),
                array('boletimDeOcorrecia' => $this->boletim_ocorrencia),
                array('dados_adicionais' => $this->dados_adicionais),
                array('mais_caracteristicas' => $this->mais_caracteristcas),
            )
        );

        //format the data
        $formattedData = json_encode($arr_json);

        //set the filename
        $filename = 'Desaparecidos_PR_' . $this->cont . '.json';

        //open or create the file
        $handle = fopen( __DIR__ . '/../json/Desaparecidos_PR/'.$filename, 'w+');

        //write the data into the file
        fwrite($handle, $formattedData);

        //close the file
        fclose($handle);
    }
}