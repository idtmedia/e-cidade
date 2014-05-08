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
 * Classe responsável pela comunicacao com o Ecidade dos dados referentes as Guias geradas para pagamento do ISS
 */
class Contribuinte_Model_GuiaEcidade {

  const SERVICO_TOMADO   = 'e';
  const SERVICO_PRESTADO = 's';

  /**
   * Gera a guia do prestador no eCidade
   *
   * @param                                $oContribuinte
   * @param Contribuinte_Model_Competencia $oCompetencia
   * @param DateTime                       $oDataPagamento
   * @return StdClass
   */
  public static function gerarGuiaNFSE($oContribuinte,
                                       Contribuinte_Model_Competencia $oCompetencia,
                                       DateTime $oDataPagamento) {

    $iMesCompetencia      = $oCompetencia->getMesComp();
    $iAnoCompetencia      = $oCompetencia->getAnoComp();
    $aNotas               = $oCompetencia->getNotas();
    $sDataPagamento       = $oDataPagamento->format('d/m/Y');
    $oUsuarioContribuinte = Administrativo_Model_UsuarioContribuinte::getById(
                                                                    $oContribuinte->getIdUsuarioContribuinte());
    $oGuiaGerada          = self::gerarGuia($oUsuarioContribuinte, $iAnoCompetencia, $iMesCompetencia, $aNotas,
                                            $sDataPagamento);
    $oGuia                = new Contribuinte_Model_Guia();
    $oGuia->gerarGuiaPrestador($oContribuinte, $oGuiaGerada, $oCompetencia, $oDataPagamento);

    $oGuiaGerada->arquivo_guia = self::salvarPdf($oGuiaGerada->debito->dados_boleto->arquivo_guia, 'guia_substituto');

    return $oGuiaGerada;
  }

  /**
   * Metodo responsavel por criar as guias dms do prestador
   *
   * @param Contribuinte_Model_Dms $oDms
   * @param                        $sDataPagamento
   * @return StdClass
   */
  public static function gerarGuiaDmsPrestador(Contribuinte_Model_Dms $oDms, $sDataPagamento) {

    $aNotas = array();

    foreach ($oDms->getDmsNotas() as $oDadosNota) {

      $oNota = new Contribuinte_Model_DmsNota($oDadosNota);

      // Ignora notas prestadas e retidas pelo tomador
      if ($oDms->getOperacao() == 's' && $oNota->getServicoImpostoRetido() == TRUE) {
        continue;
      }

      // Ignora notas tomadas e retidas pelo tomador
      if ($oDms->getOperacao() == 'e' && $oNota->getServicoImpostoRetido() == FALSE) {
        continue;
      }

      // Ignora notas anuladas, extraviadas ou canceladas
      if ($oNota->getStatus() == 5 || in_array($oNota->getSituacaoDocumento(), array('E', 'C'))) {
        continue;
      }

      // Ignora notas isentas
      if ($oNota->getEmiteGuia() == FALSE) {
        continue;
      }

      // Ignora notas com aliquota ou servico zerados
      if (floatval($oNota->getServicoAliquota()) <= 0) {
        continue;
      } else if (floatval($oNota->getServicoValorImposto()) <= 0) {
        continue;
      }

      $aNotas[$oNota->getId()] = $oNota;
    }

    $iMesCompetencia = $oDms->getMesCompetencia();
    $iAnoCompetencia = $oDms->getAnoCompetencia();

    $oUsuarioContribuinte = Administrativo_Model_UsuarioContribuinte::getById($oDms->getIdContribuinte());

    $oGuiaGerada = self::gerarGuia($oUsuarioContribuinte,
                                   $iAnoCompetencia,
                                   $iMesCompetencia,
                                   $aNotas,
                                   $sDataPagamento);

    $oDms->setCodigoPlanilha($oGuiaGerada->codigo_planilha);
    $oDms->setStatus('emitida');
    foreach ($oGuiaGerada->notas as $oNotaProcessada) {

      $oNota = $aNotas[$oNotaProcessada->codigo_documento];
      $oNota->setCodigoNotaPlanilha($oNotaProcessada->codigo_nota_planilha);
      $oNota->setNumpre($oGuiaGerada->debito_planilha);
    }
    $oDms->persist();
    $sDataPagamento = DBSeller_Helper_Date_Date::invertDate($sDataPagamento);
    $oGuia          = new Contribuinte_Model_Guia();
    $oContribuinte  = Administrativo_Model_UsuarioContribuinte::getContribuinte($oDms->getIdContribuinte());
    $oGuia->gerarGuiaDmsPrestador($oContribuinte, new DateTime($sDataPagamento), $iMesCompetencia,
                                  $iAnoCompetencia, $oGuiaGerada->debito, $oDms);

    $oGuiaGerada->arquivo_guia = self::salvarPdf($oGuiaGerada->debito->dados_boleto->arquivo_guia, 'guia_substituto');

    return $oGuiaGerada;
  }

