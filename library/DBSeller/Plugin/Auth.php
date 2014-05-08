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
 * Classe auxiliar para autenticação de usuários validando com a ACL
 */
class DBSeller_Plugin_Auth extends Zend_Controller_Plugin_Abstract {

  /**
   * @var Zend_Acl
   */
  protected static $_acl = NULL;

  /**
   * @var Zend_Auth
   */
  protected $_auth = NULL;

  /**
   * @var
   */
  protected $oUsuarioAcoes = NULL;

  /**
   * @var array
   */
  protected $aUrlAcessoNegado = array('controller' => 'error', 'action' => 'forbidden', 'module' => 'default');

  /**
   * @var array
   */
  protected $aUrlErro = array('controller' => 'error', 'action' => 'error', 'module' => 'default');

  /**
   * Método construtor
   */
  public function __construct() {

    $oSessao = Zend_Session::namespaceGet('sessao');

    if (isset($oSessao['acl'])) {

      $this->_auth = Zend_Auth::getInstance();
      $this->_acl  = $oSessao['acl'];
    }
  }

  /**
   * Checa se usuário tem permissão em determinada ação
   *
   * @param string $sAcesso
   * @return bool
   * @throws Exception
   */
  public static function checkPermission($sAcesso) {

    if (is_string($sAcesso)) {

      $aAcesso     = array_reverse(explode('/', $sAcesso));
      $oRequest    = Zend_Controller_Front::getInstance()->getRequest();
      $sModule     = isset($aAcesso[2]) ? $aAcesso[2] : NULL;
      $sController = isset($aAcesso[1]) ? $aAcesso[1] : NULL;
      $sAction     = isset($aAcesso[0]) ? $aAcesso[0] : NULL;

      if ($sModule) {
        $oRequest->setModuleName($sModule);
      }

      if ($sController) {
        $oRequest->setControllerName($sController);
      }

      if ($sAction) {
        $oRequest->setActionName($sAction);
      }
    } else {
      throw new Exception ('Parametro informado é de tipo inválido');
    }

    if (self::_isAuthorized(Zend_Controller_Front::getInstance()->getRequest())) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Validação das Acls a cada chamada de metodo
   *
   * @param $oRequest
   * @return boolean
   */
  protected function _isAuthorized(Zend_Controller_Request_Abstract $oRequest) {
    
    $sModule     = $oRequest->getModuleName();
    $sController = $oRequest->getControllerName();
    $sAction     = $oRequest->getActionName();
    $oSessao     = Zend_Session::namespaceGet('sessao');
    $oAcl        = $oSessao['acl'];

    self::checkVersaoSistema($oSessao['versaosistema']);

    $oResource = new Zend_Acl_Resource($sModule . ':' . $sController);

    if (!$oAcl->has($oResource->getResourceId())) {

      self::menuNaoCadastrado($oRequest);
      self::carregaAuditoria($oRequest);

      return TRUE;
    }

    if ($oAcl->has($oResource->getResourceId()) && $oAcl->isAllowed('Usuario', $oResource->getResourceId(), $sAction)) {

      self::carregaAuditoria($oRequest);

      return TRUE;
    }

    return FALSE;
  }

  /**
   * Checa a versão do sistema
   *
   * @param string $sVersaoSistema
   * @throws Exception
   */
  private static function checkVersaoSistema($sVersaoSistema) {

    if ($sVersaoSistema == NULL) {

      $sMensagemErro = 'Problema na comunicação com o WebService.<br>';
      $sMensagemErro .= 'Favor Entrar em contato com setor de suporte!';

      throw new Exception($sMensagemErro);
    }

    $sVersaoEcidadeOnlineRegristrada = Zend_Registry::get('config')->ecidadeonline2->versao;

    if ($sVersaoSistema != $sVersaoEcidadeOnlineRegristrada) {
       
      $oParam = Zend_Controller_Front::getInstance();
      $oParam->setBaseUrl('default/manutencao/versao');
    }
  }

  /**
   * Verifica se o menu está cadastrado na base de dados
   *
   * @param $oRequest
   */
  protected function menuNaoCadastrado(Zend_Controller_Request_Abstract $oRequest) {

    $sCaminhoAcl = "{$oRequest->getModuleName()}:{$oRequest->getControllerName()}:{$oRequest->getActionName()}";
    $oLog        = Zend_Registry::get('logger');
    $oLog->log("Caminho da ACL não cadastrado: {$sCaminhoAcl}", Zend_Log::INFO, ' ');
  }

  /**
   * Inicia a sessão do usuário na base de dados
   *
   * @param Zend_Controller_Request_Abstract $oRequest
   */
  private static function carregaAuditoria(Zend_Controller_Request_Abstract $oRequest) {

    // Dados request
    $sModule     = $oRequest->getModuleName();
    $sController = $oRequest->getControllerName();
    $sAction     = $oRequest->getActionName();

    // Registra na base de dados o usuário logado
    $aDadosLogin            = Zend_Auth::getInstance()->getIdentity();
    $oDoctrineEntityManager = Zend_Registry::get('em');
    $oConexao               = $oDoctrineEntityManager->getConnection();
    $sSqlUsuario            = "select fc_putsession('DB_login', '{$aDadosLogin['login']}');";
    $oStatement             = $oConexao->prepare($sSqlUsuario);
    $oStatement->execute();

    // Registra na base de dados a URL acessada pelo usuário
    $sSqlAcessado = "select fc_putsession('DB_acessado', '{$sModule}/{$sController}/{$sAction}');";
    $oStatement   = $oConexao->prepare($sSqlAcessado);
    $oStatement->execute();
  }

  /**
   * Método executado antes de processar os controllers
   *
   * @param Zend_Controller_Request_Abstract $oRequest
   */
  public function preDispatch(Zend_Controller_Request_Abstract $oRequest) {

    if (!$this->_isAuthorized($oRequest)) {

      $oRequest->setActionName($this->aUrlAcessoNegado['action']);
      $oRequest->setControllerName($this->aUrlAcessoNegado['controller']);
      $oRequest->setModuleName($this->aUrlAcessoNegado['module']);
    }
  }
}