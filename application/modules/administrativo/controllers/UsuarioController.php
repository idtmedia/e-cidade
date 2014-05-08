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
 * Controle de Usuários
 *
 * @package Administrativo/Usuario
 * @see     Administrativo_Lib_Controller_AbstractController
 */

/**
 * @package Administrativo/Usuario
 * @see     Administrativo_Lib_Controller_AbstractController
 */
class Administrativo_UsuarioController extends Administrativo_Lib_Controller_AbstractController {

  /**
   * Tela inicial
   */
  public function indexAction() {

    $sBusca = $this->getRequest()->getParam('busca');

    $oPaginatorAdapter = new DBSeller_Controller_Paginator(
      Administrativo_Model_Usuario::getQuery(),
      'Administrativo_Model_Usuario',
      'Administrativo\Usuario'
    );

    $sWhere = ' 1 = 1 ';

    if ($sBusca != '') {

      $sBuscaLower = mb_strtolower($sBusca, 'UTF-8');

      $sWhere .= " AND ( ";
      $sWhere .= "   LOWER(e.nome)  LIKE '%{$sBuscaLower}%' OR ";
      $sWhere .= "   LOWER(e.login) LIKE '%{$sBuscaLower}%' OR ";
      $sWhere .= "   LOWER(e.email) LIKE '%{$sBuscaLower}%'  ";
      $sWhere .= " ) ";
    }

    // Se usuario nao for administrador e não fiscal
    if (!$this->view->user->getAdministrativo() && !in_array($this->view->user->getPerfil()->getId(), array(5, 4))) {
      $sWhere .= " AND e.cnpj = '{$this->view->user->getCnpj()}'";
    }

    // se o usuario for fiscal lista somente ele
    if (in_array($this->view->user->getPerfil()->getId(), array(5, 4))) {
      $sWhere .= " AND e.id = '{$this->view->user->getId()}'";
    }

    $oPaginatorAdapter->where($sWhere);

    $oUsuarios = new Zend_Paginator($oPaginatorAdapter);
    $oUsuarios->setItemCountPerPage(10);
    $oUsuarios->setCurrentPageNumber($this->getRequest()->getParam('page'));

    $this->view->usuarios  = $oUsuarios;
    $this->view->formBusca = $this->formBusca($sBusca);
    $this->view->busca     = $this->getRequest()->getParam('busca');
  }

  /**
   * Cadastro de usuário
   */
  public function novoAction() {

    $this->view->form = $this->formUsuario();

    if ($this->getRequest()->isPost()) {

      if ($this->view->form->isValid($_POST)) {

        $aDados        = $this->getRequest()->getPost();
        $oUsuario      = new Administrativo_Model_Usuario();
        $aCheckUsuario = Administrativo_Model_Usuario::getByAttribute('login', $aDados['login']);

        if ($aCheckUsuario !== NULL) {

          $this->view->messages[] = array('error' => $this->translate->_('O Login já está em uso no sistema.'));
          
          return;
        }

        $oUsuarioEmail = Administrativo_Model_Usuario::getByAttribute('email', $aDados['email']);

        if (!empty($oUsuarioEmail)) {
          $this->view->messages[] = array('error' => $this->translate->_('Email já está cadastrado no sistema.'));

          return;
        }

        $oUsuario->setTipo($this->view->user->getTipo());
        $oUsuario->setCnpj($this->view->user->getCnpj());
        $oUsuario->setHabilitado(TRUE);
        $oUsuario->persist($aDados);

        Administrativo_Model_Usuario::enviarEmailSenha($oUsuario);

        $aMensagem = array('success' => $this->translate->_('Usuário cadastrado com sucesso.'));

        $this->_helper->getHelper('FlashMessenger')->addMessage($aMensagem);
        $this->_redirector->gotoSimple('editar', 'usuario', 'administrativo', array('id' => $oUsuario->getId()));
      }
    }
  }

