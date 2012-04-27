<?php
/**
 * Base Controller
 * Short description for file.
 *
 * This file is Base controller file. Here is all common methods in back end section 
 *
 * @filesource
 * @package			formsbuilder
 * @subpackage		formsbuilder.controller
 * @createdby		Manoj Kumar Chauhan
 * @created			$Date: 2011-01-19 
 * @modifiedby		Manoj Kumar Chauhan
 * @lastmodified	$Date: 2011-01-19
 */

class BaseController extends Zend_Controller_Action
{
	
	
	
	// function to check the login in the admin section
	public function checklogin()
	{
		$admin_usertype ='';	
		$auth=Zend_Auth::getInstance();
		$obj = new Zend_Session_Namespace('Zend_Auth');		
		if(isset($obj->storage->usertype))$admin_usertype = $obj->storage->usertype;	
		// if session exists but it is not admin
		// if session exists but action is of login
		if($auth->hasIdentity() && $this->getRequest()->getActionName()=='login' )
		{
			$this->_redirect('/');
		}	
		// if session does not exists
		if(!$auth->hasIdentity())
		{
			//$this->_forward('login');
			Zend_Auth::getInstance()->clearIdentity();
			$this->_redirect('/');
		}
		
		$this->_request->setDispatched(true);
	}
	
}
