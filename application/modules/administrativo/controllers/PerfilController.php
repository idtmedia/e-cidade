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
 * Description of PerfilController
 *
 * @author dbeverton.heckler
 */
class Administrativo_PerfilController extends Administrativo_Lib_Controller_AbstractController {

  public function indexAction() {
    
    $busca = $this->getRequest()->getParam('busca');
    
    $paginatorAdapter = new E2Tecnologia_Controller_Paginator(
      Administrativo_Model_Perfil::getQuery(), 
     'Administrativo_Model_Perfil', 
     'Administrativo\Perfil'
    );
    
    $where = '1 = 1 ';
    
    if ($busca != '') {
      $where .= 'AND (upper(e.nome) ' . " LIKE '%" . strtoupper($busca) . "%')";
    }
    
    $paginatorAdapter->where($where);
    
    $perfils = new Zend_Paginator($paginatorAdapter);
    $perfils->setItemCountPerPage(10);
    $perfils->setCurrentPageNumber($this->_request->getParam("page"));
    
    $this->view->perfils = $perfils;
    $this->view->formBusca = $this->formBusca($busca);
    $this->view->busca = $busca;
  }

  public function novoAction() {
    
    $this->view->form = $this->formPerfil();

    if ($this->getRequest()->isPost()) {
      
      if ($this->view->form->isValid($_POST)) {
        
        $dados  = $this->getRequest()->getPost();
        $perfil = new Administrativo_Model_Perfil();
        
        $check_perfil = Administrativo_Model_Perfil::getByAttribute('nome', $dados['nome']);
        
        if ($check_perfil !== null) {
          
          $this->view->messages[] = array('error' => 'Perfil já cadastrado');
          return;
        }
        
        $perfil->persist($dados);

        $this->_helper->getHelper('FlashMessenger')->addMessage(array('notice' => 'Perfil cadastrado com sucesso.'));
        $this->_redirector->gotoSimple('editar', 'perfil', 'administrativo', array('id' => $perfil->getId()));
      }
    }
  }

  public function excluirAction() {
    
    $perfil = $this->getRequest()->getParam('id');
    $perfil = Administrativo_Model_Perfil::getById($perfil);

    if ($perfil !== null) {
      
      try {
        
        $perfil->destroy();
        $this->_helper->getHelper('FlashMessenger')->addMessage(array('notice' => 'Perfil removido.'));
        $this->_redirector->gotoSimple('index', 'perfil', 'administrativo');
      } catch (Exception $oErro) {
      
        $this->_helper->getHelper('FlashMessenger')->addMessage(array('error' => 'Ocorreu um erro e não foi possível remover o Perfil.'));
        $this->_redirector->gotoSimple('index', 'perfil', 'administrativo');
      }
    } else {
      
      $this->_helper->getHelper('FlashMessenger')->addMessage(array('error' => 'Perfil não encontrado.'));
      $this->_redirector->gotoSimple('index', 'perfil', 'administrativo');
    }
  }

  public function editarAction() {
    
    $perfil_id = $this->getRequest()->getParam('id');
    
    if ($perfil_id === null) {
      $this->_redirector->gotoSimple('index');
    }
    
    $perfil = Administrativo_Model_Perfil::getById($perfil_id);
    
    $oForm = $this->formPerfil('editar', $perfil_id);

    if ($perfil === null) {
      
      $this->_helper->getHelper('FlashMessenger')->addMessage(array('notice' => 'Perfil inválido.'));
      $this->_redirector->gotoSimple('index');
    }

    if ($this->getRequest()->isPost()) {
      
      if (!$oForm->isValidPartial($_POST)) {
        $this->view->form = $oForm;
      } else {
        
        $dados = $this->getRequest()->getPost();
        $perfil->persist($dados);
        $this->_helper->getHelper('FlashMessenger')->addMessage(array('notice' => 'Perfil modificado.'));
        $this->_redirector->gotoSimple('editar', 'perfil', 'administrativo', array('id' => $perfil->getId()));
      }
    }  else {
      
      $values = array(
        'nome' => $perfil->getNome(),
        'administrativo' => $perfil->getAdministrativo()
      );

      $this->view->form    = $this->formPerfil('editar', $perfil_id, $values);
    }
    
    // busca permissões do perfil_perfis
    $aPerfilPerfis = array();
    $oListaPerfilPerfis = $perfil->getPerfis();
    
    foreach ($oListaPerfilPerfis as $aPerfil) {
    
      $aPerfilPerfis[] = $aPerfil->getId();
    }
    
    // busca permissões do perfil_perfis
    $aPerfilAcoes = array();
    $oListaPerfilAcoes = $perfil->getAcoes();
    
    foreach ($oListaPerfilAcoes as $aAcao) {
      $aPerfilAcoes[] = $aAcao->getId();
    }
    
    $this->view->aPerfis = Administrativo_Model_Perfil::getAll();
    $this->view->aPerfilPerfis = $aPerfilPerfis;
    
    $this->view->modulosAdm = Administrativo_Model_Modulo::getAll();
    $this->view->aPerfilAcoes = $aPerfilAcoes;
    $this->view->perfil = $perfil;

  }

