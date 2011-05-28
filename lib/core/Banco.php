<?php
//18/05/11
class Banco{
    /*
        @var array $Codigo
        Armazena o código do banco
    */
    public $Codigo;

    /*
        @var array $nome
        Armazena o nome do banco
    */
    public $Nome;
    
    /*
        @var array $image
        Armazena o nome da imagem da logomarca do banco
    */
    public $Image;
    
    /*
        @var array $css
        Armazena o arquivo CSS utilizado por esse banco
    */
    public $Css;
    
    
    
    /*
        @var array $relacoes
        Armazena a relação entre os mais diversos bancos e os nomes dos
        seus arquivos. Os nomes devem estar iguais, inclusive em relação
        ao caso.
        Fonte: http://www.febraban.org.br/Bancos.asp
     */
    public $relacoes = array(
        '001' => 'BB',
        '003' => 'BancoAmazonia',
        '004' => 'BNB',
        '033' => 'Santander',
        '041' => 'Banrisul',
        '070' => 'BRB', //Banco de Brasília
        '104' => 'Caixa',
        '237' => 'Bradesco',
        '318' => 'Bmg',
        '341' => 'Itau',
        '356' => 'Real',
        '409' => 'Unibanco',
        '623' => 'Panamericano',
        '756' => 'Bancoob',
    );
    
    
    /*
        @var array $tamanhos
        Armazena os dados de posições dos valores dentro do código de barras.
        
    */
    public $tamanhos = array();
    
    /*
        @var $layoutCodigoBarras
        armazena o layout que será usado para gerar o código de barras desse banco
     */
    public $layoutCodigoBarras;
    
    /*
        @var $formataLinhaDigitavel
        Máscara para a linha digitável
     */
    public $mascaraLinhaDigitavel = '00000.00000 00000.000000 00000.000000 0 00000000000000';
    
    
    /**
      * Construtor da classe
      * 
      * @version 0.1 18/05/2011 Initial
      */
    public function __construct(){

    }
    
    /**
      * Carrega o arquivo e as configurações de layout do banco informado
      * 
      * @version 0.1 20/05/2011 Initial
      */
    public function load($codigo){
        if(array_key_exists($codigo, $this->relacoes)){
            $banco = $this->relacoes[$codigo];
            $filename = OB_DIR . '/lib/bancos/' . $banco . '.php';

            if(file_exists($filename)){
                require $filename;
                return new $banco;
            }
            else{
                throw new Exception('O arquivo /lib/bancos/' . $banco. '.php não existe.');
            }
        }
        else{
            throw new Exception('O banco ' . $codigo. ' não existe em Banco::$relacoes');
        }
    }
    
    /**
      * Normaliza as variáveis de acordo com os seus tamanhos exatos
      * 
      * @version 0.1 18/05/2011 Initial
      */
    public function normalize($valor, $variavel){
        if(array_key_exists($variavel, $this->tamanhos)){
            $length = $this->tamanhos[$variavel];
            if(strlen($valor) < $length){
                return OB::zeros($valor, $length);
            }
            else{
                return String::left($valor, $length);
            }
        }
        else{
            throw new Exception(" A chave \"{$variavel}\" não existe no layout");
        }
    }
    
    
    /**
      * particularidade() Faz em tempo de execução mudanças que sejam imprescindíveis
      * para a geração correta do código de barras
      * Esse método será estendido por todas as classes filhas, portanto só é
      * necessário declará-la caso haja algo para mudar
      *
      * @version 0.1 28/05/2011 Initial
      */
    public function particularidade(&$data){}
    
    
    
    /**
      * Avalia se todos os campos necessários para a geração do código de barras
      * estão preenchidos
      *
      * @version 0.1 28/05/2011
      */
    public function verificaObrigatorios($data){
        $obrigatorios = array_keys($this->tamanhos);
        //pr($data);
        foreach($data as $chave => $valor){
            if(!array_key_exists($chave, $data) || is_null($data[$chave])){
                throw new Exception('O campo "' . $chave . '" é obrigatório para
                    a geração do código de barras do banco "' . $this->Nome . '"');
            }
        }
    }
    
}