  /**
   * Remite a guia no eCidade
   *
   * @param object   $oGuia
   * @param dateTime $oData
   * @return object
   */

  public static function reemitirGuia($oGuia, $oData) {

    if (is_string($oData)) {
      $oData = new DateTime($oData);
    }

    $aDados                           = array(
      'numero_debito' => $oGuia->getNumpre(),
      'data'          => $oData->format('Y-m-d')
    );
    $oRetornoWebService               = Contribuinte_Lib_Model_WebService::processar('reemitirGuia', $aDados);
    $oRetornoWebService->arquivo_guia = self::salvarPdf($oRetornoWebService->arquivo_guia, 'reemissao');

    return $oRetornoWebService;
  }

  /**
   * Gera a planilha de retencao no eCidade
   *
   * @param integer $iCpfCnpj
   * @param integer $iInscricaoMunicipal
   * @param integer $iAnoCompetencia
   * @param integer $iMesCompetencia
   * @throws Exception
   * @return integer
   */
  public static function gerarPlanilhaRetencao($iCpfCnpj, $iInscricaoMunicipal, $iAnoCompetencia, $iMesCompetencia) {

    throw new Exception('Metodo Deprecated Contribuinte_Model_GuiaEcidade::gerarPlanilhaRetencao');
    /*
    $iCpf  = strlen($iCpfCnpj) == 11 ? $iCpfCnpj : NULL;
    $iCnpj = strlen($iCpfCnpj) == 14 ? $iCpfCnpj : NULL;

    $aDados          = array(
      'cpf'               => $iCpf,
      'cnpj'              => $iCnpj,
      'inscricao_tomador' => $iInscricaoMunicipal,
      'competencia_ano'   => $iAnoCompetencia,
      'competencia_mes'   => $iMesCompetencia
    );
    $iCodigoPlanilha = Contribuinte_Lib_Model_WebService::processar('gerarPlanilhaRetencao', $aDados);

    return $iCodigoPlanilha;
    */
  }

  /**
   * Lanca a planilha de retencao no eCidade
   *
   * @param $iCodigoPlanilha
   * @param $oNota
   * @param $dData
   * @param $dServico
   * @return mixed
   * @throws Exception
   */
  public static function lancarPlanilhaRetencao($iCodigoPlanilha, $oNota, $dData, $dServico) {

    throw new Exception('Metodo Deprecated Contribuinte_Model_GuiaEcidade::lancarPlanilhaRetencao');
    /*
    $aDados = array(
      'cod_planilha'           => $iCodigoPlanilha,
      'cnpj_prestador'         => $oNota->getP_cnpjcpf(),
      'inscricao_prestador'    => $oNota->getP_im(),
      'nome'                   => $oNota->getP_razao_social(),
      'numero_nf'              => $oNota->getNota(),
      'data_nf'                => $oNota->getDt_nota()->format('Y-m-d'),
      'servico_prestado'       => $dServico, //$dServico,
      'valor_servico_prestado' => $oNota->getS_vl_servicos(),
      'valor_deducao'          => $oNota->getS_vl_deducoes(),
      'valor_base_calculo'     => $oNota->getS_vl_bc(),
      'aliquota'               => $oNota->getS_vl_aliquota(),
      'valor_imposto_retido'   => $oNota->getS_vl_iss(),
      'ano_competencia'        => $oNota->getAno_comp(),
      'mes_competencia'        => $oNota->getMes_comp(),
      'data_pagamento'         => $dData->format('Y-m-d'),
      'retido'                 => TRUE
    );

    return parent::processar('lancarPlanilhaRetencao', $aDados);
    */
  }

