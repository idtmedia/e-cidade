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
 * Classe para controle dos parametros de cada contribuinte.
 *
 * @author dbseller
 * @package Contribuinte
 * @subpackage Model
 */
class Contribuinte_Model_ParametroContribuinte extends Contribuinte_Lib_Model_Doctrine {

  static protected $entityName = "Contribuinte\ParametroContribuinte";
  static protected $className  = __CLASS__;

  /**
   * Entity do model
   * @var Contribuinte\ParametroContribuinte
   */
  protected $entity;
  
  /**
   * Cria uma nova instancia de ParametroContribuinte
   * @param Contribuinte\ParametroContribuinte $entity Entidade com os dados do parametro
   */
  public function __construct(Contribuinte\ParametroContribuinte $entity = null) {
    
    parent::__construct($entity);
    if ($entity === null) {
      $this->entity->setMaxDeducao(0);
    }
  }
  
  /**
   * Persiste os dados do parametro do contribuinte
   */
  public function salvar() {
    
    $em = $this->getEm();
    $em->persist($this->entity);
    $em->flush();
  }
}