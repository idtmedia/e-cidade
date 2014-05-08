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
 * Model Responsavel por retornar os dados da prefeitura
 *
 * @author  dbeverton.heckler
 * @package Administrativo/Models
 */

/**
 * @author  dbeverton.heckler
 * @package Administrativo/Models
 */
class Administrativo_Model_Prefeitura {

  /**
   * Retorna os dados da prefeitura do webservice
   *
   * @tutorial Campos de Retorno para exemplo:
   *           $oRetorno->sDescricao                = PREFEITURA MUNICIPAL DE TESTE DO SUL
   *           $oRetorno->sDescricaoAbreviada       = PM TESTE DO SUL
   *           $oRetorno->sCnpj                     = XXXXXXXXXXXXXXX
   *           $oRetorno->sLogradouro               = LOGRADOURO DA PREFEITURA
   *           $oRetorno->sMunicipio                = MUNICIPIO
   *           $oRetorno->sBairro                   = BAIRRO
   *           $oRetorno->sTelefone                 = XX XXXXXXXX
   *           $oRetorno->sSite                     = www.prefeitura.rs.gov.br
   *           $oRetorno->sEmail                    = contato@prefeitura.rs.gov.br
   *           $oRetorno->iCodigoMunicipio          = 531654
   *           $oRetorno->iNumeroCgm                = 5
   *           $oRetorno->sLogoPrefeituraBaseEncode = files/imagens/brasao_5249e01492774.jpg
   * @return mixed
   * @throws Exception
   */
  public static function getDadosPrefeituraWebService() {

    try {

      $aValores      = array('lprefeitura' => TRUE);
      $oDadosRetorno = Administrativo_Lib_Model_WebService::processar('DadosPrefeitura', $aValores);

      if ($oDadosRetorno->sLogoPrefeituraBaseEncode != '') {

        $sImagem      = $oDadosRetorno->sLogoPrefeituraBaseEncode;
        $sImagem      = str_replace('data:image/png;base64,', '', $sImagem);
        $sImagem      = str_replace(' ', '+', $sImagem);
        $oImagemDados = base64_decode($sImagem);
        $sNomeArquivo = 'brasao.jpg';
        $lSucesso     = file_put_contents('global/img/' . $sNomeArquivo, $oImagemDados);

        unset($oDadosRetorno->sLogoPrefeituraBaseEncode);

        if (!$lSucesso) {
          throw new Exception('Problemas ao importar o arquivo logo da prefeitura.');
        }
      }

      return $oDadosRetorno;
    } catch (Exception $oErro) {
      throw new Exception("Erro para carregar dados da Prefeitura: {$oErro->getMessage()}");
    }
  }

  /**
   * Retorna os dados da prefeitura
   *
   * @return mixed
   * @throws Exception
   */
  public static function getDadosPrefeituraBase() {

    $oDadosPrefeitura = Administrativo_Model_ParametroPrefeitura::getAll();

    if (count($oDadosPrefeitura) > 1) {
      throw new Exception('Cadastro de Prefeitura Inconsistente. Favor entre em contato com o Suporte');
    }

    return $oDadosPrefeitura[0];
  }
}