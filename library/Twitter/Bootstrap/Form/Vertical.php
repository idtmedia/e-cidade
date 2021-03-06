<?php

/**
 * Form definition
 *
 * @category Forms
 * @package Twitter_Bootstrap
 * @subpackage Form
 * @author Christian Soronellas <csoronellas@emagister.com>
 */

/**
 * Base class for default form style
 *
 * @category Forms
 * @package Twitter_Bootstrap
 * @subpackage Form
 * @author Christian Soronellas <csoronellas@emagister.com>
 */
class Twitter_Bootstrap_Form_Vertical extends Twitter_Bootstrap_Form {

    /**
     * Class constructor override.
     *
     * @param null $options
     */
    public function __construct($options = null) {
        $this->_initializePrefixes();

        $decorators = array(
            array('FieldSize'),
            array('ViewHelper'),
            array('ElementErrors'),
            array('Description', array('tag' => 'p', 'class' => 'help-block')),
            array('Addon'),
            array('Label', array('class' => 'control-label')));

        if ($options !== null && isset($options['addDecorator'])) {
            foreach ($options['addDecorator'] as $o) {
                $decorators[] = $o;
            }
        }
        $this->setElementDecorators($decorators);



        parent::__construct($options);
    }

}
