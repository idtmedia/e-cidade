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
 * Define uma classe para tratamento das notas fiscais de serviço eletrônicas
 *
 * @package Contribuinte_Model
 * @see     Contribuinte_Interface_DocumentoNota
 * @see     Contribuinte_Lib_Model_Doctrine
 */

/**
 * Classe para controle de notas fiscais de serviço eletrônicas
 *
 * @package Contribuinte_Model
 * @see     Contribuinte_Interface_DocumentoNota
 * @see     Contribuinte_Lib_Model_Doctrine
 */
class Contribuinte_Model_Nota extends Contribuinte_Lib_Model_Doctrine implements Contribuinte_Interface_DocumentoNota {

  /**
   * Grupo dos documentos NFSe
   *
   * @var integer
   */
  const GRUPO_NOTA_NFSE = 2;

  /**
   * Grupo dos documentos RPS (Recibo Provisório de Serviços)
   *
   * @var integer
   */
  const GRUPO_NOTA_RPS = 7;

  /**
   * Prestador do serviço, fica como responsavel pelo imposto
   *
   * @var integer
   */
  const PRESTADOR_RETEM_ISS = 1;

  /**
   * Substituicao Tributaria - o Tomador do serviço, fica como responsavel pelo imposto
   *
   * @var integer
   */
  const TOMADOR_RETEM_ISS = 2;

  /**
   * Grupo dos documentos DMS (Declaração Manual de Serviços)
   *
   * @var string
   */
  const GRUPO_NOTA_DMS = '1,3,4,5,6,8';

  /**
   * Todos os grupo de documentos
   *
   * @var string
   */
  const GRUPO_NOTA_ALL = '1,2,3,4,5,6,7,8';

  /**
   * Objeto Entidade
   *
   * @var string
   */
  static protected $entityName = 'Contribuinte\Nota';

  /**
   * Nome da classe
   *
   * @var string
   */
  static protected $className = __CLASS__;

  /**
   * Descrição da lista de serviço
   *
   * @var string
   */
  private $descricao_lista_servico = NULL;

