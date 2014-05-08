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
 * Modelo para o contribuinte do município
 *
 * @package Contribuinte/Models
 * @see     Contribuinte_Model_ContribuinteAbstract
 * @see     Contribuinte_Interface_Contribuinte
 */

/**
 * @package Contribuinte/Models
 * @see     Contribuinte_Model_ContribuinteAbstract
 * @see     Contribuinte_Interface_Contribuinte
 */
class Contribuinte_Model_Contribuinte extends Contribuinte_Model_ContribuinteAbstract
  implements Contribuinte_Interface_Contribuinte {

  /**
   * Campos retornados do webservice
   *
   * @var array
   */
  private static $aCampos = array(
    'getDadosCadastroNotas' => array(
      'tipo',
      'cgccpf',
      'nome',
      'nome_fanta',
      'identidade',
      'inscr_est',
      'tipo_lograd',
      'lograd',
      'numero',
      'complemento',
      'bairro',
      'cod_ibge',
      'munic',
      'uf',
      'cod_pais',
      'pais',
      'cep',
      'telefone',
      'fax',
      'celular',
      'email',
      'inscricao',
      'data_inscricao',
      'tipo_classificacao',
      'optante_simples',
      'optante_simples_baixado',
      'tipo_emissao',
      'exigibilidade',
      'subst_tributaria',
      'regime_tributario',
      'incentivo_fiscal',
      'numero_cgm'
    ),
    'getDadosEmpresa'       => array(
      'razao_social',
      'codigo_empresa',
      'cnpj',
      'endereco',
      'cgm'
    )
  );

  /**
   * Retorna os dados do contribuinte
   *
   * @param integer $iInscricaoMunicipal
   * @return object
   */
  public function getDadosContribuinteEcidade($iInscricaoMunicipal) {

    $oContribuinteWebService = NULL;

    if ($iInscricaoMunicipal != NULL) {
      $aParametros   = array(array('inscricao' => $iInscricaoMunicipal), self::$aCampos['getDadosCadastroNotas']);
      $aContribuinte = Contribuinte_Lib_Model_WebService::consultar('getDadosCadastroNotas', $aParametros);
      if (is_array($aContribuinte)) {
        $oContribuinteWebService = $aContribuinte[0];
      }
    }

    return $oContribuinteWebService;
  }

  /**
   * Retorna contribuinte pela inscrição municipal
   *
   * @param $iInscricaoMunicipal
   * @return Contribuinte_Model_Contribuinte|null|object
   */
  public static function getByInscricaoMunicipal($iInscricaoMunicipal) {

    $oContribuinteWebService = NULL;

    if ($iInscricaoMunicipal != NULL) {

      $oContribuinteWebService = self::getDadosContribuinteEcidade($iInscricaoMunicipal);
      $iUsuarioLogadoSessao    = Zend_Auth::getInstance()->getIdentity();
      $iUsuarioLogado          = Administrativo_Model_Usuario::getById($iUsuarioLogadoSessao['id']);
      $oUsuarioContribuinte    = Administrativo_Model_UsuarioContribuinte::getByUsuarioContribuinte($iUsuarioLogado,
                                                                                                    $iInscricaoMunicipal);

      // verifica se tá cadastrado e se tem contribuinte vinculado no nota.
      if (empty($oUsuarioContribuinte) || (is_array($oUsuarioContribuinte) && count($oUsuarioContribuinte) == 1)) {

        $oContribuinte = self::preencherInstanciaContribuinte($oContribuinteWebService);

        // caso não exista ela não está cadastrado no nota então ele não tem id ainda
        if (!empty($oUsuarioContribuinte)) {
          $oContribuinte->setIdUsuarioContribuinte($oUsuarioContribuinte[0]->getId());
        }

        return $oContribuinte;
      }
    }

    return NULL;
  }

  /**
   * Retorna contribuinte pelo CNPJ
   *
   * @param string $sCnpj
   * @return Contribuinte_Model_Contribuinte|object
   */
  public static function getByCpfCnpj($sCnpj) {

    $oContribuinteWebService = NULL;

    if ($sCnpj != NULL) {

      // Limpa máscaras
      $sCnpj = DBSeller_Helper_Number_Format::getNumbers($sCnpj);

      // Consulta WebService
      $aParametros   = array(array('cnpj' => $sCnpj), self::$aCampos['getDadosEmpresa']);
      $aContribuinte = Contribuinte_Lib_Model_WebService::consultar('getDadosEmpresa', $aParametros);

      if (is_array($aContribuinte)) {
        $oContribuinteWebService = $aContribuinte[0];
      }
    }

    return self::preencherInstanciaContribuinte($oContribuinteWebService);
  }

  /**
   * Retorna uma instancia de Contribuinte atravéz do código do contribuinte.
   *
   * @param $iCodigoContribuinte
   * @return Contribuinte_Model_Contribuinte|null|object
   */
  public static function getById($iCodigoContribuinte) {

    $oUsuarioContribuinte    = Administrativo_Model_UsuarioContribuinte::getById($iCodigoContribuinte);
    $oContribuinteWebService = self::getDadosContribuinteEcidade($oUsuarioContribuinte->getIm());

    if (empty($oContribuinteWebService)) {
      return NULL;
    }

    $oContribuinte = self::preencherInstanciaContribuinte($oContribuinteWebService);
    $oContribuinte->setIdUsuarioContribuinte($iCodigoContribuinte);

    return $oContribuinte;
  }

  /**
   * Preenche os dados do contribuinte
   * @param null $oContribuinteWebService
   *
   * @return Contribuinte_Model_Contribuinte|null
   */
  private static function preencherInstanciaContribuinte($oContribuinteWebService = NULL) {

    $oContribuinte = NULL;

    if (is_object($oContribuinteWebService)) {

      $oContribuinte = new Contribuinte_Model_Contribuinte();

      if (isset($oContribuinteWebService->razao_social)) {
        $oContribuinte->setRazaoSocial($oContribuinteWebService->razao_social);
      }

      if (isset($oContribuinteWebService->codigo_empresa)) {
        $oContribuinte->setInscricaoMunicipal($oContribuinteWebService->codigo_empresa);
      }

      if (isset($oContribuinteWebService->endereco)) {
        $oContribuinte->setEndereco($oContribuinteWebService->endereco);
      }

      if (isset($oContribuinteWebService->cgm)) {
        $oContribuinte->setCgm($oContribuinteWebService->cgm);
      }

      if (isset($oContribuinteWebService->tipo)) {
        $oContribuinte->setTipoPessoa($oContribuinteWebService->tipo);
      }

      if (isset($oContribuinteWebService->cnpj)) {
        $oContribuinte->setCgcCpf($oContribuinteWebService->cnpj);
      }

      if (isset($oContribuinteWebService->cgccpf)) {
        $oContribuinte->setCgcCpf($oContribuinteWebService->cgccpf);
      }

      if (isset($oContribuinteWebService->nome)) {
        $oContribuinte->setNome($oContribuinteWebService->nome);
      }

      if (isset($oContribuinteWebService->nome_fanta)) {
        $oContribuinte->setNomeFantasia($oContribuinteWebService->nome_fanta);
      }

      if (isset($oContribuinteWebService->identidade)) {
        $oContribuinte->setIdentidade($oContribuinteWebService->identidade);
      }

      if (isset($oContribuinteWebService->inscr_est)) {
        $oContribuinte->setInscricaoEstadual($oContribuinteWebService->inscr_est);
      }

      if (isset($oContribuinteWebService->tipo_lograd)) {
        $oContribuinte->setTipoLogradouro($oContribuinteWebService->tipo_lograd);
      }

      if (isset($oContribuinteWebService->lograd)) {
        $oContribuinte->setDescricaoLogradouro($oContribuinteWebService->lograd);
      }

      if (isset($oContribuinteWebService->numero)) {
        $oContribuinte->setLogradouroNumero($oContribuinteWebService->numero);
      }

      if (isset($oContribuinteWebService->complemento)) {
        $oContribuinte->setLogradouroComplemento($oContribuinteWebService->complemento);
      }

      if (isset($oContribuinteWebService->bairro)) {
        $oContribuinte->setLogradouroBairro($oContribuinteWebService->bairro);
      }

      if (isset($oContribuinteWebService->cod_ibge)) {
        $oContribuinte->setCodigoIbgeMunicipio($oContribuinteWebService->cod_ibge);
      }

      if (isset($oContribuinteWebService->munic)) {
        $oContribuinte->setDescricaoMunicipio($oContribuinteWebService->munic);
      }

      if (isset($oContribuinteWebService->uf)) {
        $oContribuinte->setEstado($oContribuinteWebService->uf);
      }

      if (isset($oContribuinteWebService->cod_pais)) {
        $oContribuinte->setCodigoPais($oContribuinteWebService->cod_pais);
      }

      if (isset($oContribuinteWebService->pais)) {
        $oContribuinte->setDescricaoPais($oContribuinteWebService->pais);
      }

      if (isset($oContribuinteWebService->cep)) {
        $oContribuinte->setCep($oContribuinteWebService->cep);
      }

      if (isset($oContribuinteWebService->telefone)) {
        $oContribuinte->setTelefone($oContribuinteWebService->telefone);
      }

      if (isset($oContribuinteWebService->fax)) {
        $oContribuinte->setFax($oContribuinteWebService->fax);
      }

      if (isset($oContribuinteWebService->celular)) {
        $oContribuinte->setCelular($oContribuinteWebService->celular);
      }

      if (isset($oContribuinteWebService->email)) {
        $oContribuinte->setEmail(strtolower($oContribuinteWebService->email));
      }

      if (isset($oContribuinteWebService->inscricao)) {
        $oContribuinte->setInscricaoMunicipal($oContribuinteWebService->inscricao);
      }

      if (isset($oContribuinteWebService->data_inscricao)) {
        $oContribuinte->setDataInscricao(new DateTime($oContribuinteWebService->data_inscricao));
      }

      if (isset($oContribuinteWebService->tipo_classificacao)) {
        $oContribuinte->setTipoClassificacao($oContribuinteWebService->tipo_classificacao);
      }

      if (isset($oContribuinteWebService->optante_simples)) {
        $oContribuinte->setOptanteSimples($oContribuinteWebService->optante_simples);
      }

      if (isset($oContribuinteWebService->optante_simples_baixado)) {
        $oContribuinte->setOptanteSimplesBaixado($oContribuinteWebService->optante_simples_baixado);
      }

      if (isset($oContribuinteWebService->tipo_emissao)) {
        $oContribuinte->setTipoEmissao($oContribuinteWebService->tipo_emissao);
      }

      if (isset($oContribuinteWebService->exigibilidade)) {
        $oContribuinte->setExigibilidade($oContribuinteWebService->exigibilidade);
      }

      if (isset($oContribuinteWebService->subst_tributaria)) {
        $oContribuinte->setSubstituicaoTributaria($oContribuinteWebService->subst_tributaria);
      }

      if (isset($oContribuinteWebService->regime_tributario)) {
        $oContribuinte->setRegimeTributario($oContribuinteWebService->regime_tributario);
      }

      if (isset($oContribuinteWebService->incentivo_fiscal)) {
        $oContribuinte->setIncentivoFiscal($oContribuinteWebService->incentivo_fiscal);
      }

      if (isset($oContribuinteWebService->subst_tributaria)) {
        $oContribuinte->setDescricaoSubstituicaoTributaria(
                      Contribuinte_Model_SubstitutoTributario::getById($oContribuinteWebService->subst_tributaria)
        );
        $oContribuinte->setDescricaoExigibilidade(
                      Contribuinte_Model_Exigeiss::getById($oContribuinteWebService->exigibilidade)
        );
        $oContribuinte->setDescricaoIncentivoFiscal(
                      Contribuinte_Model_IncentivoFiscal::getById($oContribuinteWebService->incentivo_fiscal)
        );
        $oContribuinte->setDescricaoRegimeTributario(
                      Contribuinte_Model_Tributacao::getById($oContribuinteWebService->regime_tributario)
        );
        $oContribuinte->setDescricaoTipoClassificacao(
                      Contribuinte_Model_TipoEmpresa::getById($oContribuinteWebService->tipo_classificacao)
        );
        $oContribuinte->setDescricaoTipoEmissao(
                      Contribuinte_Model_TipoEmissao::getById($oContribuinteWebService->tipo_emissao)
        );
        $oContribuinte->setDescricaoOptanteSimples('Não');

        if ($oContribuinteWebService->optante_simples == 'Sim' &&
          $oContribuinteWebService->optante_simples_baixado == 'Sim') {
          $oContribuinte->setDescricaoOptanteSimples('Não');
        } else if ($oContribuinteWebService->optante_simples == 'Sim') {
          $oContribuinte->setDescricaoOptanteSimples('Sim');
        }
      }
    }

    return $oContribuinte;
  }

  /**
   * Retorna o tipo de emissão do contribuinte (DMS ou NFSE)
   *
   * @return integer|null
   */
  public function getTipoEmissao() {

    $aParametros   = array(
      array('inscricao' => $this->getInscricaoMunicipal()),
      self::$aCampos['getDadosCadastroNotas']
    );
    $aContribuinte = Contribuinte_Lib_Model_WebService::consultar('getDadosCadastroNotas', $aParametros);
    $iTipoEmissao  = NULL;

    if (is_array($aContribuinte)) {
      $iTipoEmissao = $aContribuinte[0]->tipo_emissao;
    }

    return $iTipoEmissao;
  }

  /**
   * Verifica via webservice se o contribuinte é optante pelo simples na data especificada
   *
   * @param DateTime $oData
   * @throws Exception
   * @return boolean
   */
  public function isOptanteSimples(DateTime $oData) {

    try {

      $aFiltro     = array('inscricao_municipal' => $this->getInscricaoMunicipal(), 'data' => $oData->format('Y-m-d'));
      $aCampos     = array('optante_simples');
      $oWebService = new Contribuinte_Lib_Model_WebService();
      $uRetorno    = $oWebService->consultar('getEmpresaOptanteSimples', array($aFiltro, $aCampos));

      return empty($uRetorno) ? FALSE : TRUE;
    } catch (Exception $oError) {
      throw new Exception("Erro ao verificar se o contribuinte é optante pelo simples: {$oError->getMessage()}");
    }
  }
}