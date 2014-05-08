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
 * Controlador para geração e consulta de Guias do contribuinte
 *
 * @package Contribuinte/Guia
 */

/**
 * @package Contribuinte/Guia
 */
class Contribuinte_GuiaController extends Contribuinte_Lib_Controller_AbstractController {

  /**
   * Action para geração das guias
   */
  public function indexAction() {

    $oContribuinte = $this->_session->contribuinte;

    //$this->view->aContribuintes = $oContribuinte->getContribuintes();
    $this->view->oCompetencias = Contribuinte_Model_Competencia::getByContribuinte($oContribuinte);
  }

  /**
   * Fecha a competência para as Notas Fiscais (NFSE)
   */
  public function fechaCompetenciaAction() {

    parent::noTemplate();

    $iMes          = $this->getRequest()->getParam('mes');
    $iAno          = $this->getRequest()->getParam('ano');
    $oContribuinte = $this->_session->contribuinte;
    $oCompetencia  = new Contribuinte_Model_Competencia($iAno, $iMes, $oContribuinte);
    
    $oCompetencia->getNotas();
    $oCompetencia->setTotaisCompetencia();
    
    $oFormCompetencia = new Contribuinte_Form_GuiaCompetencia();
    $oFormCompetencia->setAction('/contribuinte/guia/fecha-competencia');
    $oFormCompetencia->preenche($oCompetencia, NULL);
    $oFormCompetencia->getElement('data_guia')->setValue(date('d/m/Y'));

    $this->view->competencia = $oCompetencia;
    $this->view->form        = $oFormCompetencia;

    if ($this->getRequest()->isPost()) {

      $this->view->arquivo = '';
      $this->view->guia    = '';

      try {

        $lTemGuiaEmitida = Contribuinte_Model_Guia::existeGuia($oContribuinte,
                                                               $oCompetencia->getMesComp(),
                                                               $oCompetencia->getAnoComp(),
                                                               Contribuinte_Model_Guia::$DOCUMENTO_ORIGEM_NFSE);

        if ($lTemGuiaEmitida) {
          throw new Exception('Guia já emitida.');
        }

        $sDataInvertida = DBSeller_Helper_Date_Date::invertDate($this->getRequest()->getParam('data_guia'));

        try {

          $oDataPagamento = new DateTime($sDataInvertida);
          $oGuia          = Contribuinte_Model_GuiaEcidade::gerarGuiaNFSE($oContribuinte,
                                                                          $oCompetencia,
                                                                          $oDataPagamento);

          $this->view->arquivo = $oGuia->arquivo_guia;
          $this->view->guia    = '';
        } catch (Exception $oErro) {
          $this->view->mensagem_erro = $oErro->getMessage();
        }
      } catch (Exception $oErro) {
        $this->view->mensagem_erro = $oErro->getMessage();
      }
    }
  }

  /**
   * Realiza a reemisao das guias de NFSE
   */
  public function reemitirAction() {

    parent::noTemplate();

    $aDadosRequest = $this->getRequest()->getParams();
    $oContribuinte = $this->_session->contribuinte;
    $oGuia         = Contribuinte_Model_Guia::getById($aDadosRequest['guia']);
    $oCompetencia  = new Contribuinte_Model_Competencia($oGuia->getAnoComp(), $oGuia->getMesComp(), $oContribuinte);

    $oCompetencia->getNotas();
    $oCompetencia->setTotaisCompetencia();
    
    $oFormCompetencia = new Contribuinte_Form_GuiaCompetencia();
    $oFormCompetencia->setAction('/contribuinte/guia/reemitir');
    $oFormCompetencia->preenche($oCompetencia, $oGuia);
    $oFormCompetencia->getElement('data_guia')->setValue(date('d/m/Y'));

    // Validação do forumlário e geração da guia
    if ($this->getRequest()->isPost()) {

      if ($oFormCompetencia->isValidPartial($aDadosRequest)) {

        $sDataGuia = str_replace('/', '-', $aDadosRequest['data_guia']);
        $oDataGuia = new DateTime($sDataGuia);

        if ($oDataGuia->format('Ymd') < date('Ymd')) {
          $this->view->mensagem_erro = $this->translate->_('Informe uma Data de Pagamento posterior a data atual.');
        } else {

          $aNovaGuia = $oGuia->reemitir($oDataGuia->format('d/m/Y'));

          $this->view->arquivo = $aNovaGuia['arquivo'];
          $this->view->guia    = $aNovaGuia['objeto'];
        }
      } else {
        $this->view->mensagem_erro = $this->translate->_('Preencha os dados corretamente.');
      }
    }

    $this->view->form = $oFormCompetencia;
  }