  /**
   * Construtor
   *
   * @param string $oEntidadeDoctrine
   */
  public function __construct($oEntidadeDoctrine = NULL) {

    parent::__construct($oEntidadeDoctrine);
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getId()
   */
  public function getId() {

    return $this->entity->getId();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getCompetenciaMes()
   */
  public function getCompetenciaMes() {

    return $this->entity->getMes_comp();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getCompetenciaAno()
   */
  public function getCompetenciaAno() {

    return $this->entity->getAno_comp();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getCodigoNotaPlanilha()
   */
  public function getCodigoNotaPlanilha() {

    return NULL;
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getNotaNumero()
   */
  public function getNotaNumero() {

    return $this->entity->getNota();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getNotaData()
   */
  public function getNotaData() {

    return $this->entity->getDt_nota();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getServicoAliquota()
   */
  public function getServicoAliquota() {

    return $this->entity->getS_vl_aliquota();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getServicoImpostoRetido()
   */
  public function getServicoImpostoRetido() {

    return $this->entity->getS_dados_iss_retido();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getServicoBaseCalculo()
   */
  public function getServicoBaseCalculo() {

    return $this->entity->getS_vl_bc();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getServicoValorDeducao()
   */
  public function getServicoValorDeducao() {

    return $this->entity->getS_vl_deducoes();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getServicoValorImposto()
   */
  public function getServicoValorImposto() {

    return $this->entity->getS_vl_iss();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getServicoValorPagar()
   */
  public function getServicoValorPagar() {

    return $this->entity->getS_vl_servicos();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getSituacaoDocumento()
   */
  public function getSituacaoDocumento() {

    return NULL;
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getDescricaoServico()
   */
  public function getDescricaoServico() {

    return $this->entity->getS_dados_discriminacao();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getPrestadorCpfCnpj()
   */
  public function getPrestadorCpfCnpj() {

    return $this->entity->getP_cnpjcpf();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getPrestadorInscricaoMunicipal()
   */
  public function getPrestadorInscricaoMunicipal() {

    return $this->entity->getP_im();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getPrestadorRazaoSocial()
   */
  public function getPrestadorRazaoSocial() {

    return $this->entity->getP_razao_social();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getTomadorCpfCnpj()
   */
  public function getTomadorCpfCnpj() {

    return $this->entity->getT_cnpjcpf();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getTomadorInscricaoMunicipal()
   */
  public function getTomadorInscricaoMunicipal() {

    return $this->entity->getT_im();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getTomadorRazaoSocial()
   */
  public function getTomadorRazaoSocial() {

    return $this->entity->getT_razao_social();
  }

  /**
   * @see Contribuinte_Interface_DocumentoNota::getOperacao()
   */
  public function getOperacao() {

    return 's';
  }

  /**
   * @return string
   */
  public function getNotaSerie() {

    return '';
  }

  /**
   * Busca os documentos por Código de verificação e CPF / CNPJ
   *
   * @param string $sCodigoVerificacao
   * @param string $sCpfCnpj
   * @return NULL|Contribuinte_Model_Nota|Contribuinte_Model_Nota[]
   */
  public static function getByCodigoAndCpfCnpj($sCodigoVerificacao, $sCpfCnpj) {

    $oEntityManager = parent::getEm();
    $oRepositorio   = $oEntityManager->getRepository(static::$entityName);
    $aResultado     = $oRepositorio->findBy(array('cod_verificacao' => $sCodigoVerificacao, 't_cnpjcpf' => $sCpfCnpj));

    if (count($aResultado) == 0) {
      return NULL;
    }

    if (count($aResultado) == 1) {
      return new Contribuinte_Model_Nota($aResultado[0]);
    }

    $aRetorno = array();

    foreach ($aResultado as $oResultado) {
      $aRetorno[] = new Contribuinte_Model_Nota($oResultado);
    }

    return $aRetorno;
  }

  /**
   * Retorna as notas sem guia retida do contribuinte
   *
   * @param array $aListaContribuintes
   * @return Contribuinte_Model_Nota[]
   */
  public static function getNotasRetidasSemGuiaByContribuinte(array $aListaContribuintes) {

    $oEm        = parent::getEm();
    $sSql       = 'SELECT notas FROM Contribuinte\Nota notas
                    WHERE notas.s_dados_iss_retido = ' . self::PRESTADOR_RETEM_ISS . ' AND
                          notas.emite_guia = true AND
                          notas.id_contribuinte in(?1) AND
                          notas.id NOT IN (SELECT n.id FROM Contribuinte\Guia g JOIN g.notas n)';
    $oQuery     = $oEm->createQuery($sSql)->setParameter('1', $aListaContribuintes);
    $aResultado = $oQuery->getResult();
    $aRetorno   = array();

    if (is_array($aResultado) && count($aResultado) > 0) {

      foreach ($aResultado as $oResultado) {
        $aRetorno[] = new Contribuinte_Model_Nota($oResultado);
      }
    }

    return $aRetorno;
  }

  /**
   * Retorna as notas retidas para o contribuinte
   *
   * @param integer $iIdContribuinte
   * @return Contribuinte_Model_Nota[]
   */
  public static function getNotasRetidasByContribuinte($iIdContribuinte) {

    $aCamposPesquisa = array(
      's_dados_iss_retido' => self::PRESTADOR_RETEM_ISS,
      'id_contribuinte'    => $iIdContribuinte,
      'cancelada'          => FALSE
    );

    $aCamposOrdem = array('nota' => 'DESC');
    $aResultado   = self::getByAttributes($aCamposPesquisa, $aCamposOrdem);

    return $aResultado;
  }

  /**
   * Retorna as notas do prestador
   *
   * @param integer $iIdContribuinte
   * @return Contribuinte_Model_Nota[]
   */
  public static function getNotasPrestadorByContribuinte($iIdContribuinte) {

    $oEntityManager = parent::getEm();
    $oRepositorio   = $oEntityManager->getRepository(static::$entityName);
    $aResultado     = $oRepositorio->findBy(array('id_contribuinte' => $iIdContribuinte), array('nota' => 'DESC'));
    $aRetorno       = array();

    if (is_array($aResultado) && count($aResultado) > 0) {

      foreach ($aResultado as $oResultado) {
        $aRetorno[] = new Contribuinte_Model_Nota($oResultado);
      }
    }

    return $aRetorno;
  }

  /**
   * Retorna as notas do tipo informado para o contribuinte
   *
   * @param array   $aIdContribuinte
   * @param integer $iTipoNota
   * @return mixed
   */
  public static function getNotasEmitidasByContribuinteAndTipoNota(array $aIdContribuinte, $iTipoNota) {

    $oEm    = parent::getEm();
    $sSql   = 'SELECT COUNT(n.id) FROM Contribuinte\Nota n
               WHERE  n.id_contribuinte in (:id_contribuinte) and
                      n.tipo_nota       = :tiponota';
    $oQuery = $oEm->createQuery($sSql);
    $oQuery->setParameter('id_contribuinte', $aIdContribuinte);
    $oQuery->setParameter('tiponota', $iTipoNota);

    return $oQuery->getSingleScalarResult();
  }

  /**
   * Retorna a quantidade de RPS emitidos para o contribuinte
   *
   * @param array $aListaContribuinte
   * @return mixed
   */
  public static function getRpsEmitidosByContribuinte(array $aListaContribuinte) {

    $oEm    = parent::getEm();
    $sSql   = 'SELECT COUNT(n.id) FROM Contribuinte\Nota n
                WHERE n.id_contribuinte in (:id_contribuinte) AND
                      n.n_rps IS NOT NULL';
    $oQuery = $oEm->createQuery($sSql);
    $oQuery->setParameter('id_contribuinte', $aListaContribuinte);

    return $oQuery->getSingleScalarResult();
  }

  /**
   * Retorna as notas por id do contribuinte e competência
   *
   * @param integer                                 $iAnoCompetencia
   * @param integer                                 $iMesCompetencia
   * @param Contribuinte_Model_ContribuinteAbstract $oContribuinte
   * @return Contribuinte_Model_Nota[]
   */
  public static function getNotasRetidasNaCompetenciaDoContribuinte($iAnoCompetencia,
                                                                    $iMesCompetencia,
                                                                    Contribuinte_Model_ContribuinteAbstract $oContribuinte) {

    $oEntityManager = parent::getEm();
    $sSql           = 'SELECT e FROM Contribuinte\Nota e
                        WHERE e.ano_comp           = :ano
                          AND e.mes_comp           = :mes
                          AND e.id_contribuinte    in(:id_contribuinte)
                          AND e.cancelada          = false
                          AND e.s_dados_iss_retido = ' . self::PRESTADOR_RETEM_ISS . '
                          AND e.natureza_operacao  = 1
                          AND e.s_vl_iss           > 0
                          AND e.emite_guia         = true';

    $oQuery = $oEntityManager->createQuery($sSql);
    $oQuery->setParameters(array(
                             'mes'             => $iMesCompetencia,
                             'ano'             => $iAnoCompetencia,
                             'id_contribuinte' => $oContribuinte->getContribuintes()
                           ));

    $aResultado = $oQuery->getResult();
    $aRetorno   = array();

    if (is_array($aResultado)) {

      foreach ($aResultado as $oResultado) {
        $aRetorno[] = new self($oResultado);
      }
    }

    return $aRetorno;
  }

  /**
   * Retorna o número da última RPS emitida
   *
   * @param string $sCnpj
   * @return mixed
   */
  public static function getNRpsByCnpj($sCnpj) {

    $oEm    = parent::getEm();
    $sSql   = 'SELECT MAX(e.n_rps) FROM Contribuinte\Nota e WHERE e.p_cnpjcpf = :cnpj';
    $oQuery = $oEm->createQuery($sSql)->setParameter('cnpj', $sCnpj);

    return $oQuery->getSingleScalarResult();
  }

  /**
   * Retorna verdadeiro se o RPS existe para o contribuinte
   *
   * @param array   $aListaContribuintes
   * @param integer $iNumeroRps
   * @param integer $iTipoDocumento
   * @return boolean
   */
  public static function checkRpsExistsByContribuinteAndNumeroRps($aListaContribuintes, $iNumeroRps, $iTipoDocumento) {

    $oEm    = parent::getEm();
    $sSql   = 'SELECT 1 FROM Contribuinte\Nota nota
                WHERE nota.id_contribuinte in (:contribuintes)  AND
                      nota.grupo_documento   = :grupo_documento AND
                      nota.tipo_documento    = :tipo_documento  AND
                      nota.n_rps             = :numero_rps';
    $oQuery = $oEm->createQuery($sSql);
    $oQuery->setParameter('contribuintes', $aListaContribuintes);
    $oQuery->setParameter('grupo_documento', Contribuinte_Model_Nota::GRUPO_NOTA_RPS);
    $oQuery->setParameter('tipo_documento', $iTipoDocumento);
    $oQuery->setParameter('numero_rps', $iNumeroRps);

    $aResultado = $oQuery->getResult();

    return (count($aResultado) > 0);
  }

  /**
   * Verifica se o RPS já foi registrado pelo contribuinte
   *
   * @tutorial Retorna TRUE caso já exista o RPS
   * @param Contribuinte_Model_ContribuinteAbstract $oContribuinte
   * @param integer                                 $iNumeroRps
   * @param integer                                 $iTipoDocumento
   * @return boolean
   */
  public static function existeRps(Contribuinte_Model_ContribuinteAbstract $oContribuinte,
                                   $iNumeroRps,
                                   $iTipoDocumento) {

    $oEm    = parent::getEm();
    $sSql   = 'SELECT 1 FROM Contribuinte\Nota nota
                WHERE nota.id_contribuinte  = :id_contribuinte AND
                      nota.grupo_nota       = :grupo_documento AND
                      nota.tipo_nota        = :tipo_documento  AND
                      nota.n_rps            = :numero_rps';
    $oQuery = $oEm->createQuery($sSql);
    $oQuery->setParameter('id_contribuinte', $oContribuinte->getIdUsuarioContribuinte());
    $oQuery->setParameter('grupo_documento', Contribuinte_Model_Nota::GRUPO_NOTA_RPS);
    $oQuery->setParameter('tipo_documento', $iTipoDocumento);
    $oQuery->setParameter('numero_rps', $iNumeroRps);

    $aResultado = $oQuery->getResult();

    return (count($aResultado) > 0);
  }

  /**
   * Método para salvar a nota (ajustado para salvar por array ou objeto)
   *
   * @param $uDados
   * @return bool|NULL
   * @throws Exception
   */
  public function persist($uDados) {

    if (is_array($uDados)) {
      return self::notaPersist($uDados);
    } else if (is_object($uDados)) {

      $this->em->persist($uDados);
      $this->em->flush();

      return TRUE;
    } else {
      throw new Exception('O parâmetro informado é inválido para persistência de dados.');
    }
  }

  /**
   * Salva a nota (por array)
   *
   * @param array $aDados
   * @throws Exception
   * @return boolean|NULL
   */
  public function notaPersist(array $aDados) {

    // Se o numero da nota vier no vetor nao precisa salvar a nota porque provavelmente é um refresh do navegador
    if (isset($aDados['nota'])) {
      return FALSE;
    }

    // Limpa mascaras
    $aFilterDigits        = new Zend_Filter_Digits(); // filtro para retornar somente numeros
    $aDados['t_cnpjcpf']  = $aFilterDigits->filter($aDados['t_cnpjcpf']);
    $aDados['t_cep']      = $aFilterDigits->filter($aDados['t_cep']);
    $aDados['t_telefone'] = $aFilterDigits->filter($aDados['t_telefone']);

    // Converte para o formato do DB
    $aDados['s_vl_servicos']            = $this->toFloat($aDados['s_vl_servicos']);
    $aDados['s_vl_liquido']             = $this->toFloat($aDados['s_vl_liquido']);
    $aDados['s_vl_deducoes']            = $this->toFloat($aDados['s_vl_deducoes']);
    $aDados['s_vl_bc']                  = $this->toFloat($aDados['s_vl_bc']);
    $aDados['s_vl_aliquota']            = $this->toFloat($aDados['s_vl_aliquota']);
    $aDados['s_vl_iss']                 = $this->toFloat($aDados['s_vl_iss']);
    $aDados['s_vl_pis']                 = $this->toFloat($aDados['s_vl_pis']);
    $aDados['s_vl_cofins']              = $this->toFloat($aDados['s_vl_cofins']);
    $aDados['s_vl_inss']                = $this->toFloat($aDados['s_vl_inss']);
    $aDados['s_vl_ir']                  = $this->toFloat($aDados['s_vl_ir']);
    $aDados['vl_liquido_nfse']          = $aDados['s_vl_liquido'];
    $aDados['s_vl_csll']                = $this->toFloat($aDados['s_vl_csll']);
    $aDados['s_vl_condicionado']        = $this->toFloat($aDados['s_vl_condicionado']);
    $aDados['s_vl_desc_incondicionado'] = $this->toFloat($aDados['s_vl_desc_incondicionado']);
    $aDados['s_vl_outras_retencoes']    = $this->toFloat($aDados['s_vl_outras_retencoes']);
    $aDados['s_dados_discriminacao']    = $aDados['descricao'];

    // Converte a data/hora de emissão da nota
    $aDados['dt_nota']  = self::getDateTime($aDados['dt_nota']);
    $aDados['hr_nota']  = self::getDateTime($aDados['dt_nota']);
    $aDados['ano_comp'] = $aDados['dt_nota']->format('Y');
    $aDados['mes_comp'] = $aDados['dt_nota']->format('m');

    // Flag marcado ou nao
    $lPrestadorRetemISS = $aDados['s_dados_iss_retido'] == 0 ? TRUE : FALSE;

    if (!$lPrestadorRetemISS && strlen(DBSeller_Helper_Number_Format::unmaskCPF_CNPJ($aDados['t_cnpjcpf'])) == 11) {
      $lPrestadorRetemISS = TRUE;
    }

    $aDados['s_dados_iss_retido'] = $lPrestadorRetemISS ? self::PRESTADOR_RETEM_ISS : self::TOMADOR_RETEM_ISS;

    // Salva o email do tomador
    $this->salvaEmailTomador($aDados['t_cnpjcpf'], utf8_encode($aDados['t_email']));

    $oServico = Contribuinte_Model_Servico::getByCodServico($aDados['s_dados_cod_tributacao']);
    $oServico = is_array($oServico) ? $oServico[0] : $oServico;

    if (!is_object($oServico)) {
      throw new Exception('O código do serviço é inválido.');
    }

    // Outros dados do serviço
    $aDados['s_dados_cod_cnae']           = utf8_encode($oServico->attr('estrut_cnae'));
    $aDados['s_dados_item_lista_servico'] = utf8_encode($oServico->attr('cod_item_servico'));

    // Dados do prestador
    $oContribuinte = Contribuinte_Model_Contribuinte::getByInscricaoMunicipal($aDados['p_im']);

    $aDados['p_cnpjcpf']         = $oContribuinte->getCgcCpf();
    $aDados['p_razao_social']    = $oContribuinte->getNome();
    $aDados['p_nome_fantasia']   = $oContribuinte->getNomeFantasia();
    $aDados['p_ie']              = $oContribuinte->getInscricaoEstadual();
    $aDados['p_cod_pais']        = $oContribuinte->getCodigoPais();
    $aDados['p_uf']              = $oContribuinte->getEstado();
    $aDados['p_cod_municipio']   = $oContribuinte->getCodigoIbgeMunicipio();
    $aDados['p_cep']             = $oContribuinte->getCep();
    $aDados['p_bairro']          = $oContribuinte->getLogradouroBairro();
    $aDados['p_endereco_numero'] = $oContribuinte->getLogradouroNumero();
    $aDados['p_endereco_comp']   = $oContribuinte->getLogradouroComplemento();
    $aDados['p_telefone']        = $oContribuinte->getTelefone();
    $aDados['p_email']           = $oContribuinte->getEmail();
    $aDados['p_endereco']        = $oContribuinte->getTipoLogradouro() . ' ' .
      $oContribuinte->getDescricaoLogradouro();

    /*
     * Dados do RPS
     */

    if (isset($aDados['data_rps'])) {

      if (is_string($aDados['data_rps'])) {

        $aDataRps           = explode('/', $aDados['data_rps']);
        $oDataRps           = new DateTime($aDataRps[2] . '-' . $aDataRps[1] . '-' . $aDataRps[0]);
        $aDados['data_rps'] = $oDataRps;
      }

      if ($aDados['n_rps'] == '') {
        $aDados['n_rps'] = $this->getNRpsByCnpj($aDados['p_cnpjcpf']) + 1;
      } else {

        // Se o numero do RPS ja existe para este contribuinte, retorna erro
        if (self::existeRps($oContribuinte, $aDados['n_rps'], $aDados['tipo_nota'])) {
          return NULL;
        }
      }
    }

    /*
     * Dados do tomador
     */

    // Se o tomador for substituto então busca CGM apenas do e-cidade
    $oTomador = Contribuinte_Model_Empresa::getByCgcCpf($aDados['t_cnpjcpf']);

    /*
     * @todo Refatorar esse trecho de codigo
     */
    if (!empty($oTomador->eCidade)) {
      $oTomador = is_array($oTomador->eCidade) ? reset($oTomador->eCidade) : $oTomador->eCidade;
    } else if (!empty($oTomador->eNota)) {
      $oTomador = is_array($oTomador->eNota) ? reset($oTomador->eNota) : $oTomador->eNota;
    }

    // Se nao encontrou tomador, entao cadastra no banco do sistema
    if (!is_object($oTomador) || empty($oTomador)) {

      $oTomador = new Contribuinte_Model_EmpresaBase();

      if ($oTomador instanceof Contribuinte_Model_EmpresaBase && $oTomador->isEntity()) {
        $oTomador->persist($aDados);
      }
    }

    // Dados da tributacao
    $aDados['s_dec_reg_esp_tributacao'] = (int) $oContribuinte->getRegimeTributario();
    $aDados['s_dados_exigibilidadeiss'] = (int) $oContribuinte->getExigibilidade();
    $aDados['s_dec_incentivo_fiscal']   = (int) $oContribuinte->getIncentivoFiscal();
    $aDados['s_dec_simples_nacional']   = (int) $oContribuinte->getOptanteSimples();

    // Busca a proxima numeracao da nota para o contribuinte
    $aDados['nota'] = $this->proximaNotaByContribuinte($oContribuinte->getContribuintes());

    // Configura o parametro para checar se deve adicionar a nota na guia de pagamento
    $oChecaEmissaoGuiaStdClass                      = new stdClass();
    $oChecaEmissaoGuiaStdClass->data                = $aDados['dt_nota']->format('d/m/Y');
    $oChecaEmissaoGuiaStdClass->inscricao_municipal = $oContribuinte->getInscricaoMunicipal();

    // Se for RPS pega a data do mesmo
    if (isset($aDados['data_rps'])) {
      $oChecaEmissaoGuiaStdClass->data = $aDados['data_rps']->format('d/m/Y');
    }

    /* 
     * Verifica se deve incluir a nota na guia de pagamento, conforme as 
     * regras de emissão do contribuinte (sobrescreve todas as regras anteriores)
     */
    $aDados['emite_guia'] = Contribuinte_Model_EmissorGuia::checarEmissaoGuia($oChecaEmissaoGuiaStdClass);

    // Se o prestador não for responsável pela retenção não inclui a nota na guia de pagamento
    if (!$lPrestadorRetemISS && $aDados['emite_guia']) {
      $aDados['emite_guia'] = FALSE;
    }

    // Persiste na base de dados
    $this->setAttributes($aDados);
    $this->em->persist($this->entity);
    $this->em->flush();

    // Gera o código hash para autenticação
    $this->salvarCodigoHash($this->getNota());

    return TRUE;
  }

  /**
   * Retorna a data e hora de emissão da nota
   *
   * @tutorial
   *  Caso nenhuma data válida for informada, retorna a data do sistema
   *  Se for informado uma data como string 'dd/mm/yyyy' ou 'dd/mm/yyyy H:i:s' converte para DateTime
   * @param string|datetime|null $uData
   * @return DateTime
   */
  public static function getDateTime($uData = NULL) {

    if ($uData instanceof DateTime) {
      return $uData;
    }

    if (is_string($uData)) {

      // Ajusta a hora, caso não tenha sido informada
      if (strlen($uData) <= 10) {
        $uData = $uData . date(' H:i:s');
      }

      return new DateTime($uData);
    }

    return new DateTime();
  }

  /**
   * Salva email do tomador na tabela tomador_email
   *
   * @param string $sCpf   CPF do tomador
   * @param string $sEmail Email do tomador
   */
  public function salvaEmailTomador($sCpf, $sEmail) {

    $oTomadorEmail = Contribuinte_Model_EmpresaEmail::getByAttribute('cpfcnpj', $sCpf);

    if ($oTomadorEmail === NULL) {

      $oTomadorEmail = new Contribuinte_Model_EmpresaEmail();
      $oTomadorEmail->setCpfcnpj($sCpf);
    }

    $oTomadorEmail->persist(array('email' => $sEmail));
  }

  /**
   * Retorna a data formatada
   *
   * @return string
   */
  public function formatedData() {

    return $this->getDt_nota()->format('d/m/Y');
  }

  /**
   * Retorna a hora formatada
   *
   * @return string
   */
  public function formatedHora() {

    return strftime('%H:%M', $this->getHr_nota()->getTimestamp());
  }

  /**
   * Retorna a competencia formatada
   *
   * @return string
   */
  public function getComp() {

    return "{$this->getMes_comp()}/{$this->getAno_comp()}";
  }

  /**
   * Retorna o pais do prestador
   *
   * @return string
   */
  public function getPrestadorPais() {

    $pais = Default_Model_Cadenderpais::getByAttribute('cod_bacen', $this->getP_cod_pais());

    if ($pais == NULL || empty($pais)) {
      return '';
    }

    return $pais->getNome();
  }

  /**
   * Retorna o municipio do prestador
   *
   * @return string
   */
  public function getPrestadorMunicipio() {

    $oMunicipio = Default_Model_Cadendermunicipio::getByAttribute('cod_ibge', $this->getP_cod_municipio());

    if ($oMunicipio == NULL || empty($oMunicipio)) {
      return '';
    }

    return $oMunicipio->getNome();
  }

  /**
   * Retorna o pais do tomador
   *
   * @return string
   */
  public function getTomadorPais() {

    $oPais = Default_Model_Cadenderpais::getByAttribute('cod_bacen', $this->getT_cod_pais());

    if ($oPais == NULL || empty($oPais)) {
      return '';
    }

    return $oPais->getNome();
  }

  /**
   * Retorna o municipio do tomador
   *
   * @return string
   */
  public function getTomadorMunicipio() {

    $oMunicipio = Default_Model_Cadendermunicipio::getByAttribute('cod_ibge', $this->getT_cod_municipio());

    if ($oMunicipio == NULL || empty($oMunicipio)) {
      return '';
    }

    return $oMunicipio->getNome();
  }

  /**
   * Retorna o pais do serviço
   *
   * @return string
   */
  public function getServicoPais() {

    $oPais = Default_Model_Cadenderpais::getByAttribute('cod_bacen', $this->getS_dados_cod_pais());

    if ($oPais == NULL || empty($oPais)) {
      return '';
    }

    return $oPais->getNome();
  }

  /**
   * Retorna o municipio do serviço
   *
   * @return string
   */
  public function getServicoMunicipio() {

    $oMunicipio = Default_Model_Cadendermunicipio::getByAttribute('cod_ibge', $this->getS_dados_municipio_incidencia());

    if ($oMunicipio == NULL || empty($oMunicipio)) {
      return '';
    }

    return $oMunicipio->getNome();
  }

  /**
   * Retorna a descrição do serviço
   *
   * @return string
   */
  public function getDescricaoListaServico() {

    if ($this->descricao_lista_servico === NULL) {
      $this->obtemDadosServico();
    }

    return $this->descricao_lista_servico;
  }

  /**
   * Retorna o substituto tributario
   *
   * @return mixed
   */
  public function getSubstitudoTributario() {

    return Contribuinte_Model_IssRetido::getById($this->getS_dados_iss_retido());
  }

  /**
   * Retorna os dados do serviço
   */
  private function obtemDadosServico() {

    if ($this->entity->getS_dados_cod_tributacao()) {

      $servico                       = Contribuinte_Model_Servico::getByCodServico($this->entity->getS_dados_cod_tributacao());
      $this->descricao_lista_servico = $servico[0]->attr('desc_item_servico');
    } else {
      $this->descricao_lista_servico = 'Serviço/Atividade Não Informado';
    }
  }

  /**
   * Converte valores para float
   *
   * @param string $sString
   * @return float
   */
  private function toFloat($sString) {

    $fRetorno = 0;

    if ($sString != '' && $sString != NULL) {

      $sString  = str_replace('.', '', $sString);
      $fRetorno = str_replace(',', '.', $sString);
    }

    return (float) $fRetorno;
  }

  /**
   * Define o(s) atributos informados na lista
   *
   * @param array $aAtributos ['nome_do_atributo' => 'valor']
   */
  private function setAttributes(array $aAtributos) {

    // Chama todos os set de todos os tributos contidos na array
    foreach ($aAtributos as $sIndice => $sValor) {

      $sMetodo = 'set' . ucfirst($sIndice);

      if (method_exists($this->entity, $sMetodo)) {
        call_user_func_array(array($this->entity, $sMetodo), array($sValor));
      }
    }
  }

  /**
   * Gera o código Hash
   *
   * @param integer $iIdNota
   * @param integer $iContador
   * @return string
   */
  private function gerarCodigoHash($iIdNota, $iContador) {

    // Pega o unixtime atual
    $tTime = time();

    // Gera o hash md5 referente a: "id da nota . unix-time . numero de tentativas" e pega os 8 primeiros caracteres
    $sCodigoHash = substr(md5("{$iIdNota}{$tTime}{$iContador}"), 0, 8);

    return $sCodigoHash;
  }

  /**
   * Salva o código Hash
   *
   * @param integer $iIdNota
   * @param integer $iContador
   * @throws Exception
   */
  private function salvarCodigoHash($iIdNota, $iContador = 0) {

    try {

      $sCodigoHash = $this->gerarCodigoHash($iIdNota, $iContador);

      $this->entity->setCod_verificacao($sCodigoHash);
      $this->em->persist($this->entity);
      $this->em->flush();
    } catch (Exception $oError) {

      /*
       * Pega o código da exceção do banco. Caso o código da exceção seja
       * '23505' (chave única violada) então tenta gerar outro código de verificação.
       * Se for outro código, então mostra a exceção.
       */
      $iCodigoErro = $oError->getPrevious()->getCode();

      if ($iCodigoErro == 23505 && $iContador < 5) {
        $this->salvaCodigo($iIdNota, $iContador + 1);
      } else {

        $this->em->getConnection()->rollback();
        $this->em->close();

        throw new Exception ($oError->getMessage());
      }
    }
  }

  /**
   * Retorna o número para ser utilizado na próxima Nota do contribuinte
   *
   * @param array $aListaContribuinte Lista de contribuintes
   * @return mixed
   */
  private function proximaNotaByContribuinte(array $aListaContribuinte) {

    $sSql   = 'SELECT (COALESCE(MAX(n.nota), MAX(n.nota), 0) + 1)
                 FROM Contribuinte\Nota n
                WHERE n.id_contribuinte in (:id_contribuinte)';
    $oQuery = $this->em->createQuery($sSql);
    $oQuery->setParameter('id_contribuinte', $aListaContribuinte);

    return $oQuery->getSingleScalarResult();
  }

  /**
   * Retorna a quantidade de AIDOFs liberadas para emissão conforme tipo de documento
   *
   * @param null $iCodigoContribuinte
   * @param null $iTipoNota
   * @return int
   * @throws Zend_Exception
   */
  public static function getQuantidadeNotasPendentesByContribuinteAndTipoNota($iCodigoContribuinte = NULL,
                                                                              $iTipoNota = NULL) {

    if (!$iCodigoContribuinte) {
      throw new Zend_Exception('Informe o código do contribuinte');
    }

    if (!$iTipoNota) {
      throw new Zend_Exception('Informe o tipo de documento');
    }

    $oContribuinte = Contribuinte_Model_Contribuinte::getById($iCodigoContribuinte);

    $aFiltro  = array('inscricao_municipal' => $oContribuinte->getInscricaoMunicipal(), 'tipo_documento' => $iTipoNota);
    $aCampos  = array('qntLiberadas');
    $oRetorno = parent::consultar('getQuantidadeAidofsLiberadas', array($aFiltro, $aCampos));

    if (is_array($oRetorno) && isset($oRetorno[0])) {
      return $oRetorno[0]->quantidade_aidofs_liberadas;
    }

    return 0;
  }

  /**
   * Busca os tipos de nota do e-Cidade
   *
   * @param string $sGrupoNota
   * @param bool   $lRetonarArraySimples
   * @return array
   * @throws Exception
   */
  public static function getTiposNota($sGrupoNota, $lRetonarArraySimples = TRUE) {

    if (!$sGrupoNota) {
      throw new Exception('Informe o Grupo de Nota');
    }

    $aFiltro     = array('codigo_grupo_notaiss' => $sGrupoNota);
    $aCampos     = array('codigo', 'nota', 'descricao', 'codigo_grupo');
    $oWebService = new Contribuinte_Lib_Model_WebService();
    $aTipoNota   = $oWebService::consultar('getTiposNota', array($aFiltro, $aCampos));

    if (is_array($aTipoNota)) {

      if ($lRetonarArraySimples) {

        foreach ($aTipoNota as $oTipoNota) {
          $aRetorno[$oTipoNota->codigo] = DBSeller_Helper_String_Format::wordsCap($oTipoNota->descricao);
        }
      } else {
        $aRetorno = $aTipoNota;
      }
    }

    return isset($aRetorno) ? $aRetorno : array();
  }

  /**
   * Busca a descricao do tipos de nota do e-Cidade
   *
   * @param string $iTipoNota
   * @param bool   $lRetonarArraySimples
   * @return array
   * @throws Exception
   */
  public static function getDescricaoTipoNota($iTipoNota, $lRetonarArraySimples = TRUE) {

    if (!$iTipoNota) {
      throw new Exception('Informe o tipo de nota');
    }

    $aRetorno    = array();
    $aFiltro     = array('codigo' => $iTipoNota);
    $aCampos     = array('codigo', 'nota', 'descricao', 'codigo_grupo');
    $oWebService = new Contribuinte_Lib_Model_WebService();
    $aTipoNota   = $oWebService::consultar('getTiposNota', array($aFiltro, $aCampos));

    if (is_array($aTipoNota)) {

      if ($lRetonarArraySimples) {

        foreach ($aTipoNota as $oTipoNota) {
          $aRetorno[$oTipoNota->codigo] = DBSeller_Helper_String_Format::wordsCap($oTipoNota->descricao);
        }
      } else {
        $aRetorno = $aTipoNota;
      }
    }

    return $aRetorno;
  }

  /**
   * Busca dados do tipos de nota do e-Cidade
   *
   * @param string $iTipoNota
   * @return mixed|null|stdClass
   */
  public static function getTipoNota($iTipoNota) {

    if (!$iTipoNota) {

      $oTipoNota               = new stdClass();
      $oTipoNota->codigo       = '';
      $oTipoNota->nota         = '';
      $oTipoNota->descricao    = 'Nota Fiscal de Serviço Eletrônica';
      $oTipoNota->codigo_grupo = '';

      return $oTipoNota;
    }

    // Sessão
    $oSessaoTipoNota = new Zend_Session_Namespace('webservice_contribuinte_tipo_nota');

    // Retorna o tipo de nota da sessão, caso exista
    if (!$iTipoNota && isset($oSessaoTipoNota->lista[$iTipoNota])) {
      return $oSessaoTipoNota->lista[$iTipoNota];
    }

    $aFiltro     = array('codigo' => $iTipoNota);
    $aCampos     = array('codigo', 'nota', 'descricao', 'codigo_grupo');
    $oWebService = new Contribuinte_Lib_Model_WebService();
    $aTipoNota   = $oWebService::consultar('getTiposNota', array($aFiltro, $aCampos));

    if (count($aTipoNota) > 0) {

      // Salva na sessão para evitar consultas no web service e retorna os dados
      return $oSessaoTipoNota->lista[$iTipoNota] = reset($aTipoNota);
    }

    return NULL;
  }

  /**
   * Retorna a ultima nota emitida do contribuinte
   *
   * @param array $aIdContribuintes lista de id_contribuintes
   * @return null
   */
  public static function getUltimaNotaEmitidaByContribuinte($aIdContribuintes) {

    $oEntityManager = parent::getEm();
    $oQuery         = $oEntityManager->createQueryBuilder();
    $oQuery->select('MAX(n.dt_nota)');
    $oQuery->from('Contribuinte\Nota', 'n');
    $oQuery->where('n.id_contribuinte in(?1)');
    $oQuery->setParameters(array('1' => $aIdContribuintes));
    $aResultado = $oQuery->getQuery()->getResult();

    if (is_array($aResultado) && count($aResultado[0]) > 0) {

      foreach ($aResultado[0] as $uData) {
        return $uData;
      }
    }

    return NULL;
  }

  /**
   * Cancela a nota
   *
   * @param string $sJustificativa
   * @return bool
   */
  public function cancelar($sJustificativa) {

    $oConexao = $this->em->getConnection();
    $oConexao->beginTransaction();

    try {

      if (!$this->getId()) {
        throw new Exception('Identificador não encontrado.');
      }

      if (strlen(trim($sJustificativa)) < 1) {
        throw new Exception('Justificativa não informada.');
      }

      $this->setCancelada(TRUE);
      $this->setCancelamentoJustificativa($sJustificativa);
      $this->persist($this->getEntity());

      $oConexao->commit();

      return TRUE;
    } catch (Exception $e) {

      echo "Erro ao cancelar a NFSE: {$e->getMessage()}";
      $oConexao->rollback();

      return FALSE;
    }
  }

  /**
   * Busca o documento por Código de Verificação e CPF/CNPJ do Prestador
   *
   * @param string $sPrestadorCnpjCpf
   * @param string $sCodigoVerificacao
   * @return array|Contribuinte_Model_Nota|null
   * @throws Exception
   */
  public static function getByPrestadorAndCodigoVerificacao($sPrestadorCnpjCpf, $sCodigoVerificacao) {

    if (empty($sPrestadorCnpjCpf)) {
      throw new Exception('Informe o CNPJ do Prestador.');
    }

    if (empty($sCodigoVerificacao)) {
      throw new Exception('Informe o Código de Verificação da NFSe.');
    }

    $oEntityManager    = parent::getEm();
    $oZendFilter       = new Zend_Filter_Digits();
    $sPrestadorCnpjCpf = $oZendFilter->filter($sPrestadorCnpjCpf);
    $oRepositorio      = $oEntityManager->getRepository(static::$entityName);
    $aResultado        = $oRepositorio->findBy(array(
                                                 'cod_verificacao' => $sCodigoVerificacao,
                                                 'p_cnpjcpf'       => $sPrestadorCnpjCpf
                                               ));

    if (count($aResultado) == 0) {
      return NULL;
    }

    if (count($aResultado) == 1) {
      return new Contribuinte_Model_Nota($aResultado[0]);
    }

    $aRetorno = array();

    foreach ($aResultado as $oResultado) {
      $aRetorno[] = new Contribuinte_Model_Nota($oResultado);
    }

    return $aRetorno;
  }

  /**
   * Retorna os valores da NFSE calculado
   *
   * @param stdClass $oParametros
   *                                 $oParametros->perc_deducao;            // Percentual Deducao
   *                                 $oParametros->perc_inss;               // Percentual INSS
   *                                 $oParametros->perc_pis;                // Percentual PIS
   *                                 $oParametros->perc_cofins;             // Percentual COFINS
   *                                 $oParametros->perc_ir;                 // Percentual IR
   *                                 $oParametros->perc_csll;               // Percentual CSLL
   *                                 $oParametros->perc_aliquota;           // Percentual Aliquota
   *                                 $oParametros->vlr_servico;             // Valor Bruto do Serviço
   *                                 $oParametros->vlr_outras_retencoes;    // Valor Outras Retenções
   *                                 $oParametros->vlr_desc_condicionado;   // Valor Desconto Condicionado
   *                                 $oParametros->vlr_desc_incondicionado; // Valor Desconto Incondicionado
   *                                 $oParametros->imposto_retido_tomador;  // Imposto retido pelo tomador
   *                                 $oParametros->deducao_editavel;        // Habilita edição da dedução
   *                                 $oParametros->formatar_valores_ptbr;   // Retorna os valores em formato PTBR
   * @throws Exception
   * @see DBSeller_Helper_Number_Format
   * @return object stdClass
   */
  public static function calcularValores($oParametros) {

    if (!is_object($oParametros)) {
      throw new Exception('O parâmetro deve ser um objeto com os valores.');
    }

    // Limpa máscaras
    $oParametros->vlr_servico             = DBSeller_Helper_Number_Format::toFloat($oParametros->vlr_servico);
    $oParametros->s_vl_deducoes           = DBSeller_Helper_Number_Format::toFloat($oParametros->s_vl_deducoes);
    $oParametros->perc_aliquota           = DBSeller_Helper_Number_Format::toFloat($oParametros->perc_aliquota);
    $oParametros->vlr_inss                = DBSeller_Helper_Number_Format::toFloat($oParametros->vlr_inss);
    $oParametros->vlr_pis                 = DBSeller_Helper_Number_Format::toFloat($oParametros->vlr_pis);
    $oParametros->vlr_cofins              = DBSeller_Helper_Number_Format::toFloat($oParametros->vlr_cofins);
    $oParametros->vlr_ir                  = DBSeller_Helper_Number_Format::toFloat($oParametros->vlr_ir);
    $oParametros->vlr_csll                = DBSeller_Helper_Number_Format::toFloat($oParametros->vlr_csll);
    $oParametros->vlr_outras_retencoes    = DBSeller_Helper_Number_Format::toFloat($oParametros->vlr_outras_retencoes);
    $oParametros->vlr_desc_condicionado   = DBSeller_Helper_Number_Format::toFloat($oParametros->vlr_desc_condicionado);
    $oParametros->vlr_desc_incondicionado = DBSeller_Helper_Number_Format::toFloat($oParametros->vlr_desc_incondicionado);

    /*
     * Calculo dos os valores
     */
    $oParametros->vlr_base = $oParametros->vlr_servico - $oParametros->s_vl_deducoes;
    $oParametros->vlr_base -= $oParametros->vlr_desc_incondicionado;

    $oParametros->vlr_iss = $oParametros->vlr_base * ($oParametros->perc_aliquota / 100);

    $oParametros->vlr_liquido = $oParametros->vlr_servico;
    $oParametros->vlr_liquido -= $oParametros->vlr_inss;
    $oParametros->vlr_liquido -= $oParametros->vlr_pis;
    $oParametros->vlr_liquido -= $oParametros->vlr_cofins;
    $oParametros->vlr_liquido -= $oParametros->vlr_ir;
    $oParametros->vlr_liquido -= $oParametros->vlr_csll;
    $oParametros->vlr_liquido -= $oParametros->vlr_outras_retencoes;
    $oParametros->vlr_liquido -= $oParametros->vlr_desc_condicionado;
    $oParametros->vlr_liquido -= $oParametros->vlr_desc_incondicionado;

    // Desconta o valor do imposto quando retido pelo tomador
    if (isset($oParametros->imposto_retido_tomador) && $oParametros->imposto_retido_tomador) {
      $oParametros->vlr_liquido -= $oParametros->vlr_iss;
    }

    // Configura para o formato de moeda PTBR
    if (isset($oParametros->formatar_valores_ptbr) || $oParametros->formatar_valores_ptbr) {

      $oParametros->vlr_servico             = DBSeller_Helper_Number_Format::toMoney($oParametros->vlr_servico);
      $oParametros->vlr_liquido             = DBSeller_Helper_Number_Format::toMoney($oParametros->vlr_liquido);
      $oParametros->s_vl_deducoes           = DBSeller_Helper_Number_Format::toMoney($oParametros->s_vl_deducoes);
      $oParametros->vlr_base                = DBSeller_Helper_Number_Format::toMoney($oParametros->vlr_base);
      $oParametros->vlr_iss                 = DBSeller_Helper_Number_Format::toMoney($oParametros->vlr_iss);
      $oParametros->vlr_pis                 = DBSeller_Helper_Number_Format::toMoney($oParametros->vlr_pis);
      $oParametros->vlr_cofins              = DBSeller_Helper_Number_Format::toMoney($oParametros->vlr_cofins);
      $oParametros->vlr_inss                = DBSeller_Helper_Number_Format::toMoney($oParametros->vlr_inss);
      $oParametros->vlr_ir                  = DBSeller_Helper_Number_Format::toMoney($oParametros->vlr_ir);
      $oParametros->vlr_csll                = DBSeller_Helper_Number_Format::toMoney($oParametros->vlr_csll);
      $oParametros->vlr_outras_retencoes    = DBSeller_Helper_Number_Format::toMoney($oParametros->vlr_outras_retencoes);
      $oParametros->vlr_desc_condicionado   = DBSeller_Helper_Number_Format::toMoney($oParametros->vlr_desc_condicionado);
      $oParametros->vlr_desc_incondicionado = DBSeller_Helper_Number_Format::toMoney($oParametros->vlr_desc_incondicionado);
    }

    return $oParametros;
  }

  /**
   * Busca o documento por CPF/CNPJ do Prestador e Número do RPS
   *
   * @param string $sPrestadorCnpjCpf
   * @param string $sNumeroRps
   * @return array|Contribuinte_Model_Nota|null
   * @throws Exception
   */
  public static function getByPrestadorAndNumeroRps($sPrestadorCnpjCpf, $sNumeroRps) {

    if (empty($sPrestadorCnpjCpf)) {
      throw new Exception('Informe o CPF/CNPJ do Prestador.');
    }

    if (empty($sNumeroRps)) {
      throw new Exception('Informe o Número do RPS.');
    }

    $oEntityManager    = parent::getEm();
    $oZendFilter       = new Zend_Filter_Digits();
    $sPrestadorCnpjCpf = $oZendFilter->filter($sPrestadorCnpjCpf);
    $oRepositorio      = $oEntityManager->getRepository(static::$entityName);
    $aResultado        = $oRepositorio->findBy(array('p_cnpjcpf' => $sPrestadorCnpjCpf, 'n_rps' => $sNumeroRps));

    if (count($aResultado) == 0) {
      return NULL;
    }

    if (count($aResultado) == 1) {
      return new Contribuinte_Model_Nota($aResultado[0]);
    }

    $aRetorno = array();

    foreach ($aResultado as $oResultado) {
      $aRetorno[] = new Contribuinte_Model_Nota($oResultado);
    }

    return $aRetorno;
  }

  /**
   * Retorna a url criptografada para verificacao da NFSe
   *
   * @param string $sTipoRetornoParametros
   * @return array|string
   */
  public function getUrlVerificacaoNota($sTipoRetornoParametros = 'string') {

    $aUrlVerificacao = array(
      'module'     => 'default',
      'controller' => 'index',
      'action'     => 'autentica',
      'url'        => array(
        'codigo_verificacao' => $this->entity->getCod_verificacao(),
        'prestador_cnpjcpf'  => $this->entity->getP_cnpjcpf()
      )
    );

    return DBSeller_Helper_Url_Encrypt::encrypt($aUrlVerificacao, $sTipoRetornoParametros);
  }

  /**
   * @deprecated
   * @param integer $iInscricaoMunicipal
   * @return Contribuinte_Model_Nota[]
   */
  public static function getNotasRetidasSemGuia($iInscricaoMunicipal) {

    $oContribuinte = Contribuinte_Model_Contribuinte::getByInscricaoMunicipal($iInscricaoMunicipal);

    return self::getNotasRetidasSemGuiaByContribuinte($oContribuinte->getContribuintes());
  }

  /**
   * @deprecated
   * @param integer $iInscricaoMunicipal
   * @return Contribuinte_Model_Nota[]
   */
  public static function getNotasRetidas($iInscricaoMunicipal) {

    $oContribuinte = Contribuinte_Model_Contribuinte::getByInscricaoMunicipal($iInscricaoMunicipal);

    return self::getNotasRetidasByContribuinte($oContribuinte->getContribuintes());
  }

  /**
   * @deprecated
   * @param integer $iInscricaoMunicipal
   * @return Contribuinte_Model_Nota[]
   */
  public static function getNotasPrestador($iInscricaoMunicipal) {

    $oContribuinte = Contribuinte_Model_Contribuinte::getByInscricaoMunicipal($iInscricaoMunicipal);

    return self::getNotasPrestadorByContribuinte($oContribuinte->getContribuintes());
  }

  /**
   * @deprecated
   * @param integer $iInscricaoMunicipal
   * @param integer $iTipoNota
   * @return mixed
   */
  public static function getNotasEmitidas($iInscricaoMunicipal, $iTipoNota) {

    $oContribuinte = Contribuinte_Model_Contribuinte::getByInscricaoMunicipal($iInscricaoMunicipal);

    return self::getNotasEmitidasByContribuinteAndTipoNota($oContribuinte->getContribuintes(), $iTipoNota);
  }

  /**
   * @deprecated
   * @param integer $iInscricaoMunicipal
   * @return mixed
   */
  public static function getRpsEmitidos($iInscricaoMunicipal) {

    $oContribuinte = Contribuinte_Model_Contribuinte::getByInscricaoMunicipal($iInscricaoMunicipal);

    return self::getRpsEmitidosByContribuinte($oContribuinte->getContribuintes());
  }

  /**
   * @deprecated
   * @param integer $iAnoCompetencia
   * @param integer $iMesCompetencia
   * @param integer $iInscricaoMunicipal
   * @return mixed
   */
  public static function getByCompetenciaIm($iAnoCompetencia, $iMesCompetencia, $iInscricaoMunicipal) {

    $oContribuinte = Contribuinte_Model_Contribuinte::getByInscricaoMunicipal($iInscricaoMunicipal);

    return self::getByCompetenciaAndContribuinte(
               $iAnoCompetencia,
               $iMesCompetencia,
               $oContribuinte->getContribuintes()
    );
  }

  /**
   * @deprecated
   * @param string $iInscricaoMunicipal
   * @param string $iTipoNota
   */
  public static function getQuantidadeNotasPendentes($iInscricaoMunicipal = NULL, $iTipoNota = NULL) {

    $oContribuinte = Contribuinte_Model_Contribuinte::getByInscricaoMunicipal($iInscricaoMunicipal);

    return self::getQuantidadeNotasPendentesByIdContribuinteAndTipoNota(
               $oContribuinte->getContribuintes(),
               $iTipoNota
    );
  }

  /**
   * @deprecated
   * @param integer $iInscricaoMunicipal
   * @return null
   */
  public static function getUltimaNotaEmitida($iInscricaoMunicipal) {

    $oContribuinte = Contribuinte_Model_Contribuinte::getByInscricaoMunicipal($iInscricaoMunicipal);

    return self::getUltimaNotaEmitidaByContribuinte($oContribuinte->getContribuintes());
  }

  /**
   * Retorna as notas lançadas pelo contribuinte na competencia informada
   *
   * @param array    $aIdContribuinte
   * @param stdClass $oCompetencia
   *                 $oCompetencia->iMes
   *                 $oCompetencia->iAno
   * @return mixed
   */
  public static function getByContribuinteAndCompetencia(array $aIdContribuinte, stdClass $oCompetencia) {

    $aCamposPesquisa = array(
      'id_contribuinte' => $aIdContribuinte,
      'ano_comp'        => $oCompetencia->iAno,
      'mes_comp'        => $oCompetencia->iMes
    );
    $aCamposOrdem    = array('nota' => 'DESC');
    $aResultado      = self::getByAttributes($aCamposPesquisa, $aCamposOrdem);

    return $aResultado;
  }
}