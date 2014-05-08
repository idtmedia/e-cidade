<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2014  DBSeller Servicos de Informatica           
 *                            www.dbseller.com.br                     
 *                         e-cidade@dbseller.com.br                   
 *                                                                    
 *  Este programa e software livre; voce pode redistribui-lo e/ou     
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme  
 *  publicada pela Free Software Foundation; tanto a versao 2 da      
 *  Licenca como (a seu criterio) qualquer versao mais nova.          
 *                                                                    
 *  Este programa e distribuido na expectativa de ser util, mas SEM   
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de              
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM           
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais  
 *  detalhes.                                                         
 *                                                                    
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU     
 *  junto com este programa; se nao, escreva para a Free Software     
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA          
 *  02111-1307, USA.                                                  
 *  
 *  Copia da licenca no diretorio licenca/licenca_en.txt 
 *                                licenca/licenca_pt.txt 
 */


/**
 * Modelo responsável pelo processamento dos métodos solicitados pelo cliente SOAP
 * @author Everton Heckler <dbeverton.heckler>
 */
class WebService_Model_RecepcionarLoteRps { 
  
  /**
   * Nome do arquivo xml sendo criado para processamento
   * @var string
   */
  private $sNomeArquivo = NULL;
  
  /**
   * Caminho completo do arquivo
   * @var string
   */
  private $sCaminhoNomeArquivo = NULL;
  
  /**
   * Modelo de importação dos dados
   * @var object
   */
  private $oModeloImportacao = NULL;
  
  /**
   * Dados do usuário que está tentando processar o arquivo
   * @var Object
   */
  private $oDadosUsuario = NULL;
  
  /**
   * Dados do Contribuinte que está sendo processado
   * @var Object
   */
  private $oContribuinte = NULL;
  
  /**
   * Construtor da classe
   */
  public function __construct() {
    $this->oModeloImportacao = new Contribuinte_Model_ImportacaoArquivoRpsModelo1();
  }
  
  /**
   * Processa o arquivo Webservice
   */
  public function processamentoArquivo() {
    
    try {
       
      /**
       * Verifica o nome do arquivo
       */
      if (!$this->sNomeArquivo) {
        $this->oModeloImportacao->setMensagemErro('E160');
      }

      /**
       * Verifica se o usuário está autenticado
       */
      if (!$this->oDadosUsuario || !$this->autenticaUsuario($this->oDadosUsuario->getLogin())) {
        $this->oModeloImportacao->setMensagemErro('E157', 'Usuario: ' . $this->oDadosUsuario->getLogin());
      }
      
      $this->oModeloImportacao->setArquivoCarregado($this->sCaminhoNomeArquivo);
      
      $oArquivoCarregado = $this->oModeloImportacao->carregar();
      
      /**
       * Verifica se o modelo está válido e processa o arquivo de importação
       */
      if ($oArquivoCarregado && !$this->oModeloImportacao->getErro() && $this->oModeloImportacao->validaArquivoCarregado()) {
        
        /**
         * Valida as regras de negócio e processa a importação
         */
        $oImportacaoProcessamento = new Contribuinte_Model_ImportacaoArquivoProcessamento();
        
        $oImportacaoProcessamento->setCodigoUsuarioLogado($this->oDadosUsuario->getId());
        
        $oImportacaoProcessamento->setArquivoCarregado($oArquivoCarregado);
        
        /**
         * Processa a importação
         */
        $oDadosImportacao = $oImportacaoProcessamento->processarImportacaoRps();
        
        return $this->oModeloImportacao->processaSucessoWebService($oDadosImportacao);
      } else {
        return $this->oModeloImportacao->processaErroWebService($oArquivoCarregado->lote->numero);
      }
    } catch (Exception $oErro) {
      return $oErro->getMessage();
    } 
  }
  
