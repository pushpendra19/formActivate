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
 * @createdby		Saurabh Agarwal
 * @created			$Date: 2011-02-17 
 * @modifiedby		Saurabh Agarwal
 * @lastmodified	$Date: 2011-02-21
 */
require_once 'BaseController.php';
//require_once('Zend/Session.php');

class FormsController extends BaseController
{
	
	   /**
	     * init() -Method for zend initialization
	     *
	     * @access public
		 * @return void
	     */	
	   public function init()
	    {
	       //echo date('h:i:s');
			date_default_timezone_set('America/Los_Angeles');
	    	$auth=Zend_Auth::getInstance();	
	    	$session = new Zend_Session_Namespace('Zend_Auth');        
	        
	        if(!empty($session->member_id) && ($session->user_type==0) )
	        {
	            if($this->limitTrialUserAccess($session->member_id))
	                $this->_redirect('/customers/editplandetails/id/'.$session->member_id);
			}

    	//allowed actions without login
		//$this->_redirect(WEBSITE_URL.'admin/index');	
		
        $string=$_SERVER['REQUEST_URI'];

        $authorizationkey=$this->getRequest()->getPost('authorizationkey');
        $form_id=$this->getRequest()->getPost('fid');

        $runtime_session= new Zend_Session_Namespace('Zend_Auth');

        if(($authorizationkey!='' && $form_id!='') || $runtime_session->runtime_session!=''){

        }else{
                $find='inquiry';
                $pos=strpos($string,$find);

                $getform = "getform";
                $pos2 = strpos($string, $getform);

                if($pos===false){
                    if(empty($session->member_id) && $pos2 === false) {$this->_redirect('/customers/login'); 		 }
                }
        }
        //echo "49"; exit;
    }

    /////////////////////////////////////////////////////////////////////////////////////////////
    function limitTrialUserAccess($cid)
    {
        $session = new Zend_Session_Namespace('Zend_Auth');
        
        $members = new members();
        $member_data = $members->fetchRow($members->select()->where('id='.$cid));
        $session->plan_id = $plan_id = $member_data['plan_id'];
        
        if($plan_id == 5)
        {
            $today = strtotime(date("Y-m-d"));
            $plan_end_date = strtotime($member_data['plan_end_date']);

            if($today >= $plan_end_date && $member_data['login_override'] != 'YES'){
                $session->trialMsg = "Your 7 day trial has expired. Please select a plan from the list below. You are free to switch plans at any time - there will never be a termination or activation fee for any FormActivate plans.";
                return true;
            }
        }

        return false;
    }


    function getTotalCustomerRulesByCustomerID($cid)
    {
        $customers_rule = new customerrules();
        $customers_rule_data = $customers_rule->fetchAll($customers_rule->select()->where('customer_id='.$cid));

        return count($customers_rule_data);
    }

    function getTotalCustomerFormsByCustomerID($cid)
    {
        $forms = new Forms();
        $forms_data = $forms->fetchAll($forms->select()->where('customer_id='.$cid));

        return count($forms_data);
    }

