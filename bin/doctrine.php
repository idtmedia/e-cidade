<?php

require_once("bootstrap.php");

/**
* @var Doctrine\ORM\EntityManager $entityManager
*/
$entityManager = Zend_Registry::get('em');
$helperSet = new \Symfony\Component\Console\Helper\HelperSet(
                 array('db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($entityManager->getConnection()),
                       'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($entityManager)));

\Doctrine\ORM\Tools\Console\ConsoleRunner::run($helperSet);

?>
