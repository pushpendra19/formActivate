<?php
class ApiController extends Zend_Rest_Controller
{
	private $inquaryId; 
	
    public function init()
    {
    	$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

	public function indexAction()
    {
         $this->getResponse()->appendBody("From indexAction() returning all articles".$this->_request->getParam('customers'));
    }

    public function responsereturn($inquiryId,$auth_key)
    {
    	$inquiryTable = new inquiry();
    	
	 	
	 	$id = $inquiryId;
	 	$auth_key = $auth_key;
	 	
	 	
	 		$authKey_format = explode("-", $auth_key);
	 	
                 $membercheck = new members();
                  $select = $membercheck->select()->setIntegrityCheck(false);
                  
                  $condition = $select->from($inquiryTable, array('inquiry.*'))
                            ->joinInner('forms', 'inquiry.form_id = forms.id', array('id'))
							 ->where("forms.authorizationkey='".$authKey_format[0]."' and inquiry.id='".$id."'");
							 
                    $inquiry_data=$inquiryTable->fetchRow($condition);  
	 	
					$status = 1;
                    
                    $formTable = new Forms();
	        		$data=$formTable->fetchRow($formTable->select()->where("authorizationkey='".$authKey_format[0]."' and status='".$status."'"));


	     $pos = 0;
		$redirectUrl = "";
		$find =  PROCESS_URL;
		 if($authKey_format[1] == "default" || empty($authKey_format[1]))
		 {
		 	if($data['redirect_type'] == 1)
		 	{
		 		$redirectUrl = trim($data['redirect_url']);
		 		
		 	}
		 	else
		 	{
		 	 	$redirectUrl =  trim($_SERVER['HTTP_REFERER']);
		 	}
		 	
			if(trim($redirectUrl) != '')
		 		echo "<script type='text/javascript'>window.top.location.href='{$redirectUrl}'</script>";
		 	
		 }	
	 	else if(count($inquiry_data) == 1 && ($authKey_format[1] == "json" || $authKey_format[1] == "xml" ))
	 	{
	 		
	    	$test_array = array (
								    'status' => 'success',
								    'statusCode' => '200',
								    'data' => array (
	    								'call_data' => $inquiry_data['statucallbackdata']
								    ),
								);

				if($authKey_format[1] == "json")
				{				
					header('Content-type: application/json');
					echo json_encode($test_array );
				}
				if($authKey_format[1] == "xml" || $authKey_format[1] == '' || $authKey_format[1] == "default")
				{	
					$xml = RestUtils::array_to_xml($test_array, new SimpleXMLElement('<response/>'))->asXML();
					RestUtils::sendResponse(200, $xml, 'application/xml');
				}
	 	}
	 	
	 	else  if($authKey_format[1] == "none")
		 {
		 	$redirectUrl =  trim($_SERVER['HTTP_REFERER']);
		 	 if(trim($redirectUrl) != '')	
		 	 	echo "<script type='text/javascript'>window.top.location.href='{$redirectUrl}'</script>";
		 }
	 	else
	 	{
	 		
	 		$redirectUrl =  trim($authKey_format[1]);
	 		
		 	 if(trim($redirectUrl) != '')		
		 		 echo "<script type=text/javascript>window.top.location.href='http://$redirectUrl'</script>";
	 	}
	 	
    	exit;
    	
    }
    
    
    public function getAction()
    {
    	
    	$this->putAction();
	 	
    }
    
    public function postAction()
    {
        $this->getResponse()->appendBody("From postAction() creating the requested article");

    }
    
    public function putAction()
    {
        
       		$form_id = trim($this->_request->getParam('form-id'));
       		$api_key = trim($this->_request->getParam('api-key'));
       		
       		$test_array = array();
       		$members = new members();	
			$member_data = $members->fetchRow($members->select()->where("api_key='$api_key'"));
			$member_id = $member_data['id'];
		 	
		 	$format = trim($this->_request->getParam('response'));
		 	
   	 		if(empty($api_key) || empty($member_id) || empty($form_id))
       		{
       		$test_array = array (
									    'Error' => 'Unauthorized access',
									    'statusCode' => '406',
									);
        		
									
        		if($format == "json")
        		{
        			header('Content-type: application/json');
					echo json_encode($test_array );
        		}
        		else
        		{
        			$xml = RestUtils::array_to_xml($test_array, new SimpleXMLElement('<response/>'))->asXML();
					RestUtils::sendResponse(406, $xml, 'application/xml');
        		}
        		
							exit;
       		
       		}
		 	
		 /********* start validation of Required fields *******/	
		 	
   		 $isError = false;
		 	$requiredField = "";
		 	$co = 0;
		 	
		 	$form_std_fields = new FormsStd();
		 	$conditionStdData = $form_std_fields->select()->where("status='1' and customer_id='".$member_id."' and form_id=".$form_id)->order('form_std_field.id asc');
        	$stdData = $form_std_fields->fetchAll($conditionStdData);
        	
        	foreach($stdData as $row)
        	{
        		if(array_key_exists(trim($row['inquiry_table_field']), $this->_request->getParams()))
				{
				
					if($row['field_required']=='yes' && trim($this->_request->getParam($row['inquiry_table_field'])) == '')
					{
						if($co == 0)
							$requiredField = $row['label'];
						else
							$requiredField .= ",".$row['label'];
							
							$co++;
							$isError = true;
					}
        		}
        		
        	}
        	
			
        	if($isError)
        	{
        		$test_array = array (
									    'status' => $requiredField.' is empty',
									    'statusCode' => '406',
									);
        		
									
        		if($format == "json")
        		{
        			header('Content-type: application/json');
					echo json_encode($test_array );
        		}
        		else
        		{
        			$xml = RestUtils::array_to_xml($test_array, new SimpleXMLElement('<response/>'))->asXML();
					RestUtils::sendResponse(406, $xml, 'application/xml');
        		}
        		
							exit;
        			
        	}
		 	 
		 /********* end validation of Required fields *******/
		 	
		 	/********* start validation of phone number *******/
		
		$formats = array(
                    '##########',
                    '###-###-####',
                    '(###)###-####',
                    '(###)#######',
                    '(###) ###-####',
                    '(###) #######',
                    '###.###.####'
                );

			if(trim($this->_request->getParam('phonenumber')) != '') 
			{
                
				 $number = trim(preg_replace('/[0-9]/', '#', trim($this->_request->getParam('phonenumber'))));
	
	       		 if (!in_array($number, $formats))
				 {
					
				 	 $test_array = array (
								    'status' => 'Invalid Phone Number',
								    'statusCode' => '406',
								);

				 if($format == "json")
        		{
        			header('Content-type: application/json');
					echo json_encode($test_array );
        		}
        		else
        		{
        			$xml = RestUtils::array_to_xml($test_array, new SimpleXMLElement('<response/>'))->asXML();
					RestUtils::sendResponse(406, $xml, 'application/xml');
        		}
						
						exit;	
				 }
			 
			}
			
			 /********* End validation of phone number *******/

			
			
			/********* start validation of Email *******/ 
		 	
				$validator = new Zend_Validate_EmailAddress();
				 
				 if(trim($this->_request->getParam('email')) != '') 
				{
					if (!$validator->isValid(trim($this->_request->getParam('email'))))
					{
					    $test_array = array (
								    'status' => 'Invalid Email',
								    'statusCode' => '406',
								);
								
					if($format == "json")
	        		{
	        			header('Content-type: application/json');
						echo json_encode($test_array );
	        		}
	        		else
	        		{
	        			$xml = RestUtils::array_to_xml($test_array, new SimpleXMLElement('<response/>'))->asXML();
						RestUtils::sendResponse(406, $xml, 'application/xml');
	        		}
	        		
						exit;
					    
					} 
				}
		 	
			 /********* End validation of Email *******/ 
			 
				
				
				
				
				
			$formTable = new Forms();
	        $data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));
	        $authorizationkey=$data['authorizationkey'];
	        
		 
		 if(empty($form_id) && empty($authorizationkey))
		 {
				//$this->_redirect('/forms/overview'); 
				$errorAutoKey = "abc";
				$this->responsereturn($this->inquaryId,$errorAutoKey);
				 //$this->_redirect('/web/'.$this->inquaryId.'/?auth_key='.$errorAutoKey);   
					//exit;
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
			
			// CUSTOMER (FORM OWNER ) CURRENT TIME  IN SECOANDS
			
			date_default_timezone_set($operation_hours_data['time_zone_code']);
			
			/************* Start updated by hasan**************/
			
					/*$customer_current_time=time();
                        $servertimezone=-18000;
			
                        $customer_current_time = $customer_current_time + ($time_zone_customer-$servertimezone);	*/	

			                         			
						$UTCtime = new DateTime('now', new DateTimeZone($operation_hours_data['time_zone_code']));
						$customer_current_time = strtotime($UTCtime->format("Y-m-d H:i:s"));
						$tm = date("H:i:s", $customer_current_time);
						$customer_current_time = $this->time2seconds($tm);
						
			/************* End updated by hasan**************/			
							
			/* SERVER IS BASED IN INDIA SO SERVER TIMEZONE IST WHICH IS 5:30 =19800 SECONDS*/			//$servertimezone=19800;  				 
			/* SERVER IS BASED IN US SO SERVER TIMEZONE IST WHICH IS -8:00 =51600 SECONDS*/
//
//                        $d = "customer time : ". $customer_current_time. "server time:";
//
//                        file_put_contents('test.txt', $d);
		 	
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
		   $customerRuleFlag = false;
		  
		  /* CHECKING CUSTOMER RULES FOR SENDING CALL SET NUMBER FROM CUSTOMERRULES TABLE, START HERE */	 
		  /* IN CUSTOMER RULES NOT SPECIFIED THEN CHECKING OPTHRS FOR SENDING CALL , START HERE */	 
		  if(count($customerrules_data)>0){
		  	
			   /* preferred time is like id_seconds so explode it to get the preferred time in seconds*/
			  $prefered_time=explode("_",$customerrules_data['prefered_time']); 
			  $prefered_time_in_seconds=$prefered_time[1];			  
			  $phone=$customerrules_data['phone'];
			
				  
			  $form_std_fields_buzznes_rule = new FormsStd();
                 $membercheck = new members();
                  $select = $membercheck->select()->setIntegrityCheck(false);
                  $condition = $select->from($form_std_fields_buzznes_rule, array('form_std_field.*'))
                            ->joinInner('customer_rules', 'form_std_field.field_id=customer_rules.field_data', array('rule_id'))
							 ->where( 'customer_rules.rule_id=' . $customerrules_data['rule_id'].' and form_std_field.form_id='.$form_id) ;
							 
                    $form_std_fields_buzznes_datas=$form_std_fields_buzznes_rule->fetchRow($condition);   
			  
			  
			  
			  
			  /* NOTE THESE RULES ARE HARD CODED IN THE ADMIN PANEL, IT WILL BE MANAGED BY THE ADMIN*/
			  			  			  
			  if($customerrules_data['rule_id']==1){
			   $inquiry_form_std_custom_data=$this->_request->getParam($form_std_fields_buzznes_datas['inquiry_table_field']);
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
			   $inquiry_form_std_custom_data=$this->_request->getParam($form_std_fields_buzznes_datas['inquiry_table_field']);
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
			   $inquiry_form_std_custom_data=$this->_request->getParam($form_std_fields_buzznes_datas['inquiry_table_field']);
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
			   $inquiry_form_std_custom_data=$this->_request->getParam($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:- If the inquiry is received BEFORE a specific time , do not place the call
				   if($prefered_time_in_seconds >= $customer_current_time){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;	
					   $inquiry_type='After hours';
				   }
			  }
			  
			  if($customerrules_data['rule_id']==5){
			   $inquiry_form_std_custom_data=$this->_request->getParam($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:- If the inquiry contains a data field field with a value that is empty, set the connection number to [phone number]
			   
				   if(trim($inquiry_form_std_custom_data)==''){
				   // SET THE PHONE NUMBER FOR CALLING
				   	 $phone_flag=1;
					 $email_flag=1;	
					 $customerRuleFlag = true;
				   }
			  }
			  
			  if($customerrules_data['rule_id']==6){
			   $inquiry_form_std_custom_data=$this->_request->getParam($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:-If the inquiry contains a data field field with a value that is empty, do not place the call
				   if(trim($inquiry_form_std_custom_data)==''){
				   // DON'T SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=0;
					   $email_flag=1;
					   $customerRuleFlag = true;	
				   }
			  }
			  
			  if($customerrules_data['rule_id']==7){
			   $inquiry_form_std_custom_data=$this->_request->getParam($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:-If the inquiry contains a data field field with a value that is NOT empty, set the connection number to [phone number]
				   if(trim($inquiry_form_std_custom_data)!=''){
				   // SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=1;
					   $email_flag=1;
					   $customerRuleFlag = true;
				   }			  
			  }
			  
			  if($customerrules_data['rule_id']==8){
			   $inquiry_form_std_custom_data=$this->_request->getParam($form_std_fields_buzznes_datas['inquiry_table_field']);
				// Rule:-If the inquiry contains a data field field with a value that is NOT empty, do not place the call
			   if(trim($inquiry_form_std_custom_data)!=''){
				   // DON'T PLACE THE CALL 
   					   $phone_flag=0;
					   $email_flag=1;	
					   $customerRuleFlag = true;
				   }
			  }
			  
			  if($customerrules_data['rule_id']==9){
			   $inquiry_form_std_custom_data=$this->_request->getParam($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:-If the inquiry contains a data field field with a value equal to [value], set the connection number to [phone number]
				   if(trim($inquiry_form_std_custom_data)==$customerrules_data['field_data_value']){
				   // SET THE PHONE NUMBER FOR CALLING
   					   $phone_flag=1;
					   $email_flag=1;	
					   $customerRuleFlag = true;

				   }
			  }
			  
			   if($customerrules_data['rule_id']==10){
			   $inquiry_form_std_custom_data=$this->_request->getParam($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:-If the inquiry contains a data field field with a value equal to [value], do not place the call
				   if(trim($inquiry_form_std_custom_data)==$customerrules_data['field_data_value']){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;					   
					   $customerRuleFlag = true;			   
				   }
			  }
			  
			   if($customerrules_data['rule_id']==11){
			   $inquiry_form_std_custom_data=$this->_request->getParam($form_std_fields_buzznes_datas['inquiry_table_field']);
			 // Rule:-If the inquiry contains a data field field with a value NOT equal to [value], set the connection number to [phone number]
			   if(trim($inquiry_form_std_custom_data)!=$customerrules_data['field_data_value']){
				   // SET THE CALL
					   $phone_flag=1;
					   $email_flag=1;					   
					   $customerRuleFlag = true;				   
				   }
			  }
			  
			   if($customerrules_data['rule_id']==12){
			   $inquiry_form_std_custom_data=$this->_request->getParam($form_std_fields_buzznes_datas['inquiry_table_field']);
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
                       	  $phone_flag=1;
                               $email_flag=1;
                               if($forms_data['business_phone_validated'] == 1)
                              	 $phone=$forms_data['business_phone'];
                               if(empty ($phone))
                               {
                               		if($forms_data['home_phone_validated'] == 1)
                                   		$phone=$forms_data['home_phone'];
                               }
                       }else{
                               if($forms_data['home_phone_validated'] == 1)
                             		  $phone=$forms_data['home_phone'];
                               if(empty ($phone))
                               {
                               	   if($forms_data['business_phone_validated'] == 1)	
                                  	 $phone=$forms_data['business_phone'];
                               }
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
			     
      		
			$firstname= trim($this->_request->getParam('firstname'));	
		 	$lastname= trim($this->_request->getParam('lastname'));	
		 	
			
		 	  $stringData = "";
			
        $form_std_fields=new FormsStd();

        $condition = $form_std_fields->select()->where('status="1" and customer_id='.$member_id.' and form_id='.$form_id)->order('form_std_field.id asc');
        $this->fields = $form_std_fields->fetchAll($condition);
        
        $customBody = "";
        $check = "";
        $co = 0;
       	$isError = false;
       	$requiredField = "";
       	$anounceNames = array();
       	$boolNames = false;
       	$bodyForName = "";
       	$string_to_pass_to_twilio_for_name = "";
        foreach($this->fields as $row)
        {
        	
			
			if(array_key_exists(trim($row['inquiry_table_field']), $this->_request->getParams()))
			{
				$Body="";
				$string_to_pass_to_twilio='';
					
					$form_std_fields_for_anounce_name=new FormsStd();
				if($co ==0)
				{
			        $conditions = $form_std_fields_for_anounce_name->select()->where("status='1' and customer_id='$member_id' and form_id='$form_id' and inquiry_table_field in ('firstname','lastname') and field_announce = 'yes'");
			        $anounceNames = $form_std_fields_for_anounce_name->fetchAll($conditions);
				}
			        
				
					
					if(count($anounceNames) == 2)
					{
						$anounceNames = array();
						$boolNames = true;
						$string_to_pass_to_twilio_for_name .="Their name is ".$firstname." ".$lastname.".";						
					}
					
						if($row['inquiry_table_field'] == 'firstname')
						{
						 	if($row['field_announce']=='yes' &&  !$boolNames)
						 	{
						 		$string_to_pass_to_twilio_for_name .=" Their name is ".$firstname."." ;
						 	}
						 	$bodyForName.=" First Name: ".$firstname."<br><br>";
						}
						if($row['inquiry_table_field'] == 'lastname')
						{		
						 	if($row['field_announce']=='yes' && !$boolNames)
						 	{
						 		$string_to_pass_to_twilio_for_name .=" Their name is ".$lastname."." ;
						 	}
						 	$bodyForName.=" Last Name: ".$lastname."<br><br>";
						}
					 	
					
				 	
						
					$co++;
				 	
          $customerHeader = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;font-family:Arial;font-size:13px'><div style='color:black;'>Dear ".$firstname .",<br><br>";
          $customerHeader .="Thank you for your interest in '".$form_owner_data['companyname']."'.  We have received your information and will be in touch shortly. Please keep a copy of this email for your reference.<br><br>Sincerely, '".$form_owner_data['companyname']."'<br><br>";
          $ownerHeader = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow:hidden;padding:15px;font-family:Arial;font-size:13px'><img src='". WEBSITE_IMG_URL. "branding.png' /><hr size='1'/><br>We wanted to let you know that a new inquiry was submitted to FormActivate. Details of the inquiry are outlined below:<br><br>";
				 	
				 	/**************** EMAIL START HERE ********************************/
				 	
					$email=trim($this->_request->getParam('email'));
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'email'){	 	
						$string_to_pass_to_twilio .=" Their email address is ".$email.".";				
					}
					
					if($email!=''){	 					
						$Body.=" Email: ".$email."<br><br>";
					}			
					/**************** EMAIL END HERE ********************************/
					
					/**************** COMPANY START HERE ********************************/
					$company=trim($this->_request->getParam('company'));
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'company' && $company != ''){	 	
							$string_to_pass_to_twilio .=" Their company name is ".$company.".";
					}
					if($company!=''){	 					
						$Body.=" Company: ".$company."<br><br>";
					}
					/**************** COMPANY END HERE ********************************/
					
					/**************** Street Address START HERE ***********************/
					$streetaddress=trim($this->_request->getParam('streetaddress'));
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'streetaddress' && $streetaddress != ''){	
						$string_to_pass_to_twilio .=" Their street address is ".$streetaddress.".";
						
					}
					if($streetaddress!=''){	 					
						$Body.=" Street Address: ".$streetaddress."<br><br>";
					}
					/**************** Street Address END HERE ********************************/
					
					/**************** City START HERE ***********************/
					$city=trim($this->_request->getParam('city'));
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'city' && $city != ''){
						$string_to_pass_to_twilio .=" Their city is ".$city.".";
						
					}
		
					if($city!=''){	 					
						$Body.=" City: ".$city."<br><br>";
					}
					/**************** City END HERE ****************/
					
					/**************** State START HERE ***********************/
					$state=trim($this->_request->getParam('state'));
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'state' && $state != ''){
						$string_to_pass_to_twilio .=" Their state is ".$state.".";
					}
					
					if($state!=''){	 					
						$Body.=" State: ".$state."<br><br>";
					}
					/**************** Zip START HERE ***********************/
					$zip=trim($this->_request->getParam('zip'));
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'zip' && $zip != ''){
							$string_to_pass_to_twilio .=" Their zip is ".$zip.".";
					}
					
					if($zip!=''){	 					
						$Body.=" Zip: ".$zip."<br><br>";
					}
					/**************** Zip End HERE ***********************/
					
					/**************** Country START HERE ***********************/
					$country=trim($this->_request->getParam('country'));
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'country' && $country != ''){
						$string_to_pass_to_twilio .=" Their country is ".$country.".";
						
					}
					if($country!=''){	 					
						$Body.=" Country: ".$country."<br><br>";
					}
					/**************** Country End HERE ***********************/
					
					/**************** Phone number START HERE ***********************/
					$homephone=trim($this->_request->getParam('phonenumber'));
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'phonenumber' && $homephone != ''){
						$string_to_pass_to_twilio .=" Their phone number is ".$homephone.".";
						
					}
		                        if($homephone!=''){
						$Body.=" Phone Number : ".$homephone."<br><br>";
					}
					
					
					
					
					/**************** Phone number End HERE ***********************/
					
					if($homephone==''){
					$inquiry_type='Incomplete';
					}else if(($customer_current_time<$start_time) && ($customer_current_time > $end_time))
					{
					$inquiry_type='After hours';
					}
					
					$form_std_fields=new FormsStd();			
					
					/**************** Custom 1 Start HERE ***********************/
					$custom1=trim($this->_request->getParam('custom1'));			
					/*$ann_cust=explode('_',$this->_request->getPost('announce_custom1'));		
					if($ann_cust[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust[1]));		*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom1' && $custom1 != ''){
					$string_to_pass_to_twilio .=", ".$row['label'].", ".$custom1.". ";
					
					
					
					//$Body.=$forms_data['label']." : ".$custom1."<br><br>";
					}
					if($custom1 != '' && $row['inquiry_table_field'] == 'custom1'){	 	
					//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust[1]));					
					$customBody.=$row['label'].": ".$custom1."<br><br>";
					}
					/**************** Custom 1 End HERE ***********************/
					
					/**************** Custom 2 Start HERE ***********************/
					$custom2=trim($this->_request->getParam('custom2'));
					/*$ann_cust2=explode('_',$this->_request->getPost('announce_custom2'));						
					if($ann_cust2[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust2[1]));	*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom2' && $custom2 != ''){
						$string_to_pass_to_twilio .=", ".$row['label'].", ".$custom2.". ";
						
						//$Body.=$forms_data['label']." : ".$custom2."<br><br>";
					}
					
					if($custom2 != '' && $row['inquiry_table_field'] == 'custom2'){	 	
					//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust2[1]));					
						$customBody.=$row['label'].": ".$custom2."<br><br>";
					}			
					/**************** Custom 2 End HERE ***********************/
					
					/**************** Custom 3 Start HERE ***********************/
					$custom3=trim($this->_request->getParam('custom3'));			
					/*$ann_cust3=explode('_',$this->_request->getPost('announce_custom3'));						
					if($ann_cust3[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust3[1]));	*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom3' && $custom3 != ''){
						$string_to_pass_to_twilio .=", ".$row['label'].", ".$custom3.". ";
						//$Body.=$forms_data['label']." : ".$custom3."<br><br>";
					}
					if($custom3 != '' && $row['inquiry_table_field'] == 'custom3'){	 	
					//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust3[1]));					
						$customBody.=$row['label'].": ".$custom3."<br><br>";
					}
					
					/**************** Custom 3 End HERE ***********************/
					
					/**************** Custom 4 Start HERE ***********************/
					$custom4=trim($this->_request->getParam('custom4'));
					/*$ann_cust4=explode('_',$this->_request->getPost('announce_custom4'));						
					if($ann_cust4[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust4[1]));	*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom4' && $custom4 != ''){
							$string_to_pass_to_twilio .=", ".$row['label'].", ".$custom4.". ";
							//$Body.=$forms_data['label']." : ".$custom4."<br><br>";
					}
					
					if($custom4 != '' && $row['inquiry_table_field'] == 'custom4'){	 	
						//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust4[1]));						
							$customBody.=$row['label'].": ".$custom4."<br><br>";
					}
					
					/**************** Custom 4 End HERE ***********************/
					
					/**************** Custom 5 Start HERE ***********************/
					
					$custom5=trim($this->_request->getParam('custom5'));					
					/*$ann_cust5=explode('_',$this->_request->getPost('announce_custom5'));						
					if($ann_cust5[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust5[1]));	*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom5' && $custom5 != ''){
						$string_to_pass_to_twilio .=", ".$row['label'].", ".$custom5.". ";
						//$Body.=$forms_data['label']." : ".$custom5."<br><br>";				
					}
					if($custom5 != '' && $row['inquiry_table_field'] == 'custom5'){	 	
						//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust5[1]));				
						$customBody.=$row['label'].": ".$custom5."<br><br>";				
					}
			}
        }	
        
        	$temp = $Body;
        	$Body = "";
			$Body .= $bodyForName;
			$Body .= $temp;
        	$Body .= $customBody;
        
        	$temp1 = $string_to_pass_to_twilio;
        	$string_to_pass_to_twilio = "";
        	$string_to_pass_to_twilio .= $string_to_pass_to_twilio_for_name;
        	$string_to_pass_to_twilio .= $temp1;
        	
			/**************** Custom 4 End HERE ***********************/
			
			//$user_phone = '';
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

				$string_to_pass_to_twilio .=" To connect with the customer press the 1 key ";
                                $string_to_pass_to_twilio .=", To announce this message again press the 0 key ";
                                
                                /*
                                if($form_owner_data['plan_id'] == 4 || $form_owner_data['plan_id'] == 6)
                                    $string_to_pass_to_twilio .=", To forward the message, press the 4 key ";
                                if($form_owner_data['plan_id'] == 6)
                                    $string_to_pass_to_twilio .=", To call back after five minuites the message, press the 6 key ";
                                */
                                
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
                        	    if(empty($subject) || $subject == '')
                        	    	$subject = "Thank you for your interest";  
									$image ="<div style='padding-left: 150px'><a href='http://formactivate.com' target='_blank'><img src='http://formactivate.com/images/powered_by_formactivate.png' border='0' alt='FormActivate: Web Form Submission to Live Phone Contact in 20 Seconds' title='FormActivate: Web Form Submission to Live Phone Contact in 20 Seconds'></a></div>";
                          $mailBody = $customerHeader.$Body." Thanks,<br><span style='color:black;'> ".$form_owner_data['companyname']."</span></div></div>";
                          $mail->setBodyHtml($mailBody);
                          $mail->setFrom(SITE_NO_REPLY_EMAIL, $form_owner_data['companyname']);//$form_owner_data['email']
                          $mail->addTo($email,$firstname.' '.$lastname);                                
                                $mail->setSubject($subject);
                          $result=$mail->send();
                        }
                            

                        $mail = new Zend_Mail();
                        $Body.=" Thanks,<br><span style='color:black;'> ".WEBSITE_NAME."</span><br><br><a href='http://formactivate.com' target='_blank'><img src='http://formactivate.com/images/powered_by_formactivate.png' border='0' alt='FormActivate: Web Form Submission to Live Phone Contact in 20 Seconds' title='FormActivate: Web Form Submission to Live Phone Contact in 20 Seconds'></a></div></div>";
                        $mail->setBodyHtml($ownerHeader.$Body);
                        $mail->setFrom(SITE_SUPPORT_EMAIL, WEBSITE_NAME);                            
                        $mail->setSubject('A New FormActivate Inquiry');
                        $mail->addTo($form_owner_data['email'],'FormActivate Notification Recipient');

                        if($notification_email!='' && $notification_email != $form_owner_data['email'])
                        {
                            //$mail->addTo($notification_email,'FormActivate Adminsitration');
                        }

                        $result=$mail->send();                            

			

						
			if($connecttotwilio==1)
			{		
				$this->connecttotwilioAction(md5($time),$last_insert_id,$form_id,$user_phone,$notification_email,$authorizationkey,$format);
				
			}
			
			
			
			
                           $this->responsereturn($last_insert_id,$authorizationkey."-".$format); exit;
		 }
		 else
		 {
		 			 
			$errorAutoKey = "abc";
		$this->responsereturn($last_insert_id,$errorAutoKey);
			exit;
		 }		
        

    }
    