    /////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * index() -Method for index page
     *
     * @access public
	 * @return void
     */
    public function indexAction()
    {
    	
    	$session = new Zend_Session_Namespace('Zend_Auth'); 
    	$this->view->actionName = 'forms';    	
    	
		//check user is logged in or not		
		
		if(!empty($session->member_id) || ($session->user_type==1)){
		
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
		//exit;
		
		// sorting parameters
		$field_name=$this->getRequest()->getParam('name');
		$sort=$this->getRequest()->getParam('sort');
						
		$this->view->columnname = $field_name;	
		$this->view->sortby = $sort;	
		$order_by = 'id desc';
		
			if(!empty($field_name) && !empty($sort))
			{
				if($field_name=='date')
				{
					$order_by="id ".$sort;
				}
				else
				{
					$order_by=$field_name." ".$sort;
				}
			}
			
			$formTable=new Forms();
			$forms_data = array();
			$forms_data=$formTable->fetchAll($formTable->select()->where('customer_id="'.$session->member_id.'"')->order($order_by));
			
			if(count($forms_data)>0)
			$this->view->form_type = $forms_data['form_type'];
		
			$session_form_sorting = new Zend_Session_Namespace('Zend_Auth'); 
			
			if($this->_getParam('page')!=''){
			$page_number=$this->_getParam('page');
			}elseif($session_form_sorting->page!=''){
			$page_number=$session_form_sorting->page;		
			}else{
			$page_number=1;
			}			
			$this->view->page_number = $page_number;
			
		$this->view->total_forms = count($forms_data);
		
		$total_active_forms=$formTable->fetchAll($formTable->select()->where('status=1 and customer_id="'.$session->member_id.'"'));				
		
		$this->view->total_active_forms =count($total_active_forms);
						

			if(count($forms_data))
			{
				$paginator  = Zend_Paginator::factory($forms_data); 
				$view=Zend_View_Helper_PaginationControl::setDefaultViewPartial('partials/my_pagination_control.phtml');   
				$paginator->setItemCountPerPage(10) 
						  ->setPageRange(10) 
						  ->setCurrentPageNumber($this->_getParam('page')); 
				$paginator->setDefaultScrollingStyle('Sliding');
				$paginator->setView($view);

				$this->view->paginator = $paginator;
				$this->view->fromtypes = $paginator;
				$this->view->pageno = $this->_getParam('page');
				$this->view->recordPerPage = 10;
			}			
			return;  
    }


    /**
     * validatePhoneNumberAction() -Method . This method will validate customer phone number on FORM OVERVIEW STEP
     *
     * @access public
	 * @return void
     */

    public function validatePhoneNumberAction()
    {
        $filter=new Zend_Filter_StripTags();
        
        $this->_helper->layout->disableLayout();

        $client_number = trim($filter->filter($this->_request->getPost('number')));

        $number_type = trim($filter->filter($this->_request->getPost('number_type')));

        $form_id = trim($filter->filter($this->_request->getPost('form_id')));

        $phone_type = trim($filter->filter($this->_request->getPost('phone_type')));
		
        $session = new Zend_Session_Namespace('Zend_Auth');
        
        $customerId = $session->member_id;
        $number = $client_number;

        $formats = array(
                    '##########',
                    '###-###-####',
                    '(###)###-####',
                    '(###)#######',
                    '(###) ###-####',
                    '(###) #######',
                    '###.###.####'
                );

        $number = trim(preg_replace('/[0-9]/', '#', $number));

        if (in_array($number, $formats)) {
            require "twilio.php";

            /* Twilio REST API version */

           		$formTable=new Forms();
				$forms_data = array();
             	$form_data[$phone_type] = 0;
             	$formTable->update($form_data,'id='.$form_id. ' and customer_id='.$customerId);
            $ApiVersion = TWILIO_API_VERSION; // config variable
            $AccountSid = TWILIO_ACCOUNT_SID; // take from config file
            $AuthToken  = TWILIO_AUTH_TOKEN;

            $this->view->client_number = $client_number;

            $client = new TwilioRestClient($AccountSid, $AuthToken);
            //validate user phone number

            /* Initiate a new outbound call by POST'ing to the Calls resource */
            $response = $client->request("/$ApiVersion/Accounts/$AccountSid/OutgoingCallerIds",
                "POST", array(
                "PhoneNumber"  => $client_number,
                "FriendlyName" => "Testing Phone from form@ativate",
            	"StatusCallback" => WEBSITE_URL."updateConnectionPhoneNumbersVeryficationStatus.php?form_id={$form_id}&number_type={$number_type}&phone_type={$phone_type}&customerId={$customerId}"
            ));

    //       echo '<pre>';
    //       print_r($response);

             $this->view->ErrorMessage   = $response->ErrorMessage;
             $this->view->number_type    = $number_type;
             $this->view->number         = $client_number;
             $this->view->ValidationCode = $response->ResponseXml->ValidationRequest->ValidationCode;
             
             if($response->ErrorMessage == 'Phone number is already verified.')
             {
             	$formTable=new Forms();
				$forms_data = array();
             	$form_data[$phone_type] = 1;
             	$formTable->update($form_data,'id='.$form_id. ' and customer_id='.$customerId);	
             }
             
        } else {
            $this->view->ErrorMessage   = "Invalid phone number.";
            $this->view->number_type    = $number_type;
            $this->view->number         = $client_number;
            $this->view->ValidationCode = '';
        }

        	
    }


    function getPhoneValidateStatusAction()
    {
        //error_reporting(E_ALL);

        $this->_helper->layout->disableLayout();

        $filter=new Zend_Filter_StripTags();

       

        $client_number = trim($filter->filter($this->_request->getPost('number')));
        $number_type = trim($filter->filter($this->_request->getPost('number_type')));
        
        $form_id = trim($filter->filter($this->_request->getPost('form_id')));
        
		$fromdatabase = trim($filter->filter($this->_request->getPost('database')));

			 $session = new Zend_Session_Namespace('Zend_Auth');
        $number = $client_number;

        $formats = array(
                    '##########',
                    '###-###-####',
                    '(###)###-####',
                    '(###)#######',
                    '(###) ###-####',
                    '(###) #######',
                    '###.###.####'
                );

        $number = trim(preg_replace('/[0-9]/', '#', $number));

        $response = "";
        if (in_array($number, $formats) && !empty($form_id))
         {
         	
         	
         	$formTable=new Forms();
			$forms_data = array();
			$forms_data=$formTable->fetchRow($formTable->select()->where("customer_id=".$session->member_id." and id=".$form_id));
			
			if(empty($fromdatabase) || $fromdatabase == '')
			{
				if($number_type == "home_phone_status" && trim($forms_data['home_phone']) == $client_number)
					$response = $forms_data['business_phone_validated']."@@".$forms_data['home_phone_validated'];
					
				if($number_type == "business_phone_status" && trim($forms_data['business_phone']) == $client_number)
					$response = $forms_data['business_phone_validated']."@@".$forms_data['home_phone_validated'];
					
				if($number_type == "business_phone_status" && trim($forms_data['business_phone']) != $client_number)
					$response = "0"."@@".$forms_data['home_phone_validated'];
					
			 	if($number_type == "home_phone_status" && trim($forms_data['home_phone']) != $client_number)
					$response = $forms_data['business_phone_validated']."@@"."0";
			}
			else if($fromdatabase == 'database')
			{
				$response = $forms_data['business_phone_validated']."@@".$forms_data['home_phone_validated'];
			}
			else $response = $forms_data['business_phone_validated']."@@".$forms_data['home_phone_validated'];
				
			
			/*if(count($forms_data) > 0)
				$response = $forms_data['business_phone_validated']."@@".$forms_data['home_phone_validated'];
			else
					$response = "";*/
					
					
			 		
           /* require "twilio.php";

			
            $ApiVersion = TWILIO_API_VERSION; // config variable
            $AccountSid = TWILIO_ACCOUNT_SID; // take from config file
            $AuthToken  = TWILIO_AUTH_TOKEN;

            $this->view->client_number = $client_number;

            $client = new TwilioRestClient($AccountSid, $AuthToken);
            //validate user phone number

            $response = $client->request("/$ApiVersion/Accounts/$AccountSid/OutgoingCallerIds",
                "POST", array(
                "PhoneNumber"  => $client_number,
                "FriendlyName" => "Testing Phone from form@ativate"
            ));
            

            if($response->ErrorMessage == 'Phone number is already verified.')
            {
//                 if($number_type == 'business_phone_status')
//                     $session->business_phone_status = 1;
//
//                 if($number_type == 'home_phone_status')
//                     $session->home_phone_status = 1;

                 echo 1;
             }
             else
             {
//                 if($number_type == 'business_phone_status')
//                     $session->business_phone_status = 0;
//
//                 if($number_type == 'home_phone_status')
//                     $session->home_phone_status = 0;

                 echo 0;
             }*/
         	echo $response;
        }
        else
        {
           echo $response;
        }         
    }


    /**
     * overview() -Method for overview page
     *
     * @access public
	 * @return void
     */
    public function overviewAction()
    {         
    	 $session = new Zend_Session_Namespace('Zend_Auth'); 
    	 $this->view->loggedin_customer_id=$session->member_id;         
    	 
    	 $this->view->actionName = 'forms';
		
    	 $id = $this->getRequest()->getParam('id');
		if((($this->getRequest()->getParam('id')!=$session->member_id) || ($this->getRequest()->getPost('id')!=$session->member_id)) &&  ($session->user_type==1)){
			$id = $this->getRequest()->getParam('id');
		}else if($session->member_id>0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
		
		$form_id=$this->getRequest()->getParam('form_id');
		
		$this->view->form_id=$form_id;

                if(empty ($form_id))
                {
                    $totalForms = $this->getTotalCustomerFormsByCustomerID($session->member_id);
                     if($session->plan_id == 5 && $totalForms >= 1)
                     {
                        $session->deleteError="<font color='black'><b>Your current subscription plan only allows for  ".$totalForms." Form. If you would like to add more forms, please upgrade your account <a href='customers/editplandetails/id/$id' >Edit Account Details</a></b></font>";
                        $this->_redirect('/forms');
                     }

                     if($session->plan_id == 2 && $totalForms >= 1)
                     {
                        $session->deleteError="<font color='black'><b>Your current subscription plan only allows for  ".$totalForms." Form. If you would like to add more forms, please upgrade your account <a href='customers/editplandetails/id/$id' >Edit Account Details</a></b></font>";
                        $this->_redirect('/forms');
                     }

                     if($session->plan_id == 3 && $totalForms >= 3)
                     {
                        $session->deleteError="<font color='black'><b>Your current subscription plan only allows for  ".$totalForms." Form. If you would like to add more forms, please upgrade your account <a href='customers/editplandetails/id/$id' >Edit Account Details</a></b></font>";
                        $this->_redirect('/forms');
                     }

                     if($session->plan_id == 4 && $totalForms >= 5)
                     {
                        $session->deleteError="<font color='black'><b>Your current subscription plan only allows for  ".$totalForms." Form. If you would like to add more forms, please upgrade your account <a href='customers/editplandetails/id/$id' >Edit Account Details</a></b></font>";
                        $this->_redirect('/forms');
                     }

                     if($session->plan_id == 6 && $totalForms >= 10)
                     {
                        $session->deleteError="<font color='black'><b>Your current subscription plan only allows for  ".$totalForms." Form. If you would like to add more forms, please upgrade your account <a href='customers/editplandetails/id/$id' >Edit Account Details</a></b></font>";
                        $this->_redirect('/forms');
                     }                

                    $authorizationkey = sha1(md5('a1b2c3d4e5fg6h7i8jklmnop9qrs0tuvwxz'.time()));
			// END SIX DIGIT ALPHANUMERIC AUTHORIZATION KEY
                    $form_data['authorizationkey']=$authorizationkey;
                    $form_data['customer_id']=$session->member_id;
                    $form_data['date_created']=date('Y-m-d');
                    $formTable = new Forms();
                    $last_insert_id=$formTable->insert($form_data);
                    $session->form_session_id =  $last_insert_id;
                    $form_id = $last_insert_id;
                    $session->overview_visited = false;
                    $session->standard_form_visited = false;
                    $session->emailnotification_visited = false;
                    $session->redirect_visited = false;
                }
                
		$this->view->form_id=$form_id;
		$this->view->apikey=$session->api_key;
		if($form_id!='')
		{                        
                        $this->formCompletionStatus($form_id);

			$formTable = new Forms();
			$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
			//print_r($forms_data);
			//exit;
			$this->view->formname =  $forms_data['form_name'];
			$this->view->form_id =  $forms_data['id'];
			$this->view->status =  $forms_data['status'];
			$this->view->formType =  $forms_data['form_type'];
			$this->view->toRepeatTheAnnouncement =  trim($forms_data['to_repeat_the_announcement']);
			$this->view->form_type = $forms_data['form_type'];
			$this->view->business_phone =  $forms_data['business_phone'];
			$this->view->home_phone =  $forms_data['home_phone'];
			$this->view->caller_id =  $forms_data['caller_id'];
                        $this->view->business_phone_validated = $forms_data['business_phone_validated'];
                        $this->view->home_phone_validated     = $forms_data['home_phone_validated'];
		}
                		
		if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();
			$formname = $filter->filter($this->_request->getPost('formname'));
			$status = $filter->filter($this->_request->getPost('status'));
			$business_phone = $filter->filter($this->_request->getPost('business_phone'));
			$home_phone = $filter->filter($this->_request->getPost('home_phone'));
			$caller_id = $filter->filter($this->_request->getPost('caller_id'));
                        $business_phone_status = $filter->filter($this->_request->getPost('business_phone_status'));
                        $home_phone_status     = $filter->filter($this->_request->getPost('home_phone_status'));
		 	$form_type = $filter->filter($this->_request->getPost('formType'));
		 	$toRepeatTheAnnouncement = $filter->filter($this->_request->getPost('toRepeatTheAnnouncement'));
			
			$form_id = $filter->filter($this->_request->getPost('form_id'));
		/******************** start => when the form type will be changed  **************/	
			
			if(!empty($form_id) && !empty($session->member_id))
			{
				$formTable = new Forms();
				$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));
				
				if(trim($forms_data['form_type']) != trim($form_type))
				{
					
					$form_std_fields=new FormsStd();
					$condition_customer = $form_std_fields->select()->where('customer_id='.$session->member_id.' and form_id='.$form_id)->order('form_std_field.field_id asc');
					
					$fieldsFormStd = $form_std_fields->fetchAll($condition_customer);  
					
					if(count($fieldsFormStd) > 0)
					{
						$form_std_fields=new FormsStd();
						$form_std_fields->delete("form_id=".$form_id." and customer_id=".$session->member_id);
					}
					
					$temp_form_std_fields=new TempFormsStd();
			 		$condition = $temp_form_std_fields->select()->where('customer_id='.$session->member_id.' and form_id='.$form_id)->order('temp_form_std_field.field_id asc');
			 		
			 		$fieldsTempFormStd = $temp_form_std_fields->fetchAll($condition);
					
			 		if(count($fieldsTempFormStd) > 0)
			 		{
						$temp_form_std_fields=new TempFormsStd();
						$temp_form_std_fields->delete("form_id=".$form_id." and customer_id=".$session->member_id);
			 		}
					
					
				}
				
			}
			
		/******************** End => when the form type will be changed  **************/		
			$formTable = new Forms();
			$form_data = array();
			//$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));
                    		
			$form_data['form_name']=$formname;
			$form_data['to_repeat_the_announcement']=$toRepeatTheAnnouncement;
			$form_data['status']=$status;
			$form_data['business_phone'] = $business_phone;
			$form_data['home_phone']= $home_phone;
			$form_data['form_type'] = $form_type;
			$form_data['caller_id']= ($business_phone_status == 1 || $home_phone_status == 1) ? $caller_id : '';

                        $form_data['business_phone_validated'] = $business_phone_status;
                        $form_data['home_phone_validated']     = $home_phone_status;
			
			if($form_id!='')
			{
				 $formTable->update($form_data,'id='.$form_id);	
				 				
				 if($form_type == "wufoo")
					$this->_redirect('forms/wufooformfield/form_id/'.$form_id);
				 else
				 $this->_redirect('forms/stdfield/form_id/'.$form_id);
			}
			else
			{			
				// GET SIX DIGIT ALPHANUMERIC AUTHORIZATION KEY
			/*	$allowed_chars = 'a1b2c3d4e5fg6h7i8jklmnop9qrs0tuvwxz'.time();
				$allowed_count = strlen($allowed_chars);
				$authorizationkey = null;
				$authorizationkey_length = 6;
				
				while($authorizationkey === null) {
				    $authorizationkey = '';
				    for($i = 0; $i < $authorizationkey_length; ++$i) {
				        $authorizationkey .= $allowed_chars{mt_rand(0, $allowed_count - 1)};
				    }
				}*/			
				
			    $this->_redirect('forms/stdfield/form_id/'.$last_insert_id);	
			}
		}
    }
    
    /**
	 * mykeygen() - Method , used to genertate api key
	 *
	 * @access public
	 * @return void
	 */
	
	public function mykeygen($l = 0) 
	{
	  if ((int)$l < 1)
	  {
	    $l = 32;
	  }
	
	  $k = '';
	  $vc = array_merge(range('A','Z'), range(0,9));
	  for ($i = 0; $i < $l; $i++) 
	  {
	    $k .= $vc[array_rand($vc)];
	  }
	
	  return $k;
	} 
    
     /**
     * deletewufoomappingAction() -Method for standard filed
     *
     * @access public
	 * @return void
     */
	
	public function deletewufoomappingAction()
	{
		$form_id = $this->getRequest()->getParam('form_id');
		
		$temp_form_std_fields=new TempFormsStd();
		$temp_form_std_fields->delete("form_id=".$form_id);
		
		$form_std_fields=new FormsStd();
		$form_std_fields->delete("form_id=".$form_id);
		
		$this->_redirect('forms/wufooformfield/form_id/'.$form_id);	
		
		
	}
	
     /**
     * wufooformfieldAction() -Method for standard filed
     *
     * @access public
	 * @return void
     */
    
    public function wufooformfieldAction()
    {
    	$form_id = $this->getRequest()->getParam('form_id');
    	
    	
    	$session = new Zend_Session_Namespace('Zend_Auth'); 
       
    	$this->view->loggedin_customer_id=$session->member_id;


        ////FOR COMPLETION OF FORM /////////////////////////

        if(!empty ($form_id))
        {
            $this->formCompletionStatus($form_id);
        }
    	
    	
        if(empty($form_id))
		{
			$this->_redirect('/forms/overview'); 
		}
		if((($this->getRequest()->getParam('id')!=$session->member_id) || ($this->getRequest()->getPost('id')!=$session->member_id)) &&  ($session->user_type==1))
		{
			$id = $this->getRequest()->getParam('id');
		}
		else if(!empty($session->member_id))
		{
			$id = $session->member_id;
		}
		else
		{
			$this->_redirect('/customers/login'); 
		}	
    	
    	
		
		if(!empty($form_id))
		{
			$customer_id = $session->member_id;
			$form_std_fields=new FormsStd();
			
			$condition_customer = $form_std_fields->select()->where('customer_id='.$customer_id.' and form_id='.$form_id)->order('form_std_field.field_id asc');
			
		}
		
		$forUpdateCheck = $form_std_fields->fetchAll($condition_customer);
		  
 		$this->view->fields = $form_std_fields->fetchAll($condition_customer);  
		
		if(count($this->view->fields) == 0)
		{
	    	$temp_form_std_fields=new TempFormsStd();
	 		$condition = $temp_form_std_fields->select()->where('form_id='.$form_id)->order('temp_form_std_field.field_id asc');
	 		$this->view->fields = $temp_form_std_fields->fetchAll($condition);
		}
    	
    	
    	
    	$formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
    	$this->view->form_type = $forms_data['form_type'];
    	$this->view->form_id = $form_id;

        
        $memberTable = new members();
        $member_data = $memberTable->fetchRow($memberTable->select()->where('id='.$session->member_id));
        
        $this->view->api_key = $member_data['api_key'];
         
         
        if($this->_request->isPost())
		{  
		$totalRows = intval($this->_request->getPost('countOfRow'));

		if(!empty($totalRows) && intval($totalRows) > 0)
		{
			
			$form_ids = array();
			$field_mapping = array();
			$custom_field = array();
			$field_announce = array();
			$form_std_table_ids = array();
			$field_values = array();
			$field_Label = array();
			
			foreach($this->_request->getParams() as $key=>$value)	
			{
				
					if($key == "fieldValue")
						{
							foreach($value as $key1=>$value1)
							{
								$field_values[$key1] = $value1;
							}
						}
					
					if($key == "fieldId")
					{
						foreach($value as $key1=>$value1)
						{
							$form_ids[$key1] = $value1;
						}
					}
					
					if($key == "field_mapping")
					{
						foreach($value as $key1=>$value1)
						{
							$field_mapping[$key1] = $value1;
						}
					}
					
					
					if($key == "customField")
					{
						foreach($value as $key1=>$value1)
						{
							$custom_field[$key1] = $value1;
						}
					}
					
					if($key == "field_announce")
					{
						foreach($value as $key1=>$value1)
						{
							$field_announce[$key1] = $value1;
						}
					}
					
					if($key == "formStdtableId")
						{
							foreach($value as $key1=>$value1)
							{
								$form_std_table_ids[$key1] = $value1;
							}
						}
						
					if($key == "fieldLable")
					{
						foreach($value as $key1=>$value1)
						{
							$field_Label[$key1] = $value1;
						}
					}
			}
			
			
			for($i=0; $i<$totalRows; $i++)
			{
				$form_std_data_array = array();
				$form_std_data_array['form_id'] = $this->_request->getPost('formid');
				$form_std_data_array['customer_id'] = $session->member_id;
				
				if($field_mapping[$i] == "custom")
				{
					$form_std_data_array['field_status'] = "custom";
					$form_std_data_array['label'] = $custom_field[$i];
					$form_std_data_array['field_type'] = $custom_field[$i];
				}
				else
				{
					$form_std_data_array['field_status'] = "standard";
					
					if($field_mapping[$i] == "firstname")
						{
							$form_std_data_array['field_type'] = "First Name";
							$form_std_data_array['label'] = "First Name";
						}
					if($field_mapping[$i] == "lastname")
						{
							$form_std_data_array['field_type'] = "Last Name";
							$form_std_data_array['label'] = "Last Name";
						}
					if($field_mapping[$i] == "email")
						{
							$form_std_data_array['field_type'] = "Email Address";
							$form_std_data_array['label'] = "Email Address";
						}
					if($field_mapping[$i] == "phonenumber")
						{
							$form_std_data_array['field_type'] = "Phone Number";
							$form_std_data_array['label'] = "Phone Number";
						}
					if($field_mapping[$i] == "company")
						{
							$form_std_data_array['field_type'] = "Company Name";
							$form_std_data_array['label'] = "Company Name";
						}
					if($field_mapping[$i] == "streetaddress")
						{
							$form_std_data_array['field_type'] = "Street Address";
							$form_std_data_array['label'] = "Street Address";
						}
					if($field_mapping[$i] == "city")
						{
							$form_std_data_array['field_type'] = "City";
							$form_std_data_array['label'] = "City";
						}
					if($field_mapping[$i] == "state")
						{
							$form_std_data_array['field_type'] = "State";
							$form_std_data_array['label'] = "State";
						}
					if($field_mapping[$i] == "zip")
						{
							$form_std_data_array['field_type'] = "Zip";
							$form_std_data_array['label'] = "Zip";
						}
					if($field_mapping[$i] == "country")
						{
							$form_std_data_array['field_type'] = "Country";
							$form_std_data_array['label'] = "Country";
						}
					if($field_mapping[$i] == "ignore")
					{
							$form_std_data_array['field_type'] = "ignore";
							$form_std_data_array['label'] = $field_Label[$i];
					}			
				}
					
					
				$form_std_data_array['inquiry_table_field'] = $field_mapping[$i];
					
				$form_std_data_array['field_id'] = substr($form_ids[$i],5,strlen($form_ids[$i])) ;
				
				$form_std_data_array['field_announce'] = $field_announce[$i];
				
				$form_std_data_array['status'] = 1;
				
				$form_std_data_array['field_value'] = $field_values[$i];
				
				$form_std_fields=new FormsStd();
				
				if(count($forUpdateCheck) > 0)
					$form_std_fields->update($form_std_data_array,'id='.$form_std_table_ids[$i]);
				else	
					$last_insert_id = $form_std_fields->insert($form_std_data_array);
			}
			
   		 	if($this->_request->getPost('form_action')=='Previous'){
						$this->_redirect('forms/overview/form_id/'.$form_id);
					
				}
			if($this->_request->getPost('form_action')=='Next'){
					$this->_redirect('forms/opthrs/form_id/'.$form_id);	
				}
		}
    	
		}
				
				
    }
    
    /**
     * stdfieldAction() -Method for standard filed
     *
     * @access public
	 * @return void
     */
    public function stdfieldAction()
    {
    	$session = new Zend_Session_Namespace('Zend_Auth'); 
       
    	$this->view->loggedin_customer_id=$session->member_id;

		

        ////FOR COMPLETION OF FORM /////////////////////////
        $form_id = $this->getRequest()->getParam('form_id');
            
		$forms = new Forms();
		$condition_forms =$forms->fetchRow($forms->select()->where('customer_id='.$session->member_id.' and id='.$form_id));
		$this->view->form_type = $condition_forms['form_type'];
		
        if(!empty ($form_id))
        {
            $this->formCompletionStatus($form_id);
        }

        
        ///////////////////////////////////////////////////////

    	$this->view->form_id=$this->getRequest()->getParam('form_id');
    	$this->view->preview_hidden_value=$this->getRequest()->getParam('pre');
    	
		$form_id=$this->getRequest()->getParam('form_id');
		$this->view->actionName = 'forms';
		//exit;
		if(empty($form_id))
		{
			$this->_redirect('/forms/overview'); 
		}
		if((($this->getRequest()->getParam('id')!=$session->member_id) || ($this->getRequest()->getPost('id')!=$session->member_id)) &&  ($session->user_type==1)){
			$id = $this->getRequest()->getParam('id');
		}else if($session->member_id>0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
    	
    	$form_std_fields=new FormsStd();
 		$condition = $form_std_fields->select()->where('customer_id=0 and field_status="standard" and status="1"')->order('form_std_field.id asc');		
 		$this->view->fields = $form_std_fields->fetchAll($condition);   
 						
		// chk for saved standard fields data filled by user
		
		if(!empty($form_id))
		{
			$customer_id = $session->member_id;
			$condition_customer = $form_std_fields->select()->where('customer_id='.$customer_id.' and form_id='.$form_id.' and field_status="standard" and status="1"')	->order('form_std_field.id asc');
			
		}
 		$this->view->selected_fields = $form_std_fields->fetchAll($condition_customer);   
 		//exit;
 		
	if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();
						
			$field_id_arr=array();
			$field_id_arr=$this->_request->getPost('field_id');
			//print_r($field_id_arr);
			$posted_field_ids='';
			$posted_field_ids=implode(',',$field_id_arr);
			
			$condition_customer = $form_std_fields->select()->where('customer_id='.$customer_id.' and form_id='.$form_id.' and field_status="standard" and status="1"')	->order('form_std_field.id asc');
			$form_std_fields->fetchAll($condition_customer);
			//exit;
			$field_type_arr=array();
			$field_type_arr=$this->_request->getPost('field_type');
			
			$label_arr=array();
			$label_arr=$this->_request->getPost('label');			
			
			$field_validation_arr=array();
			$field_validation_arr=$this->_request->getPost('field_validation');
			
			
			$inquiry_table_field_arr=array();
			$inquiry_table_field_arr=$this->_request->getPost('inquiry_table_field');
			
			$field_required_arr=array();
			$field_required_arr=$this->_request->getPost('field_required');
			
			$field_announce_arr=array();
			$field_announce_arr=$this->_request->getPost('field_announce');			
			
			$field_validate_class_arr=array();
			$field_validate_class_arr=$this->_request->getPost('validate_class');
			//print_r($this->_request->getPost('field_required'));
			//print_r($field_required_arr);
		    $total_sel_fields=count($this->_request->getPost('field_id'));		    
		  
		    
		    // delete all unselected fields from standard data
			$delete_std_field_data = $form_std_fields->delete("customer_id=".$customer_id." and form_id=".$form_id." and field_status='standard' and status='1'");
		   
		    if($posted_field_ids!='')
		    {
				
			$form_fields=array();
			// delete unselected fields from standard data
			$delete_std_field_data = $form_std_fields->delete("field_id not in (".$posted_field_ids.") and customer_id=".$customer_id." and form_id=".$form_id." and field_status='standard' and status='1'");
			for($i=0;$i<$total_sel_fields;$i++)
			{
				$field_id = 0;
				$field_id = $field_id_arr[$i];
				$column_id = $field_id-1;
				$form_fields['form_id']=$form_id;
				$form_fields['customer_id']=$session->member_id;
				$form_fields['field_id']=$field_id_arr[$i];
				$form_fields['field_type']=$field_type_arr[$column_id];
				$form_fields['label']=$label_arr[$column_id];			
				$form_fields['field_validate']=$field_validation_arr[$column_id];
				$form_fields['field_required']=$field_required_arr[$column_id];
				$form_fields['validate_class']=$field_validate_class_arr[$column_id];
				$form_fields['inquiry_table_field']=$inquiry_table_field_arr[$column_id];
				$form_fields['field_announce']=$field_announce_arr[$column_id];				
				$form_fields['field_status']='standard';

			// get field record id 
			// if id then update else insert row 
			$forms_data=array();
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('form_id='.$form_id.' and field_id='.$form_fields['field_id']));
			$id='';
		    $id=$forms_data['id'];
		    if(!empty($id))
			{				
				$form_std_fields->update($form_fields,'id='.$id);					
			}
			else
			{			
				$session = new Zend_Session_Namespace('Zend_Auth');
				$form_std_fields->insert($form_fields);				   
			 }
			}
		}	
			
				
			if($this->_request->getPost('preview_hidden_value')==1){
			//$this->view->preview_hidden_value=2; // Two will indicate that value saved and show preview.
			$this->_redirect('forms/stdfield/form_id/'.$form_id.'/pre/2/');
			}else{
				
					if($this->_request->getPost('form_action')=='Previous'){
				$this->_redirect('forms/overview/form_id/'.$form_id);
					}
					if($this->_request->getPost('form_action')=='Next'){
				$this->_redirect('forms/customfield/form_id/'.$form_id);
					}
				}
			
		}
    }
    
     /**
     * customfieldAction() -Method for custom filed
     *
     * @access public
	 * @return void
     */
    public function customfieldAction()
    {
    	$session = new Zend_Session_Namespace('Zend_Auth');
               
    	$this->view->loggedin_customer_id=$session->member_id;

        ////FOR COMPLETION OF FORM /////////////////////////
        $form_id = $this->getRequest()->getParam('form_id');
        if(!empty ($form_id))
        {
            $this->formCompletionStatus($form_id);
        }
        ///////////////////////////////////////////////////////

        $forms = new Forms();
		$condition_forms =$forms->fetchRow($forms->select()->where('customer_id='.$session->member_id.' and id='.$form_id));
		$this->view->form_type = $condition_forms['form_type'];
        

		$this->view->form_id=$this->getRequest()->getParam('form_id');
		$form_std_fields=new FormsStd();
		$customer_id=$session->member_id;
		$this->view->form_id=$this->getRequest()->getParam('form_id');
		$form_id=$this->getRequest()->getParam('form_id');
		$this->view->actionName = 'forms';
		$this->view->preview_hidden_value=$this->getRequest()->getParam('pre');
		//exit;
		if(empty($form_id)){
			$this->_redirect('/forms/overview'); 
		}
		if((($this->getRequest()->getParam('id')!=$session->member_id) || ($this->getRequest()->getPost('id')!=$session->member_id)) &&  ($session->user_type==1)){
			$id = $this->getRequest()->getParam('id');
		}else if($session->member_id>0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
		// view previously saved data
		if(!empty($form_id))
		{
			$condition_customer = $form_std_fields->select()->where('customer_id='.$customer_id.' and form_id='.$form_id.' and field_status="custom" and status="1"')	->order('form_std_field.id asc');
			
		}
 		$this->view->selected_fields = $form_std_fields->fetchAll($condition_customer);  
		
		
		
	// saving user data
	if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();
			$label_arr = $this->_request->getPost('label');
			//$label_arr=$this->_request->getPost('label');
			$field_required_arr=$this->_request->getPost('field_required');
			$field_valdidate_arr=$this->_request->getPost('field_validate');
			$field_announce_arr=$this->_request->getPost('field_announce');	
			$field_data_type = 	$this->_request->getPost('field_data_type');		
			
			// delete all custom field data
			$delete_custom_field_data = $form_std_fields->delete("customer_id=".$customer_id." and form_id=".$form_id." and field_status='custom' and status='1'");
			$i=1;
			foreach($label_arr as $key=>$val)
			{
				if($val!='')
				{
					$val=$filter->filter($val);
					$custom_label[$i]=$val;
					$custom_field_required[$i]=$field_required_arr[$key];
					$custom_field_validate[$i]=$field_valdidate_arr[$key];
					//echo '<br/>';
					$custom_data=array();
					$custom_data['field_id']=$i;
					$custom_data['form_id']=$form_id;
					$custom_data['customer_id']=$session->member_id;
					$custom_data['field_type']=$val;
					$custom_data['label']=$val;					
					$custom_data['inquiry_table_field']='custom'.$i;
					$custom_data['field_required']=$field_required_arr[$key];
					$custom_data['field_validate']=$field_valdidate_arr[$key];
					$custom_data['validate_class']=$field_valdidate_arr[$key];
					$custom_data['field_announce']=$field_announce_arr[$key];	
					$custom_data['data_type']=$field_data_type[$key];
					$custom_data['field_status']='custom';
					$custom_data['status']='1';
					$form_std_fields->insert($custom_data);		
					$i++;
				}
			}
			
			if($this->_request->getPost('preview_hidden_value')==1){
			//$this->view->preview_hidden_value=2; // Two will indicate that value saved and show preview.
			$this->_redirect('forms/customfield/form_id/'.$form_id.'/pre/2/');
			}else{
				
				
				if($this->_request->getPost('form_action')=='Previous'){
					$this->_redirect('forms/stdfield/form_id/'.$form_id);
				}
				if($this->_request->getPost('form_action')=='Next'){
					$this->_redirect('forms/opthrs/form_id/'.$form_id);	
				}
			
			}
			//exit;
		}
		
    }
    
     /**
     * opthrsAction() -Method for opthrs
     *
     * @access public
	 * @return void
     */
    public function opthrsAction()
    {
    	$session = new Zend_Session_Namespace('Zend_Auth');
       
    	$this->view->loggedin_customer_id=$session->member_id;


        ////FOR COMPLETION OF FORM /////////////////////////
        $form_id = $this->getRequest()->getParam('form_id');
        
        $formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
    	$this->view->form_type = $forms_data['form_type'];

        if(!empty ($form_id))
        {
            $this->formCompletionStatus($form_id);
        }
        ///////////////////////////////////////////////////////


		$this->view->form_id=$this->getRequest()->getParam('form_id');
		$customer_id=$session->member_id;
		$form_id=$this->getRequest()->getParam('form_id');
		$this->view->actionName = 'forms';
		$timetable = new timetable();
		
		//exit;
		if(empty($form_id))
		{
			$this->_redirect('/forms/overview'); 
		}
		$opt_hrs=new opthrs();
		// view previously saved data
		if(!empty($form_id))
		{
			$condition_customer = $opt_hrs->select()->where('customer_id='.$customer_id.' and form_id='.$form_id.' and status="1"')	->order('hours_of_operation.id asc');
			
		}
		
		/************* GET TIME TABLE START HERE **********************************************/
 		$timetable_data=$timetable->fetchAll($timetable->select());
 	/*	echo "<pre>";
 		print_r($timetable_data); */
 		$this->view->timetable_datas=$timetable_data;
 		
 		/************* GET TIME TABLE END HERE ************************************************/
		
 		$this->view->selected_fields = $opt_hrs->fetchAll($condition_customer); 
		
		if((($this->getRequest()->getParam('id')!=$session->member_id) || ($this->getRequest()->getPost('id')!=$session->member_id)) &&  ($session->user_type==1)){
			$id = $this->getRequest()->getParam('id');
		}else if($session->member_id>0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
    	// saving user data
		if($this->_request->isPost())
		{
			//delete previously saved data
			 $delete_opt_hrs_data = $opt_hrs->delete("customer_id=".$customer_id." and form_id=".$form_id); 
			//print_r($this->getRequest());
			
			// insert new data
			$this->_request->getPost('time_zone'); 
			 $time_zones = explode('##',$this->_request->getPost('time_zone'));
			 $time_zone=$time_zones[0];			 
			 $time_zone_code=$time_zones[1];				
			 $start_time_arr = $this->_request->getPost('start_time');			
			 $end_time_arr=$this->_request->getPost('end_time');			
			 $week_day_arr=$this->_request->getPost('week_day');
			$i=1;
			
			
			foreach($week_day_arr as $key=>$val)
			{
				$opt_hrs_data=array();
				$opt_hrs_data['form_id']=$form_id;
				$opt_hrs_data['customer_id']=$customer_id;
				$opt_hrs_data['time_zone']=$time_zone;
				$opt_hrs_data['time_zone_code']=$time_zone_code;
				$opt_hrs_data['week_day']=$i;
				$opt_hrs_data['start_time']=$start_time_arr[$i];
				$opt_hrs_data['end_time']=$end_time_arr[$i];
				$opt_hrs_data['status']='1';
				
				 $opt_hrs->insert($opt_hrs_data);		
				$i++;
			}
					
			if($this->_request->getPost('form_action')=='Previous'){
					if($this->view->form_type == "wufoo")
						$this->_redirect('forms/wufooformfield/form_id/'.$form_id);
					else
					$this->_redirect('forms/customfield/form_id/'.$form_id);
				}
			if($this->_request->getPost('form_action')=='Next'){
					$this->_redirect('forms/businessrule/form_id/'.$form_id);	
				}
			
			
		}
    }
    
    
    // Business Rules listing
    public function businessruleAction()
    {
    	$session = new Zend_Session_Namespace('Zend_Auth'); 
    

    	$this->view->loggedin_customer_id=$session->member_id;


        ////FOR COMPLETION OF FORM /////////////////////////
        $form_id = $this->getRequest()->getParam('form_id');
        $formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
    	$this->view->form_type = $forms_data['form_type'];
        if(!empty ($form_id))
        {
            $this->formCompletionStatus($form_id);
        }
        
        ///////////////////////////////////////////////////////


		$this->view->form_id=$this->getRequest()->getParam('form_id');
		$this->view->actionName = 'forms';
		if(empty($session->member_id))
		{
			$this->_redirect('/customers/login'); 
		}
		$customer_id=$session->member_id;
		$form_id=$this->getRequest()->getParam('form_id');
    	if(empty($form_id))
		{
			$this->_redirect('/forms/overview'); 
		}
		
		if((($this->getRequest()->getParam('id')!=$session->member_id) || ($this->getRequest()->getPost('id')!=$session->member_id)) &&  ($session->user_type==1)){
			$id = $this->getRequest()->getParam('id');
		}else if($session->member_id>0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}
		
		$customer_rules=new customerrules();
		$rule_data=$customer_rules->fetchAll($customer_rules->select()->where("customer_id=".$customer_id." and form_id=".$form_id)->order('id desc'));
    	
		    if(count($rule_data))
			{
				$paginator  = Zend_Paginator::factory($rule_data); 
				$view=Zend_View_Helper_PaginationControl::setDefaultViewPartial('partials/my_pagination_control.phtml');   
				$paginator->setItemCountPerPage(10) 
						  ->setPageRange(10) 
						  ->setCurrentPageNumber($this->_getParam('page')); 
				$paginator->setDefaultScrollingStyle('Sliding');
				$paginator->setView($view);

				$this->view->paginator = $paginator;
				$this->view->fromtypes = $paginator;
				$this->view->pageno = $this->_getParam('page');
				$this->view->recordPerPage =10;
			}
			
			return;
    }
    
   // Delete customer.
	public function deleteAction()
	{
            if($this->getRequest()->getParam('id'))
            {
                $form_id= $this->getRequest()->getParam('id'); //form_id
                $cid    = $this->getRequest()->getParam('cid'); //customer_id
                $forms = new Forms();
                $emailnotifications = new emailnotification();
                $customerruless = new customerrules();
                $formreports = new formreport();
                $formsStds = new FormsStd();
                $inquirys = new inquiry();
                $opthrss = new opthrs();

                //echo $forms->select()->where('id='.$form_id); exit;
                
                $form_data=$forms->fetchRow($forms->select()->where('id='.$form_id));
                $forms->delete("id=".$this->getRequest()->getParam('id'));
                $emailnotifications->delete("form_id=".$this->getRequest()->getParam('id'));
                $customerruless->delete("form_id=".$this->getRequest()->getParam('id'));
                
               // $formreports->delete("form_id=".$this->getRequest()->getParam('id')); /* Form reports can't be deleted @Pushpendra 02/27/2012 */
               
                $formsStds->delete("form_id=".$this->getRequest()->getParam('id'));
                
               // $inquirys->delete("form_id=".$this->getRequest()->getParam('id'));  /* Form inquiries can't be deleted @Pushpendra 02/27/2012 */
               
                
                $opthrss->delete("form_id=".$this->getRequest()->getParam('id'));

                $deleteError = new Zend_Session_Namespace('Zend_Auth');
                $deleteError->deleteError="<font color='black'><b>Your form, ".$form_data['form_name'].",  has been deleted successfully!</b></font>";
                $this->_redirect('/forms/index/cid/'.$cid);
                exit;
            }
	}
	
	
	// Change Customer Status.
	public function changeformstatusAction()
	{
		
		$forms = new Forms();
		$page=$this->getRequest()->getParam('page');
		if($this->getRequest()->getParam('id'))
        {
		 
			$form_id = $this->getRequest()->getParam('id');
			$data['status']=$this->getRequest()->getParam('status');
			$form_data=array();
			$form_data=$forms->fetchRow($forms->select()->where('id='.$form_id));
			
			if($data['status']=='0') $status='inactive'; else $status='active';
			$deleteError = new Zend_Session_Namespace('Zend_Auth');
			$deleteError->deleteError="<font color='black'><b>Your form, ".$form_data['form_name'].", has been set to ".$status.".</b></font>";
			$n = $forms->update($data, "id =".$form_id);  
			$this->_redirect('/forms/index/page/'.$page);
			exit;
		}
	}
	
	// Change Customer Rule Status.
	public function changebusinessstatusAction()
	{
		
		$rules = new customerrules();

		if($this->getRequest()->getParam('id'))
        {
		 
			$id = $this->getRequest()->getParam('id');
			$form_id=$this->getRequest()->getParam('form_id');
			$data['status']=$this->getRequest()->getParam('status');
			if($data['status']=='0') $status='inactive'; else $status='active';
			$n = $rules->update($data, "id =".$id);  
			$rule_data=$rules->fetchRow($rules->select()->where('id='.$id));
			$deleteError = new Zend_Session_Namespace('Zend_Auth');
			
			
			$deleteError->deleteError="<font color='black'><b>Your rule, ".$rule_data['rule_name'].", has been set to ".$status.".</b></font>";

			$this->_redirect('/forms/businessrule/form_id/'.$form_id);
			exit;
		}
	}
	
	// delete customer rule
	   // Delete customer.
	public function deleteruleAction()
	{
		
		if($this->getRequest()->getParam('id'))
        {	 
        	$rules = new customerrules();
        	$form_id=$this->getRequest()->getParam('form_id');
        	$rule_data=$rules->fetchRow($rules->select()->where('id='.$this->getRequest()->getParam('id')));
			$rules->delete("id=".$this->getRequest()->getParam('id'));	
			$deleteError = new Zend_Session_Namespace('Zend_Auth');
			
			$deleteError->deleteError="<font color='black'><b>Your rule, ".$rule_data['rule_name'].", has been deleted successfully!</b></font>";
			$this->_redirect('/forms/businessrule/form_id/'.$form_id);
			exit;
		}
	}
	
	// create new rule
	public function newbusinessruleAction()
	{
		$session = new Zend_Session_Namespace('Zend_Auth');
                $this->view->loggedin_customer_id=$session->member_id;

		$this->view->form_id=$this->getRequest()->getParam('form_id');
		$this->view->id=$this->getRequest()->getParam('id');
		$customer_id=$session->member_id;
                //echo $this->getTotalCustomerRulesByCustomerID($customer_id);

		$form_id=$this->getRequest()->getParam('form_id');
		$formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
    	$this->view->form_type = $forms_data['form_type'];
		$id=$this->getRequest()->getParam('id');
		$rule_data=array();
		$admin_rule=new Businessrule();
		$customer_rule=new customerrules();
		$timetable = new timetable();
		$form_std_fields = new FormsStd();	
		$this->view->actionName = 'forms';
		
		if(empty($form_id))
		{
			$this->_redirect('/forms/overview'); 
		}
		
		/********* GET TIME TABLE START HERE **********************************************/
 		$timetable_data=$timetable->fetchAll($timetable->select());
 	
 		$this->view->timetable_datas=$timetable_data;
 		
 		/************* GET TIME TABLE END HERE ********************************************/

 		/************* Drop down coloumn data Start HERE *********************************/
 		$formstd_data_query = $form_std_fields->select()->where('form_id='.$form_id.' and status="1"')->order('id');		
 		$this->view->formstd_data = $form_std_fields->fetchAll($formstd_data_query); 
 		/************* Drop down coloumn data End HERE *********************************/

		
		// CHECK FOR HOW MANY BUSINESS RULE USER CAN CREATE START HERE
		$members = new members();	
		$subscriptions = new subscriptions();	
		$customerrules = new customerrules();	
		$member_data = $members->fetchRow($members->select()->where('id='.$session->member_id));
		
		 $subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id='.$member_data['plan_id']));
		
                 $totalCustomerRules = $this->getTotalCustomerRulesByCustomerID($session->member_id);
                 if($session->plan_id == 5 && $totalCustomerRules >= 1)
                 {
                    $session->deleteError="<font color='black'><b>You have already created ".$totalCustomerRules." rules!</b></font>";
                    $this->_redirect('/forms/businessrule/form_id/'.$form_id);
                 }

                 if($session->plan_id == 2 && $totalCustomerRules >= 1)
                 {
                    $session->deleteError="<font color='black'><b>You have already created ".$totalCustomerRules." rules!</b></font>";
                    $this->_redirect('/forms/businessrule/form_id/'.$form_id);
                 }

                 if($session->plan_id == 3 && $totalCustomerRules >= 3)
                 {
                    $session->deleteError="<font color='black'><b>You have already created ".$totalCustomerRules." rules!</b></font>";
                    $this->_redirect('/forms/businessrule/form_id/'.$form_id);
                 }

                 if($session->plan_id == 4 && $totalCustomerRules >= 10)
                 {
                    $session->deleteError="<font color='black'><b>You have already created ".$totalCustomerRules." rules!</b></font>";
                    $this->_redirect('/forms/businessrule/form_id/'.$form_id);
                 }


                /*
		$customerrules_data = $customerrules->fetchAll($customerrules->select()->where('customer_id='.$session->member_id.' and form_id ='.$form_id));
		if($form_id!='' && $id!=''){}else{			
		if(($subscriptions_data['available_rule']>count($customerrules_data)) || ($subscriptions_data['available_rule']=='unlimited')){
		
		}else{
		$deleteError = new Zend_Session_Namespace('Zend_Auth');
		$deleteError->deleteError="<font color='black'><b>You have already created ".count($customerrules_data)." rules!</b></font>";
		$this->_redirect('/forms/businessrule/form_id/'.$form_id); 
		}
		}
                */

		// CHECK FOR HOW MANY BUSINESS RULE USER CAN CREATE END HERE		
				
		$rule_data=$admin_rule->fetchAll($admin_rule->select()->where("status='1'")->order('rule_id asc'));
		$this->view->rule_data = $rule_data;
		
		if(!empty($id))
		{
		$customer_rule_data = $admin_rule->fetchRow($customer_rule->select()->where("form_id=".$form_id." and customer_id=".$customer_id." and id=".$id));
					
		$this->view->rule_name =  $customer_rule_data['rule_name'];
		$this->view->rule_id =  $customer_rule_data['rule_id'];
		$this->view->prefered_time =  $customer_rule_data['prefered_time'];
		$this->view->status =  $customer_rule_data['status'];
		$this->view->phone =  $customer_rule_data['phone'];		
		$this->view->default_selected_sorting_column = $customer_rule_data['field_data'];
		$this->view->field_data_value= $customer_rule_data['field_data_value'];		
		}
		
		if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();
			$data['rule_name']=$filter->filter($this->_request->getPost('rule_name'));
			$data['status']=$filter->filter($this->_request->getPost('status'));
			$data['rule_id']=$filter->filter($this->_request->getPost('rule_id'));
			$data['prefered_time']=$filter->filter($this->_request->getPost('prefered_time'));
			$data['phone']=$filter->filter($this->_request->getPost('phone'));
			$data['field_data']=$filter->filter($this->_request->getPost('field_data'));
			$data['field_data_value']=$filter->filter($this->_request->getPost('field_data_value'));		
						
			$data['form_id']=$form_id;
			$data['customer_id']=$customer_id;
			//print_r($data);
			//exit;
			if($this->_request->getPost('id')!='')
			{
				$id=$this->_request->getPost('id');
				$deleteError = new Zend_Session_Namespace('Zend_Auth');
				$deleteError->deleteError="<font color='black'><b>Your rule, ".$data['rule_name'].",  has been updated successfully!</b></font>";
	
				$customer_rule->update($data,'id='.$id);					
			}
			else {
				$customer_rule->insert($data);	
				$deleteError = new Zend_Session_Namespace('Zend_Auth');
				$deleteError->deleteError="<font color='black'><b>Your rule, ".$data['rule_name'].",  has been added successfully!</b></font>";
	
			}
			$this->_redirect('forms/businessrule/form_id/'.$form_id);
		}
		
		//exit;
	
	}
	
	/**
     * emailnotificationAction() -Method for Email notification
     *
     * @access public
	 * @return void
     */
		public function emailnotificationAction($id=null)
    	{
    	
    		 $session = new Zend_Session_Namespace('Zend_Auth');
          	 $emailnotifications = new emailnotification();	    	     	
    	     $session->customer_id=$session->member_id;


             ////FOR COMPLETION OF FORM /////////////////////////
        $form_id = $this->getRequest()->getParam('form_id');
        if(!empty ($form_id))
        {
            $this->formCompletionStatus($form_id);
        }
        
        ///////////////////////////////////////////////////////
    	     
    	     $form_id=$this->getRequest()->getParam('form_id');
    	     $formTable = new Forms();
			 $forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
	    	 $this->view->form_type = $forms_data['form_type'];
    	     
    	     $this->view->form_id=$form_id;
    	     
    	     if(empty($form_id))
    		 {
    			$this->_redirect('/forms/overview'); 
    		 }
    	     	
    	 if($this->getRequest()->getParam('form_id')){   		    	
    	 	
			$emailnotifications_data = $emailnotifications->fetchRow($emailnotifications->select()->where('form_id='.$this->getRequest()->getParam('form_id')));
		//	 $this->view->data = $emailnotifications_data;	
			 $this->view->incomplete_calls = $emailnotifications_data['incomplete_calls'];
			 $this->view->after_hour_calls =  $emailnotifications_data['after_hour_calls'];
			 $this->view->unanswered_calls = $emailnotifications_data['unanswered_calls'];
			 $this->view->answered_calls =  $emailnotifications_data['answered_calls'];
			 $this->view->connections =  $emailnotifications_data['connections'];
			 $this->view->notification_email =  $emailnotifications_data['notification_email'];			 
			 $this->view->send_email_notification_pros_leads =$emailnotifications_data['send_email_notification_pros_leads'];
			 $this->view->email_notification_id=$emailnotifications_data['id'];			 
			 }
			//$this->view->actionName = 'emailnotification';
			$this->view->actionName = 'forms';
			
			
    	
		if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();
			$incomplete_calls=$filter->filter($this->_request->getPost('incomplete_calls'));
			$after_hour_calls   = $filter->filter($this->_request->getPost('after_hour_calls'));
			$unanswered_calls   = $filter->filter($this->_request->getPost('unanswered_calls'));
			$notification_email   = $filter->filter($this->_request->getPost('notification_email'));	
			
			$answered_calls  = $filter->filter($this->_request->getPost('answered_calls'));
			$connections   = $filter->filter($this->_request->getPost('connections'));
			$send_email_notification_pros_leads = $filter->filter($this->_request->getPost('send_email_notification_pros_leads'));			
			$email_notification_id=$filter->filter($this->_request->getPost('email_notification_id'));
			$form_id = $filter->filter($this->_request->getPost('form_id'));	
			 			
		if( empty($incomplete_calls) && empty($after_hour_calls) && empty($unanswered_calls) && empty($answered_calls) && empty($connections) && empty($connections) && empty($send_email_notification_pros_leads))
			{	
				
				if($this->_request->getPost('form_action')=='Previous'){
							$this->_redirect('forms/businessrule/form_id/'.$form_id);
						}
						if($this->_request->getPost('form_action')=='Next'){
							if($this->view->form_type == "wufoo")
								$this->_redirect('forms/reporting/form_id/'.$form_id);
							else
							$this->_redirect('forms/formredirect/form_id/'.$form_id);	
						}
				// $this->_redirect('forms/formredirect/form_id/'.$form_id);
					/*$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='red''><b>Please select atleast one notification.</b></font>";*/
			}	
			else
			{
					$notification_data['incomplete_calls'] = trim($incomplete_calls);
					$notification_data['after_hour_calls'] =  trim($after_hour_calls);
					$notification_data['unanswered_calls'] = trim($unanswered_calls);
					$notification_data['answered_calls'] =  trim($answered_calls);
					$notification_data['connections'] =  trim($connections);
					$notification_data['notification_email'] =  trim($notification_email);					
					$notification_data['send_email_notification_pros_leads'] =trim($send_email_notification_pros_leads);
					$notification_data['user_id'] =$session->customer_id;
					$notification_data['form_id'] =$form_id;					
							 	
					if($email_notification_id!=''){
					 $emailnotifications->update($notification_data, 'id='.$email_notification_id);
					 if($this->_request->getPost('form_action')=='Previous'){
							$this->_redirect('forms/businessrule/form_id/'.$form_id);
						}
						if($this->_request->getPost('form_action')=='Next'){
							if($this->view->form_type == "wufoo")
								$this->_redirect('forms/reporting/form_id/'.$form_id);
							else
							$this->_redirect('forms/formredirect/form_id/'.$form_id);	
						}
					// $this->_redirect('forms/formredirect/form_id/'.$form_id);						
					}
					else{			
					$session = new Zend_Session_Namespace('Zend_Auth'); 
				    $last_insert_id=$emailnotifications->insert($notification_data);
				    	if($this->_request->getPost('form_action')=='Previous'){
							$this->_redirect('forms/businessrule/form_id/'.$form_id);
						}
						if($this->_request->getPost('form_action')=='Next'){
							if($this->view->form_type == "wufoo")
								$this->_redirect('forms/reporting/form_id/'.$form_id);
							else
							$this->_redirect('forms/formredirect/form_id/'.$form_id);	
						}
				    
				    
				    
				   //	$this->_redirect('forms/formredirect/form_id/'.$form_id);	
					}																
			}
			
		}else{
			//$this->_redirect('customers/');	
		}				   	    	
    }
    
    /**
     * formredirectAction() -Method for Form Redirection
     *
     * @access public
	 * @return void
     */
    public function formredirectAction()
    {
        $session = new Zend_Session_Namespace('Zend_Auth');
              
    	$form_id=$this->getRequest()->getParam('form_id');
    	if(empty($form_id))
    	{
    		$this->_redirect('/forms/overview'); 
    	}
    	$this->view->actionName = 'forms';

        ////FOR COMPLETION OF FORM /////////////////////////
        $form_id = $this->getRequest()->getParam('form_id');
        if(!empty ($form_id))
        {
            $ob = new emailnotification();
            $obData = $ob->fetchRow('form_id = '. $form_id);
            if(count($obData) > 0)
                $session->emailnotification_done = true;
            else
                $session->emailnotification_done = false;
        }
        
        ///////////////////////////////////////////////////////

    	if($this->getRequest()->getParam('form_id'))
    	{
    		$form_id = $this->getRequest()->getParam('form_id'); 
    		$this->view->form_id=$form_id;
    		
    		$customer_id=$session->member_id;
    		$forms=new Forms();
    		$forms_data=$forms->fetchRow($forms->select()->where("id=".$form_id));
    		$this->view->url_type=$forms_data['redirect_type'];
    		$this->view->url=$forms_data['redirect_url'];
		$this->view->form_type=$forms_data['form_type'];
    		if($this->_request->isPost())
                {
                        $filter=new Zend_Filter_StripTags();
                        $redirect_type=$filter->filter($this->_request->getPost('redirect_type'));
                        $redirect_url=$filter->filter($this->_request->getPost('url'));
                        $data['redirect_type']=$redirect_type;
                        $data['redirect_url']= $redirect_type == 1 ? $redirect_url : '';
                        if($form_id)
                        {
                                 $forms->update($data,'id='.$form_id);
                        }

                        if($this->_request->getPost('form_action')=='Previous'){
                                $this->_redirect('forms/emailnotification/form_id/'.$form_id);
                        }
                        if($this->_request->getPost('form_action')=='Next'){
                               if($this->view->form_type != "wufoo" && $this->view->form_type != "api")
								$this->_redirect('forms/preview/form_id/'.$form_id);
                        }

                        //$this->_redirect('forms/reporting/form_id/'.$form_id);
                }
    	
    	}
    			
    }
    /**
     * reportingAction() -Method for Form Reporting
     *
     * @access public
	 * @return void
     */
    public function reportingAction()
    {
    	 $session = new Zend_Session_Namespace('Zend_Auth'); 
    	 $this->view->loggedin_customer_id=$session->member_id;
    	 $form_report_table = new formreport();
    	 $this->view->actionName = 'forms';

                 ////FOR COMPLETION OF FORM /////////////////////////
        $form_id = $this->getRequest()->getParam('form_id');
        $formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
    	$this->view->form_type = $forms_data['form_type'];
        if(!empty ($form_id))
        {
            $this->formCompletionStatus($form_id);
        }

        ///////////////////////////////////////////////////////
        //
        //
    	 //echo $session->member_id; 
		
		if($session->member_id>0 && $session->user_type==1){
			$id = $session->member_id;
		}else if($session->member_id>0 && $session->user_type==0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
				
		$form_id=$this->getRequest()->getParam('form_id');
		if($session->overview_done == false)
                {
                    $this->_redirect('/forms/overview/form_id/'.$form_id);
                }
		$this->view->form_id=$form_id;
		if($form_id!='')
		{
			
		$form_std_fields = new FormsStd();	
		
		if($session->member_id>0 && $session->user_type==1){
			$id = $session->member_id;
			$form_std_fields_datas=$form_std_fields->fetchAll($form_std_fields->select()->where('form_id='.$form_id));	
			// This is for Admin Section 
		}else if($session->member_id>0 && $session->user_type==0){
			$id = $session->member_id;
			$form_std_fields_datas=$form_std_fields->fetchAll($form_std_fields->select()->where('form_id='.$form_id.' and customer_id='.$id));	
			// This is for User Section 
		}	
		//echo count($form_std_fields_datas); exit;
		
		if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();	
			
			$selected_field_id=$this->_request->getPost('selected_field_id');
			$selected_field_id1=$this->_request->getPost('selected_field_id1');
			
			if($selected_field_id!=''){			
			$selected_field_id =implode(',',$selected_field_id);	 
			}
			if($selected_field_id1!=''){			
			$selected_field_id1 =implode(',',$selected_field_id1);
			}
			
			if($selected_field_id !='' && $selected_field_id1 !=''){
			$selected_fields =$selected_field_id.','.$selected_field_id1;
			}else if($selected_field_id !=''){
			$selected_fields =$selected_field_id;
			}else if($selected_field_id1 !=''){
			$selected_fields =$selected_field_id1;
			}else{
			$selected_fields ='';
			}
			/*if($selected_fields!='')
			$selected_flds_array=explode(',',$selected_fields);
			if(count($selected_flds_array)>5)
			{
				//return error message
				 $deleteError = new Zend_Session_Namespace('Zend_Auth');
				 $deleteError->deleteError="<font color='red'><b>Please select only five fields for reporting!</b></font>";		
			}*/
			
			
			/*echo "<pre>";
			print_r($this->_request->getParams()); 
			echo "</pre>";*/
			$reporting_id = $filter->filter($this->_request->getPost('reporting_id'));
			$form_id = $filter->filter($this->_request->getPost('form_id'));
			$field_sort= $filter->filter($this->_request->getPost('field_sort'));		
			
			$form_report_data = array();	
			$form_report_data['selected_field_id']=$selected_fields;
			$form_report_data['field_sort']=$field_sort;			
			if($reporting_id!=''){
			$form_report_data['id']=$reporting_id;
			}
			$form_report_data['form_id']=$form_id;
			
			if($reporting_id!='')
			{
				 $form_report_table->update($form_report_data,'id='.$reporting_id);
				 $deleteError = new Zend_Session_Namespace('Zend_Auth');
				 $deleteError->deleteError="<font color='black'><b>Your report has been updated successfully!</b></font>";					}
			else
			{			
				$session = new Zend_Session_Namespace('Zend_Auth');
				$form_report_data['date_created']=date('Y-m-d'); 
				$form_report_data['customer_id']=$session->member_id; 				
			    $last_insert_id=$form_report_table->insert($form_report_data);
			    $session->form_session_id =  $last_insert_id;
				$deleteError = new Zend_Session_Namespace('Zend_Auth');
				$deleteError->deleteError="<font color='black'><b>Your report has been saved successfully!</b></font>";					
			   // $this->_redirect('forms/stdfield/form_id/'.$last_insert_id);	
			}
			
		}
		
		if(count($form_std_fields_datas)>0){
				
				$form_report_datas=$form_report_table->fetchRow($form_report_table->select()->where('form_id='.$form_id)->order('id'));			
				
		 		$formstd_customer_data_query = $form_std_fields->select()->where('form_id='.$form_id.' and field_status="custom" and status="1"')->order('id');		
		 		$this->view->formstd_data_custom = $form_std_fields->fetchAll($formstd_customer_data_query);   				
		 		$formstd_standard_data_query = $form_std_fields->select()->where('form_id='.$form_id.' and field_status="standard" and status="1"')->order('id');	
		 		$this->view->formstd_data_standard= $form_std_fields->fetchAll($formstd_standard_data_query); 
		 		 
		 		$formstd_data_query = $form_std_fields->select()->where('form_id='.$form_id.' and status="1"')->order('id');		
		 		$this->view->formstd_data = $form_std_fields->fetchAll($formstd_data_query); 

		 		if(count($form_report_datas)>0){ 
		 			$this->view->default_selected_sorting_column =$form_report_datas->field_sort;
		 			$this->view->selected_field_id=explode(',',$form_report_datas->selected_field_id);
		 			$this->view->reporting_id=$form_report_datas->id;
		 		}else{
		 			$this->view->default_selected_sorting_column =0;
		 			$this->view->selected_field_id='';
		 			$this->view->reporting_id='';
		 		}
		 		if($this->_request->getPost('form_action')=='Previous'){
		 			if($this->view->form_type == "wufoo")
		 				$this->_redirect('forms/emailnotification/form_id/'.$form_id);
		 			else
					$this->_redirect('forms/formredirect/form_id/'.$form_id);
				}
				if($this->_request->getPost('form_action')=='Next'){
					if($this->view->form_type != "wufoo" && $this->view->form_type != "api")
						$this->_redirect('forms/preview/form_id/'.$form_id);
				}
		 		
			}else{
				if($this->view->form_type == "wufoo")
					$this->_redirect('forms/wufooformfield/form_id/'.$form_id);
				else
			 $this->_redirect('forms/stdfield/form_id/'.$form_id);	
			}
		}
    }  
    
    /**
     * previewAction() -Method for Form Preview
     *
     * @access public
	 * @return void
     */
     public function previewAction()
    {
 		
    	$session = new Zend_Session_Namespace('Zend_Auth');
    	$this->view->loggedin_customer_id=$session->member_id;
    	$this->view->form_id=$this->getRequest()->getParam('form_id');
		$form_id=$this->getRequest()->getParam('form_id');
		$this->view->actionName = 'forms';
		//exit;
		$formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
    	$this->view->form_type = $forms_data['form_type'];

                if(!empty ($form_id))
                {
                    $this->formCompletionStatus($form_id);
                }

		if(empty($form_id))
		{
			$this->_redirect('/forms/overview'); 
		}
		
		if($session->member_id>0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
    	
    	$form_std_fields=new FormsStd();
 		$condition = $form_std_fields->select()->where('status="1" and customer_id='.$session->member_id.' and form_id='.$form_id)->order('form_std_field.id asc');		
 		$this->view->fields = $form_std_fields->fetchAll($condition);  		
    }   
     
     /**
     * popuppreviewAction() -Method for Popup for visual editor
     *
     * @access public
	 * @return void
     */
     public function popuppreviewAction()
  	 {
 		$session = new Zend_Session_Namespace('Zend_Auth');
    	$this->view->loggedin_customer_id=$session->member_id;
    	$this->view->form_id=$this->getRequest()->getParam('form_id');
		$form_id=$this->getRequest()->getParam('form_id');
		//exit;
		if(empty($form_id))
		{
			$this->_redirect('/forms/overview'); 
		}
		
		if($session->member_id>0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
		
		$formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
		    	
    	$form_std_fields=new FormsStd();
 		$condition = $form_std_fields->select()->where('status="1" and customer_id='.$session->member_id.' and form_id='.$form_id)->order('form_std_field.id asc');		
 		$this->fields = $form_std_fields->fetchAll($condition); ?>
                        <div id="formUserDetails" class="hr1b" style="position: absolute; left: 25%;">
                        <a onclick="hideme('previewhiddendiv')" class="buttonsNext" href="javascript:;" style="float:right;">Close</a>
                         <h3><?php echo $forms_data['form_name']?><span> Preview</span></h3>
                           <fieldset>
                           <?php 
                            if(count($this->fields)>0){
                           foreach ($this->fields as $data_key =>$data_val) { ?> 
                           <label><span><?php echo stripslashes($data_val['label']); ?>
                           <?php if(($data_val['field_required']=='yes') || $data_val['field_validate']!='none'){?> <strong>*</strong> <?php } ?>
                           </span>                            	 
                           </label>
                              <? }}else{ echo "No Preview Found.";}?> 
                            <div class="buttonsNextPrev">                                
                               	<input type="hidden"  name="form_id" id="form_id"   value="<?php echo (isset($this->form_id))? htmlspecialchars(stripslashes($this->form_id)) : '';?>"/>  
                             </div>    
                            </fieldset>
                           </div>
 		<?php
 		 exit; 		
}   


/**
     * popuppreviewAction() -Method for Popup for visual editor
     *
     * @access public
	 * @return void
     */
     public function popupcustompreviewAction()
  	 {
 		$session = new Zend_Session_Namespace('Zend_Auth');
    	$this->view->loggedin_customer_id=$session->member_id;
    	$this->view->form_id=$this->getRequest()->getParam('form_id');
		$form_id=$this->getRequest()->getParam('form_id');
		//exit;
		if(empty($form_id))
		{
			$this->_redirect('/forms/overview'); 
		}
		
		if($session->member_id>0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
		
		$formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
		    	
    	$form_std_fields=new FormsStd();
 		/*$condition = $form_std_fields->select()->where('status="1" and customer_id='.$session->member_id.' and form_id='.$form_id. ' and field_status="custom"')->order('form_std_field.id asc');		*/
 		$condition = $form_std_fields->select()->where(' customer_id='.$session->member_id.' and form_id='.$form_id)->order('form_std_field.id asc');		
 		$this->fields = $form_std_fields->fetchAll($condition); ?>
                       
                           <?php 
                            if(count($this->fields)>0){?>
                            <div id="formUserDetails" class="hr1b" style="position: absolute; left: 25%;">
                        <a onclick="hideme('previewhiddendiv')" class="buttonsNext" href="javascript:;" style="float:right;">Close</a>
                         <h3><?php echo $forms_data['form_name']?><span> Preview</span></h3>
                           <fieldset>
                            <?php
                           foreach ($this->fields as $data_key =>$data_val) { ?> 
                            
                           <label><span><?php echo stripslashes($data_val['label']); ?>
                           <?php if(($data_val['field_required']=='yes') || $data_val['field_validate']!='none'){?> <strong>*</strong> <?php } ?>
                           </span>
                            	 </label>                     
                              <? }?>
                               <div class="buttonsNextPrev">                                
                               	<input type="hidden"  name="form_id" id="form_id"   value="<?php echo (isset($this->form_id))? htmlspecialchars(stripslashes($this->form_id)) : '';?>"/>  
                             </div>    
                            </fieldset>
                           </div>     
                              <?}else{ echo "";}?> 
                            
 		<?php
 		 exit; 		
}   


/**
     * popuppreviewAction() -Method for Popup for visual editor
     *
     * @access public
	 * @return void
     */
     public function popupstdpreviewAction()
  	 {
 		$session = new Zend_Session_Namespace('Zend_Auth');
    	$this->view->loggedin_customer_id=$session->member_id;
    	$this->view->form_id=$this->getRequest()->getParam('form_id');
		$form_id=$this->getRequest()->getParam('form_id');
		//exit;
		if(empty($form_id))
		{
			$this->_redirect('/forms/overview'); 
		}
		
		if($session->member_id>0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}
		
		$formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
		    	
    	$form_std_fields=new FormsStd();
 		/*$condition = $form_std_fields->select()->where('status="1" and customer_id='.$session->member_id.' and form_id='.$form_id.' and field_status="standard"')->order('form_std_field.id asc');*/		
 		$condition = $form_std_fields->select()->where('customer_id='.$session->member_id.' and form_id='.$form_id)->order('form_std_field.id asc');
 		
 		$this->fields = $form_std_fields->fetchAll($condition); ?>
                           <?php 
                            if(count($this->fields)>0){?>
                            <div id="formUserDetails" class="hr1b" style="position: absolute; left: 25%;">
                        <a onclick="hideme('previewhiddendiv')" class="buttonsNext" href="javascript:;" style="float:right;">Close</a>
                         <h3><?php echo $forms_data['form_name']?><span> Preview</span></h3>
                           <fieldset>
                            <?php 
                           foreach ($this->fields as $data_key =>$data_val) { ?> 
                           <label><span><?php echo stripslashes($data_val['label']); ?>
                           <?php if(($data_val['field_required']=='yes') || $data_val['field_validate']!='none'){?> <strong>*</strong> <?php } ?>
                           </span>
                            	 </label>                       
                              <? }?> 
                              <div class="buttonsNextPrev">                                
                               	<input type="hidden"  name="form_id" id="form_id"   value="<?php echo (isset($this->form_id))? htmlspecialchars(stripslashes($this->form_id)) : '';?>"/>  
                             </div>    
                            </fieldset>
                           </div>      
                              <?}else{ echo "No Preview Found.";}?> 
                            
 		<?php
 		 exit; 		
    }


    /**
     * formhtmlAction() -Method for Generate html
     *
     * @access public
	 * @return void
     */
    public function getformAction()
    {
        $this->_helper->layout->disableLayout();
        
        $form_id   = $this->getRequest()->getParam('form_id');

        $member_id = $this->getRequest()->getParam('member_id');

        if(trim($form_id)=='')
        {
                $this->_redirect('/forms/overview'); exit;
        }

        $formTable = new Forms();
        $forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));
        $this->view->formname=$forms_data['form_name'];
        $this->view->authorizationkey=$forms_data['authorizationkey'];
        $this->view->form_id = $form_id;


        $form_std_fields=new FormsStd();

        $condition = $form_std_fields->select()->where('status="1" and customer_id='.$member_id.' and form_id='.$form_id)->order('form_std_field.id asc');
        $this->view->fields = $form_std_fields->fetchAll($condition);
    }


    /**
     * formhtmlAction() -Method for Generate html
     *
     * @access public
	 * @return void
     */
    public function formhtmlAction()
    {
        $session = new Zend_Session_Namespace('Zend_Auth');
    	$this->view->form_id = $form_id =  $this->getRequest()->getParam('form_id');

    	$formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$this->view->form_id));	
    	$this->view->form_type = $forms_data['form_type'];
        if(trim($form_id)=='')
        {
             $this->_redirect('/forms/overview'); exit;
        }

    	$form_std_fields=new FormsStd();
        $condition = $form_std_fields->select()->where('status="1" and customer_id='.$session->member_id.' and form_id='.$form_id)->order('form_std_field.id asc');

        $fields = $form_std_fields->fetchAll($condition);

        if(count($fields) == 0)
        {
            $this->_redirect('/forms/preview/form_id/'.$form_id); exit;
        }     
        
        $member = new Members();
        $member_data=$member->fetchRow($member->select()->where('id='.$session->member_id));	
        $this->view->api_key = $member_data['api_key'];
        if($session->member_id>0)
        {
             $id = $session->member_id;
             $this->view->member_id = $id;
        }else{
            $this->_redirect('/customers/login');
	}
    }
    
    /**
     * inquiryAction() -Method . This method will be used when the customer will submit the form.
     *
     * @access public
	 * @return void
     */
    public function inquiryAction()
    {
    	if($this->_request->isPost())
		{		 
		 $filter=new Zend_Filter_StripTags();				
		 $form_id=$this->_request->getPost('fid');
		 $authorizationkey=$this->_request->getPost('authorizationkey');
		 if(empty($form_id) && empty($authorizationkey))
		 {
				$this->_redirect('/forms/overview'); 
		 }
		 $this->view->actionName = 'forms';
		 		 
		 $formTable = new Forms();		
		 $inquiryTable = new inquiry();
		 $opthrsTable = new  opthrs();
		 $timeTable = new timetable();
		 $customerrulesTable = new customerrules();
		 $emailnotificationTable = new emailnotification();		
		 $members = new members();
		 
		 $forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id.' and authorizationkey="'.$authorizationkey.'" and status=1'));

		 if(count($forms_data)>0){			 	
		 	
			$redirect_type=$forms_data['redirect_type'];
		 	$redirect_url=$forms_data['redirect_url'];
		 	$caller_id=$forms_data['caller_id'];
		 	$form_owner_customer_id=$forms_data['customer_id'];	// GET THE OWNER OF FORM'S USER ID 
	
			$form_owner_data = $members->fetchRow($members->select()->where('id='.$form_owner_customer_id));
			
			
		 	$today_day=strftime("%w");
			if($today_day==0){ $today_day=7;}		
			
			$operation_hours_data = $opthrsTable->fetchRow($opthrsTable->select()->where('form_id='.$form_id.' and week_day="'.$today_day.'"'));		 
						
			/* it will return gmt value in seconds*/
			$time_zone_customer=$operation_hours_data['time_zone'];
			$start_time_with_id=explode("_",$operation_hours_data['start_time']);  // THIS WILL BE USED FOR HOURS OF OPERATION
			$start_time=$start_time_with_id[1];
			$end_time_with_id=explode("_",$operation_hours_data['end_time']);  // THIS WILL BE USED FOR HOURS OF OPERATION
			$end_time=$end_time_with_id[1];
			
			date_default_timezone_set($operation_hours_data['time_zone_code']);
			 			
			$UTCtime = new DateTime('now', new DateTimeZone($operation_hours_data['time_zone_code']));
			$customer_current_time = strtotime($UTCtime->format("Y-m-d H:i:s"));
			$tm = date("H:i:s", $customer_current_time);
			$customer_current_time = $this->time2seconds($tm);
			
			
                        
                       
                        
                        
		 	 // HERE WE GET EMAIL NOTIFICATIONS FOR SENDING THE EMAIL, START HERE
		 $emailnotification_data=$emailnotificationTable->fetchRow($emailnotificationTable->select()->where('form_id='.$form_id));		  
		 $notification_email=$emailnotification_data['notification_email'];
		 // HERE WE GET EMAIL NOTIFICATIONS FOR SENDING THE EMAIL, END HERE
		 	
		 	
                 $form_std_fields = new FormsStd();
                 $form_std_fields_announce_datas=$form_std_fields->fetchAll($form_std_fields->select()->where('form_id='.$form_id.' AND `field_announce` = "yes"'));


                  /* CHECKING CUSTOMER RULES FOR SENDING CALL, START HERE */
		  $customerrules_data=$customerrulesTable->fetchRow($customerrulesTable->select()->where('form_id='.$form_id.' and status=1'));

          	  $phone_flag=0;
		  $email_flag=0;		  
		  
		  
		  /* CHECKING CUSTOMER RULES FOR SENDING CALL SET NUMBER FROM CUSTOMERRULES TABLE, START HERE */	 
		  /* IN CUSTOMER RULES NOT SPECIFIED THEN CHECKING OPTHRS FOR SENDING CALL , START HERE */	 
		  $customerRuleFlag = false;
		  if(count($customerrules_data)>0)
		  {
		  	
			   /* preferred time is like id_seconds so explode it to get the preferred time in seconds*/
			  $prefered_time=explode("_",$customerrules_data['prefered_time']); 
			  $prefered_time_in_seconds=$prefered_time[1];			  
			  $phone=$customerrules_data['phone'];
			
				  
			  /* NOTE THESE RULES ARE HARD CODED IN THE ADMIN PANEL, IT WILL BE MANAGED BY THE ADMIN*/
			  			  			  
			  if($customerrules_data['rule_id']==1)
			  {
			   $inquiry_form_std_custom_data=$this->_request->getPost("standard".$customerrules_data['field_data']);
			   // Rule:-If the inquiry is received AFTER a specific time , set the connection number to [phone number]
				   if($prefered_time_in_seconds <= $customer_current_time){
				   // SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=1;
					   $email_flag=1;
					   $customerRuleFlag = true;
				   }else{
				   $inquiry_type='After hours';
				   }
			  }
			  
			  if($customerrules_data['rule_id']==2){
			   $inquiry_form_std_custom_data=$this->_request->getPost("standard".$customerrules_data['field_data']);
			   // Rule:- If the inquiry is received AFTER a specific time , do not place the call
				  if($prefered_time_in_seconds <= $customer_current_time){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;
					   $inquiry_type='After hours';
					   $customerRuleFlag = true;
				   }
			  }
			  if($customerrules_data['rule_id']==3){
			   $inquiry_form_std_custom_data=$this->_request->getPost("standard".$customerrules_data['field_data']);
			   //Rule:- If the inquiry is received BEFORE a specific time , set the connection number to [phone number]
				 if($prefered_time_in_seconds >= $customer_current_time){
				   // SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=1;
					   $email_flag=1;
					   $customerRuleFlag = true;
				   }else{
				   $inquiry_type='After hours';
				   }
			  }
			  
			  if($customerrules_data['rule_id']==4){
			   $inquiry_form_std_custom_data=$this->_request->getPost("standard".$customerrules_data['field_data']);
			   // Rule:- If the inquiry is received BEFORE a specific time , do not place the call
				   if($prefered_time_in_seconds >= $customer_current_time){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;	
					   $inquiry_type='After hours';
					   $customerRuleFlag = true;
				   }
			  }
			  
			  if($customerrules_data['rule_id']==5){
			   $inquiry_form_std_custom_data=$this->_request->getPost("standard".$customerrules_data['field_data']);
			   // Rule:- If the inquiry contains a data field field with a value that is empty, set the connection number to [phone number]
			   
				   if(trim($inquiry_form_std_custom_data)==''){
				   // SET THE PHONE NUMBER FOR CALLING
				   	 $phone_flag=1;
					 $email_flag=1;	
					 $customerRuleFlag = true;
				   }
			  }
			  
			  if($customerrules_data['rule_id']==6){
			   $inquiry_form_std_custom_data=$this->_request->getPost("standard".$customerrules_data['field_data']);
			   // Rule:-If the inquiry contains a data field field with a value that is empty, do not place the call
				   if(trim($inquiry_form_std_custom_data)==''){
				   // DON'T SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=0;
					   $email_flag=1;	
					   $customerRuleFlag = true;
				   }
			  }
			  
			  if($customerrules_data['rule_id']==7){
			   $inquiry_form_std_custom_data=$this->_request->getPost("standard".$customerrules_data['field_data']);
			   // Rule:-If the inquiry contains a data field field with a value that is NOT empty, set the connection number to [phone number]
				   if(trim($inquiry_form_std_custom_data)!=''){
				   // SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=1;
					   $email_flag=1;
					   $customerRuleFlag = true;
				   }			  
			  }
			  
			  if($customerrules_data['rule_id']==8){
			   $inquiry_form_std_custom_data=$this->_request->getPost("standard".$customerrules_data['field_data']);
				// Rule:-If the inquiry contains a data field field with a value that is NOT empty, do not place the call
			   if(trim($inquiry_form_std_custom_data)!=''){
				   // DON'T PLACE THE CALL 
   					   $phone_flag=0;
					   $email_flag=1;	
					   $customerRuleFlag = true;
				   }
			  }
			  
			  if($customerrules_data['rule_id']==9){
			   $inquiry_form_std_custom_data=$this->_request->getPost("standard".$customerrules_data['field_data']);
			   // Rule:-If the inquiry contains a data field field with a value equal to [value], set the connection number to [phone number]
				   if(trim($inquiry_form_std_custom_data)==$customerrules_data['field_data_value']){
				   // SET THE PHONE NUMBER FOR CALLING
   					   $phone_flag=1;
					   $email_flag=1;	

					   $customerRuleFlag = true;
				   }
			  }
			  
			   if($customerrules_data['rule_id']==10){
			   $inquiry_form_std_custom_data=$this->_request->getPost("standard".$customerrules_data['field_data']);
			   // Rule:-If the inquiry contains a data field field with a value equal to [value], do not place the call
				   if(trim($inquiry_form_std_custom_data)==$customerrules_data['field_data_value']){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;					   
					   $customerRuleFlag = true;				   
				   }
			  }
			  
			   if($customerrules_data['rule_id']==11){
			   $inquiry_form_std_custom_data=$this->_request->getPost("standard".$customerrules_data['field_data']);
			 // Rule:-If the inquiry contains a data field field with a value NOT equal to [value], set the connection number to [phone number]
			   if(trim($inquiry_form_std_custom_data)!=$customerrules_data['field_data_value']){
				   // SET THE CALL
					   $phone_flag=1;
					   $email_flag=1;					   
					   $customerRuleFlag = true;				   
				   }
			  }
			  
			   if($customerrules_data['rule_id']==12){
			   $inquiry_form_std_custom_data=$this->_request->getPost("standard".$customerrules_data['field_data']);
			   // Rule:-If the inquiry contains a data field field with a value NOT equal to [value], do not place the call
				   if(trim($inquiry_form_std_custom_data)!=$customerrules_data['field_data_value']){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;					   
					   $customerRuleFlag = true;				   
				   }
			  }
		  }
		  
		  if(!$customerRuleFlag){
		  	
		  // WHEN RULES IS NOT SET THEN WE WILL USE OPERATION DATA TIMES

		  /* NOTE THESE RULES ARE HARD CODED IN THE ADMIN PANEL, IT WILL BE MANAGED BY THE ADMIN*/
			  
                       if(($start_time <= $customer_current_time)&& ($customer_current_time <= $end_time))
                       {
                       	
                      	 // SET THE PHONE NUMBER FOR CALLING
                               $phone_flag=1;
                               $email_flag=1;
                               if($forms_data['business_phone_validated'] == 1)
                              	 $phone=$forms_data['business_phone'];
                               if(empty ($phone))
                               {
                               		if($forms_data['home_phone_validated'] == 1)
                                   		$phone=$forms_data['home_phone'];
                               }
                       }
                       else
                       {
                       			$phone = "";
                       			if($forms_data['home_phone_validated'] == 1 && !empty($forms_data['home_phone']))
                             		  $phone=$forms_data['home_phone'];
                               /*if(empty ($phone))
                               {
                               	   if($forms_data['business_phone_validated'] == 1)	
                                  	 $phone=$forms_data['business_phone'];
                               }*/
                                   
                       }
				   //echo $phone.	'------1500';
		  }		  
		  
		  
		 /* CHECKING CUSTOMER RULES FOR SENDING CALL END HERE */
			//echo "count form fields ".count($form_std_fields_announce_datas);
			if(count($form_std_fields_announce_datas)>0){
			$connecttotwilio=1;   // IT SHOWS IF ANNOUNCE DATA IS AVAILABLE
			}else{
			$inquiry_type="Incomplete"; // Do not contain sufficient information
			$connecttotwilio=0; // IT SHOWS IF ANNOUNCE DATA NOT AVAILABLE
			}			
			     
      		$Body="";
			$string_to_pass_to_twilio=''; 	
		 	$firstname=trim($this->_request->getPost('standard1'));			 	
			$lastname=trim($this->_request->getPost('standard2'));
		 	
		 	if($this->_request->getPost('announce_1')=='1' && $this->_request->getPost('announce_2')=='1' && !empty($firstname) && !empty($lastname)){	
		 		$string_to_pass_to_twilio .="Their Name is ".$firstname." ".$lastname.".";
		 	}else if($this->_request->getPost('announce_1')=='1' && !empty($firstname)){	 			
		 		$string_to_pass_to_twilio .="Their Name is ".$firstname.".";
		 	}else if($this->_request->getPost('announce_2')=='1' && !empty($lastname)){	 			
		 		$string_to_pass_to_twilio .="Their Name is ".$lastname.".";
		 	}
		 	
		 	if(!empty($firstname))
		 		$Body.=" First Name : ".$firstname."<br><br>";
		 	if(!empty($lastname))	
		 		$Body.=" Last Name : ".$lastname."<br><br>";
		 	
		 		 $sub = "new inquiry";
		 		 if(empty($phone))
                    $sub = "new After Hours inquiry";
                        $customerHeader = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'><div style='color:black;'>Dear ".$firstname .",<br><br>";
		 	$customerHeader .="Thank you for your interest in '".$form_owner_data['companyname']."'.  We have received your information and will be in touch shortly. Please keep a copy of this email for your reference.<br><br>Sincerely, '".$form_owner_data['companyname']."'<br><br>";

                        $ownerHeader = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'><img src='". WEBSITE_IMG_URL. "branding.png' /> <hr />You have received a $sub through FormActivate. Details of the inquiry are included below <br><br>";
		 	/**************** EMAIL START HERE ********************************/
		 	
			$email=trim($this->_request->getPost('standard3'));
			if($this->_request->getPost('announce_3')=='1' && !empty($email)){	 	
				$string_to_pass_to_twilio .=" Their Email is ".$email.".";				
			}
			
			if($email!=''){	 					
				$Body.=" Email : ".$email."<br><br>";
			}			
			/**************** EMAIL END HERE ********************************/
			
			/**************** COMPANY START HERE ********************************/
			$company=trim($this->_request->getPost('standard5'));
			if($this->_request->getPost('announce_5')=='1' && !empty($company)){
					$string_to_pass_to_twilio .=" Their Company Name is ".$company.".";
			}
			if($company!=''){	 					
				$Body.=" Company : ".$company."<br><br>";
			}
			/**************** COMPANY END HERE ********************************/
			
			/**************** Street Address START HERE ***********************/
			$streetaddress=trim($this->_request->getPost('standard6'));
			if($this->_request->getPost('announce_6')=='1' && !empty($streetaddress)){
				$string_to_pass_to_twilio .=" Their Street Address is ".$streetaddress.".";
				
			}
			if($streetaddress!=''){	 					
				$Body.=" Street Address : ".$streetaddress."<br><br>";
			}
			/**************** Street Address END HERE ********************************/
			
			/**************** City START HERE ***********************/
			$city=trim($this->_request->getPost('standard7'));
			if($this->_request->getPost('announce_7')=='1' && !empty($city)){
				$string_to_pass_to_twilio .=" Their City is ".$city.".";
				
			}

			if($city!=''){	 					
				$Body.=" City : ".$city."<br><br>";
			}
			/**************** City END HERE ****************/
			
			/**************** State START HERE ***********************/
			$state=trim($this->_request->getPost('standard8'));
			if($this->_request->getPost('announce_8')=='1' && !empty($state)){
				$string_to_pass_to_twilio .=" Their State is ".$state.".";
			}
			
			if($state!=''){	 					
				$Body.=" State : ".$state."<br><br>";
			}
			/**************** Zip START HERE ***********************/
			$zip=trim($this->_request->getPost('standard9'));
			if($this->_request->getPost('announce_9')=='1' && !empty($zip)){
					$string_to_pass_to_twilio .=" Their Zip is ".$zip.".";
			}
			
			if($zip!=''){	 					
				$Body.=" Zip : ".$zip."<br><br>";
			}
			/**************** Zip End HERE ***********************/
			
			/**************** Country START HERE ***********************/
			$country=trim($this->_request->getPost('standard10'));
			if($this->_request->getPost('announce_10')=='1' && !empty($country)){
				$string_to_pass_to_twilio .=" Their Country is ".$country.".";
				
			}
			if($country!=''){	 					
				$Body.=" Country : ".$country."<br><br>";
			}
			/**************** Country End HERE ***********************/
			
			/**************** Phone number START HERE ***********************/
			$homephone=trim($this->_request->getPost('standard4'));
			if($this->_request->getPost('announce_4')=='1' && !empty($homephone)){
				$string_to_pass_to_twilio .=" Their Phone Number is ".$homephone.".";
				
			}
                        if($homephone!=''){
				$Body.=" Phone Number is : ".$homephone."<br><br>";
			}
			
			/**************** Phone number End HERE ***********************/
			
			if($homephone==''){
			$inquiry_type='Incomplete';
			}else if(($customer_current_time>$start_time) && ($customer_current_time > $end_time))
			{
			$inquiry_type='After hours';
			}
			
			$form_std_fields=new FormsStd();			
			
			/**************** Custom 1 Start HERE ***********************/
			$custom1=trim($this->_request->getPost('custom1'));			
			$ann_cust=explode('_',$this->_request->getPost('announce_custom1'));		
			if($ann_cust[0]=='1' && !empty($custom1)){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust[1]));		
			$string_to_pass_to_twilio .=" the value entered for the field, ".$forms_data['label'].", is ".$custom1.".";
			$Body.=$forms_data['label']." : ".$custom1."<br><br>";
			}
			if($ann_cust[0]=='0' && !empty($custom1)){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust[1]));					
			$Body.=$forms_data['label']." : ".$custom1."<br><br>";
			}
			/**************** Custom 1 End HERE ***********************/
			
			/**************** Custom 2 Start HERE ***********************/
			$custom2=trim($this->_request->getPost('custom2'));
			$ann_cust2=explode('_',$this->_request->getPost('announce_custom2'));						
			if($ann_cust2[0]=='1' && !empty($custom2)){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust2[1]));	
				$string_to_pass_to_twilio .=" the value entered for the field, ".$forms_data['label'].", is ".$custom2.".";
				$Body.=$forms_data['label']." : ".$custom2."<br><br>";
			}
			
			if($ann_cust2[0]=='0' && !empty($custom2)){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust2[1]));					
				$Body.=$forms_data['label']." : ".$custom2."<br><br>";
			}			
			/**************** Custom 2 End HERE ***********************/
			
			/**************** Custom 3 Start HERE ***********************/
			$custom3=trim($this->_request->getPost('custom3'));			
			$ann_cust3=explode('_',$this->_request->getPost('announce_custom3'));						
			if($ann_cust3[0] == '1' && !empty($custom3))
			{	 	
				$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust3[1]));	
				$string_to_pass_to_twilio .=" the value entered for the field, ".$forms_data['label'].", is ".$custom3.".";
				$Body.=$forms_data['label']." : ".$custom3."<br><br>";
			}
			if($ann_cust3[0]=='0' && !empty($custom3))
			{	 	
				$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust3[1]));					
				$Body.=$forms_data['label']." : ".$custom3."<br><br>";
			}
			
			/**************** Custom 3 End HERE ***********************/
			
			/**************** Custom 4 Start HERE ***********************/
			$custom4=trim($this->_request->getPost('custom4'));
			$ann_cust4=explode('_',$this->_request->getPost('announce_custom4'));						
			if($ann_cust4[0]=='1' && !empty($custom4)){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust4[1]));	
					$string_to_pass_to_twilio .=" the value entered for the field, ".$forms_data['label'].", is ".$custom4.".";
					$Body.=$forms_data['label']." : ".$custom4."<br><br>";
			}
			
			if($ann_cust4[0]=='0' && !empty($custom4)){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust4[1]));						
					$Body.=$forms_data['label']." : ".$custom4."<br><br>";
			}
			
			/**************** Custom 4 End HERE ***********************/
			
			/**************** Custom 5 Start HERE ***********************/
			
			$custom5=trim($this->_request->getPost('custom5'));					
			$ann_cust5=explode('_',$this->_request->getPost('announce_custom5'));						
			if($ann_cust5[0]=='1' && !empty($custom5)){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust5[1]));	
				$string_to_pass_to_twilio .=" the value entered for the field, ".$forms_data['label'].", is ".$custom5.".";
				$Body.=$forms_data['label']." : ".$custom5."<br><br>";				
			}
			if($ann_cust5[0]=='0' && !empty($custom5)){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust5[1]));				
				$Body.=$forms_data['label']." : ".$custom5."<br><br>";				
			}
			/**************** Custom 4 End HERE ***********************/
			
			
			if(trim($string_to_pass_to_twilio)!='')
                        {
				if(!empty($homephone))
                                {
                                    $user_phone = $homephone;
                                }
                                else
                                {
                                    $user_phone = '';
                                }

				$string_to_pass_to_twilio .=" To connect with the user, press 1 ";
                                $string_to_pass_to_twilio .=", To announce the message again, press the 0 key ";
                                
                                if($form_owner_data['plan_id'] == 4 || $form_owner_data['plan_id'] == 6)
                                    $string_to_pass_to_twilio .=", To forward the message, press the 4 key ";
                                if($form_owner_data['plan_id'] == 6)
                                    $string_to_pass_to_twilio .=", To call back after five minuites the message, press the 6 key ";

				$string_to_pass_to_twilio .="@#@#@@#".$user_phone."@#@#@@#".$caller_id;

				
				
				// NEED TO CONNECT TO NUMBER USER HAS ENTERED IN THE FORM
			}
			
			
			$form_submitted_data = array();				
			$form_submitted_data['form_id']=$form_id;
			$form_submitted_data['customer_id']=$forms_data['customer_id'];	
			//$form_submitted_data['caller_id']=$caller_id;
            $form_submitted_data['caller_id']=$homephone;	
			$form_submitted_data['customer_phone']=$phone;	
			$form_submitted_data['firstname']=$firstname;
			$form_submitted_data['lastname']=$lastname;
			$form_submitted_data['email']=$email;
			$form_submitted_data['company']=$company;
			$form_submitted_data['streetaddress']=$streetaddress;
			$form_submitted_data['city']=$city;
			$form_submitted_data['state']=$state;
			$form_submitted_data['zip']=$zip;
			$form_submitted_data['country']=$country;
			$form_submitted_data['phonenumber']=$homephone;
			$form_submitted_data['custom1']=$custom1;
			$form_submitted_data['custom2']=$custom2;
			$form_submitted_data['custom3']=$custom3;
			$form_submitted_data['custom4']=$custom4;
			$form_submitted_data['custom5']=$custom5;
			$form_submitted_data['inquiry_type']=$inquiry_type;
			$form_submitted_data['url']=$_SERVER['HTTP_REFERER'];
			$form_submitted_data['ip']=$_SERVER['REMOTE_ADDR'];
			$form_submitted_data['user_agent']=$_SERVER['HTTP_USER_AGENT'];
			$form_submitted_data['date_created']=date('Y-m-d',time()); // need to store orginal time
			$form_submitted_data['time']=date('h:i A',time());
			$form_submitted_data['announced_data']=$string_to_pass_to_twilio;
			$last_insert_id=$inquiryTable->insert($form_submitted_data);
			$time=time()-rand(0, 9999999999); // I used this for secure encryption logic.
			$runtime_session_for_form_submitter = new Zend_Session_Namespace('Zend_Auth');
			$runtime_session_for_form_submitter->runtime_session=md5($time);
					
			/* EMAIL CONSTRUCTION FOR OWNER  START HERE */
			//echo $string_to_pass_to_twilio;  exit;

                        
