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
require_once 'BaseController.php';
require_once('Zend/Session.php');

class MembersController extends BaseController
{

   public function init()
    {
    	$auth=Zend_Auth::getInstance();		
		//allowed actions without login
		//$this->_redirect(WEBSITE_URL.'admin/index');		
    }

     public function indexAction()
    {
    	$auth=Zend_Auth::getInstance();
    	//echo WEBSITE_URL;   	
		//echo "<br>testin index"; exit;
		//allowed actions without login	
		
    }
    
    public function signupAction($id=null)
    {
    	 if($this->getRequest()->getParam('id')){   		    		
    			$members = new members();				
				$member_data = $members->fetchRow($members->select()->where('id='.$this->getRequest()->getParam('id')));
				$this->view->data = $member_data;				
				//print_r($member_data);exit;								
				$this->view->id=$this->getRequest()->getParam('id');				
			}
    	
		if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();
			$firstname = $filter->filter($this->_request->getPost('firstname'));
			 $lastname   = $filter->filter($this->_request->getPost('lastname'));
			 $companyname   = $filter->filter($this->_request->getPost('companyname'));
			 $email  = $filter->filter($this->_request->getPost('email'));
			 $pass   = $filter->filter($this->_request->getPost('pass'));
			 $address1   = $filter->filter($this->_request->getPost('address1'));
			 $address2   = $filter->filter($this->_request->getPost('address2'));
			 $city   = $filter->filter($this->_request->getPost('city'));
			 $state   = $filter->filter($this->_request->getPost('state'));
			 $zip   = $filter->filter($this->_request->getPost('zip'));
			 $phone   = $filter->filter($this->_request->getPost('phone'));			
			$id= $filter->filter($this->_request->getPost('id'));			
			$memberTable = new members();
			
			//if username or password is empty show an error message
			/*if($email!='')
				{	
					if($id>0){
					$member_data = $memberTable->fetchRow($transaction->select()->where('id='.$this->getRequest()->getParam('id') and '$email !='.$email));
					if(count($member_data)>1){
					$login_error->loginError="<font color='white'><b>This email is already in use.</b></font>";
					$this->view->email = $email ;				
				
					}}else{
						$member_data = $memberTable->fetchRow($transaction->select()->where('$email='.$email));
						if(count($member_data)>0){
						$login_error->loginError="<font color='white'><b>This email is already in use.</b></font>";
						$this->view->email = $email ;
						}					
					}
				
				}*/
			
			if( empty($firstname) || empty($lastname) || empty($companyname) || empty($email) ||  empty($pass) || empty($address1) || empty($address2) || empty($city) ||  empty($state) || empty($zip) || empty($phone))
			{
				
				
				if(empty($firstname))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter first name.</b></font>";
				}
				else if(empty($lastname))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter username.</b></font>";
					$this->view->lastname = $lastname ;
				}
				else if(empty($companyname))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter company name.</b></font>";
					$this->view->companyname = $companyname ;
				}
				
				else if(empty($email))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter email.</b></font>";
					$this->view->email = $email ;								
				}			
				
				else if(empty($pass))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter password.</b></font>";
					$this->view->lastname = $pass ;
				}
				else if(empty($address1))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter company name.</b></font>";
					$this->view->address1 = $address1 ;
				}
				
				else if(empty($address2))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter address2.</b></font>";
					$this->view->address2 = $address2 ;
				}
				else if(empty($city))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter city.</b></font>";
					$this->view->city = $city ;
				}
				else if(empty($state))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter state.</b></font>";
					$this->view->state= $state;
				}
				else if(empty($zip))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter zip.</b></font>";
					$this->view->zip = $zip ;
				}
				else if(empty($phone))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter phone.</b></font>";
					$this->view->state= $phone;
				}

				//$this->_redirect('members/signup'); 
			}	
			else
			{
					$members_data['firstname'] = $firstname;
					$members_data['lastname'] = $lastname;
					$members_data['companyname'] = $companyname;
					$members_data['email'] = $email;
					$members_data['pass']= md5($pass);
					$members_data['address1'] = $address1;
					$members_data['address2'] = $address2;
					$members_data['city'] = $city;
					$members_data['state'] = $state;
					$members_data['zip']= $zip;					
					$members_data['phone']= $phone;	
				    $members_data['status'] = 'Inactive';		   		 	
				 	
					if($id!=''){
					 $members->update($members_data, 'id='.$id);					
					 $this->_redirect('members/signupplan/id/'.$id);
					// exit;
					}
					else{			
					$session = new Zend_Session_Namespace('Zend_Auth'); 
				    $last_insert_id=$memberTable->insert($members_data);				   
				    $session->signup_session_id =  $last_insert_id;
				    $this->_redirect('members/signupplan/id/'.$last_insert_id);	
					}
																	
			}
			
		}else{
			//$this->_redirect('members/');	
		}	
			   	    	
    }

    public function signupplanAction()
    {    	 
		$session = new Zend_Session_Namespace('Zend_Auth'); 
    	$id = $this->getRequest()->getParam('id');
		$this->view->id = $id;
		if($id!=$session->signup_session_id){
		$this->_redirect(WEBSITE_URL.'members/signup');		
		}  
		
		$subscriptions = new subscriptions();			
		$subscriptions_data = $subscriptions->fetchAll($subscriptions->select()->where("1"));		
		$this->view->subscriptions = $subscriptions_data;	
		
		$members = new members();	
		$member_data = $members->fetchRow($members->select()->where('id='.$this->getRequest()->getParam('id')));
		$this->view->data = $member_data;
						   	    	
    }
    
  
  	 /**
     * savesingupAction() - Method to save Signup Step 1 Data
     *
     * @access public
	 * @return void
     */
	
	public function signupplansaveAction()
    {	
    	//echo "signupplansave"; exit;
		if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();
			$plan_id= $filter->filter($this->_request->getPost('plan_id'));
			$card_type   = $filter->filter($this->_request->getPost('card_type'));
			$expiry_month  = $filter->filter($this->_request->getPost('expiry_month'));			
			$expiry_year  = $filter->filter($this->_request->getPost('expiry_year'));			
			$card_name   = $filter->filter($this->_request->getPost('card_name'));			
			$card_cvv   = $filter->filter($this->_request->getPost('card_cvv'));
			$card_num = $filter->filter($this->_request->getPost('card_num'));
			$id= $filter->filter($this->_request->getPost('id'));
			
			
			
			$memberTable = new members();			
			
			//if username or password is empty show an error message
			if( empty($plan_id) || empty($card_type) || empty($expiry_month) || empty($expiry_year) ||  empty($card_name) || empty($card_cvv))
			{
				if(empty($plan_id))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please select plan.</b></font>";
				}
				else if(empty($card_type))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please select card type.</b></font>";
					$this->view->card_type = $card_type ;
				}
				else if(empty($expiry_month))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please select expiry month.</b></font>";
					$this->view->expiry_month = $expiry_month ;
				}
				
				else if(empty($expiry_year))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter $expiry_year.</b></font>";
					$this->view->$expiry_year = $expiry_year ;
				}
				else if(empty($card_name))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter name on card.</b></font>";
					$this->view->card_name = $card_name ;
				}
				else if(empty($card_cvv))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter card CVV number.</b></font>";
					$this->view->card_cvv = $card_cvv ;
				}				

				$this->_redirect('members/signupplan/id/'.$id); 
			}	
			else
			{	
					$members_data['plan_id'] = $plan_id;
					$members_data['card_type'] = $card_type;
					$members_data['expiry_month'] = $expiry_month;
					$members_data['expiry_year'] = $expiry_year;
					$members_data['card_name']= $card_name;
					$members_data['card_cvv'] = $card_cvv;	
					$members_data['card_num'] = $card_num;	
					$memberTable->update($members_data, 'id='.$id);					
					$this->_redirect('members/signupplanview/id/'.$id);					
			}
			
		}else{
			$this->_redirect('members/');	
		}
    }  
        
    
   /**
    * function to Transaction Section
    */
	public function signupplanviewAction($id=null)
    {
    	$filter=new Zend_Filter_StripTags();	
		$id = $this->getRequest()->getParam('id');
		
		$session = new Zend_Session_Namespace('Zend_Auth'); 
		if($id==$session->signup_session_id){
		$membersTable = new members();								
		$members_data=$membersTable->fetchRow($membersTable->select()->where('id='.$id));	
		$subscriptions = new subscriptions();	
			
		//$this->view->signup_session_id =  $signup_session_id;
		$this->view->firstname =  $members_data['firstname'];
		$this->view->id =  $members_data['id'];
		$this->view->lastname =  $members_data['lastname'];
		$this->view->email =  $members_data['email'];
		$this->view->companyname =  $members_data['companyname'];
		$this->view->address1 =  $members_data['address1'];
		$this->view->address2 =  $members_data['address2'];
		$this->view->city =  $members_data['city'];
		$this->view->state =  $members_data['state'];
		$this->view->zip =  $members_data['zip'];
		$this->view->phone =  $members_data['phone'];
		//$this->view->plan_id =  $members_data['plan_id'];
		
		$subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id='.$members_data['plan_id']));
		$this->view->plan_id = $subscriptions_data['name'];
		
		$this->view->card_name =  $members_data['card_name'];
		$this->view->card_type =  $members_data['card_type'];	
		$this->view->card_num =  $members_data['card_num'];
		$this->view->card_cvv =  $members_data['card_cvv'];
		$this->view->expiry_month =  $members_data['expiry_month'];
		$this->view->expiry_year =  $members_data['expiry_year'];	
		}else{
		$this->_redirect('members/signup/');		
		}    	   
    }
    
    
    /**
    * function to Transaction Section
    */   
    
   public function emailvalidationAction()
	{		
		if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();			
			$id= $filter->filter($this->_request->getPost('id'));		 
		 	$email= $filter->filter($this->_request->getPost('email'));		 
			$member = new Member();	
			if($id>0){
			$data=$member->fetchRow(array('id != ?' => $id,'email = ?' => $email));
			}else{
			$data=$member->fetchRow(array('email = ?' => $email));
			}
			//print_r($data['firstname']); exit;
			echo count($data);exit;	
		
		}
	}
	
	/**
     * login() -Method to login to admin section
     *
     * @access public
	 * @return void
     */
	
	public function loginAction()
    {
		if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();
			$email = $filter->filter($this->_request->getPost('email'));
			$pass   = $filter->filter($this->_request->getPost('pass'));
			
			//if username or password is empty show an error message
			if( empty($email) || empty($pass) )
			{
				if(empty($pass) && empty($email))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter email and password.</b></font>";
				}
				else if(empty($email))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter email.</b></font>";
					$this->view->email = $email ;
				}
				else if(empty($pass))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='white'><b>Please enter password.</b></font>";
					$this->view->$pass = $pass ;
				}

				$this->_redirect('/members/login'); 
			}	
			else
			{						
				$filter=new Zend_Filter_StripTags();	
				$member = new members();				
				$data=$member->fetchRow(array('pass = ?' => md5($pass),'email = ?' => $email));
				if(count($data)>0){					
					$session = new Zend_Session_Namespace('Zend_Auth'); 
					$session->member_id=$data['id'];
					$session->email=$data['email'];
					$this->_redirect('/members/home'); 
				}else{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError='Your email address or password was not recognized. Please re-enter this information below.';
				}
				
			}
			
		}else{
			//$this->checklogin();
		}
    }
    
    /**
     * logout() -Method to logout from admin section
     *
     * @Zend_Auth :: getInstance mehtods to get the instant of class and clear identity
	 * @return void
     */

	function logoutAction()
	{
		Zend_Auth::getInstance()->clearIdentity();
		$this->_redirect('/members/login');
		exit;
	}

	/**
     * homepage() -Method after logged In to Home
     *
     */

	function homeAction()
	{
		//Zend_Auth::getInstance()->clearIdentity();
		//$this->_redirect('/member/login');
		//exit;
	}
	
	/**
     * forgotpassword() - method to send email with password 
     *
     * @admin an object of Admin Model
	 * @mail an object of Zend_Mail class model
	 * @loginError an variable to show message
	 * @access public
     * @return void
     */

     public function forgotpasswordAction() 
     {
       //$this->_redirect('/members/forgotpassword');
     } 	
}
?>