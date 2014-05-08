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
 * Classe responsável pela instalação do sistema
 *
 * @package Administrativo
 * @see     Administrativo_Lib_Controller_AbstractController
 */

/**
 * @package Administrativo
 * @see     Administrativo_Lib_Controller_AbstractController
 */
class Administrativo_InstalacaoController extends Administrativo_Lib_Controller_AbstractController {

  /**
   * Página inicial
   */
  public function indexAction() {

    $aConfiguracoes   = Zend_Registry::get('config')->doctrine->connectionParameters;
    $this->view->form = new Administrativo_Form_ParametrosConexao($aConfiguracoes);

    if ($this->getRequest()->isPost()) {

      if ($this->view->form->isValid($_POST)) {

        $oDadosInstaladorSistema = $this->getRequest()->getPost();

        // PREPARA APPLICATION INI (INICIO)
        $aParametrosAlterar = array(
          'doctrine.connectionParameters.host'     => $oDadosInstaladorSistema['servidor'],
          'doctrine.connectionParameters.user'     => $oDadosInstaladorSistema['usuario'],
          'doctrine.connectionParameters.password' => $oDadosInstaladorSistema['senha'],
          'doctrine.connectionParameters.dbname'   => $oDadosInstaladorSistema['base_dados'],
          'doctrine.connectionParameters.port'     => $oDadosInstaladorSistema['porta'],
          'webservice.client.url'                  => $oDadosInstaladorSistema['client_url'],
          'webservice.client.location'             => $oDadosInstaladorSistema['client_location'],
          'webservice.client.uri'                  => $oDadosInstaladorSistema['client_uri']
        );

        $oArquivo = file(APPLICATION_PATH . '/configs/application.ini');

        $sLinhaArquivoNovo = array();

        foreach ($oArquivo as $ikey => $sLinha) {

          $aParametro = explode('=', $sLinha);
          $aParametro = reset($aParametro);

          if (array_key_exists(trim($aParametro), $aParametrosAlterar)) {
            $sLinhaArquivoNovo[$ikey] = trim($aParametro) . " = " . trim($aParametrosAlterar[trim($aParametro)]) . "\n";
          } else {
            $sLinhaArquivoNovo[$ikey] = trim($sLinha) . "\n";
          }
        }

        $oArquivo     = implode($sLinhaArquivoNovo);
        $sNomeArquivo = APPLICATION_PATH . '/configs/application.ini';

        if (is_writable($sNomeArquivo)) {

          if (!$handle = fopen($sNomeArquivo, 'w+')) {

            echo "Não foi possível abrir o arquivo ($sNomeArquivo)<br>";
            exit;
          }

          if (fwrite($handle, $oArquivo) === FALSE) {

            echo "Não foi possível escrever no arquivo ($sNomeArquivo)<br>";
            exit;
          }

          //echo "Sucesso: Escrito no arquivo ($sNomeArquivo)";

          fclose($handle);
        } else {

          echo "O arquivo $sNomeArquivo não pode ser alterado";
          exit;
        }
        // PREPARA APPLICATION INI (FIM)

        // PREPARA BANCO DE DADOS (INI)
        try {

          new PDO("pgsql:host={$oDadosInstaladorSistema['servidor']} port={$oDadosInstaladorSistema['porta']}
                             dbname={$oDadosInstaladorSistema['base_dados']}",
                  $oDadosInstaladorSistema['usuario'], $oDadosInstaladorSistema['senha']);
        } catch (Exception $e) {

          if ($e->getCode() == 7) {

            $oConexao = new PDO("pgsql:host={$oDadosInstaladorSistema['servidor']} port={$oDadosInstaladorSistema['porta']}",
                                $oDadosInstaladorSistema['usuario'], $oDadosInstaladorSistema['senha']);

            $oConexao->query("CREATE DATABASE {$oDadosInstaladorSistema['base_dados']};");
          }
        }

        chdir(dirname(APPLICATION_PATH));

        $sComando = '/usr/bin/php bin/ruckus.php ';
        $sComando .= escapeshellarg('db:migrate') . ' ' . escapeshellarg('env=' . APPLICATION_ENV);

        echo exec($sComando, $aRetorno, $iCodigoRetorno);

        if ($iCodigoRetorno != 0) {
          throw new Exception('Falha na migração, foram encontrados erros nos scripts de migração.<br>' . $aRetorno);
        }

        $this->_redirector->gotoSimple('index', 'logout', 'auth');
      }
    } else {
      $this->view->form->preenche();
    }
  }
}