//                        print_r($Body);
//
//                        die();
                        


                        // first send customer or form submitter email

                        $mail = new Zend_Mail();

                        if($emailnotification_data['send_email_notification_pros_leads']=='yes')
                        {                                
                        		$image = "";  
                        		$subject = "";        
                        	    if(empty($subject))
                        	    	$subject = "We have received your information";  
									$image ="<div style='padding-left: 150px'><a href='http://formactivate.com' target='_blank'><img src='http://formactivate.com/images/powered_by_formactivate.png' border='0' alt='FormActivate: Web Form Submission to Live Phone Contact in 20 Seconds' title='FormActivate: Web Form Submission to Live Phone Contact in 20 Seconds'></a></div>";
                                $mailBody = $customerHeader.$body." Thanks <br><span style='color:black;'> ".$form_owner_data['companyname']."</span></div></div>";
                                $mail->setBodyHtml($mailBody);
                                $mail->setFrom($form_owner_data['email'], $form_owner_data['companyname']);
                                $mail->addTo($email,$firstname.' '.$lastname);                                
                                $mail->setSubject($subject);
                                $result=$mail->send();
                        }
			
                            $mail = new Zend_Mail();
                            $Body.=" Thanks <br><span style='color:black;'> ".WEBSITE_NAME."</span></div></div>";
                            $mail->setBodyHtml($ownerHeader.$Body);
                            $mail->setFrom(SITE_SUPPORT_EMAIL, WEBSITE_NAME);                            
                            $mail->setSubject('You have received a new inquiry through '. WEBSITE_NAME);
                            $mail->addTo($form_owner_data['email'],'Owner');

                            if($notification_email!='' && $notification_email != $form_owner_data['email'])
                            {
                                $mail->addTo($notification_email,'Admin');
                            }

                            //$mail->setSubject('We have received your information');
                            $result=$mail->send();                            
			
			
			/* EMAIL SEND END HERE */	
			/*echo "connect to phone ";
			echo $phone_flag; echo "connect to twilio";
			echo $connecttotwilio;	*/
