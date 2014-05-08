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
 * Form Responsável pela Liberacao do Cadastro de Pessoa (eventual)
 */
class Fiscal_Form_CadastroPessoaLiberacao extends Twitter_Bootstrap_Form_Vertical {
  
  /**
   * Construtor da classe, utilizado padrão HTML para uso do TwitterBootstrap
   *
   * @param string $aOptions
   * @see Twitter_Bootstrap_Form_Horizontal
   */
  public function __construct($aOptions = NULL) {
    parent::__construct($aOptions);
  }

  /**
   * Renderiza o formulário
   *
   * @see Zend_Form::init()
   * @return Zend_form
   */
  public function init() {
    
    $this->setName('formCadastroPessoaLiberacao');
    $this->setAction('/fiscal/usuario-eventual/liberar-cadastro-salvar');
    $this->setMethod('post');
    
    $oElm = $this->createElement('hidden', 'id');
    $this->addElement($oElm);
    
    $oElm = $this->createElement('hidden', 'cnpjcpf');
    $this->addElement($oElm);
    
    $oElm = $this->createElement('select', 'tipo_liberacao');
    $oElm->setLabel('Tipo de Liberação:');
    $oElm->setAttrib('class', 'span5');
    $oElm->setRequired(TRUE);
    $this->addElement($oElm);
    
    return $this;
  }

  /**
   * Carrega a lista de tipos de liberação, seta o valor do elemento se informado 
   * 
   * @param boolean $lOcultaCgm
   * @param integer $iIdTipoLiberacao
   */
  public function carregarTipoLiberacao($lOcultaCgm = TRUE, $iIdTipoLiberacao = NULL) {
    
    $aTiposLiberacao[''] = '- Selecione -';
    
    if ($lOcultaCgm) {
      $aTiposLiberacao[Contribuinte_Model_CadastroPessoa::TIPO_LIBERACAO_USUARIO]     = 'Liberar Usuário';
    } else {
      $aTiposLiberacao[Contribuinte_Model_CadastroPessoa::TIPO_LIBERACAO_USUARIO_CGM] = 'Liberar Usuário e Gerar CGM';
    }
    
    $this->tipo_liberacao->addMultiOptions($aTiposLiberacao);
    $this->tipo_liberacao->setValue($iIdTipoLiberacao);
  }
}