  /**
   * Reemite a guia de pagamento das Guias de DMS
   */
  public function reemitirDmsGuiaAction() {

    parent::noTemplate();

    $iIdGuia    = $this->getRequest()->getParam('guia', NULL);
    $sDataGuia  = $this->getRequest()->getParam('data_guia', NULL);
    $oGuia      = Contribuinte_Model_Guia::getById($iIdGuia);
    $oDadosGuia = $oGuia->getEntity();

    if ($this->getRequest()->isPost()) {

      $oValidaDatas   = new Zend_Validate_Date();
      $sDataValidacao = DBSeller_Helper_Date_Date::invertDate($sDataGuia, '');

      if ($sDataValidacao < date('Ymd')) {
        $this->view->message = $this->translate->_('Informe uma data posterior a data atual.');
      } else if (!$oValidaDatas->isValid($sDataGuia)) {
        $this->view->message = $this->translate->_('Informe uma data para pagamento válida.');
      } else {

        $aNovaGuia = $oGuia->reemitir($sDataGuia);

        $this->view->arquivo = $aNovaGuia['arquivo'];
        $this->view->guia    = $aNovaGuia['objeto'];
      }
    }

    $oFormCompetencia = new Contribuinte_Form_GuiaCompetencia();
    $oFormCompetencia->setAction('/contribuinte/guia/reemitir-dms-guia');
    $oFormCompetencia->removeElement('total_iss');
    $oFormCompetencia->removeElement('total_servico');
    $oFormCompetencia->getElement('data_guia')->setValue(date('d/m/Y'));
    $oFormCompetencia->preencheDms($oDadosGuia);

    $this->view->form = $oFormCompetencia;
  }

  /**
   * Action para listagem dos dados da competencia das notas Fiscais
   */
  public function competenciaAction() {

    $iMes          = $this->getRequest()->getParam('mes');
    $iAno          = $this->getRequest()->getParam('ano');
    $oContribuinte = $this->_session->contribuinte;
    $oCompetencia  = new Contribuinte_Model_Competencia($iAno, $iMes, $oContribuinte);

    $this->view->contribuinte = $oContribuinte;
    $oCompetencia->getNotas();
    $oCompetencia->setTotaisCompetencia();
    
    $this->view->competencia  = $oCompetencia;
  }

  /**
   * Lista os dados da competencia das DMS
   */
  public function competenciaDmsAction() {

    $iMes           = $this->getRequest()->getParam('mes');
    $iAno           = $this->getRequest()->getParam('ano');
    $oContribuinte  = $this->_session->contribuinte;
    $aContribuintes = $oContribuinte->getContribuintes();

    $this->view->oCompetenciaDMS = Contribuinte_Model_Dms::getDadosPorCompetencia($aContribuintes, $iAno, $iMes);
  }

  /**
   * Fechamento das notas de Tomadores
   *
   * @deprecated
   */
  public function tomadorAction() {

    throw new Exception('Metodo Deprecated Contribuinte_GuiaController::tomadorAction');
  }

  /**
   * Lista as notas do tomador
   *
   * @deprecated
   */
  public function notasTomadorAction() {

    throw new Exception('Metodo Deprecated Contribuinte_GuiaController::notasTomadorAction');
  }

  /**
   * Action para listagem das guias com substituição tributária
   */
  public function substitutoAction() {

    $oGuia = new Contribuinte_Model_Guia();

    $this->view->notas_retidas = Contribuinte_Model_Nota::getNotasRetidasSemGuia($this->_session->im);
    $this->view->guias         = $oGuia->getSubstituto($this->_session->im);
  }

  /**
   * Action para listagem das notas de substituicao tributaria
   */
  public function notasSubstitutoAction() {

    $iId   = $this->getRequest()->getParam('id');
    $oGuia = Contribuinte_Model_Guia::getById($iId);

    $this->view->guia = $oGuia;
  }