  /**
   * Exclusão de usuário
   */
  public function excluirAction() {

    $usuario = $this->getRequest()->getParam('id');
    $usuario = Administrativo_Model_Usuario::getById($usuario);

    if ($usuario !== NULL) {

      try {

        $usuario->destroy();

        $this->_helper->getHelper('FlashMessenger')->addMessage(
                      array('success' => $this->translate->_('Usuário removido com sucesso.'))
        );
        $this->_redirector->gotoSimple('index', 'usuario', 'administrativo');
      } catch (Exception $e) {

        $this->_helper->getHelper('FlashMessenger')->addMessage(array(
                                                                  'error' => $this->translate->_('Erro ao remover o usuário.')
                                                                ));

        $this->_redirector->gotoSimple('index', 'usuario', 'administrativo');
      }
    } else {

      $this->_helper->getHelper('FlashMessenger')->addMessage(
                    array('error' => $this->translate->_('Usuário não encontrado.'))
      );
      $this->_redirector->gotoSimple('index', 'usuario', 'administrativo');
    }
  }

  /**
   * Desativação de usuário
   */
  public function desativarAction() {

    $iUsuario = $this->getRequest()->getParam('id');
    $oUsuario = Administrativo_Model_Usuario::getById($iUsuario);

    if (is_object($oUsuario)) {

      try {

        $oUsuario->trocaStatus(FALSE);

        $this->_helper->getHelper('FlashMessenger')->addMessage(
                      array('success' => $this->translate->_('Usuário desativado com sucesso.'))
        );
        $this->_redirector->gotoSimple('index', 'usuario', 'administrativo');
      } catch (Exception $e) {

        $this->_helper->getHelper('FlashMessenger')->addMessage(array(
                                                                  'error' => $this->translate->_('Erro ao desativar o usuário.')
                                                                ));
        $this->_redirector->gotoSimple('index', 'usuario', 'administrativo');
      }
    } else {

      $this->_helper->getHelper('FlashMessenger')->addMessage(
                    array('error' => $this->translate->_('Usuário não encontrado.'))
      );
      $this->_redirector->gotoSimple('index', 'usuario', 'administrativo');
    }
  }

  /**
   * Ativação de usuário
   */
  public function ativarAction() {

    $iUsuario = $this->getRequest()->getParam('id');
    $oUsuario = Administrativo_Model_Usuario::getById($iUsuario);

    if (is_object($oUsuario)) {

      try {

        $oUsuario->trocaStatus(TRUE);

        $aMensagem = array('success' => $this->translate->_('Usuário reativado com sucesso.'));

        $this->_helper->getHelper('FlashMessenger')->addMessage($aMensagem);
        $this->_redirector->gotoSimple('index', 'usuario', 'administrativo');
      } catch (Exception $e) {

        $aMensagem = array('error' => $this->translate->_('Erro ao reativar o usuário.'));

        $this->_helper->getHelper('FlashMessenger')->addMessage($aMensagem);
        $this->_redirector->gotoSimple('index', 'usuario', 'administrativo');
      }
    } else {

      $aMensagem = array('error' => $this->translate->_('Usuário não encontrado.'));

      $this->_helper->getHelper('FlashMessenger')->addMessage($aMensagem);
      $this->_redirector->gotoSimple('index', 'usuario', 'administrativo');
    }
  }

