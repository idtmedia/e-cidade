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
 * Model para o controle da compentencia das guias
 *
 * @author dbseller
 * @package Contribuinte
 * @subpackage Model
 */
class Contribuinte_Model_Competencia {
  
  private $oContribuinte       = NULL;
  private $aNotas              = array();
  private $aGuia               = array();
  private $iMesCompetencia     = NULL;
  private $iAnoCompetencia     = NULL;
  private $fTotalValorServicos = NULL;
  private $fTotalValorIss      = NULL;
  private $lExisteGuia         = FALSE;
  
  /**
   * Retorna EntityManager do Doctrine
   *
   * @return \Doctrine\ORM\EntityManager
   */
  protected static function getEm() {
    return Zend_Registry::get('em');
  }

  /**
   * Retorna o objeto relativo a compentência do contribuinte
   *
   * @param Contribuinte_Model_ContribuinteAbstract $oContribuinte
   * @return Contribuinte_Model_Competencia[]
   */
  public static function getByContribuinte(Contribuinte_Model_ContribuinteAbstract $oContribuinte) {
    
    $oEntityManager = self::getEm();
    $sSql           = 'SELECT DISTINCT 
                              nota.ano_comp, nota.mes_comp,
                              SUM(nota.s_vl_iss) as s_vl_iss , SUM(nota.s_vl_servicos) as s_vl_servicos
                         FROM Contribuinte\Nota nota
                        WHERE nota.id_contribuinte    in(:id_contribuinte) AND
                              nota.cancelada          = false AND
                              nota.natureza_operacao  = 1 AND
                              nota.s_vl_iss           > 0 AND
                              nota.s_dados_iss_retido = 1 AND
                              nota.emite_guia         = true AND
                              nota.importada = false
                     group by nota.ano_comp, nota.mes_comp
                     ORDER BY nota.ano_comp,
                              nota.mes_comp DESC';
    
    $oQuery = $oEntityManager->createQuery($sSql);
    $oQuery->setParameter('id_contribuinte', $oContribuinte->getContribuintes());

    $aResultados = $oQuery->getResult();
    
    foreach ($aResultados as $aCompetencia) {
      
      $oCompetencia = new self($aCompetencia['ano_comp'], $aCompetencia['mes_comp'], $oContribuinte);
      $oCompetencia->setTotalIss($aCompetencia['s_vl_iss']);
      $oCompetencia->setTotalServico($aCompetencia['s_vl_servicos']);
      
      $lExisteGuia = Contribuinte_Model_Guia::existeGuia($oContribuinte, $aCompetencia['mes_comp'], $aCompetencia['ano_comp']);
      $oCompetencia->setExisteGuia($lExisteGuia);
      
      $aRetorno[] = $oCompetencia;
    }
    
    return $aRetorno;
  }

  /**
   * Instancia a compentencia para um Contribuinte
   *
   * @param integer $iAnoCompetencia Ano da competencia
   * @param integer $iMesCompetencia Mes da Competencia
   * @param Contribuinte_Model_ContribuinteAbstract $oContribuinte Instância do contribuinte
   */
  public function __construct($iAnoCompetencia,
                              $iMesCompetencia, Contribuinte_Model_ContribuinteAbstract $oContribuinte) {
    
    $this->iAnoCompetencia = $iAnoCompetencia;
    $this->iMesCompetencia = $iMesCompetencia;
    $this->oContribuinte   = $oContribuinte;    
  }
  
  
  public function setTotaisCompetencia() {

    $fTotalIss     = 0;
    $fTotalServico = 0;
    
    foreach ($this->aNotas as $oNota) {
      
      $fTotalIss     += $oNota->getS_vl_iss();
      $fTotalServico += $oNota->getS_vl_servicos();
    }
    
    $this->setTotalIss($fTotalIss);
    $this->setTotalServico($fTotalServico);
  }

  /**
   * Soma ISS de todas as notas da competência
   *
   * @return number
   */
  public function getTotalIss() {
    return $this->fTotalValorIss;
  }
  
  /**
   * Soma do valor de todas as notas da competência
   *
   * @return number
   */
  public function getTotalServico() {
    return $this->fTotalValorServicos;
  }
  
  /**
   * Soma ISS de todas as notas da competência
   *
   * @return number
   */
  public function setTotalIss($fTotalValorIss) {
    $this->fTotalValorIss = $fTotalValorIss;
  }
  
  /**
   * Soma do valor de todas as notas da competência
   *
   * @return number
   */
  public function setTotalServico($fTotalValorServicos) {
    $this->fTotalValorServicos = $fTotalValorServicos;
  }

  /**
   * Soma do valor de todas as notas da competência
   *
   * @return number
   */
  public function getExisteGuia() {
    return $this->lExisteGuia;
  }
  
  /**
   * Soma ISS de todas as notas da competência
   *
   * @return number
   */
  public function setExisteGuia($lExisteGuia) {
    $this->lExisteGuia = $lExisteGuia;
  }
  
  
  /**
   * Retorna o valor do servico formatado como moeda
   * 
   * @return string
   */
  public function getFormatedTotalServico() {
    return 'R$ ' . number_format($this->getTotalServico(), 2, ',', '.');
  }

  /**
   * Retorna o valor do iss formatado como moeda
   *
   * @return string
   */
  public function getFormatedTotalIss() {
    return 'R$ ' . number_format($this->getTotalIss(), 2, ',', '.');
  }
  