  /**
   * Mostra a tela para Declaracao sem Movimento
   */
  public function declaracaoSemMovimentoAction() {

    // Formulario para pesquisa de declaracoes sem movimento
    $oFormPesquisar = new Contribuinte_Form_DmsCompetencia();
    $oFormPesquisar->setAction($this->view->baseUrl('/contribuinte/guia/declaracao-sem-movimento-listar'));
    $oFormPesquisar->removeElement('mes_competencia');
    $oFormPesquisar->getElement('btn_competencia')->setLabel('Pesquisar')->setName('btn_pesquisar');
    $oFormPesquisar->getDisplayGroup('group_competencia')->setLegend('Pesquisar Declarações');

    // Formulario para geracao de declaracao sem movimento
    $oFormGerar = new Contribuinte_Form_DmsCompetencia();
    $oFormGerar->setAction($this->view->baseUrl('/contribuinte/guia/declaracao-sem-movimento-gerar'));
    $oFormGerar->removeElement('ano_competencia');
    $oFormGerar->getElement('btn_competencia')->setLabel('Gerar')->setName('btn_gerar');
    $oFormGerar->getDisplayGroup('group_competencia')->setLegend('Gerar Declaração');
    $oFormGerar->removerMesesComMovimentacaoDeNotas();

    // Envia os parametros para a view
    $this->view->oFormPesquisar = $oFormPesquisar;
    $this->view->oFormGerar     = $oFormGerar;
  }

  /**
   * Retorna em HTML a lista de Declaracoes sem Movimento por inscricao municipal e ano do e-cidade
   */
  public function declaracaoSemMovimentoListarAction() {

    parent::noTemplate(); // Retorna apenas o html da view

    $iAno          = $this->_getParam('ano_competencia');
    $oContribuinte = $this->_session->contribuinte;
    $oCompetencia  = new Contribuinte_Model_Competencia($iAno, NULL, $oContribuinte);

    // Envia os parametros para a view
    $this->view->aDeclaracaoSemMovimento = $oCompetencia->getDeclaracaoSemMovimento();
  }

  /**
   * Gera a declaracao mensal Sem Movimento para o e-cidade
   */
  public function declaracaoSemMovimentoGerarAction() {

    try {

      $iMes          = intval($this->_getParam('mes_competencia'));
      $iAno          = date('Y');
      $oContribuinte = $this->_session->contribuinte;

      $oCompetencia     = new Contribuinte_Model_Competencia($iAno, $iMes, $oContribuinte);
      $lGerarComptencia = $oCompetencia->gerarDeclaracaoSemMovimento();

      if (!$lGerarComptencia) {
        throw new Exception($this->translate->_('Erro ao gerar declaração'));
      }

      $aRetornoJson['status']  = TRUE;
      $aRetornoJson['reload']  = TRUE;
      $aRetornoJson['success'] = $this->translate->_('Operação efetuada com sucesso');

      echo $this->getHelper('json')->sendJson($aRetornoJson);
    } catch (Exception $oErro) {

      $aRetornoJson['status']  = FALSE;
      $aRetornoJson['error'][] = sprintf($this->translate->_('Erro: %s'), $oErro->getMessage());

      echo $this->getHelper('json')->sendJson($aRetornoJson);
    }
  }

  /**
   * Guia DMS Entrada
   */
  public function emissaoDmsEntradaAction() {

    $oForm = new Contribuinte_Form_DmsCompetencia();
    $oForm->setAction('/contribuinte/guia/emissao-dms-lista/tipo/' . Contribuinte_Model_Dms::ENTRADA);
    $oForm->getElement('btn_competencia')->setLabel($this->translate->_('Pesquisar'));

    $this->view->oForm = $oForm;
  }

  /**
   * Guia DMS Saida
   */
  public function emissaoDmsAction() {

    $oForm = new Contribuinte_Form_DmsCompetencia();
    $oForm->setAction('/contribuinte/guia/emissao-dms-lista/tipo/' . Contribuinte_Model_Dms::SAIDA);
    $oForm->getElement('btn_competencia')->setLabel($this->translate->_('Pesquisar'));

    $this->view->oForm = $oForm;
  }

  /**
   * Modal para geração de Guia de DMS
   */
  public function dmsGerarModalAction() {

    // Retorna apenas o html da view
    parent::noTemplate();

    $this->view->id_dms             = $this->_getParam('id_dms', NULL);
    $this->view->url_guia_pagamento = $this->_getParam('url_guia_pagamento', NULL);
  }