  /**
   * Lançamento dos valores das receitas na planilha
   *
   * @param $oDms
   * @param $oDmsNota
   * @param $dDataPagamento
   * @return mixed
   * @throws Exception
   */
  public static function lancarPlanilhaDmsRetencao($oDms, $oDmsNota, $dDataPagamento) {

    throw new Exception('Metodo Deprecated Contribuinte_Model_GuiaEcidade::lancarPlanilhaDmsRetencao');
    /*
    $aDadosPlanilhaRetencao = array(
      'codigo_nota_planilha'   => $oDmsNota->getCodigoNotaPlanilha(),
      'codigo_planilha'        => $oDms->getCodigoPlanilha(),
      'ano_competencia'        => $oDms->getAnoCompetencia(),
      'mes_competencia'        => $oDms->getMesCompetencia(),
      'numero_nf'              => $oDmsNota->getNotaNumero(),
      'data_nf'                => $oDmsNota->getNotaData()->format('Y-m-d'),
      'valor_base_calculo'     => $oDmsNota->getServicoBaseCalculo(),
      'valor_deducao'          => $oDmsNota->getServicoValorDeducao(),
      'valor_imposto_retido'   => $oDmsNota->getServicoValorImposto(),
      'valor_servico_prestado' => $oDmsNota->getServicoValorPagar(),
      'aliquota'               => $oDmsNota->getServicoAliquota(),
      'data_pagamento'         => $dDataPagamento,
      'retido'                 => (bool) $oDmsNota->getServicoImpostoRetido(),
      'situacao'               => $oDmsNota->getSituacaoDocumento() == 'N' ? 0 : 1,
      'status'                 => 1,
      'servico_prestado'       => $oDmsNota->getDescricaoServico(),
    );
    if ($oDms->getOperacao() == self::SERVICO_PRESTADO) {

      $aDadosPlanilhaRetencao['cnpj_prestador']      = $oDmsNota->getTomadorCpfCnpj();
      $aDadosPlanilhaRetencao['inscricao_prestador'] = $oDmsNota->getTomadorInscricaoMunicipal();
      $aDadosPlanilhaRetencao['nome']                = $oDmsNota->getTomadorRazaoSocial();
      $aDadosPlanilhaRetencao['operacao']            = 2;
    } else {

      $aDadosPlanilhaRetencao['operacao']            = 1;
      $aDadosPlanilhaRetencao['cnpj_prestador']      = $oDmsNota->getPrestadorCpfCnpj();
      $aDadosPlanilhaRetencao['inscricao_prestador'] = $oDmsNota->getPrestadorInscricaoMunicipal();
      $aDadosPlanilhaRetencao['nome']                = $oDmsNota->getPrestadorRazaoSocial();
    }

    return Contribuinte_Lib_Model_WebService::processar('lancarPlanilhaRetencao', $aDadosPlanilhaRetencao);
    */
  }

  /**
   * Anula os valores das receitas na planilha do eCidade
   *
   * @param $iCodigoNotaPlanilha
   * @return mixed
   */
  public static function anularPlanilhaDmsRetencao($iCodigoNotaPlanilha) {

    $aDadosPlanilhaRetencao = array(
      'codigo_nota_planilha' => $iCodigoNotaPlanilha
    );

    return Contribuinte_Lib_Model_WebService::processar('anularNotaPlanilhaRetencao', $aDadosPlanilhaRetencao);
  }

  /**
   * Gera a guia do tomador no eCidade
   *
   * @param $iCodigoPlanilha
   * @deprecated
   * @return mixed
   * @throws Exception
   */
  public static function gerarGuiaTomador($iCodigoPlanilha) {

    throw new Exception('Metodo Deprecated Contribuinte_Model_GuiaEcidade::gerarGuiaTomador');
    /*
    $oData                            = new DateTime();
    $aDados                           = array(
      'codigo_planilha' => $iCodigoPlanilha,
      'data'            => $oData->format('Y-m-d')
    );
    $oRetornoWebService               = Contribuinte_Lib_Model_WebService::processar('gerarGuiaTomador', $aDados);
    $oRetornoWebService->arquivo_guia = self::salvarPdf($oRetornoWebService->arquivo_guia, 'guia_substituto');

    return $oRetornoWebService;
    */
  }

  /**
   * Atualiza a situação da guia no eCidade
   *
   * @param Integer $iNumpre
   * @param Integer $iNumpar
   * @return null
   */
  public static function atualizaSituacao($iNumpre, $iNumpar) {

    $aFiltros  = array(
      'numpre' => $iNumpre,
      'numpar' => $iNumpar
    );
    $aCampos   = array(
      'status'
    );
    $aSituacao = Contribuinte_Lib_Model_WebService::consultar('getSituacaoGuia', array($aFiltros, $aCampos));

    if (!is_array($aSituacao)) {
      return NULL;
    }

    return $aSituacao[0]->status;
  }

  /**
   * Salva o PDF gerado
   *
   * @param $sArquivoBase64
   * @param $sNomeArquivo
   * @return mixed <null, object>
   */
  private static function salvarPdf($sArquivoBase64, $sNomeArquivo) {

    $sArquivo = tempnam(TEMP_PATH . '/', $sNomeArquivo) . '.pdf';
    fopen($sArquivo, "wb+");
    file_put_contents($sArquivo, base64_decode($sArquivoBase64));
    $oArquivo = end(explode('/', $sArquivo));

    return $oArquivo;
  }

  /**
   * Anula planilha de retenção no eCidade
   *
   * @param integer $iCodigoPlanilha
   * @param string  $sMotivo
   * @return object
   */

