<?php
/**
-----------------------
    COPYRIGHT
-----------------------
    Licensed under The MIT License.
    Redistributions of files must retain the above copyright notice.

    @author  Cláudio Medeiros <contato@claudiomedeiros.net>
    @package ObjectBoleto http://github.com/klawdyo/PHP-Object-Boleto
    @subpackage ObjectBoleto.Lib.Bancos
    @license http://www.opensource.org/licenses/mit-license.php The MIT License
    
-----------------------
    CHANGELOG
-----------------------
    18/10/2013
    [+] Inicial
    
    
    
  */
class BancoAmazonia extends Banco{
    public $Codigo = '003';
    public $Nome = 'Banco da Amazônia';
    //public $Css = 'bnb.css';
    public $Image = 'banco_da_amazonia.png';
    
    /*
        @var array $tamanhos
        Armazena os tamanhos dos campos na geração do código de barras
        e da linha digitável
        
        ----
        Formatação no bloqueto
        ----
        Os 3 primeiros campos da chave ASBACE (zeros, agencia e conta), devem ser separados
        por um traço ou espaço e impressos no quadrilátero "Agência/Código Cedente" no bloqueto
        de cobrança
        Ex.:
        Agência/ Código do Cedente
		000–058-6002006
		
		Os demais campos são juntos e impressos no quadrilátero "Nosso Número"
		Ex.:
		Nosso Número
		100000107045
        
        ----
        Descrição dos campos
        ----
        O campo "Carteira" (ou "categoria") identifica os tipos de cobrança. Que são:
        Tipo "1" - Cobrança direta sem registro
		- Bloqueto pré impresso fornecido pelo BRB- Banco de Brasília S.A ao cedente;
		- Emissão própria do cedente em papel A4;
		- Impressão local pelo cedente em papel A4 utilizando o módulo de Cobrança Offline)        
        Tipo "2" - 	Cobrança direta com registro
        - Bloqueto impresso pelo cliente e entregue diretamente ao Sacado. É necessário a
        transmissão do arquivo de remessa para o Banco.
        
        O campo "sequencial", ou "nosso número", é montado a critério do cedente. Pode
        ser um número sequencial iniciado em 1, ou um número composto.
        Ex.: As 2 primeiras posições pode ser o ano, e as outras 4 uma sequência. O mais
			importante é que esse número não se repita
			
		O campo "banco" sempre é 070, que é o código para Banco de Brasília S.A.
		
		DV1 e DV2 são calculados a partir das 23 posições anteriores
        
    */
    public $tamanhos = array(
        #Campos comuns a todos os bancos
        'Banco'             => 3,   //identificação do banco
        'Moeda'             => 1,   //Código da moeda: real=9
        'DV'                => 1,   //Dígito verificador geral da linha digitável
        'FatorVencimento'   => 4,   //Fator de vencimento (Dias passados desde 7/out/1997)
        'Valor'             => 10,  //Valor nominal do título
        #Campos variávies.
        'Agencia'           => 4,   //Código da agencia, COM dígito
        'Convenio'          => 4,   //Número da conta
        'NossoNumero'       => 16,  //Nosso número, também chamado de sequencial
      //'Identificador'     => 1,   //Identificador no Sistema: 8
    );

    /*
        @var string $layoutCodigoBarras
        armazena o layout que será usado para gerar o código de barras desse banco.
        Cada variável é precedida por dois-pontos (:), que serão substituídas
        pelos seus respectivos valores
     */
    public $layoutCodigoBarras = ':Banco:Moeda:FatorVencimento:Valor:Agencia:Convenio:NossoNumero8';
	

	/**
      * particularidade() Faz em tempo de execução mudanças que sejam imprescindíveis
      * para a geração correta do código de barras
      *
      *
      *
      * @version 0.1 18/10/2013 Initial
      */
    public function particularidade($object){
		$object->Data['Convenio'] = $object->Vendedor->Convenio;
		
		//Formato o Nosso Número de acordo com o padrão do banco
		//$object->Boleto->NossoNumero = $this->layoutNossoNumero($object->Data);
    }
	
	/**
	  * Formata o Nosso Número desse banco
	  *
	  * @version 0.1 18/1/2013
	  * 
	  * @param array $data Array com todos os dados constantes na classe
	  * @return string String formatada no padrão do Nosso Número do banco
	  */
	public function layoutNossoNumero($data){
		return String::insert(':NossoNumero:Banco:DV1:DV2', $data);
	}
	
	/**
	  * Formata o campo Agência/Codigo do Cliente no boleto, de acordo com as especificações de cada banco
	  * Esse método é opcional na classe, e quando não é declarado, o boleto bancário exibe
	  * a agência com dígito e a conta com dígito, no formato: 123-4/ 23346-3
	  *
	  * @version 0.1 18/01/2013 Initial
	  * 
	  * @param array $data Array com todos os dados constantes na classe
	  * @return string String formatada no padrão do Nosso Número do banco
	  */
	public function agenciaCodigoCedente(){
		return String::insert(':Agencia-:Convenio', $this->parent->Data);
	}
	
	/**
	  * Cálculo do Dígito Verificador 1
	  * 
	  * Módulo 10 das 23 posições anteriores da chave ASBACE
	  *
	  * @version 0.1 18/10/2013
	  * 
	  * @param array $data Array com todos os dados constantes na classe
	  * @return int Inteiro contendo o dígito procurado
	  */
	public function dv1($data){
		$string = String::insert('000:Agencia:Conta:Carteira:NossoNumero:Banco', $data);
		return Math::Mod10($string);
	}

	/**
	  * Cálculo do Dígito Verificador 2
	  * 
	  * Módulo 10 das 24 posições anteriores da chave ASBACE, incluindo o DV1
	  *
	  * @version 0.1 18/10/2013
	  * 
	  * @param array $data Array com todos os dados constantes na classe
	  * @return int Inteiro contendo o dígito procurado
	  */
	public function dv2($data){
		$string = String::insert('000:Agencia:Conta:Carteira:NossoNumero:Banco:DV1', $data);
		return Math::Mod10($string);
	}
}