  /**
   * Gera a guia da DMS
   */
  public function dmsGerarAction() {

    $sDataPagamento         = $this->_getParam('data_pagamento', NULL);
    $iCodigoDms             = $this->_getParam('id_dms', NULL);
    $aRetornoJson           = array();
    $aRetornoJson['status'] = TRUE;

    try {

      $oDms  = Contribuinte_Model_Dms::getById($iCodigoDms);
      $oGuia = Contribuinte_Model_GuiaEcidade::gerarGuiaDmsPrestador($oDms, $sDataPagamento);

      $aRetornoJson['url']     = $oGuia->arquivo_guia;
      $aRetornoJson['success'] = $this->translate->_('Guia emitida com sucesso.');
    } catch (Exception $oError) {

      $aRetornoJson['status']  = FALSE;
      $aRetornoJson['error'][] = $oError->getMessage();
    }

    echo $this->getHelper('json')->sendJson($aRetornoJson);
  }

  /**
   * Lista de DMS Entrada
   */
  public function emissaoDmsEntradaListaAction() {

    parent::noTemplate();

    unset($this->view->aDms);
    unset($aListaDms);

    $iMes          = $this->_getParam('mes_competencia', NULL);
    $iAno          = $this->_getParam('ano_competencia', NULL);
    $oContribuinte = $this->_session->contribuinte;
    $oDms          = new Contribuinte_Model_Dms();
    $aResultado    = $oDms->getDMSSemGuiaNaCompetencia($oContribuinte, $iAno, $iMes, Contribuinte_Model_Dms::ENTRADA);

    // Filtra as notas isentas
    foreach ($aResultado as $oDms) {

      $lEmiteGuia = FALSE;

      foreach ($oDms->getDmsNotas() as $oNota) {

        // Verifica se deve ser emitida a guia
        if ($oNota->getEmiteGuia()) {
          $lEmiteGuia = TRUE;
        }
      }

      // Não mostra o dms se não tiver notas para emitir guia
      if ($lEmiteGuia) {
        $aListaDms[] = $oDms;
      }
    }

    $this->view->aDms = isset($aListaDms) ? $aListaDms : array();
  }

  /**
   * Lista de DMS Saida
   */
  public function emissaoDmsListaAction() {

    parent::noTemplate();

    $iMes          = $this->_getParam('mes_competencia', NULL);
    $iAno          = $this->_getParam('ano_competencia', NULL);
    $sDmsTipo      = $this->_getParam('tipo', NULL);
    $oContribuinte = $this->_session->contribuinte;
    $oDms          = new Contribuinte_Model_Dms();
    $aResultado    = $oDms->getDMSSemGuiaNaCompetencia($oContribuinte, $iAno, $iMes, $sDmsTipo);

    // Filtra as notas isentas
    foreach ($aResultado as $oDms) {

      $iEmiteGuia = 0;

      foreach ($oDms->getDmsNotas() as $oNota) {

        // nota: anulada, extraviada ou cancelada
        if ($oNota->getStatus() == 5 || in_array($oNota->getSituacaoDocumento(), array('E', 'C'))) {
          continue;
        }

        if ($oNota->getEmiteGuia()) {
          $iEmiteGuia++;
        }
      }

      if ($iEmiteGuia > 0) {
        $aListaDms[] = $oDms;
      }
    }

    $this->view->aDms         = isset($aListaDms) ? $aListaDms : array();
    $this->view->urlEdicaoDms = '/contribuinte/dms/emissao-manual-saida';

    if ($sDmsTipo == Contribuinte_Model_Dms::ENTRADA) {
      $this->view->urlEdicaoDms = '/contribuinte/dms/emissao-manual-entrada';
    }
  }

  /**
   * Consulta as guias de nota
   */
  public function consultaEmissaoNotaAction() {

    $this->consultaEmissao(Contribuinte_Model_ContribuinteAbstract::TIPO_EMISSAO_NOTA);
  }

  /**
   * Consulta as guias de DMS
   */
  public function consultaEmissaoDmsAction() {

    $this->consultaEmissao(Contribuinte_Model_ContribuinteAbstract::TIPO_EMISSAO_DMS);
  }

  /**
   * consulta as guias de um contribuinte
   */
  protected function consultaEmissao($iTipoEmissao) {

    $this->_helper->viewRenderer('consulta');

    $oContribuinte            = $this->_session->contribuinte;
    $aGuias                   = Contribuinte_Model_Guia::getGuiasByDocumentoOrigem($oContribuinte, $iTipoEmissao);
    $this->view->iTipoEmissao = $iTipoEmissao;
    $this->view->oGuias       = $aGuias;
  }
}