  /**
   * Alteração de usuário
   */
  public function editarAction() {

    $usuario_id   = $this->getRequest()->getParam('id');
    $contribuinte = $this->getRequest()->getParam('cont');

    if ($usuario_id == NULL) {
      $this->_redirector->gotoSimple('index');
    }

    $usuario = Administrativo_Model_Usuario::getById($usuario_id);
    $form    = $this->formUsuario('editar', $usuario_id);

    if ($usuario == NULL) {

      $aMensagem = array('error' => $this->translate->_('Usuário inválido.'));

      $this->_helper->getHelper('FlashMessenger')->addMessage($aMensagem);
      $this->_redirector->gotoSimple('index');
    }

    if ($this->getRequest()->isPost()) {

      if (!$form->isValidPartial($_POST)) {
        $this->view->form = $form;
      } else {

        $dados         = $this->getRequest()->getPost();
        $oUsuarioEmail = Administrativo_Model_Usuario::getByAttribute('email', $dados['email']);

        if ($oUsuarioEmail <> NULL && $oUsuarioEmail->getId() <> $usuario->getId()) {

          $aMensagem = array('error' => $this->translate->_('Email já está cadastrado no sistema.'));

          $this->_helper->getHelper('FlashMessenger')->addMessage($aMensagem);
          $this->_redirector->gotoSimple('editar', 'usuario', 'administrativo', array('id' => $usuario->getId()));
        }

        $usuario->persist($dados);

        $aMensagem = array('success' => $this->translate->_('Usuário alterado com sucesso.'));

        $this->_helper->getHelper('FlashMessenger')->addMessage($aMensagem);
        $this->_redirector->gotoSimple('editar', 'usuario', 'administrativo', array('id' => $usuario->getId()));
      }
    } else {

      $values = array(
        'login'          => $usuario->getLogin(),
        'nome'           => $usuario->getNome(),
        'email'          => $usuario->getEmail(),
        'fone'           => $usuario->getTelefone(),
        'administrativo' => $usuario->getAdministrativo(),
        'perfil'         => $usuario->getPerfil()->getId()
      );

      $this->view->formVincularContribuinte = $this->formVincularContribuinte($usuario_id);
      $this->view->form                     = $this->formUsuario('editar', $usuario_id, $values);
    }

    if ($contribuinte !== NULL) {
      $contribuinte = Administrativo_Model_UsuarioContribuinte::getById($contribuinte);
    }

    if ($contribuinte != NULL) {
      $this->view->contribuinte_id = $contribuinte->getId();
    }

    $usuarios_contribuintes = $usuario->getUsuariosContribuintes();

    $paginatorAdapter = new Zend_Paginator_Adapter_Array($usuarios_contribuintes);
    $paginator        = new Zend_Paginator($paginatorAdapter);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_request->getParam('page'));

    $empresas = array();

    foreach ($paginator as $p) {

      $oRetorno = Administrativo_Model_UsuarioContribuinte::getContribuinte($p->getId());

      if (empty($oRetorno)) {
        continue;
      }

      $empresas[$p->getId()] = $oRetorno;
    }

    // busca permissoes administrativas
    $permissoes_adm = array();
    $acoes          = $usuario->getAcoesAdm();

    foreach ($acoes as $a) {
      $permissoes_adm[] = $a->getId();
    }

