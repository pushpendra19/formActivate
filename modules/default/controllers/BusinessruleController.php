<?php
/**
 * Short description for file.
 *
 * This file is Businessrule controller file. Here is all methods related with member in back end section 
 *
 * @filesource
 * @package			formsbuilder
 * @subpackage		formsbuilder.controller
 * @createdby		Manoj Kumar Chauhan
 * @created			$Date: 2011-01-20 
 * @modifiedby		Manoj Kumar Chauhan
 * @lastmodified	$Date: 2011-01-20
 */

require_once 'BaseController.php';
class BusinessruleController extends BaseController
{
     /**
     * init() -Method to call prior to every function call in this class
     *
     * @access public
	 * @return void
     */
  
	public function init()
    {
    	$auth=Zend_Auth::getInstance();
		$this->view->UserStatus=$auth->hasIdentity();
		
		//allowed actions without login
		$allowed_action = array();
	
		if(!in_array($this->getRequest()->getActionName(),$allowed_action))
		{		
			if(!$auth->hasIdentity())
			{	
			  $this->_redirect(WEBSITE_URL.'admin/index');
			}
		}		   	    	
    }
    
    /**
     * indec() - Method to show Business Rule Listing 
     *
     * @access public
	 * @return void
     */
    
    public function BusinessruleAction()
    {
		//echo "testing";	 exit;
    	$businessrule = new Businessrule();			
			$businessrule_data = $businessrule->fetchAll($businessrule->select()->where("1"));		
			$this->view->businessrules = $businessrule_data;	
			//$this->render();	
    }
    

	// Change Customer Status.
	public function changestatusAction()
	{		
		$businessrule = new Businessrule();
		
		if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();			
			$businessrule = new Businessrule();			 			 	
			$rule_id= $filter->filter($this->_request->getPost('rule_id'));		 
			$status= $filter->filter($this->_request->getPost('status'));
			if($status==1){ $data['status']=0;}else{ $data['status']=1;}		 
			$n = $businessrule->update($data, "rule_id =".$rule_id);
			echo $data['status'];exit;					
		}
	}
	
	 /**
     * edit() - method to edit  user details in admin section
     *
     * @user an object of User Model
	 * @data and array of user data
	 * @signup_error Zend_Session_Namespace
     * @param  array $data
     * @return void
     */
	public function editAction()
	{			
		$businessrulesTable = new Businessrule();		
		$data=array();	
    	$filter=new Zend_Filter_StripTags();	
		$rule_id = $this->getRequest()->getParam('rule_id');	
		if($rule_id>0){								
		$businessrules_data=$businessrulesTable->fetchRow($businessrulesTable->select()->where('rule_id='.$rule_id));	
		$this->view->rule_desc =  $businessrules_data['rule_desc'];
		$this->view->rule_id =  $businessrules_data['rule_id'];		
		}			
	}
	
	
	 
	// Delete customer.
	public function deleteAction()
	{
		
		if($this->getRequest()->getParam('rule_id'))
        {	 
        	$businessrule = new Businessrule();
			$businessrule->delete("rule_id=".$this->getRequest()->getParam('rule_id'));	
			$signup_error = new Zend_Session_Namespace('Zend_Auth');
			$signup_error->Errormsg="<font color='red'><b>Businessrule has been deleted successfully!</b></font>";
			$this->_redirect('/admin/businessrule');
			exit;
		}
	}
	
		// Delete customer.
	public function emailvalidationAction()
	{		
		if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();			
			$id= $filter->filter($this->_request->getPost('id'));		 
		 	$email= $filter->filter($this->_request->getPost('email'));		 
			$businessrule = new Businessrule();	
			$data=$businessrule->fetchRow(array('id != ?' => $id,'email = ?' => $email));
			//print_r($data['firstname']); exit;
			echo count($data);exit;	
		
		}
	}
}