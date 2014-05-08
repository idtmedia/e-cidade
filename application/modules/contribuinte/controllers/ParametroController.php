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
 * Classe controller para as acoes de cadastro de parametros dos contribuintes do municpio
 *
 * @author dbseller
 * @package Contribuinte
 * @subpackage Controller
 */
class Contribuinte_ParametroController extends Contribuinte_Lib_Controller_AbstractController {

  /**
   * Metodo para renderizar o formulario de cadastro/alteracao dos parametros do contribuinte.
   *
   * Retorna para a view a instancia do formulario Contribuinte_Form_ParametrosContribuinte
   */
  public function contribuinteAction() {
    
    $oForm                  = new Contribuinte_Form_ParametrosContribuinte();
    $oParametroContribuinte = $this->buscaParametroContribuinte($this->view->contribuinte->getIdUsuarioContribuinte());
    $aDados = $this->getRequest()->getPost();
    
    if ($this->getRequest()->isPost() && $oForm->isValid($aDados)) {

      try {
        
        $oDoctrine = Zend_Registry::get('em');
        $oDoctrine->getConnection()->beginTransaction();
        $oParametroContribuinte->setAvisofimEmissaoNota($aDados["avisofim_emissao_nota"]);
        $oParametroContribuinte->setCofins(DBSeller_Helper_Number_Format::toDataBase($aDados["cofins"]));
        $oParametroContribuinte->setCsll(DBSeller_Helper_Number_Format::toDataBase($aDados["csll"]));
        $oParametroContribuinte->setIdContribuinte($this->view->contribuinte->getIdUsuarioContribuinte());
        $oParametroContribuinte->setInss(DBSeller_Helper_Number_Format::toDataBase($aDados["inss"]));
        $oParametroContribuinte->setIr(DBSeller_Helper_Number_Format::toDataBase($aDados["ir"]));
        $oParametroContribuinte->setMaxDeducao(DBSeller_Helper_Number_Format::toDataBase($aDados["max_deducao"]));
        $oParametroContribuinte->setPis(DBSeller_Helper_Number_Format::toDataBase($aDados["pis"]));
        $oParametroContribuinte->salvar();
        $oDoctrine->getConnection()->commit();
        $this->view->messages[] = array('success' => 'Parâmetros modificados com sucesso.');
      } catch (Exception $oErro) {
        
        $oDoctrine->getConnection()->rollback();
        $this->view->messages[] = array('error' => $oErro->getMessage());
      }
    }
    
    $oDados->im                     = $this->view->contribuinte->getInscricaoMunicipal();
    $oDados->nome_contribuinte      = $this->view->contribuinte->getNome();
    $oDados->avisofim_emissao_nota  = $oParametroContribuinte->getAvisofimEmissaoNota();
    $oDados->cofins                 = $oParametroContribuinte->getCofins();
    $oDados->csll                   = $oParametroContribuinte->getCsll();
    $oDados->inss                   = $oParametroContribuinte->getInss();
    $oDados->ir                     = $oParametroContribuinte->getIr();
    $oDados->max_deducao            = $oParametroContribuinte->getMaxDeducao();
    $oDados->pis                    = $oParametroContribuinte->getPis();
    $oForm->preenche($oDados);
    $this->view->form = $oForm;
  }
  
  /**
   * Realiza a busca dos dados do contribuinte
   */
  public function buscaContribuinteAction() {
    
    $this->setAjaxContext('getContribuinte');
    
    $dados = Contribuinte_Model_Contribuinte::getByIm($this->getRequest()->getParam('term'));
    
    if (empty($dados) || $dados == null) {
      
      echo json_encode(null);
      
      return;
    }
    
    $parametro = $this->buscaParametroContribuinte($dados[0]->attr('inscricao'));
    
    if ($parametro === null || empty($parametro)) {
      
      echo json_encode(null);
      
      return;
    }
    
    $retorno = new stdClass();
    
    $retorno->dados      = $dados[0]->toArray();
    $retorno->parametros = $parametro->toJson();
    
    echo json_encode($retorno);
  }
  
  /**
   * Retorna o parametro cadastrado para contribuinte
   * @param integer $iCodigoContribuinte Codigo do Contribuinte
   * @return Contribuinte_Model_ParametroContribuinte Instancia do model de parametros
   */
  public static function buscaParametroContribuinte($iCodigoContribuinte) {
    
    $parametro = Contribuinte_Model_ParametroContribuinte::getById($iCodigoContribuinte);
    
    if ($parametro == null) {
      
      $parametro = new Contribuinte_Model_ParametroContribuinte();
      $parametro->setIm($im);
    }
    
    return $parametro;
  }
}