  public static function anularPlanilha($iCodigoPlanilha, $sMotivo) {

    $aDados   = array(
      'motivo_anulacao' => $sMotivo,
      'codigo_planilha' => $iCodigoPlanilha
    );
    $sRetorno = Contribuinte_Lib_Model_WebService::processar('anularPlanilhaRetencao', $aDados);

    return $sRetorno;
  }

  /**
   * @param Administrativo_Model_UsuarioContribuinte $oContribuinte
   * @param int                                      $iAnoCompetencia
   * @param int                                      $iMesCompetencia
   * @param array                                    $aNotas
   * @param string                                   $sDataPagamento
   * @return mixed
   * @throws Exception
   */
  public static function gerarGuia(Administrativo_Model_UsuarioContribuinte $oContribuinte,
                                   $iAnoCompetencia,
                                   $iMesCompetencia,
                                   $aNotas,
                                   $sDataPagamento) {

    $oValidateDate  = new Zend_Validate_Date();
    $sData          = DBSeller_Helper_Date_Date::invertDate($sDataPagamento);
    $sDataValidacao = DBSeller_Helper_Date_Date::invertDate($sDataPagamento, '');

    if ($sDataValidacao < date('Ymd')) {
      throw new Exception('Informe uma data posterior a data atual!');
    }

    if ($oValidateDate->isValid($sDataPagamento)) {
      $oData = new DateTime($sData);
    } else {
      throw new Exception('Informe uma data para pagamento válida!');
    }

    $oGuiaGerar                      = new StdClass();
    $oGuiaGerar->inscricao_municipal = $oContribuinte->getIm();
    $oGuiaGerar->cpf_cnpj            = $oContribuinte->getCnpjCpf();
    $oGuiaGerar->numcgm              = $oContribuinte->getCgm();
    $oGuiaGerar->mes_competencia     = $iMesCompetencia;
    $oGuiaGerar->ano_competencia     = $iAnoCompetencia;
    $aListaNotas                     = array();

    foreach ($aNotas as $oDocumentoNota) {

      $oNota                         = new stdClass();
      $oNota->numero_nota_fiscal     = $oDocumentoNota->getNotaNumero();
      $oNota->codigo_documento       = $oDocumentoNota->getId();
      $oNota->data_nota_fiscal       = $oDocumentoNota->getNotaData()->format('Y-m-d');
      $oNota->serie_nota_fiscal      = $oDocumentoNota->getNotaSerie();
      $oNota->valor_base_calculo     = $oDocumentoNota->getServicoBaseCalculo();
      $oNota->valor_deducao          = $oDocumentoNota->getServicoValorDeducao();
      $oNota->valor_imposto_retido   = $oDocumentoNota->getServicoValorImposto();
      $oNota->valor_servico_prestado = $oDocumentoNota->getServicoValorPagar();
      $oNota->aliquota               = $oDocumentoNota->getServicoAliquota();
      $oNota->data_pagamento         = $oData->format('Y-m-d');
      $oNota->retido                 = $oDocumentoNota->getServicoImpostoRetido();
      $oNota->situacao               = $oDocumentoNota->getSituacaoDocumento() == 'N' ? '0' : '1';
      $oNota->status                 = 1;
      $oNota->servico_prestado       = $oDocumentoNota->getDescricaoServico();

      // Serviços prestados
      if ($oDocumentoNota->getOperacao() == self::SERVICO_PRESTADO) {

        $oNota->cnpj_prestador      = $oDocumentoNota->getPrestadorCpfCnpj();
        $oNota->inscricao_prestador = $oDocumentoNota->getPrestadorInscricaoMunicipal();
        $oNota->nome                = $oDocumentoNota->getPrestadorRazaoSocial();
        $oNota->operacao            = 2;
      } else {

        $oNota->cnpj_prestador      = $oDocumentoNota->getTomadorCpfCnpj();
        $oNota->inscricao_prestador = $oDocumentoNota->getTomadorInscricaoMunicipal();
        $oNota->nome                = $oDocumentoNota->getTomadorRazaoSocial();
        $oNota->operacao            = 1;
      }

      // Limita para o tamanho do campo no ecidade
      if (strlen($oNota->nome) > 60) {
        $oNota->nome = substr($oNota->nome, 57) . '...';
      }

      // Adiciona a nota na lista
      $aListaNotas[] = $oNota;
    }

    $oGuiaGerar->notas = $aListaNotas;
    $aDados            = array(
      'planilha'       => $oGuiaGerar,
      'data_pagamento' => $oData->format('Y-m-d')
    );
    $oRetorno          = Contribuinte_Lib_Model_WebService::processar('geraDebitoIssContribribuinte', $aDados);

    return $oRetorno;
  }
}