    // formulario para vincular contribuinte
    $this->view->paginator     = $paginator;
    $this->view->contribuintes = $empresas;
    $this->view->modulosAdm    = Administrativo_Model_Modulo::getByAttribute('modulo', 'Administrativo');
    $this->view->permissoesAdm = $permissoes_adm;
    $this->view->modulosFiscal = Administrativo_Model_Modulo::getByAttribute('modulo', 'Fiscal');
    $this->view->usuario       = $usuario;
  }

  /**
   * Busca contribuintes
   */
  public function getContribuinteAction() {

    $iInscricaoMunicipal = $this->getRequest()->getParam('term');
    $aContribuintes      = Administrativo_Model_Contribuinte::getByIm($iInscricaoMunicipal);
    $aRetornoJson        = NULL;

    if (count($aContribuintes) > 0) {
      $aRetornoJson = $aContribuintes[0]->toArray();
    }

    echo $this->getHelper('json')->sendJson($aRetornoJson);
  }

  /**
   * Busca o contribuinte pelo CNPJ/CPF
   */
  public function getContribuinteCnpjAction() {

    $aRetornoJson = NULL;
    $iCnpj        = DBSeller_Helper_Number_Format::getNumbers($this->_getParam('term'));
    $oDados       = Contribuinte_Model_Contribuinte::getByCpfCnpj($iCnpj);
    $aRetorno     = NULL;

    if ($oDados != NULL) {

      $aRetorno = array(
        'razao_social' => $oDados->getRazaoSocial(),
        'login'        => $oDados->getCgcCpf(),
        'nome'         => $oDados->getRazaoSocial(),
        'email'        => $oDados->getEmail(),
        'telefone'     => DBSeller_Helper_Number_Format::maskPhoneNumber($oDados->getTelefone())
      );
    }

    echo $this->getHelper('json')->sendJson($aRetorno);
  }

  /**
   * Vincula os usuários ao contadores
   */
  public function vincularAction() {

    $usuario      = $this->getRequest()->getParam('usuario');
    $usuario      = Administrativo_Model_Usuario::getById($usuario);
    $contribuinte = $this->getRequest()->getParam('contribuinte');

    if ($contribuinte == NULL) {

      $this->_helper->getHelper('FlashMessenger')->addMessage(array(
                                                                'error' => $this->translate->_('Não foi possível vincular o contribuinte.')
                                                              ));

      $this->_redirector->gotoSimple('editar', 'usuario', 'administrativo', array('id' => $usuario->getId()));
    }

    $usuario_contrib    = new Administrativo_Model_UsuarioContribuinte();
    $oDadosContribuinte = $usuario_contrib->getByAttribute('im', $contribuinte);

    if ($oDadosContribuinte != NULL) {

      $this->_helper->getHelper('FlashMessenger')->addMessage(array(
                                                                'error' => $this->translate->_('Contribuinte já está vinculado ao usuário.')
                                                              ));

      $this->_redirector->gotoSimple('editar', 'usuario', 'administrativo', array('id' => $usuario->getId()));
    }

    $res = $usuario_contrib->persist(array(
                                       'usuario'      => $usuario,
                                       'contribuinte' => $contribuinte
                                     ));

    $usuario->addUsuarioContribuinte($usuario_contrib);

    if ($res === NULL) {

      $this->_helper->getHelper('FlashMessenger')->addMessage(array(
                                                                'error' => $this->translate->_('Não foi possível vincular o contribuinte.')
                                                              ));
    } else {
      $this->_helper->getHelper('FlashMessenger')->addMessage(
                    array('success' => $this->translate->_('Contribuinte vinculado com sucesso.'))
      );
    }

    $this->_redirector->gotoSimple('editar', 'usuario', 'administrativo', array(
      'id'   => $usuario->getId(),
      'cont' => $usuario_contrib->getId()
    ));
  }

  /**
   * Desvincula os usuários dos contadores
   */
  public function desvincularAction() {

    $uc      = $this->getRequest()->getParam('id');
    $uc      = Administrativo_Model_UsuarioContribuinte::getById($uc);
    $usuario = $uc->getUsuario();
    $uc->setHabilitado(FALSE);
    $uc->persist();

    $this->_helper->getHelper('FlashMessenger')->addMessage(
                  array('success' => $this->translate->_('Contribuinte desvinculado com sucesso.'))
    );
    $this->_redirector->gotoSimple('editar', 'usuario', 'administrativo', array('id' => $usuario->getId()));
  }

  /**
   * Configura as permissões dos usuários
   */
  public function setPermissaoAction() {

    $usuario_contribuinte = $this->getRequest()->getParam('contribuinte');
    $usuario              = $this->getRequest()->getParam('usuario');
    $usuario              = Administrativo_Model_Usuario::getById($usuario);
    $acoes                = $this->getRequest()->getParam('acao');

    // remove todas as permissoes cadastradas
    if ($usuario_contribuinte !== NULL) {

      $usuario_contribuinte = Administrativo_Model_UsuarioContribuinte::getById($usuario_contribuinte);
      $usuario_contribuinte->limparAcoes();
    }

    $usuario_contribuinte_acao = array();

    foreach ($acoes as $id => $a) {

      if ($a === 'on') {

        $acao                        = Administrativo_Model_Acao::getById($id);
        $usuario_contribuinte_acao[] = $acao;
      }
    }

    if ($usuario_contribuinte !== NULL) {

      $usuario_contribuinte->adicionaAcoes($usuario_contribuinte_acao);
      $usuario_contribuinte = $usuario_contribuinte->getId();
    }

    $this->_helper->getHelper('FlashMessenger')->addMessage(array(
                                                              'success' => $this->translate->_('Permissões modificadas com sucesso.')
                                                            ));

    $this->_redirector->gotoSimple('editar', 'usuario', 'administrativo', array(
      'id'   => $usuario->getId(),
      'cont' => $usuario_contribuinte
    ));
  }

  /**
   * Configura as permissões do perfil administrativo
   */
  public function setPermissaoAdmAction() {

    $usuario = $this->getRequest()->getParam('usuario');
    $usuario = Administrativo_Model_Usuario::getById($usuario);
    $acoes   = $this->getRequest()->getParam('acao');

    $usuario->limparAcoes();

    $usuario_acao = array();

    foreach ($acoes as $id => $a) {

      if ($a == 'on') {

        $acao           = Administrativo_Model_Acao::getById($id);
        $usuario_acao[] = $acao;
      }
    }

    $usuario->adicionaAcoes($usuario_acao);

    $this->_helper->getHelper('FlashMessenger')->addMessage(array(
                                                              'success' => $this->translate->_('Permissões modificadas com sucesso.')
                                                            ));

    $this->_redirector->gotoSimple('editar', 'usuario', 'administrativo', array('id' => $usuario->getId()));
  }

  /**
   * Tela para configurar as permissões
   */
  public function permissaoAction() {

    parent::noTemplate();

    $usuario_cont_id = $this->getRequest()->getParam('id');
    $usuario_cont    = Administrativo_Model_UsuarioContribuinte::getById($usuario_cont_id);
    $usuario         = $usuario_cont->getUsuario();

    // Gera um vetor com todas as acoes permitidas a esse usuario
    $acoes      = ($usuario_cont == NULL) ? array() : $usuario_cont->getAcoes();
    $permissoes = array();

    foreach ($acoes as $a) {
      $permissoes[] = $a->getId();
    }

    $this->view->contribuinte         = Administrativo_Model_UsuarioContribuinte::getContribuinte($usuario_cont->getId());
    $this->view->usuario_contribuinte = $usuario_cont->getId();
    $this->view->usuario              = $usuario;
    $this->view->modulos              = Administrativo_Model_Modulo::getByAttribute('modulo', array('Contribuinte', 'WebService'));

    if (count($this->view->modulos) == 1) {
      $this->view->modulos = array($this->view->modulos);
    }

    $this->view->permissoes    = $permissoes;
    $this->view->permissoesAdm = array();
  }

  /**
   * Busca permissões do usuário
   */
  public function getPermissaoAction() {

    $iIdUsuarioContribuinte = $this->_getParam('usuario_contribuinte');
    $oUsuarioContribuinte   = Administrativo_Model_UsuarioContribuinte::getById($iIdUsuarioContribuinte);
    $aRetornoJson           = array();

    if ($oUsuarioContribuinte) {

      $aAcoes = $oUsuarioContribuinte->getAcoes();

      foreach ($aAcoes as $oAcao) {
        $aRetornoJson[] = $oAcao->getId();
      }
    } else {

      $iIdUsuario = $this->_getParam('usuario');
      $oUsuario   = Administrativo_Model_Usuario::getById($iIdUsuario);

      foreach ($oUsuario->getAcoes() as $oAcao) {
        $aRetornoJson[] = $oAcao->getId();
      }
    }

    echo $this->getHelper('json')->sendJson($aRetornoJson);
  }

  /**
   * Busca os contadores
   */
  public function getContadoresAction() {

    $oContadores  = Administrativo_Model_Contador::getAll();
    $aRetornoJson = $oContadores->toArray();

    echo $this->getHelper('json')->sendJson($aRetornoJson);
  }

  /**
   * Ação para alteração de senha
   */
  public function trocarSenhaAction() {

    $this->checkIdentity();
    $this->view->form = $this->formSenha();

    if ($this->getRequest()->isPost()) {

      $dados = $this->getRequest()->getPost();

      if ($this->view->form->isValidPartial($_POST) && $dados['senha'] === $dados['senha_confirma']) {

        $usuario = $this->view->user;

        // Encriptografa a senha
        $dados['senha'] = sha1($dados['senha']);

        if ($usuario->trocaSenha($dados['senha_antiga'], $dados['senha'])) {

          $this->_helper->getHelper('FlashMessenger')->addMessage(
                        array('success' => $this->translate->_('Senha alterada com sucesso.'))
          );
          $this->_redirector->gotoSimple('trocar-senha', 'usuario', 'administrativo');
        } else {
          $this->_helper->getHelper('FlashMessenger')->addMessage(
                        array('error' => $this->translate->_('Não foi possível alterar a senha.'))
          );
        }
      } else {
        $this->_helper->getHelper('FlashMessenger')->addMessage(
                      array('error' => $this->translate->_('Preencha os dados corretamente.'))
        );
      }
    }
  }

  /**
   * Formulário para alteração de senha
   *
   * @return Administrativo_Form_Senha
   */
  private function formSenha() {

    $oForm = new Administrativo_Form_Senha();
    $oForm->setAction($this->view->baseUrl('/administrativo/usuario/trocar-senha'));

    return $oForm;
  }

  /**
   * Retorna formulario para cadastro/edicao de Usuario
   *
   * @param string $sAction
   * @param null   $iId
   * @param array  $aValues
   * @return Twitter_Bootstrap_Form_Horizontal
   */
  private function formUsuario($sAction = 'novo', $iId = NULL, array $aValues = array()) {

    if ($iId) {
      $sAction .= "/id/{$iId}";
    }

    $oForm = new Twitter_Bootstrap_Form_Horizontal();
    $oForm->setAction($this->view->baseUrl("/administrativo/usuario/{$sAction}"))->setMethod('post');

    if ($iId) {

      $oElm = $oForm->createElement('hidden', 'id');
      $oElm->setValue($iId);
      $oForm->addElement($oElm);
    }

    if ($iId == NULL && $this->view->user->getAdministrativo()) {

      $aTipos = Administrativo_Model_TipoUsuario::getLista();

      $oElm = $oForm->createElement('select', 'tipo', array('multiOptions' => $aTipos));
      $oElm->setLabel('Tipo:');
      $oElm->setAttrib('ajax-url', $this->view->baseUrl('/administrativo/usuario/get-contadores'));
      $oElm->setRequired(TRUE);
      $oForm->addElement($oElm);

      $oElm = $oForm->createElement('hidden', 'cnpj');
      $oForm->addElement($oElm);

      $aContadores      = Administrativo_Model_Contador::getAll();
      $aListaContadores = array();

      foreach ($aContadores as $oContador) {
        $aListaContadores[$oContador->attr('cnpj')] = $oContador->attr('nome') . ' - ' . $oContador->attr('cnpj');
      }

      $oElm = $oForm->createElement('select', 'contador', array('multiOptions' => $aListaContadores));
      $oElm->setLabel('Contador:');
      $oForm->addElement($oElm);

      $oElmBuscar = $oForm->createElement('button', 'buscador', array(
        'label'        => '',
        'icon'         => 'search',
        'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT
      ));

      $oElm = $oForm->createElement('text', 'cnpj', array(
        'append'      => $oElmBuscar,
        'class'       => 'numerico',
        'description' => '-'
      ));
      $oElm->setAttrib('url-buscador', $this->view->baseUrl('administrativo/usuario/get-contribuinte-cnpj'));
      $oElm->setLabel('CNPJ:');
      $oForm->addElement($oElm);
    }

    $oElm = $oForm->createElement('text', 'login');
    $oElm->setLabel('Login:');
    $oElm->removeDecorator('errors');

    if ($this->view->user->getAdministrativo()) {
      $oElm->setAttrib('readonly', 'readonly');
    }

    $oElm->setRequired(TRUE);

    if (isset($aValues['login'])) {
      $oElm->setValue($aValues['login']);
    }

    $oForm->addElement($oElm);

    $oElm = $oForm->createElement('text', 'nome');
    $oElm->setLabel('Nome:');
    $oElm->setRequired(TRUE);
    $oElm->removeDecorator('errors');

    if (isset($aValues['nome'])) {
      $oElm->setValue($aValues['nome']);
    }

    $oForm->addElement($oElm);

    $oElm = $oForm->createElement('text', 'email');
    $oElm->setLabel('Email:');
    $oElm->setRequired(TRUE);
    $oElm->removeDecorator('errors');

    if (isset($aValues['email'])) {
      $oElm->setValue($aValues['email']);
    }

    $oForm->addElement($oElm);

    $oElm = $oForm->createElement('text', 'fone');
    $oElm->setLabel('Telefone:');
    $oElm->setAttrib('class', 'telefone');
    $oElm->setValidators(array(
                           new Zend_Validate_Alnum(),
                           new Zend_Validate_StringLength(array('max' => 13))
                         ));

    if (isset($aValues['fone'])) {
      $oElm->setValue($aValues['fone']);
    }

    $oForm->addElement($oElm);

    $aPerfis = array();

    if ($this->view->user->getAdministrativo()) {

      foreach (Administrativo_Model_Perfil::getAll() as $oPerfil) {
        $aPerfis[$oPerfil->getEntity()->getId()] = $oPerfil->getEntity()->getNome();
      }
    } else {

      foreach ($this->view->user->getPerfil()->getPerfis() as $oPerfil) {
        $aPerfis[$oPerfil->getId()] = $oPerfil->getNome();
      }
    }

    $oElm = $oForm->createElement('select', 'perfil', array('multiOptions' => $aPerfis));
    $oElm->setRequired(TRUE);
    $oElm->removeDecorator('errors');

    if (isset($aValues['perfil'])) {
      $oElm->setValue($aValues['perfil']);
    }

    $oElm->setLabel('Perfil:');
    $oElm->setRequired(TRUE);
    $oForm->addElement($oElm);

    $oForm->addElement('submit', 'submit', array(
      'label'      => 'Salvar',
      'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_SUCCESS
    ));

    return $oForm;
  }

  /**
   * Formulário para vinculo de usuários
   *
   * @param $iId
   * @return Administrativo_Form_VincularContribuinte
   */
  private function formVincularContribuinte($iId) {

    $oForm = new Administrativo_Form_VincularContribuinte();
    $oForm->setAction($this->view->baseUrl('/administrativo/usuario/vincular'));
    $oForm->usuario->setValue($iId);

    return $oForm;
  }

  /**
   * Formulário de busca
   *
   * @param string $sBusca
   * @return Twitter_Bootstrap_Form_Search
   */
  private function formBusca($sBusca = '') {

    return new Twitter_Bootstrap_Form_Search(array(
                                               'inputName'   => 'busca',
                                               'value'       => $sBusca,
                                               'submitLabel' => 'Buscar',
                                               'action'      => $this->view->baseUrl('administrativo/usuario/index')
                                             ));
  }
}