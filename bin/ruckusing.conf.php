<?php

//----------------------------
// DATABASE CONFIGURATION
//----------------------------

/**
 * Pega a configuração do application.ini
 *
 * @tutorial
 *   Em desenvolvimento deve ser informado os parametros: host, port, dbname, user, password
 */
$sApplicationIni = realpath(dirname(__FILE__) . '/../application/configs/application.ini');
$aApplicationIni = parse_ini_file($sApplicationIni, TRUE);
$aDbConfig       = NULL;

if (isset($aApplicationIni['production'])) {

  $aConfig                             = $aApplicationIni['production'];
  $aDbConfig['production']['type']     = 'pgsql';
  $aDbConfig['production']['host']     = $aConfig['doctrine.connectionParameters.host'];
  $aDbConfig['production']['port']     = $aConfig['doctrine.connectionParameters.port'];
  $aDbConfig['production']['database'] = $aConfig['doctrine.connectionParameters.dbname'];
  $aDbConfig['production']['user']     = $aConfig['doctrine.connectionParameters.user'];
  $aDbConfig['production']['password'] = $aConfig['doctrine.connectionParameters.password'];
}

if (isset($aApplicationIni['development : production'])) {

  $aConfig                              = $aApplicationIni['development : production'];
  $aDbConfig['development']['type']     = 'pgsql';
  $aDbConfig['development']['host']     = $aConfig['doctrine.connectionParameters.host'];
  $aDbConfig['development']['port']     = $aConfig['doctrine.connectionParameters.port'];
  $aDbConfig['development']['database'] = $aConfig['doctrine.connectionParameters.dbname'];
  $aDbConfig['development']['user']     = $aConfig['doctrine.connectionParameters.user'];
  $aDbConfig['development']['password'] = $aConfig['doctrine.connectionParameters.password'];
}

if (!$aDbConfig) {
  exit("\n\nVerifique as configurações no arquivo de configuração (application.ini).\n\n\n");
}

/**
 * Valid types (adapters) are Postgres & MySQL:
 * 'type' must be one of: 'pgsql' or 'mysql'
 */
return array(
  'db'             => $aDbConfig,
  'migrations_dir' => RUCKUSING_WORKING_BASE . DIRECTORY_SEPARATOR . 'migrations',
  'db_dir'         => RUCKUSING_WORKING_BASE . DIRECTORY_SEPARATOR . 'db',
  'log_dir'        => RUCKUSING_WORKING_BASE . DIRECTORY_SEPARATOR . 'logs',
  'ruckusing_base' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '/../library/ruckusing-migrations'
);