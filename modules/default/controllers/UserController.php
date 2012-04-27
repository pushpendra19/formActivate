<?php
/**
 * Base Controller
 * Short description for file.
 *
 * This file is Base controller file. Here is all common methods in back end section 
 *
 * @filesource
 * @package			formsbuilder
 * @subpackage		Index.controller
 * @createdby		Manoj Kumar Chauhan
 * @created			$Date: 2011-01-19 
 * @modifiedby		Manoj Kumar Chauhan
 * @lastmodified	$Date: 2011-01-19
 */
class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
        //echo "test";exit();
        $this->_helper->layout->setLayout('layout');
    }
    

}

?>