  public function setPermissaoPerfilAction() {
    
    $iCodigoPerfil = $this->getRequest()->getParam('perfil');
    
    $aAcoes = $this->getRequest()->getParam('acao');
    
    $oPerfil = Administrativo_Model_Perfil::getById($iCodigoPerfil);
    
    $oPerfil->limparAcoes();
    
    $aPerfilAcao = array();
    
    foreach ($aAcoes as $id => $sAcao) {
      
      if ($sAcao === 'on') {
        
        $oAcao = Administrativo_Model_Acao::getById($id);
        $aPerfilAcao[] = $oAcao;
      }
    }
    
    if ($aPerfilAcao !== null) {
      
      $oPerfil->adicionaAcoes($aPerfilAcao);
      $iCodigoPerfil = $oPerfil->getId();
    }
  
    $this->_helper->getHelper('FlashMessenger')->addMessage(array('notice' => 'Permissões modificadas para o Perfil.'));
    $this->_redirector->gotoSimple('editar', 'perfil', 'administrativo', array('id' => $oPerfil->getId()));
  }

  public function setPerfilPerfisAction() {
    
    $iCodigoPerfil = $this->getRequest()->getParam('id');
  
    $aPerfis = $this->getRequest()->getParam('perfilperfis');
  
    $oPerfil = Administrativo_Model_Perfil::getById($iCodigoPerfil);
    $oPerfil->limparPerfis();
    
    $aPerfilPerfis = array();
     
    foreach ($aPerfis as $id => $sPerfil) {
      
      if ($sPerfil === 'on') {
        $aPerfilPerfis[] = Administrativo_Model_Perfil::getById($id);
      }
    }

    if ($aPerfilPerfis !== null) {
      
      $iCodigoPerfil = $oPerfil->getId();
      
      $oPerfil->adicionaPerfis($aPerfilPerfis);
    }
  
    $this->_helper->getHelper('FlashMessenger')->addMessage(array('notice' => 'Permissões para perfis modificadas para o Perfil.'));
    $this->_redirector->gotoSimple('editar', 'perfil', 'administrativo', array('id' => $oPerfil->getId()));
  }
    
  private function formPerfil($action = 'novo', $id = null, $values = array()) {
    
    $oForm = new Twitter_Bootstrap_Form_Horizontal();

    if ($id !== NULL) {
      $action .= "/id/{$id}";
    }

    $oForm->setAction($this->view->baseUrl('/administrativo/perfil/' . $action))->setMethod('post');

    if ($id !== NULL) {
      
      $oElm = $oForm->createElement('hidden', 'id');
      $oElm->setValue($id);
      $oForm->addElement($oElm);
    }

    $oElm = $oForm->createElement('text', 'nome');
    $oElm->setLabel('Nome');
    $oElm->setRequired();
    
    if (isset($values['nome'])) {
      $oElm->setValue($values['nome']);
    }
    
    $oForm->addElement($oElm);
    
    $oElm = $oForm->createElement('select', 'administrativo', array('multiOptions' => array('1' => 'Sim', '0'=>'Não')));
    $oElm->setLabel('Administrativo');

    if (isset($values['administrativo']) and ($values['administrativo'] == true)) {
      $oElm->setValue('1');
    } else {
      $oElm->setValue('0');
    }
    
    $oForm->addElement($oElm);
    
    $oForm->addElement('submit', 'submit', array(
      'label' => 'Salvar',
      'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_SUCCESS
    ));
    
    return $oForm;
  }

  private function formBusca($busca = '') {
    
    return new Twitter_Bootstrap_Form_Search(array(
      'inputName'   => 'busca',
      'value'       => $busca,
      'submitLabel' => 'Buscar',
      'action'      => $this->view->baseUrl('administrativo/perfil/index')
    ));
  }
}