    public function deleteAction()
    {
        $this->getResponse()
            ->appendBody("From deleteAction() deleting the requested article");

    }
    
    
 /** $this->connecttotwilioAction(md5($time),$last_insert_id,$form_id,$user_phone,$notification_email);
     * connecttotwilioAction() -Method . This method will be used to connect to twilio when the customer will submit the form.
     *
     * @access public
	 * @return void
     */
    
    public function connecttotwilioAction($ression ,$last_insert_id,$form_id,$user_phone,$notification_email,$authorizationkey,$format)
    {
    	 $filter=new Zend_Filter_StripTags();
    	 
         
         $rsession= $ression;	     
    	 $user_phone= $user_phone;
         $notification_email = $notification_email;
         $format = $format;
         $authKey = $authorizationkey."-".$format;
    	 $this->_helper->layout->setLayout('layout_twilio');
    	 

    	 $id=$last_insert_id;
    	 $this->inquaryId = $id;
    	 $form_id=$form_id;	
    	 
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

                $nowww = ereg_replace('http://','',$formsTableData['redirect_url']);
                $redirect_url='http://'.ereg_replace('www\.','',$nowww);
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

    		
		if(!empty($inquiryTableData['announced_data']) && $remaining_call > 0  &&  !empty($to)) // if number of call did not exceed 
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

                if(!empty ($response->IsError)) //means error occured
                {
                	
                	
                    //echo "Error: {$response->ErrorMessage}";
					$inquiry_update_data['response_error']=$response->ResponseText;
					$inquiryTable->update($inquiry_update_data,'id='.$id);

					//   print $this->db->getProfiler()->getLastQueryProfile()->getQuery();
					//   die();
					//   save in db instead of echo
					//$runtime_session = new Zend_Session_Namespace('Zend_Auth');
					//$runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!!!!</b></font>";
					//$redirect_type=$formsTableData['redirect_type'];
		 			//$redirect_url=$formsTableData['redirect_url'];
		 		/*if($redirect_type==1)
		 		{
                   echo "<script type='text/javascript'>window.top.location.href='{$redirect_url}'</script>"; exit;
					//	$redirect_url = $redirect_url.'?msg='.urlencode($response->ErrorMessage);
					//	$this->_redirect($redirect_url);  exit;
				}*/
				if($redirect_type !=1)
				{			
					$this->responsereturn($this->inquaryId,$authKey); exit;
					//$this->_redirect('/web/'.$this->inquaryId.'/?auth_key='.$authKey);   exit;
					//$this->processResponse();exit;
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
                        <img src='". WEBSITE_IMG_URL. "branding.png' /> <hr size='1'/><br>";
                        $plan_mail_body.="Hi {$name},<br /><br />We wanted to let you know that you are running low on the number of included minutes for your FormActivate monthly plan. <br />
                        Once your minutes have been used up, you will be billed on a per-minute basis in accordance with your plan settings. <br />
                        If you would like to change your plan, please do so by clicking on the following link:<br />";
                        $plan_mail_body .= WEBSITE_URL.'customers/editplandetails/id/'.$customer_id . "<br /><br />";
                        $plan_mail_body.=" Thanks,<br><span style='color:black;'> ".WEBSITE_NAME."</span></div></div>";

                        $plan_mail_body2 = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'>
                        <img src='". WEBSITE_IMG_URL. "branding.png' /> <hr size='1'/><br>";
                        $plan_mail_body2.="Hi {$name},<br /><br />We wanted to let you know that you have run out of included minutes for your  FormActivate monthly plan. <br />
                        At this time, you will be billed on a per-minute basis in accordance with your plan settings. <br />
                        If you would like to change your plan, please do so by clicking on the following link:<br />";
                        $plan_mail_body2 .= WEBSITE_URL.'customers/editplandetails/id/'.$customer_id . "<br /><br />";
                        $plan_mail_body2.=" Thanks,<br><span style='color:black;'> ".WEBSITE_NAME."</span></div></div>";

                        $mail  = new Zend_Mail();
                        $mail2 = new Zend_Mail();

                        $mail->setBodyHtml($plan_mail_body);
                        $mail->setFrom(SITE_SUPPORT_EMAIL, WEBSITE_NAME);
                        $mail->addTo($plan_email, 'FormActivate Notification Recipient');
                        $mail->setSubject('Your monthly included minutes are running low');
                        

                        $mail2->setBodyHtml($plan_mail_body);
                        $mail2->setFrom(SITE_SUPPORT_EMAIL, WEBSITE_NAME);
                        $mail2->addTo($plan_email, 'FormActivate Notification Recipient');
                        $mail2->setSubject('Your monthly included minutes have run out');
                        
                        
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

                        
                            $this->responsereturn($this->inquaryId,$authKey); exit;
	    	
                }
	   
            }
            else
            {
            	

			 $test_array = array (
								    'status' => 'Your total remaining calls is finished or Your announce data is empty or Your Connection Numbers is not validated! ',
								    'statusCode' => '406',
								);

			if($format == "xml" || $format == '' || $format == "default")
        		{			
					$xml = RestUtils::array_to_xml($test_array, new SimpleXMLElement('<response/>'))->asXML();
					RestUtils::sendResponse(406, $xml, 'application/xml');
        		}
			
			 if($format == "json")
			 {
			 	header('Content-type: application/json');
					echo json_encode($test_array );
			 }
			
			exit;
			//$this->_redirect('/customers/login'); 
		 }
        }
    }
    
	function selfURL()
	{
	    
	     $serverrequri =    $_SERVER['REQUEST_URI'];
	    
	    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	    
	    $protocol = strtolower($_SERVER["SERVER_PROTOCOL"], "/").$s;
	    
	   
	    
	    return $protocol."://".$_SERVER['SERVER_NAME'].$serverrequri;   
	}
	function time2seconds($time='00:00:00')
	{
	    list($hours, $mins, $secs) = explode(':', $time);
	    return ($hours * 3600 ) + ($mins * 60 ) + $secs;
	}

}
?>