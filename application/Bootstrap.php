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


require_once APPLICATION_PATH . '/../library/Doctrine/Common/ClassLoader.php';

use Doctrine\Common\Cache\ApcCache as DoctrineApcCache;
use Doctrine\Common\Cache\ArrayCache as DoctrineArrayCache;
use Doctrine\Common\ClassLoader;
use Doctrine\Common\EventManager as DoctrineEventManager;
use Doctrine\ORM\Configuration as DoctrineConfiguration;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;

/**
 * Responsável pelo inicialização da aplicação
 *
 * @tutorial Todos os método desta classe serão executados em "TODOS" os módulos, antes de qualquer controller e
 *           na ordem em que estão dispostos na classe.
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

  /**
   * Configura os módulos
   */
  protected function _initAutoload() {

    $oIniConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
    $aIniConfig = $oIniConfig->toArray();
    $aModules   = array();

    if (isset($aIniConfig['production']['app']['module'])) {
      $aModules = $aIniConfig['production']['app']['module'];
    }

    // Estancia os modulos
    foreach ($aModules as $sModule => $sNamespace) {

      $oModule = new Zend_Application_Module_Autoloader(array(
                                                          'namespace' => (String) ucfirst($sNamespace),
                                                          'basePath'  => APPLICATION_PATH . "/modules/{$sModule}"
                                                        ));
      $oModule->addResourceTypes(array(
                                   'library'   => array('path' => 'library/', 'namespace' => 'Lib'),
                                   'dao'       => array('path' => 'dao/', 'namespace' => 'Dao'),
                                   'form'      => array('path' => 'forms/', 'namespace' => 'Form'),
                                   'model'     => array('path' => 'models/', 'namespace' => 'Model'),
                                   'interface' => array('path' => 'interfaces/', 'namespace' => 'Interface')
                                 ));
    }
  }

  /**
   * Configura as bibliotecas externas
   *
   * @todo Usado pela E2Tecnologia, analisar e remover para utilizat somente pelo INI
   */
  public function _initClassLoaders() {

    $oLoader = new ClassLoader('Doctrine\ORM');
    $oLoader->register();

    $oLoader = new ClassLoader('Doctrine\Common');
    $oLoader->register();

    $oLoader = new ClassLoader('Doctrine\DBAL');
    $oLoader->register();

    $oLoader = new ClassLoader('Symfony', 'Doctrine');
    $oLoader->register();

    $oLoader = new ClassLoader('Entity', APPLICATION_PATH . '/default/models');
    $oLoader->register();

    $oLoader = new ClassLoader('Administrativo', APPLICATION_PATH . '/entidades');
    $oLoader->register();

    $oLoader = new ClassLoader('Geral', APPLICATION_PATH . '/entidades');
    $oLoader->register();

    $oLoader = new ClassLoader('Contribuinte', APPLICATION_PATH . '/entidades');
    $oLoader->register();
  }

  /**
   * Configura os parâmetros do Doctrine
   *
   * @return DoctrineEntityManager
   */
  public function _initDoctrineEntityManager() {

    $this->bootstrap(array('classLoaders', 'doctrineCache'));

    $aZendConfig           = $this->getOptions();
    $aConnectionParameters = $aZendConfig['doctrine']['connectionParameters'];
    $oConfiguration        = new DoctrineConfiguration();
    $oConfiguration->setAutoGenerateProxyClasses($aZendConfig['doctrine']['autoGenerateProxyClasses']);
    $oConfiguration->setProxyDir($aZendConfig['doctrine']['proxyPath']);
    $oConfiguration->setProxyNamespace($aZendConfig['doctrine']['proxyNamespace']);
    $oConfiguration->setMetadataDriverImpl(
                   $oConfiguration->newDefaultAnnotationDriver($aZendConfig['doctrine']['entityPath']));

    $oEventManager  = new DoctrineEventManager();
    $oEntityManager = DoctrineEntityManager::create($aConnectionParameters, $oConfiguration, $oEventManager);

    Zend_Registry::set('em', $oEntityManager);

    return $oEntityManager;
  }

  /**
   * Configura o cache do Doctrine
   *
   * @return DoctrineApcCache|DoctrineArrayCache|null
   */
  public function _initDoctrineCache() {

    $oCache = NULL;

    if (APPLICATION_ENV === 'development') {
      $oCache = new DoctrineArrayCache();
    } else {
      $oCache = new DoctrineApcCache();
    }

    return $oCache;
  }

  /**
   * Configura o sistema
   *
   * @todo Usado pela E2Tecnologia, analisar e retirar esse passo
   * @return Zend_Config
   */
  protected function _initConfig() {

    $oConfig = new Zend_Config($this->getOptions(), TRUE);
    Zend_Registry::set('config', $oConfig);

    return $oConfig;
  }

  /**
   * Configura o Log
   *
   * @return Zend_Log
   */
  protected function _initLogger() {

    $this->bootstrap('log');
    $oLogger = $this->getResource('log');
    $oLogger->setEventItem('ip', $_SERVER['REMOTE_ADDR']);
    Zend_Registry::set('logger', $oLogger);

    return $oLogger;
  }

  /**
   * Configura a sessão do PHP
   */
  protected function _initSession() {

    if (!Zend_Session::isStarted()) {
      Zend_Session::start(TRUE);
    }
  }

  /**
   * Configura as permissões ACL (Lista de Controle de Acesso)
   *
   * @return DBSeller_Acl_Setup
   */
  protected function _initAcl() {

    return new DBSeller_Acl_Setup();
  }

  /**
   * Configura o Log de traduções
   *
   * @return Zend_Translate|null
   */
  protected function _initLoggerTranslate() {

    // Somente em ambiente de desenvolvimento
    if (APPLICATION_ENV == 'development') {

      $oWriter    = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../logs/translate.log', 'a');
      $oLog       = new Zend_Log($oWriter);
      $oResource  = $this->getPluginResource('translate');
      $oTranslate = $oResource->getTranslate();
      $oTranslate->setOptions(array('log' => $oLog, 'logUntranslated' => TRUE));

      return $oTranslate;
    }

    return NULL;
  }
}