<?php

namespace scraping;

require_once __DIR__ .'/../simplehtmldom_1_8_1/simple_html_dom.php';
require_once __DIR__.'/../Controller/../scraping/Scraping.php';

class Desaparecidosdobrasil implements Scraping
{
    private $nome;
	private $idade;
	private $local_desaparecimento;
    private $dt_nascimento;
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

    public function clearAttributes(){

	    $this->imagem = null;
	    $this->fonte = null;
	    $this->dt_nascimento = null;
	    $this->data_desaparecimento = null;
	    $this->local_desaparecimento = null;
	    $this->circunstancia_desaparecimento = null;
        $this->situacao = null;
        $this->estado = null;
        $this->cidade = null;
	    $this->nome = null;
        $this->idade = null;
        $this->mais_caracteristicas = null;
        $this->dados_adicionais = null;
        $this->boletim_ocorrencia = null;
    }


    public function dataToPerson($data,$bool_BO,$bool_delegacia,$bool_marcas)
    {
        $this->clearAttributes();
        $this->imagem = $data["Foto"];
        $this->fonte = $data["Fonte"];
        $this->local_desaparecimento = $data["Localizacao"];
        $auxiliar1 = explode(',',$data["Localizacao"]);
        $auxiliar2 = count($auxiliar1);
        $auxiliar3 = $auxiliar2 - 2;
        if($auxiliar3>=0)
        {
            $this->estado = $auxiliar1[$auxiliar3];
            $auxiliar3 = $auxiliar2 - 3;
            if($auxiliar3>=0)
            {
                $this->cidade = $auxiliar1[$auxiliar3];
            }
        }
        $tamanho = strlen($data["* Nome Completo da pessoa desaparecida:"]);
        $tamanho = $tamanho - 1;
        for($i=1;$i<=$tamanho;$i++)
        {
            $j = $i-1;
            $data["* Nome Completo da pessoa desaparecida:"][$j] = $data["* Nome Completo da pessoa desaparecida:"][$i];
        }
        $this->nome = $data["* Nome Completo da pessoa desaparecida:"];
        if(strlen($data["Data de Nascimento ou idade::"])>36 && $data["Data de Nascimento ou idade::"][0]!=' ')
        {
            $tamanho = strlen($dt_nascimento);
            $this->dt_nascimento  = str_replace(" ",'', $data["Data de Nascimento ou idade::"]);
            if($this->dt_nascimento[2]!='/' && $tamanho==8)
            {
                $tamanho = $tamanho+2;
                for($i=$tamanho;$i>=0;$i--)
                {                     
                    if($i==2 || $i==5)
                    {
                        $auxiliar1[$i] = '/';
                    }
                    else if($i>5)
                    {
                        $auxiliar[$i+2] = $this->dt_nascimento[$i];
                    }
                    else if($i>2)
                    {
                        $auxiliar[$i+1] = $this->dt_nascimento[$i];
                    }
                    else
                    {
                        $auxiliar[$i] = $this->dt_nascimento[$i];
                    }
                }
                $this->dt_nascimento = $auxiliar1;
            }
        }
        else
        {
            $this->idade = $data["Data de Nascimento ou idade::"];
        }
        $this->data_desaparecimento = str_replace(" ",'', $data["Data do desaparecimento ou ano:"]);
        $this->circunstancia_desaparecimento = $data["Descricao"];
        if($bool_marcas)
        {
            $this->mais_caracteristicas = "Marca de Nascenca, Cicatriz,Tatuagem: " . str_replace(" ",'', $data["Marca de Nascença, Cicatriz,Tatuagem:"]) . 
                ", Quais? Descreva:" . $data["Quais? Descreva.:"];
        }
        else
        {
            $this->mais_caracteristicas = "Marca de Nascenca, Cicatriz,Tatuagem: " . $data["Marca de Nascença, Cicatriz,Tatuagem:"];
        }
        if($bool_BO)
        { 
            $this->boletim_ocorrencia = $data["Nr. do Boletim de Ocorrência (B.O.):"];
        }
        if ($bool_delegacia)
        {
            $this->dados_adicionais = "Delegacia: Cidade/Estado + Fone:" . $data["Delegacia: Cidade/Estado + Fone:"];
        }
        $this->situacao = "Desaparecida";
            
    }

