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
 * Classe para controle do módulo do WebService
 *
 * @package WebService/Controllers
 * @see Webservice_Lib_Controller_AbstractController
 */
class WebService_IndexController extends WebService_Lib_Controller_AbstractController{

  /**
   * Executa os métodos no ambiente de produção do webservice SOAP
   */
  public function producaoAction() {

    $this->noLayout();
    
    try {
    
      ini_set("soap.wsdl_cache_enabled", "0");
      
      $sWsdl  = PUBLIC_PATH . '/webservice/wsdlValidations/modelo1.wsdl';
      $server = new SoapServer($sWsdl,
          array('soap_version' => SOAP_1_1,
                 'uri'          => Zend_Registry::get('config')->webservice->server->uri,
                 'trace'        => true
      ));
      
      $server->setClass('WebService_Model_Processar');
      $server->addFunction('RecepcionarLoteRps'); 
       
      $server->handle();
    } catch ( Exception $oExcecao ) {
      throw new SoapFault(utf8_encode($oExcecao->getMessage()));
    }
  }
  
  /**
   * Executa os métodos no ambiente de homologação do webservice SOAP
   */
  public function homologacaoAction() {
  
    $this->noLayout();
  
    try {
  
      ini_set("soap.wsdl_cache_enabled", "0");
  
      $sWsdl  = PUBLIC_PATH . '/webservice/wsdlValidations/modelo1.wsdl';
      $server = new SoapServer($sWsdl,
          array('soap_version' => SOAP_1_1,
              'uri'          => Zend_Registry::get('config')->webservice->server->uri,
              'trace' => true
          ));
  
      $server->setClass('WebService_Model_Processar');
      $server->addFunction('RecepcionarLoteRps');
       
      $server->handle();
    } catch ( Exception $oExcecao ) {
      throw new SoapFault(utf8_encode($oExcecao->getMessage()));
    }
  }  
}