//
//                             var_dump(md5($time), $last_insert_id, $form_id, $phone);
//                             echo '................<br />';
//
//			echo '/forms/connecttotwilio/rsession/'.md5($time).'/id/'.$last_insert_id.'/form_id/'.$form_id.'/user_phone/'.$phone;
//
//                        die();

			if($connecttotwilio==1)
			{				
				$this->_redirect('/forms/connecttotwilio/rsession/'.md5($time).'/id/'.$last_insert_id.'/form_id/'.$form_id.'/user_phone/'.$user_phone.'/notification_email/'.$notification_email);	exit;
			}
			
			$nowww = ereg_replace('http://','',$redirect_url);
			$redirect_url='http://'.ereg_replace('www\.','',$nowww);
			
			if($redirect_type==1){
			   //REAL SENE $this->_redirect($redirect_url);  exit;
                            echo "<script type='text/javascript'>window.top.location.href='{$redirect_url}'</script>"; exit;
//                            $runtime_session = new Zend_Session_Namespace('Zend_Auth');
//                            $runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
//                            $this->_redirect('/forms/thanks');   exit;
			}else{
                            $runtime_session = new Zend_Session_Namespace('Zend_Auth');
                            $runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
                            $this->_redirect('/forms/thanks');   exit;
			}
										 	
			//exit;
		 }else{		 
		 $login_error = new Zend_Session_Namespace('Zend_Auth');
		 $login_error->loginError="<font color='black'><b>The Form you are submitting is not exist.</b></font>";
		 $this->_redirect('/customers/login'); 
		// echo "1088";//$this->_redirect($forms_data['redirect_url']);  
			//exit;
		 }				
    } 
   }     
    
     /**
     * connecttotwilioAction() -Method . This method will be used to connect to twilio when the customer will submit the form.
     *
     * @access public
	 * @return void
     */
    
    public function connecttotwilioAction()
    {
    	 $filter=new Zend_Filter_StripTags();
    	 $rsession=$this->_request->getParam('rsession');	     
    	 $user_phone=$this->_request->getParam('user_phone');
         $notification_email = $this->_request->getParam('notification_email');
    	 $this->_helper->layout->setLayout('layout_twilio');
    	 
    	 $id=$this->_request->getParam('id');
    	 $form_id=$this->_request->getParam('form_id');	  
    	 $runtime_session= new Zend_Session_Namespace('Zend_Auth');	    
    	 	 		 
         if($runtime_session->runtime_session==$rsession)
         {
                $inquiryTable = new inquiry();
                $inquiryTableData=$inquiryTable->fetchRow($inquiryTable->select()->where('id='.$id));
				$customer_phone	= $inquiryTableData['customer_phone'];

                $formsTable = new Forms();
                $formsTableData=$formsTable->fetchRow($formsTable->select()->where('id='.$form_id));
                $client_number = $formsTableData['caller_id'];
                $customer_id   = $formsTableData['customer_id'];

                $redirect_type=$formsTableData['redirect_type'];

		$nowww = preg_replace('/http:\/\//','',$formsTableData['redirect_url']);
		$redirect_url='http://'.preg_replace('/www\./','',$nowww);

                //$domain = parse_url($nowww);

                require "twilio.php";	/* 	Include Twilio api	require "twilio.php";
                 */

                // need to check remaining credits before making the call.

                $members = new members();
                $callInfoData=$members->fetchRow($members->select()->where('id='.$customer_id));
                $remaining_call = $callInfoData['total_remaining_calls'];



                /* Twilio REST API version */


                /* Set our AccountSid and AuthToken */
                //$ApiVersion = "2010-04-01"; // config variable
                //$AccountSid = "AC2dbe8a176e89ff7f6641d4d03c047bca"; // take from config file
                //$AuthToken = "1a0e256d656ebef49cbe84cd5e441501";
		    
                 $ApiVersion = TWILIO_API_VERSION; // config variable
                 $AccountSid =  TWILIO_ACCOUNT_SID; // take from config file
                 $AuthToken =  TWILIO_AUTH_TOKEN;
     
                // Outgoing Caller ID you have previously validated with Twilio
                     //$CallerID = '858-401-2688';  //to come dynamically
                //$CallerID = '917-338-7987';
				if(empty($customer_phone))
					$customer_phone = $client_number;

                $CallerID = CALLER_ID;
                $to       = $customer_phone;//$client_number; Changed by Hasan
                
                
    		$client = new TwilioRestClient($AccountSid, $AuthToken);
                //validate user phone number
    		
		if(!empty($inquiryTableData['announced_data']) && $remaining_call > 0 && !empty($to)) // if number of call did not exceed 
		{
			$data=$inquiryTableData['announced_data']."@@@@".$formsTableData['to_repeat_the_announcement']; // to add by hasan ."%".$formsTableData['to_repeat_the_announcement']
			/* Instantiate a new Twilio Rest Client */
					
                /* Initiate a new outbound call by POST'ing to the Calls resource */
              	 $response = $client->request("/$ApiVersion/Accounts/$AccountSid/Calls",
                    "POST", array(
                        "From" => $CallerID,
                        "To"   => $to,
                        "Url"  => WEBSITE_URL."hello.php?data=".base64_encode($data),
                        "StatusCallback" => WEBSITE_URL."twilio_call_time_update.php?notification_email={$notification_email}&customer_id={$customer_id}"
                    ));

                    
                    
//               echo '<pre>';
//               print_r($response);
//               die();

                $inquiryTable = new inquiry();
                $inquiry_update_data = array();

                if( !empty ($response->IsError)) //means error occured
                {
                    //echo "Error: {$response->ErrorMessage}";
					$inquiry_update_data['response_error']=$response->ResponseText;
					$inquiryTable->update($inquiry_update_data,'id='.$id);

					//   print $this->db->getProfiler()->getLastQueryProfile()->getQuery();
					//   die();
					//   save in db instead of echo
					$runtime_session = new Zend_Session_Namespace('Zend_Auth');
					$runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!!!!</b></font>";
					//$redirect_type=$formsTableData['redirect_type'];
		 			//$redirect_url=$formsTableData['redirect_url'];
		 		if($redirect_type==1)
		 		{
                   echo "<script type='text/javascript'>window.top.location.href='{$redirect_url}'</script>"; exit;
					//	$redirect_url = $redirect_url.'?msg='.urlencode($response->ErrorMessage);
					//	$this->_redirect($redirect_url);  exit;
				}
				else
				{			
					$this->_redirect('/forms/thanks');   exit;
				}
               }
               else
               {
                        $inquiry_update_data['response_error']=$response->ResponseText;

                        $inquiryTable->update($inquiry_update_data,'id='.$id);

                        $members = new members();
							
                        /*START changed By Pushpendra to substract to only used minutes action did in  twilio_call_time_update.php */
                      		 // $members->update(array('total_remaining_calls' => new Zend_Db_Expr( 'total_remaining_calls-1')), 'id='.$customer_id);
						 /*END changed By Pushpendra*/
                        
                        /* FOR PLAN RUN OUT EMAIL SENDING */
                        $member_data = $members->fetchRow($members->select()->where('id='.$customer_id));
                        $plan_id               = $member_data['plan_id'];
                        $total_remaining_calls = $member_data['total_remaining_calls'];
                        $plan_email            = $member_data['email'];
                        $name                  = $member_data['firstname']. ' '. $member_data['lastname'];
                        
                        $plan_mail_body = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'>
                        <img src='". WEBSITE_IMG_URL. "branding.png' /> <hr />";
                        $plan_mail_body.="Hi {$name},<br /><br />We wanted to let you know that your FormActivate monthly plan has almost run out. <br />
                        Once your plan runs out, you will continue to receive email notifications, <br />
                        but you will no longer receive any phone calls for new leads. <br />
                        If you would like to change your plan, please do so by clicking on the following link:<br />";
                        $plan_mail_body .= WEBSITE_URL.'customers/editplandetails/id/'.$customer_id . "<br /><br />";
                        $plan_mail_body.=" Thanks <br><span style='color:black;'> ".WEBSITE_NAME."</span></div></div>";

                        $plan_mail_body2 = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'>
                        <img src='". WEBSITE_IMG_URL. "branding.png' /> <hr />";
                        $plan_mail_body2.="Hi {$name},<br /><br />We wanted to let you know that your FormActivate monthly plan has run out. <br />
                        At this time you will continue to receive email notifications, <br />
                        but you will no longer receive any phone calls for new leads. <br />
                        If you would like to change your plan, please do so by clicking on the following link:<br />";
                        $plan_mail_body2 .= WEBSITE_URL.'customers/editplandetails/id/'.$customer_id . "<br /><br />";
                        $plan_mail_body2.=" Thanks <br><span style='color:black;'> ".WEBSITE_NAME."</span></div></div>";

                        $mail  = new Zend_Mail();
                        $mail2 = new Zend_Mail();

                        $mail->setBodyHtml($plan_mail_body);
                        $mail->setFrom(SITE_SUPPORT_EMAIL, WEBSITE_NAME);
                        $mail->addTo($plan_email, 'Admin');
                        //$mail->setSubject('A customer has submitted a Form on '.WEBSITE_NAME);
                        $mail->setSubject('Your Form@ctive plan is run out');
                        

                        $mail2->setBodyHtml($plan_mail_body);
                        $mail2->setFrom(SITE_SUPPORT_EMAIL, WEBSITE_NAME);
                        $mail2->addTo($plan_email, 'Admin');
                        $mail2->setSubject('Your Form@ctive plan has run out');
                        
                        $percent = 1/10;// means 10% of credited minutes
                        $subscriptions = new subscriptions();
                        $subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id=' . $plan_id));
                        
                        $ten_percent_of_minute = intval($subscriptions_data['code']) * $percent;
                        
                         if($member_data['override_plan_minutes'] != null && $member_data['override_plan_minutes'] != '')
            				$ten_percent_of_minute = intval($member_data['override_plan_minutes']) * $percent;
            			else $ten_percent_of_minute = intval($subscriptions_data['code']) * $percent;	
                        
                        if($plan_id == 2 && $total_remaining_calls <= $ten_percent_of_minute)
                        {
                            $result=$mail->send(); $result2=$mail2->send();
                        }
                        else if($plan_id == 3 && $total_remaining_calls <= $ten_percent_of_minute)
                        {
                            $result=$mail->send(); $result2=$mail2->send();
                        }
                        else if($plan_id == 4 && $total_remaining_calls <= $ten_percent_of_minute)
                        {
                            $result=$mail->send(); $result2=$mail2->send();
                        }
               			else if($plan_id == 6 && $total_remaining_calls <= $ten_percent_of_minute)
                        {
                            $result=$mail->send(); $result2=$mail2->send();
                        }
                         /* END FOR PLAN RUN OUT EMAIL SENDING */

                        
                        $runtime_session = new Zend_Session_Namespace('Zend_Auth');
                        $runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
                    // save in DB this returns a unique id
                    if($redirect_type==1){
                            //$this->_redirect($redirect_url);  exit;
                                  echo "<script type='text/javascript'>window.top.location.href='{$redirect_url}'</script>"; exit;
//                                $runtime_session = new Zend_Session_Namespace('Zend_Auth');
//                                $runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
//                                $this->_redirect('/forms/thanks');   exit;
                            }else{
                                $runtime_session = new Zend_Session_Namespace('Zend_Auth');
                                $runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
                                $this->_redirect('/forms/thanks');   exit;
                            }
	    	
                }
	   
            }else{
			 $this->_redirect('/customers/login'); 
		 }
        }
    }
    /**
     * get_actual_timeAction() -Method . This method will be used to get actual time.
     *
     * @access public
	 * @return void
     */
   function getactualtimeAction($time){
   	$timeTable = new timetable(); 
    $timeTableData=$timeTable->fetchRow($timeTable->select()->where('id='.$id));	
     return $timeTableData['timevalue'];	
   }
   
     /**
     * thanksAction() - Method for Customer thanks after purchasing
     *
     * @access public
	 * @return void
     */
    public function thanksAction()
    {
        $this->_helper->layout->disableLayout();
    }

    public function formCompletionStatus($form_id = '')
    {
        $session = new Zend_Session_Namespace('Zend_Auth');

        $session->overview_done = false;
        $session->standard_form_done = false;
        $session->custom_form_done = false;
        $session->hoo_form_done = false;
        $session->business_rule_done = false;
        $session->emailnotification_done = false;
        $session->redirect_done = false;

        if(!empty($form_id))
        {
            $ob = new Forms();
            $obData = $ob->fetchRow('id = '. $form_id .' AND caller_id != ""');
            if(count($obData) > 0)
                $session->overview_done = true;
            else
                $session->overview_done = false;

            $ob = new FormsStd();
            $obData = $ob->fetchRow('form_id = '. $form_id . ' AND field_status= \'standard\'');
            if(count($obData) > 0)
                $session->standard_form_done = true;
            else
                $session->standard_form_done = false;

            $ob = new FormsStd();
            $obData = $ob->fetchRow('form_id = '. $form_id . ' AND field_status= \'custom\'');
            if(count($obData) > 0)
                $session->custom_form_done = true;
            else
                $session->custom_form_done = false;

            $ob = new opthrs();
            $obData = $ob->fetchRow('form_id = '. $form_id );
            if(count($obData) > 0)
                $session->hoo_form_done = true;
            else
                $session->hoo_form_done = false;

            $ob = new customerrules();
            $obData = $ob->fetchRow('form_id = '. $form_id .' AND status = 1');
            if(count($obData) > 0)
                $session->business_rule_done = true;
            else
                $session->business_rule_done = false;

            $ob = new emailnotification();
            $obData = $ob->fetchRow('form_id = '. $form_id);
            if(count($obData) > 0)
                $session->emailnotification_done = true;
            else
                $session->emailnotification_done = false;

            $ob = new Forms();
            $obData = $ob->fetchRow('id = '. $form_id . ' AND redirect_type != 0');
            if(count($obData) > 0)
                $session->redirect_done = true;
            else
                $session->redirect_done = false;
        }
    }
    
	function time2seconds($time='00:00:00')
	{
	    list($hours, $mins, $secs) = explode(':', $time);
	    return ($hours * 3600 ) + ($mins * 60 ) + $secs;
	}
}
?>