        //342 atualmente

    public function scraping()
    {
        $this->cont=0;
        $contador=0;
        for ($i = 1; $i <= 100; $i++) {
            $page = "http://desaparecidosdobrasil.org.br/index.php?page=search&sCategory=1&iPage=$i";
            $html = file_get_html($page);
            foreach($html->find('li.listing-card a img') as $foto)
            {
                $auxiliar4[$contador]=$foto->src;
                $contador++;
            }
            foreach ($html->find('div.listing-basicinfo a') as $people) {
                $bool_BO = false;
                $bool_delegacia = false;
                $bool_marcas = false;
                $html_people = file_get_html($people->href);
                $data = array();
                $data["Foto"] = $auxiliar4[$this->cont];
                $data["Foto"];
                $auxiliar1 = explode(':',$html_people->find('ul[id=item_location] li',0));
                $auxiliar1 = explode('<',$auxiliar1[1]);
                $data["Localizacao"] = $auxiliar1[0];
                $auxiliar1 = explode('>',$html_people->find('div[id=description] p', 0));
                $auxiliar1 = explode('<',$auxiliar1[1]);
                $data["Descricao"] = $auxiliar1[0];
                $data["Fonte"] = $people->href;
                foreach ($html_people->find('div.meta') as $metadata) {
                    $auxiliar1=explode('>',$metadata);
                    $auxiliar2=explode('<',$auxiliar1[2]);
                    $auxiliar3=explode('<',$auxiliar1[3]);
                    $data[$auxiliar2[0]]=$auxiliar3[0];
                    if($auxiliar2[0]== "Nr. do Boletim de Ocorrência (B.O.):")
                    {
                        $bool_BO = true;
                    }
                    if($auxiliar2[0] == "Delegacia: Cidade/Estado + Fone:" )
                    {
                        $bool_delegacia = true;
                    }
                    if($auxiliar2[0] == "Quais? Descreva.:")
                    {
                        $bool_marcas = true;
                    }
                }   
                $this->dataToPerson($data,$bool_BO,$bool_delegacia,$bool_marcas);
                $this->generateJson();
                $this->cont++; 
            }
        } 
    }

    public function generateJson($name='')
    {
        $arr_json = array(
            'name' => 'desaparecidosdobrasil',
            'attributes' => array(
                array('nome' => $this->nome),
			    array('idade' => $this->idade),
    			array('local_desaparecimento' => $this->local_desaparecimento),
	    		array('dt_nascimento' => $this->dt_nascimento),
                array('dt_desaparecimento' => $this->data_desaparecimento),
                array('cidade' => $this->cidade),
                array('estado' => $this->estado),
                array('imagem' => $this->imagem),
                array('fonte' => $this->fonte),
                array('circunstancia_desaparecimento' => $this->circunstancia_desaparecimento),
                array('situacao' => $this->situacao),
                array('boletimDeOcorrecia' => $this->boletim_ocorrencia),
                array('dados_adicionais' => $this->dados_adicionais),
                array('mais_caracteristicas' => $this->mais_caracteristcas),
            )
        );

        //format the data
        $formattedData = json_encode($arr_json);

        //set the filename
        $filename = 'desaparecidosdobrasil_' . $this->cont . '.json';

        //open or create the file
        $handle = fopen( __DIR__ . '/../json/desaparecidosdobrasil/'.$filename, 'w+');

        //write the data into the file
        fwrite($handle, $formattedData);

        //close the file
        fclose($handle);
    }
}