  /**
   * Retorna a competencia no formato mm/YYYY
   * 
   * @return string
   */
  public function getCompetencia() {
    return "{$this->iMesCompetencia}/{$this->iAnoCompetencia}";
  }
  
  /**
   * Retorna o mes da competencia
   * 
   * @return integer
   */
  public function getMesComp() {
    return $this->iMesCompetencia;
  }

  /**
   * Retorna o ano da competencia
   * 
   * @return integer
   */
  public function getAnoComp() {
    return $this->iAnoCompetencia;
  }

  /**
   * Verifica se a competencia é corrente
   * 
   * @return boolean
   */
  public function isCorrente() {
    
    $oDataAtual        = new DateTime();
    $oDataCompetencia  = new DateTime(DBSeller_Helper_Date_Date::invertDate('01/'.$this->getCompetencia(), '/'));
    
    $sCompetenciaAtual = $oDataAtual->format('Ym');    
    $sCompetencia      = $oDataCompetencia->format('Ym');

    return ($sCompetencia === $sCompetenciaAtual);
  }
  
  /**
   * Retorna a guia de pagamento referente a esta competencia, se a competencia ainda estiver aberta entao retorna NULL
   *
   * @return boolean
   */
  public function getGuia() {
    
    if ($this->aGuia === NULL) {
      
      // @TODO Analisar o tipo de guia (dms | nota)
      $aGuia = Contribuinte_Model_Guia::getNotasRetidasNaCompetenciaDoContribuinte($this->iAnoCompetencia,
                                                                                   $this->iMesCompetencia,
                                                                                   $this->oContribuinte,
                                                                                   NULL);
      
      if (empty($aGuia)) {
        $this->aGuia = NULL;
      } else {
        $this->aGuia = $aGuia[0];
      }
    }
    
    return $this->aGuia;
  }
  
  /**
   * Verifica se existe guia emitida
   * 
   * @return boolean
   */
  public function existeGuiaEmitida() {

    return Contribuinte_Model_Guia::existeGuia($this->oContribuinte,
                                               $this->iMesCompetencia,
                                               $this->iAnoCompetencia,
                                               10);
  }
  
  
  /**
   * Retorna as notas do mes
   * 
   * @return array|Contribuinte_Model_Nota[]
   */
  public function getNotas() {
    
    if (count($this->aNotas) == 0) {
      
      $this->aNotas = Contribuinte_Model_Nota::getNotasRetidasNaCompetenciaDoContribuinte($this->iAnoCompetencia,
                                                                                          $this->iMesCompetencia,
                                                                                          $this->oContribuinte);
    }
    
    return $this->aNotas;
  }
  
  
  /**
   * Busca Declaracoes de insencao de ISSQN variavel (Declaracao de Sem Movimento)
   *
   * @return array|null
   */
  public function getDeclaracaoSemMovimento() {
    
    $oWebService = new Contribuinte_Lib_Model_WebService();
    $aFiltro     = array('inscricao_municipal' => $this->oContribuinte->getInscricaoMunicipal(),
                         'ano'                 => $this->iAnoCompetencia);
    $aCampos     = array('inscricao_municipal', 'mes', 'ano');
    $aRetorno    = $oWebService::consultar('getCancelamentoISSQNVariavel', array($aFiltro, $aCampos));
    
    return is_array($aRetorno) ? $aRetorno : NULL;
  }

  /**
   * Retorna varias declaracoes de sem movimento para varias contribuintes
   *
   * @param array $aInscricaoMunicipal
   * @param integer $iAnoCompetencia
   * @param integer $iMesCompetencia
   * @return array|null
   */
  public static function getDeclaracaoSemMovimentoPorContribuintes($aInscricaoMunicipal,
                                                                   $iAnoCompetencia = NULL,
                                                                   $iMesCompetencia = NULL) {

    if (is_array($aInscricaoMunicipal)) {
      $aInscricaoMunicipal = implode("','", $aInscricaoMunicipal);
    }

    $oWebService = new Contribuinte_Lib_Model_WebService();
    $aCampos     = array('inscricao_municipal', 'ano', 'mes');
    $aFiltro     = array('inscricao_municipal' => $aInscricaoMunicipal);

    if ($iAnoCompetencia) {
      $aFiltro['ano'] = $iAnoCompetencia;
    }

    if ($iMesCompetencia) {
      $aFiltro['mes'] = $iMesCompetencia;
    }

    $aRetorno = $oWebService::consultar('getCancelamentoISSQNVariavel', array($aFiltro, $aCampos));

    return is_array($aRetorno) ? $aRetorno : NULL;
  }

  /**
   * Gera declaracao de insencao de ISSQN Variavel (Declaracao de Sem Movimento)
   *
   * @return array|null
   * @throws Exception
   */
  public function gerarDeclaracaoSemMovimento() {
    
    try {

      $aParamentos = array(
        'inscricaomunicipal' => $this->oContribuinte->getInscricaoMunicipal(),
        'mes'                => $this->iMesCompetencia,
        'ano'                => $this->iAnoCompetencia
      );

      $aRetorno = Contribuinte_Lib_Model_WebService::processar('CancelamentoISSQNVariavel', $aParamentos);
    } catch(Exception $oErro) {
      throw new Exception(sprintf($this->translate->_('Erro ao declarar sem movimento: %s'), $oErro->getMessage()));
    }

    return $aRetorno;
  }
}