  /**
   * Prepara os dados para processar o arquivo do webservice 
   * 
   * @param string $sArquivo
   * @return string
   */
  public function preparaDados($sArquivo) {
    
    try {
      
       $oValidacao = new DBSeller_Helper_Xml_AssinaturaDigital($sArquivo);
      
       /**
        * Validação digital do arquivo
        */
       if (!$oValidacao->validar()) {
         $this->oModeloImportacao->setMensagemErro('E1', $oValidacao->getLastError());
       }
      
      /**
       * Busca usuário contribuinte pelo cnpj_cpf
       */
      $oUsuarioContribuinte = Administrativo_Model_UsuarioContribuinte::getByAttribute('cnpj_cpf', $oValidacao->getCnpj());
      
      if (empty($oUsuarioContribuinte)) {
        throw new Exception('E45');
      }
      
      $oUsuario = Administrativo_Model_Usuario::getById($oUsuarioContribuinte->getUsuario()->getId());
      
      /**
       * Seta os dados do contribuinte
       */
      $this->oDadosUsuario                         = $oUsuario;
      $this->oContribuinte->sCpfCnpj               = $oUsuarioContribuinte->getCnpjCpf();
      $this->oContribuinte->iCodigoUsuario         = $oUsuario->getId();
      $this->oContribuinte->iIdUsuarioContribuinte = $oUsuarioContribuinte->getId();
      
      /**
       * Atualiza os dados do contribuinte na sessão
       */
      $oSessao               = new Zend_Session_Namespace('nfse');
      $oSessao->contribuinte = Contribuinte_Model_Contribuinte::getById($this->oContribuinte->iIdUsuarioContribuinte);
      
      $oData                 = new Zend_Date();
      $this->sNomeArquivo    = $oData->getTimestamp() . '-' . $this->oContribuinte->sCpfCnpj . '.xml';
      $this->sCaminhoArquivo = TEMP_PATH . '/';
      
      /**
       * Verifica se o caminho do arquivo não existe recria a pasta
       */
      if (!file_exists($this->sCaminhoArquivo)) {
        mkdir($this->sCaminhoArquivo, 0777);
      }
       
      /**
       * Escreve os dados no arquivo
       */
      $this->sCaminhoNomeArquivo = $this->sCaminhoArquivo . $this->sNomeArquivo;
      $aArquivo                  = fopen($this->sCaminhoNomeArquivo, 'w');
      fputs($aArquivo, print_r($sArquivo, true));
      
      fclose($aArquivo);
      
      return $this->sCaminhoNomeArquivo;
    } catch (Exception $oErro) {
      return $oErro->getMessage();
    }
  }
  
  /**
   * Carrega a autenticação do usuario de acordo com o retorno da validação do certificado
   * 
   * @param string $sLoginUsuario
   * @throws Exception
   * @return boolean
   */
  public function autenticaUsuario($sLoginUsuario) {
    
    try {
      
      $oAuthAdapter = new WebService_Lib_Auth_Adapter($sLoginUsuario);
      $oAuth        = Zend_Auth::getInstance();
      $oUser        = Administrativo_Model_Usuario::getByAttribute('login', $sLoginUsuario);
      
      /**
       * Verifica se o usuário informado existe
       */
      if (!$oUser) {
        throw new Exception('Usuário não encontrado!');
      }
      
      Zend_Auth::getInstance()->getStorage()->write(array('id'   => $oUser->getId(),
                                                          'login' => $sLoginUsuario));
      
      /**
       * Verifica se usuário está autenticado
       */
      if ($oAuth->authenticate($oAuthAdapter)->getCode() != 1) {
        throw new Exception('Usuário Inválido');
      }
      
      /**
       * Reecreve os dados e permissões de sessão na ACL 
       */
      new DBSeller_Acl_Setup(TRUE);
      
      /**
       * Verifica a permissão de execução da ação conforme liberação do usuário
       */
      if (!DBSeller_Plugin_Auth::checkPermission('webservice/producao/recepcionar-lote-rps')) {
        return false;
      }
      
      return true;
    } catch (Exception $oErro) {
      $this->oModeloImportacao->setMensagemErro('E157');
    }
  }
}