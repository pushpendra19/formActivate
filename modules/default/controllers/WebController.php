<?php
class WebController extends Zend_Rest_Controller
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
         
         
         $form_id = $_GET['forms'];
		 $member_id = $_GET['customers'];
		$format = $_GET['format'];
		 
		 
		  /********* start validation of Required fields *******/	
		 	
   			 $isError = false;
		 	$requiredField = "";
		 	$co = 0;
		 	
		 	$form_std_fields = new FormsStd();
		 	$conditionStdData = $form_std_fields->select()->where('status="1" and customer_id='.$member_id.' and form_id='.$form_id)->order('form_std_field.id asc');
        	$stdData = $form_std_fields->fetchAll($conditionStdData);
        	
        	foreach($stdData as $row)
        	{
        		if(array_key_exists(trim($row['inquiry_table_field']), $_GET))
				{
				
					if($row['field_required']=='yes' && $_GET[$row['inquiry_table_field']] == '')
					{
						if($co == 0)
							$requiredField = $row['inquiry_table_field'];
						else
							$requiredField .= ",".$row['inquiry_table_field'];
							
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
								
        		 if($format == "xml")
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

			if(isset($_GET['phonenumber']))
			{   
			 $number = trim(preg_replace('/[0-9]/', '#', $_GET['phonenumber']));

       		 if (!in_array($number, $formats))
			 {
				  $test_array = array (
								    'status' => 'Invalid Phone Number',
								    'statusCode' => '406',
								);
								
				 if($format == "xml")
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
		   			 
			 }
			}
			 
			 /********* End validation of phone number *******/
			 
			/********* start validation of Email *******/ 
		 	
				 $validator = new Zend_Validate_EmailAddress();
				 
				 if(isset($_GET['email']))
				{   
					if (!$validator->isValid($_GET['email'])) 
					{
					   $test_array = array (
								    'status' => 'Invalid Email',
								    'statusCode' => '406',
								);
								
					if($format == "xml")
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
					    
					} 
				}
			 /********* End validation of Email *******/ 
			 
				
				
				
			$formTable = new Forms();
	        $data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));
	        $authorizationkey=$data['authorizationkey'];
		 
		 if(empty($form_id) && empty($authorizationkey))
		 {
				///$this->_redirect('/forms/overview'); 
				$errorAutoKey = "abc";
		 		$this->_redirect('/web/'.$this->inquaryId.'/?auth_key='.$errorAutoKey);   
			exit;
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
			$customer_current_time=time();
                        $servertimezone=-18000;
			
                        $customer_current_time = $customer_current_time + ($time_zone_customer-$servertimezone);			

		 	
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
			   $inquiry_form_std_custom_data=$_GET[$form_std_fields_buzznes_datas['inquiry_table_field']];
			   // Rule:-If the inquiry is received AFTER a specific time , set the connection number to [phone number]
				   if($prefered_time_in_seconds <= $customer_current_time){
				   // SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=1;
					   $email_flag=1;
				   }else{
				   $inquiry_type='After hours';
				   }
			  }
			  
			  if($customerrules_data['rule_id']==2){
			   $inquiry_form_std_custom_data=$_GET[$form_std_fields_buzznes_datas['inquiry_table_field']];
			   // Rule:- If the inquiry is received AFTER a specific time , do not place the call
				  if($prefered_time_in_seconds <= $customer_current_time){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;
					   $inquiry_type='After hours';
				   }
			  }
			  if($customerrules_data['rule_id']==3){
			   $inquiry_form_std_custom_data=$_GET[$form_std_fields_buzznes_datas['inquiry_table_field']];
			   //Rule:- If the inquiry is received BEFORE a specific time , set the connection number to [phone number]
				 if($prefered_time_in_seconds >= $customer_current_time){
				   // SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=1;
					   $email_flag=1;
				   }else{
				   $inquiry_type='After hours';
				   }
			  }
			  
			  if($customerrules_data['rule_id']==4){
			   $inquiry_form_std_custom_data=$_GET[$form_std_fields_buzznes_datas['inquiry_table_field']];
			   // Rule:- If the inquiry is received BEFORE a specific time , do not place the call
				   if($prefered_time_in_seconds >= $customer_current_time){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;	
					   $inquiry_type='After hours';
				   }
			  }
			  
			  if($customerrules_data['rule_id']==5){
			   $inquiry_form_std_custom_data=$_GET[$form_std_fields_buzznes_datas['inquiry_table_field']];
			   // Rule:- If the inquiry contains a data field field with a value that is empty, set the connection number to [phone number]
			   
				   if(trim($inquiry_form_std_custom_data)==''){
				   // SET THE PHONE NUMBER FOR CALLING
				   	 $phone_flag=1;
					 $email_flag=1;	
				   }
			  }
			  
			  if($customerrules_data['rule_id']==6){
			   $inquiry_form_std_custom_data=$_GET[$form_std_fields_buzznes_datas['inquiry_table_field']];
			   // Rule:-If the inquiry contains a data field field with a value that is empty, do not place the call
				   if(trim($inquiry_form_std_custom_data)==''){
				   // DON'T SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=0;
					   $email_flag=1;	
				   }
			  }
			  
			  if($customerrules_data['rule_id']==7){
			   $inquiry_form_std_custom_data=$_GET[$form_std_fields_buzznes_datas['inquiry_table_field']];
			   // Rule:-If the inquiry contains a data field field with a value that is NOT empty, set the connection number to [phone number]
				   if(trim($inquiry_form_std_custom_data)!=''){
				   // SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=1;
					   $email_flag=1;
				   }			  
			  }
			  
			  if($customerrules_data['rule_id']==8){
			   $inquiry_form_std_custom_data=$_GET[$form_std_fields_buzznes_datas['inquiry_table_field']];
				// Rule:-If the inquiry contains a data field field with a value that is NOT empty, do not place the call
			   if(trim($inquiry_form_std_custom_data)!=''){
				   // DON'T PLACE THE CALL 
   					   $phone_flag=0;
					   $email_flag=1;	
				   }
			  }
			  
			  if($customerrules_data['rule_id']==9){
			   $inquiry_form_std_custom_data=$_GET[$form_std_fields_buzznes_datas['inquiry_table_field']];
			   // Rule:-If the inquiry contains a data field field with a value equal to [value], set the connection number to [phone number]
				   if(trim($inquiry_form_std_custom_data)==$customerrules_data['field_data_value']){
				   // SET THE PHONE NUMBER FOR CALLING
   					   $phone_flag=1;
					   $email_flag=1;	

				   }
			  }
			  
			   if($customerrules_data['rule_id']==10){
			   $inquiry_form_std_custom_data=$_GET[$form_std_fields_buzznes_datas['inquiry_table_field']];
			   // Rule:-If the inquiry contains a data field field with a value equal to [value], do not place the call
				   if(trim($inquiry_form_std_custom_data)==$customerrules_data['field_data_value']){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;					   
				   }
			  }
			  
			   if($customerrules_data['rule_id']==11){
			   $inquiry_form_std_custom_data=$_GET[$form_std_fields_buzznes_datas['inquiry_table_field']];
			 // Rule:-If the inquiry contains a data field field with a value NOT equal to [value], set the connection number to [phone number]
			   if(trim($inquiry_form_std_custom_data)!=$customerrules_data['field_data_value']){
				   // SET THE CALL
					   $phone_flag=1;
					   $email_flag=1;					   
				   }
			  }
			  
			   if($customerrules_data['rule_id']==12){
			   $inquiry_form_std_custom_data=$_GET[$form_std_fields_buzznes_datas['inquiry_table_field']];
			   // Rule:-If the inquiry contains a data field field with a value NOT equal to [value], do not place the call
				   if(trim($inquiry_form_std_custom_data)!=$customerrules_data['field_data_value']){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;					   
				   }
			  }
		  }else{
		  	
		  // WHEN RULES IS NOT SET THEN WE WILL USE OPERATION DATA TIMES

		  /* NOTE THESE RULES ARE HARD CODED IN THE ADMIN PANEL, IT WILL BE MANAGED BY THE ADMIN*/
			  
                       if(($start_time <= $customer_current_time)&& ($customer_current_time <= $end_time))
                       {
                       // SET THE PHONE NUMBER FOR CALLING
                               $phone_flag=1;
                               $email_flag=1;
                               $phone=$forms_data['business_phone'];
                               if(empty ($phone))
                                   $phone=$forms_data['home_phone'];
                       }else{
                               $phone=$forms_data['home_phone'];
                               if(empty ($phone))
                                   $phone=$forms_data['business_phone'];
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
			     
      		
			
		 	$firstname= $_GET['firstname'];	
		 	$lastname= $_GET['lastname'];	
			
		 	  $stringData = "";
			
        $form_std_fields=new FormsStd();

        $condition = $form_std_fields->select()->where('status="1" and customer_id='.$member_id.' and form_id='.$form_id)->order('form_std_field.id asc');
        $this->fields = $form_std_fields->fetchAll($condition);
        
        $customBody = "";
        $check = "";
        $co = 0;
        
        foreach($this->fields as $row)
        {
        	
		 	$Body="";
		 	$string_to_pass_to_twilio='';
		 			 	
		 	
			if(array_key_exists(trim($row['inquiry_table_field']), $_GET))
			{
				/********* start validation Requaired field *******/ 
				
				$check .= $row['inquiry_table_field'];
				
				if($row['field_required']=='yes' && $_GET[$row['inquiry_table_field']] == '')
				{
					$requiredField = $row['inquiry_table_field']." is empty";
					$test_array = array (
								    'status' => $requiredField,
								    'statusCode' => '204',
								);
						$xml = RestUtils::array_to_xml($test_array, new SimpleXMLElement('<response/>'))->asXML();
						RestUtils::sendResponse(204, $xml, 'application/xml');
				   			 
						exit;
					
					/*header("Location: " . $_SERVER['HTTP_REFERER']);
				   			 return false;*/
				}
				
				/********* end validation Requaired field *******/ 
					
					
					$form_std_fields_for_anounce_name=new FormsStd();

			       $conditions = $form_std_fields_for_anounce_name->select()->where("status='1' and customer_id='$member_id' and form_id='$form_id' and inquiry_table_field in ('firstname','lastname') and field_announce = 'yes'");
			        $anounceNames = $form_std_fields_for_anounce_name->fetchAll($conditions);
				
					if(count($anounceNames) == 2)
					{
						$string_to_pass_to_twilio .="Their Name is ".$firstname." ".$lastname.".";
					}
					else
					{
					 	/*if($this->_request->getPost('announce_1')=='1' && $this->_request->getPost('announce_2')=='1'){	
					 		$string_to_pass_to_twilio .="Their Name is ".$firstname." ".$lastname.".";
					 	}else */
					 	if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'firstname'){
					 		$string_to_pass_to_twilio .="Their Name is ".$firstname.".";
					 	}else if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'lastname'){	 
					 		$string_to_pass_to_twilio .="Their Name is ".$lastname.".";
					 	}
					}
				 	 
					
				 	
		                        $customerHeader = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'><div style='color:black;'>Dear ".$firstname .",<br><br>";
				 	$customerHeader .="Thank you for your interest in '".$form_owner_data['companyname']."'.  We have received your information and will be in touch shortly. Please keep a copy of this email for your reference.<br><br>Sincerely, '".$form_owner_data['companyname']."'<br><br>";
		
		                        $ownerHeader = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'><img src='". WEBSITE_IMG_URL. "branding.png' /> <hr />You have received a new inquiry through FormActivate. Details of the inquiry are included below <br><br>";
				 	/**************** EMAIL START HERE ********************************/
				 	
					$email=$_GET['email'];
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'email'){	 	
						$string_to_pass_to_twilio .=" Their Email is ".$email.".";
					}
					
					if($email!=''){	 					
						$Body.=" Email : ".$email."<br><br>";
					}			
					/**************** EMAIL END HERE ********************************/
					
					/**************** COMPANY START HERE ********************************/
					$company=$_GET['company'];
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'company' && $company != ''){	 	
							$string_to_pass_to_twilio .=" Their Company Name is ".$company.".";
					}
					if($company!=''){	 					
						$Body.=" Company : ".$company."<br><br>";
					}
					/**************** COMPANY END HERE ********************************/
					
					/**************** Street Address START HERE ***********************/
					$streetaddress=$_GET['streetaddress'];
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'streetaddress' && $streetaddress != ''){	
						$string_to_pass_to_twilio .=" Their Street Address is ".$streetaddress.".";
						
					}
					if($streetaddress!=''){	 					
						$Body.=" Street Address : ".$streetaddress."<br><br>";
					}
					/**************** Street Address END HERE ********************************/
					
					/**************** City START HERE ***********************/
					$city=$_GET['city'];
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'city' && $city != ''){
						$string_to_pass_to_twilio .=" Their City is ".$city.".";
						
					}
		
					if($city!=''){	 					
						$Body.=" City : ".$city."<br><br>";
					}
					/**************** City END HERE ****************/
					
					/**************** State START HERE ***********************/
					$state=$_GET['state'];
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'state' && $state != ''){
						$string_to_pass_to_twilio .=" Their State is ".$state.".";
					}
					
					if($state!=''){	 					
						$Body.=" State : ".$state."<br><br>";
					}
					/**************** Zip START HERE ***********************/
					$zip=$_GET['zip'];
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'zip' && $zip != ''){
							$string_to_pass_to_twilio .=" Their Zip is ".$zip.".";
					}
					
					if($zip!=''){	 					
						$Body.=" Zip : ".$zip."<br><br>";
					}
					/**************** Zip End HERE ***********************/
					
					/**************** Country START HERE ***********************/
					$country=$_GET['country'];
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'country' && $country != ''){
						$string_to_pass_to_twilio .=" Their Country is ".$country.".";
						
					}
					if($country!=''){	 					
						$Body.=" Country : ".$country."<br><br>";
					}
					/**************** Country End HERE ***********************/
					
					/**************** Phone number START HERE ***********************/
					$homephone=$_GET['phonenumber'];
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'phonenumber' && $homephone != ''){
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
					$custom1=$_GET['custom1'];			
					/*$ann_cust=explode('_',$this->_request->getPost('announce_custom1'));		
					if($ann_cust[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust[1]));		*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom1' && $custom1 != ''){
					$string_to_pass_to_twilio .=" the value entered for the field, ".$row['label'].", is ".$custom1.".";
					//$Body.=$forms_data['label']." : ".$custom1."<br><br>";
					}
					if($custom1 != '' && $row['inquiry_table_field'] == 'custom1'){	 	
					//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust[1]));					
					$customBody.=$row['label']." : ".$custom1."<br><br>";
					}
					/**************** Custom 1 End HERE ***********************/
					
					/**************** Custom 2 Start HERE ***********************/
					$custom2=$_GET['custom2'];
					/*$ann_cust2=explode('_',$this->_request->getPost('announce_custom2'));						
					if($ann_cust2[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust2[1]));	*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom2' && $custom2 != ''){
						$string_to_pass_to_twilio .=" the value entered for the field, ".$row['label'].", is ".$custom2.".";
						//$Body.=$forms_data['label']." : ".$custom2."<br><br>";
					}
					
					if($custom2 != '' && $row['inquiry_table_field'] == 'custom2'){	 	
					//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust2[1]));					
						$customBody.=$row['label']." : ".$custom2."<br><br>";
					}			
					/**************** Custom 2 End HERE ***********************/
					
					/**************** Custom 3 Start HERE ***********************/
					$custom3=$_GET['custom3'];			
					/*$ann_cust3=explode('_',$this->_request->getPost('announce_custom3'));						
					if($ann_cust3[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust3[1]));	*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom3' && $custom3 != ''){
						$string_to_pass_to_twilio .=" the value entered for the field, ".$row['label'].", is ".$custom3.".";
						//$Body.=$forms_data['label']." : ".$custom3."<br><br>";
					}
					if($custom3 != '' && $row['inquiry_table_field'] == 'custom3'){	 	
					//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust3[1]));					
						$customBody.=$row['label']." : ".$custom3."<br><br>";
					}
					
					/**************** Custom 3 End HERE ***********************/
					
					/**************** Custom 4 Start HERE ***********************/
					$custom4=$_GET['custom4'];
					/*$ann_cust4=explode('_',$this->_request->getPost('announce_custom4'));						
					if($ann_cust4[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust4[1]));	*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom4' && $custom4 != ''){
							$string_to_pass_to_twilio .=" the value entered for the field, ".$row['label'].", is ".$custom4.".";
							//$Body.=$forms_data['label']." : ".$custom4."<br><br>";
					}
					
					if($custom4 != '' && $row['inquiry_table_field'] == 'custom4'){	 	
						//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust4[1]));						
							$customBody.=$row['label']." : ".$custom4."<br><br>";
					}
					
					/**************** Custom 4 End HERE ***********************/
					
					/**************** Custom 5 Start HERE ***********************/
					
					$custom5=$_GET['custom5'];					
					/*$ann_cust5=explode('_',$this->_request->getPost('announce_custom5'));						
					if($ann_cust5[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust5[1]));	*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom5' && $custom5 != ''){
						$string_to_pass_to_twilio .=" the value entered for the field, ".$row['label'].", is ".$custom5.".";
						//$Body.=$forms_data['label']." : ".$custom5."<br><br>";				
					}
					if($custom5 != '' && $row['inquiry_table_field'] == 'custom5'){	 	
						//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust5[1]));				
						$customBody.=$row['label']." : ".$custom5."<br><br>";				
					}
			}
        }	

        	$Body .= $customBody;
        
         
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
                        	
                                $mailBody = $customerHeader.$Body." Thanks <br><span style='color:black;'> ".$form_owner_data['companyname']."</span></div></div>";
                                $mail->setBodyHtml($mailBody);
                                $mail->setFrom(SITE_NO_REPLY_EMAIL, $form_owner_data['companyname']);//$form_owner_data['email']
                                $mail->addTo($email,$firstname.' '.$lastname);                                
                                $mail->setSubject('We have received your information');
                                $result=$mail->send();
                                
                               
                                
                        }
						
                            $mail = new Zend_Mail();
                            $Body.=" Thanks <br><span style='color:black;'> ".WEBSITE_NAME."</span></div></div>";
                            $mail->setBodyHtml($ownerHeader.$Body);
                            $mail->setFrom(SITE_SUPPORT_EMAIL, WEBSITE_NAME);                            
                            //$mail->setSubject('A customer has submitted a Form on '.WEBSITE_NAME);
                            $mail->setSubject('You have received a new inquiry through FormActivate');
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
					
				//$this->_redirect('/web/connecttotwilio/rsession/'.md5($time).'/id/'.$last_insert_id.'/form_id/'.$form_id.'/user_phone/'.$user_phone.'/notification_email/'.$notification_email);	exit;
				$this->connecttotwilioAction(md5($time),$last_insert_id,$form_id,$user_phone,$notification_email,$authorizationkey,$format);
				
			}
			
			
			$nowww = ereg_replace('http://','',$redirect_url);
			$redirect_url='http://'.ereg_replace('www\.','',$nowww);
			
			
			
			/*if($redirect_type==1)
			{
				
						
			   //REAL SENE $this->_redirect($redirect_url);  exit;
                            echo "<script type='text/javascript'>window.top.location.href='{$redirect_url}'</script>"; exit;
//                            $runtime_session = new Zend_Session_Namespace('Zend_Auth');
//                            $runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
//                            $this->_redirect('/forms/thanks');   exit;
			}*/
			
			if($redirect_type !=1)
			{
					
				
                            /*$runtime_session = new Zend_Session_Namespace('Zend_Auth');
                            $runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
                           $this->_redirect('/forms/thanks'); */  
							$authKey = $authorizationkey."-".$format;
                           $this->_redirect('/web/'.$this->inquaryId.'/?auth_key='.$authKey); 
                            exit;
			}
										 	
			//exit;
		 }else{
		 			 
		 	
		/* $login_error = new Zend_Session_Namespace('Zend_Auth');
		 $login_error->loginError="<font color='black'><b>The Form you are submitting is not exist.</b></font>";*/
				
		 //$this->_redirect('/customers/login'); 
		// echo "1088";//$this->_redirect($forms_data['redirect_url']);
		$errorAutoKey = "abc";
		 $this->_redirect('/web/'.$this->inquaryId.'/?auth_key='.$errorAutoKey);   
			exit;
		 }	
            
    }

    public function getAction()
    {
    	
    	$inquiryTable = new inquiry();
    	
	 	$id = trim($this->_getParam('id', false));
	 	$auth_key = trim($this->_getParam('auth_key', false));
	 	
	 	
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


	     if($data['redirect_type'] == 1)
		 	{
		 		$redirectUrl = trim($data['redirect_url']);
		 		
		 		 echo "<script type='text/javascript'>window.top.location.href='{$redirectUrl}'</script>";
		 		
		 		/*$test_array = array (
								    'status' => 'success',
								    'statusCode' => '200',
	    							'auth_key' => $auth_key,
								    'data' => array (
								        'call-time' => '1',
	    								'redirect_url' => $data['redirect_url']
								    ),
								);
								
					//header('Content-type: application/json');
					//json_encode($test_array );
								
						$xml = RestUtils::array_to_xml($test_array, new SimpleXMLElement('<response/>'))->asXML();
						RestUtils::sendResponse(200, $xml, 'application/xml');*/
		 		 
		 	}  
	 	else if(count($inquiry_data) == 1 && $data['redirect_type'] != 1 && ($authKey_format[1] == "json" || $authKey_format[1] == "xml"))
	 	{
	 		/*$call_responce = Array('AccountSid' => 'AC2dbe8a176e89ff7f6641d4d03c047bca','ToZip' => '92131','FromState' =>'','Called' => '+18588372428',
								    'FromCountry' => 'CI',
								    'CallerCountry' => 'CI',
								    'CalledZip' => 92131,
								    'Direction' => 'outbound-api',
								    'FromCity' => '',
								    'CalledCountry' => 'US',
								    'Duration' => 1,
								    'CallerState' =>'', 
								    'CallSid' => 'CA12f98ac99af742bf91f42b728cf34e85',
								    'CalledState' => 'CA',
								    'From' => +22553743,
								    'CallerZip' => '',
								    'FromZip' => '',
								    'CallStatus' => 'completed',
								    'ToCity' => 'SAN DIEGO',
								    'ToState' => 'CA',
								    'To' => +18588372428,
								    'CallDuration' => 18,
								    'ToCountry' => 'US',
								    'CallerCity' => '', 
								    'ApiVersion' => 2010-04-01,
								    'Caller' => +22553743,
								    'CalledCity' => 'SAN DIEGO'
								);*/
	 		
	 		
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

				if($authKey_format[1] == "xml")
				{	
					$xml = RestUtils::array_to_xml($test_array, new SimpleXMLElement('<response/>'))->asXML();
					RestUtils::sendResponse(200, $xml, 'application/xml');
				}
	 	}
	 	
	 	else
	 	{
	 		$test_array = array (
								    'status' => 'Page not found',
								    'statusCode' => '404',
	    							
								);
			$xml = RestUtils::array_to_xml($test_array, new SimpleXMLElement('<response/>'))->asXML();
			RestUtils::sendResponse(404, $xml, 'application/xml');
	 	}
	 	
	 	
    }
    
    public function postAction()
    {
        $this->getResponse()->appendBody("From postAction() creating the requested article");

    }
    
    public function putAction()
    {
        //$this->getResponse()->appendBody("From putAction() updating the requested article".$this->_request->getParam('customers').$_POST['firstname']);
        $test_array = array();
        
       		 $form_id = $this->_request->getParam('forms');
		 	$member_id = $this->_request->getParam('customers');
		 	
		 	$format = $_POST['format'];
		 	
		 	
		 /********* start validation of Required fields *******/	
		 	
   			 $isError = false;
		 	$requiredField = "";
		 	$co = 0;
		 	
		 	$form_std_fields = new FormsStd();
		 	$conditionStdData = $form_std_fields->select()->where('status="1" and customer_id='.$member_id.' and form_id='.$form_id)->order('form_std_field.id asc');
        	$stdData = $form_std_fields->fetchAll($conditionStdData);
        	
        	foreach($stdData as $row)
        	{
        		if(array_key_exists(trim($row['inquiry_table_field']), $_POST))
				{
				
					if($row['field_required']=='yes' && $_POST[$row['inquiry_table_field']] == '')
					{
						if($co == 0)
							$requiredField = $row['inquiry_table_field'];
						else
							$requiredField .= ",".$row['inquiry_table_field'];
							
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
        		
        		if($format == "xml")
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

			if(isset($_POST['phonenumber'])) 
			{
                
				 $number = trim(preg_replace('/[0-9]/', '#', $this->_request->getPost('phonenumber')));
	
	       		 if (!in_array($number, $formats))
				 {
					
				 	 $test_array = array (
								    'status' => 'Invalid Phone Number',
								    'statusCode' => '406',
								);

					 if($format == "xml")
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
				 }
			 
			}
			 /********* End validation of phone number *******/
			 
			/********* start validation of Email *******/ 
		 	
				 $validator = new Zend_Validate_EmailAddress();
				 
				 if(isset($_POST['email'])) 
				{
					if (!$validator->isValid($this->_request->getPost('email'))) 
					{
					    $test_array = array (
								    'status' => 'Invalid Email',
								    'statusCode' => '406',
								);
								
				 if($format == "xml")
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
				 $this->_redirect('/web/'.$this->inquaryId.'/?auth_key='.$errorAutoKey);   
					exit;
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
			
			//date_default_timezone_set($operation_hours_data['time_zone_code']);
			$customer_current_time=time();
                        $servertimezone=-18000;
			
                        $customer_current_time = $customer_current_time + ($time_zone_customer-$servertimezone);			
				
 	
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
			   $inquiry_form_std_custom_data=$this->_request->getPost($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:-If the inquiry is received AFTER a specific time , set the connection number to [phone number]
				   if($prefered_time_in_seconds <= $customer_current_time){
				   // SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=1;
					   $email_flag=1;
				   }else{
				   $inquiry_type='After hours';
				   }
			  }
			  
			  if($customerrules_data['rule_id']==2){
			   $inquiry_form_std_custom_data=$this->_request->getPost($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:- If the inquiry is received AFTER a specific time , do not place the call
				  if($prefered_time_in_seconds <= $customer_current_time){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;
					   $inquiry_type='After hours';
				   }
			  }
			  if($customerrules_data['rule_id']==3){
			   $inquiry_form_std_custom_data=$this->_request->getPost($form_std_fields_buzznes_datas['inquiry_table_field']);
			   //Rule:- If the inquiry is received BEFORE a specific time , set the connection number to [phone number]
				 if($prefered_time_in_seconds >= $customer_current_time){
				   // SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=1;
					   $email_flag=1;
				   }else{
				   $inquiry_type='After hours';
				   }
			  }
			  
			  if($customerrules_data['rule_id']==4){
			   $inquiry_form_std_custom_data=$this->_request->getPost($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:- If the inquiry is received BEFORE a specific time , do not place the call
				   if($prefered_time_in_seconds >= $customer_current_time){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;	
					   $inquiry_type='After hours';
				   }
			  }
			  
			  if($customerrules_data['rule_id']==5){
			   $inquiry_form_std_custom_data=$this->_request->getPost($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:- If the inquiry contains a data field field with a value that is empty, set the connection number to [phone number]
			   
				   if(trim($inquiry_form_std_custom_data)==''){
				   // SET THE PHONE NUMBER FOR CALLING
				   	 $phone_flag=1;
					 $email_flag=1;	
				   }
			  }
			  
			  if($customerrules_data['rule_id']==6){
			   $inquiry_form_std_custom_data=$this->_request->getPost($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:-If the inquiry contains a data field field with a value that is empty, do not place the call
				   if(trim($inquiry_form_std_custom_data)==''){
				   // DON'T SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=0;
					   $email_flag=1;	
				   }
			  }
			  
			  if($customerrules_data['rule_id']==7){
			   $inquiry_form_std_custom_data=$this->_request->getPost($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:-If the inquiry contains a data field field with a value that is NOT empty, set the connection number to [phone number]
				   if(trim($inquiry_form_std_custom_data)!=''){
				   // SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=1;
					   $email_flag=1;
				   }			  
			  }
			  
			  if($customerrules_data['rule_id']==8){
			   $inquiry_form_std_custom_data=$this->_request->getPost($form_std_fields_buzznes_datas['inquiry_table_field']);
				// Rule:-If the inquiry contains a data field field with a value that is NOT empty, do not place the call
			   if(trim($inquiry_form_std_custom_data)!=''){
				   // DON'T PLACE THE CALL 
   					   $phone_flag=0;
					   $email_flag=1;	
				   }
			  }
			  
			  if($customerrules_data['rule_id']==9){
			   $inquiry_form_std_custom_data=$this->_request->getPost($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:-If the inquiry contains a data field field with a value equal to [value], set the connection number to [phone number]
				   if(trim($inquiry_form_std_custom_data)==$customerrules_data['field_data_value']){
				   // SET THE PHONE NUMBER FOR CALLING
   					   $phone_flag=1;
					   $email_flag=1;	

				   }
			  }
			  
			   if($customerrules_data['rule_id']==10){
			   $inquiry_form_std_custom_data=$this->_request->getPost($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:-If the inquiry contains a data field field with a value equal to [value], do not place the call
				   if(trim($inquiry_form_std_custom_data)==$customerrules_data['field_data_value']){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;					   
				   }
			  }
			  
			   if($customerrules_data['rule_id']==11){
			   $inquiry_form_std_custom_data=$this->_request->getPost($form_std_fields_buzznes_datas['inquiry_table_field']);
			 // Rule:-If the inquiry contains a data field field with a value NOT equal to [value], set the connection number to [phone number]
			   if(trim($inquiry_form_std_custom_data)!=$customerrules_data['field_data_value']){
				   // SET THE CALL
					   $phone_flag=1;
					   $email_flag=1;					   
				   }
			  }
			  
			   if($customerrules_data['rule_id']==12){
			   $inquiry_form_std_custom_data=$this->_request->getPost($form_std_fields_buzznes_datas['inquiry_table_field']);
			   // Rule:-If the inquiry contains a data field field with a value NOT equal to [value], do not place the call
				   if(trim($inquiry_form_std_custom_data)!=$customerrules_data['field_data_value']){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;					   
				   }
			  }
		  }else{
		  	
		  // WHEN RULES IS NOT SET THEN WE WILL USE OPERATION DATA TIMES

		  /* NOTE THESE RULES ARE HARD CODED IN THE ADMIN PANEL, IT WILL BE MANAGED BY THE ADMIN*/
			  
                       if(($start_time <= $customer_current_time)&& ($customer_current_time <= $end_time))
                       {
                       // SET THE PHONE NUMBER FOR CALLING
                               $phone_flag=1;
                               $email_flag=1;
                               $phone=$forms_data['business_phone'];
                               if(empty ($phone))
                                   $phone=$forms_data['home_phone'];
                       }else{
                               $phone=$forms_data['home_phone'];
                               if(empty ($phone))
                                   $phone=$forms_data['business_phone'];
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
			     
      		
			
		 	$firstname= $this->_request->getPost('firstname');	
		 	$lastname= $this->_request->getPost('lastname');	
			
		 	  $stringData = "";
			
        $form_std_fields=new FormsStd();

        $condition = $form_std_fields->select()->where('status="1" and customer_id='.$member_id.' and form_id='.$form_id)->order('form_std_field.id asc');
        $this->fields = $form_std_fields->fetchAll($condition);
        
        $customBody = "";
        $check = "";
        $co = 0;
       	$isError = false;
       	$requiredField = "";
        foreach($this->fields as $row)
        {
        	
		 	$Body="";
		 	 $string_to_pass_to_twilio='';
		 			 	
		 	
		 	
			if(array_key_exists(trim($row['inquiry_table_field']), $_POST))
			{
				/********* start validation Requaired field *******/ 
				
				
				
				//$check .= $row['inquiry_table_field'];
				
				/*if($row['field_required']=='yes' && $_POST[$row['inquiry_table_field']] == '')
				{
					
					
					$requiredField .= $row['inquiry_table_field']." is empty,";
					
						
					$isError = true;
					break;
					header("Location: " . $_SERVER['HTTP_REFERER']);
				   			 return false;
				}*/
				
				/********* end validation Requaired field *******/ 
					
				
					
					$form_std_fields_for_anounce_name=new FormsStd();

			        $conditions = $form_std_fields_for_anounce_name->select()->where("status='1' and customer_id='$member_id' and form_id='$form_id' and inquiry_table_field in ('firstname','lastname') and field_announce = 'yes'");
			        $anounceNames = $form_std_fields_for_anounce_name->fetchAll($conditions);
			        
				
					if(count($anounceNames) == 2)
					{
						$string_to_pass_to_twilio .="Their Name is ".$firstname." ".$lastname;
					}
					else
					{
					 	/*if($this->_request->getPost('announce_1')=='1' && $this->_request->getPost('announce_2')=='1'){	
					 		$string_to_pass_to_twilio .="Name is ".$firstname." ".$lastname;
					 	}else */
					 	if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'firstname'){
					 		$string_to_pass_to_twilio .="Their Name is ".$firstname.".";
					 	}else if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'lastname'){	 
					 		$string_to_pass_to_twilio .="Their Name is ".$lastname.".";
					 	}
					}
				 	
						
					
				 	
		                        $customerHeader = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'><div style='color:black;'>Dear ".$firstname .",<br><br>";
				 	$customerHeader .="Thank you for your interest in '".$form_owner_data['companyname']."'.  We have received your information and will be in touch shortly. Please keep a copy of this email for your reference.<br><br>Sincerely, '".$form_owner_data['companyname']."'<br><br>";
		
		                        $ownerHeader = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'><img src='". WEBSITE_IMG_URL. "branding.png' /> <hr />You have received a new inquiry through FormActivate. Details of the inquiry are included below <br><br>";
				 	/**************** EMAIL START HERE ********************************/
				 	
					$email=$this->_request->getPost('email');
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'email'){	 	
						$string_to_pass_to_twilio .=" Their Email is ".$email.".";				
					}
					
					if($email!=''){	 					
						$Body.=" Email : ".$email."<br><br>";
					}			
					/**************** EMAIL END HERE ********************************/
					
					/**************** COMPANY START HERE ********************************/
					$company=$this->_request->getPost('company');
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'company' && $company != ''){	 	
							$string_to_pass_to_twilio .=" Their Company Name is ".$company.".";
					}
					if($company!=''){	 					
						$Body.=" Company : ".$company."<br><br>";
					}
					/**************** COMPANY END HERE ********************************/
					
					/**************** Street Address START HERE ***********************/
					$streetaddress=$this->_request->getPost('streetaddress');
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'streetaddress' && $streetaddress != ''){	
						$string_to_pass_to_twilio .=" Their Street Address is ".$streetaddress.".";
						
					}
					if($streetaddress!=''){	 					
						$Body.=" Street Address : ".$streetaddress."<br><br>";
					}
					/**************** Street Address END HERE ********************************/
					
					/**************** City START HERE ***********************/
					$city=$this->_request->getPost('city');
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'city' && $city != ''){
						$string_to_pass_to_twilio .=" Their City is ".$city.".";
						
					}
		
					if($city!=''){	 					
						$Body.=" City : ".$city."<br><br>";
					}
					/**************** City END HERE ****************/
					
					/**************** State START HERE ***********************/
					$state=$this->_request->getPost('state');
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'state' && $state != ''){
						$string_to_pass_to_twilio .=" Their State is ".$state.".";
					}
					
					if($state!=''){	 					
						$Body.=" State : ".$state."<br><br>";
					}
					/**************** Zip START HERE ***********************/
					$zip=$this->_request->getPost('zip');
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'zip' && $zip != ''){
							$string_to_pass_to_twilio .=" Their Zip is ".$zip.".";
					}
					
					if($zip!=''){	 					
						$Body.=" Zip : ".$zip."<br><br>";
					}
					/**************** Zip End HERE ***********************/
					
					/**************** Country START HERE ***********************/
					$country=$this->_request->getPost('country');
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'country' && $country != ''){
						$string_to_pass_to_twilio .=" Their Country is ".$country.".";
						
					}
					if($country!=''){	 					
						$Body.=" Country : ".$country."<br><br>";
					}
					/**************** Country End HERE ***********************/
					
					/**************** Phone number START HERE ***********************/
					$homephone=$this->_request->getPost('phonenumber');
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'phonenumber' && $homephone != ''){
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
					$custom1=$this->_request->getPost('custom1');			
					/*$ann_cust=explode('_',$this->_request->getPost('announce_custom1'));		
					if($ann_cust[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust[1]));		*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom1' && $custom1 != ''){
					$string_to_pass_to_twilio .=" the value entered for the field, ".$row['label'].", is ".$custom1.".";
					//$Body.=$forms_data['label']." : ".$custom1."<br><br>";
					}
					if($custom1 != '' && $row['inquiry_table_field'] == 'custom1'){	 	
					//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust[1]));					
					$customBody.=$row['label']." : ".$custom1."<br><br>";
					}
					/**************** Custom 1 End HERE ***********************/
					
					/**************** Custom 2 Start HERE ***********************/
					$custom2=$this->_request->getPost('custom2');
					/*$ann_cust2=explode('_',$this->_request->getPost('announce_custom2'));						
					if($ann_cust2[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust2[1]));	*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom2' && $custom2 != ''){
						$string_to_pass_to_twilio .=" the value entered for the field, ".$row['label'].", is ".$custom2.".";
						//$Body.=$forms_data['label']." : ".$custom2."<br><br>";
					}
					
					if($custom2 != '' && $row['inquiry_table_field'] == 'custom2'){	 	
					//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust2[1]));					
						$customBody.=$row['label']." : ".$custom2."<br><br>";
					}			
					/**************** Custom 2 End HERE ***********************/
					
					/**************** Custom 3 Start HERE ***********************/
					$custom3=$this->_request->getPost('custom3');			
					/*$ann_cust3=explode('_',$this->_request->getPost('announce_custom3'));						
					if($ann_cust3[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust3[1]));	*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom3' && $custom3 != ''){
						$string_to_pass_to_twilio .=" the value entered for the field, ".$row['label'].", is ".$custom3.".";
						//$Body.=$forms_data['label']." : ".$custom3."<br><br>";
					}
					if($custom3 != '' && $row['inquiry_table_field'] == 'custom3'){	 	
					//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust3[1]));					
						$customBody.=$row['label']." : ".$custom3."<br><br>";
					}
					
					/**************** Custom 3 End HERE ***********************/
					
					/**************** Custom 4 Start HERE ***********************/
					$custom4=$this->_request->getPost('custom4');
					/*$ann_cust4=explode('_',$this->_request->getPost('announce_custom4'));						
					if($ann_cust4[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust4[1]));	*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom4' && $custom4 != ''){
							$string_to_pass_to_twilio .=" the value entered for the field, ".$row['label'].", is ".$custom4.".";
							//$Body.=$forms_data['label']." : ".$custom4."<br><br>";
					}
					
					if($custom4 != '' && $row['inquiry_table_field'] == 'custom4'){	 	
						//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust4[1]));						
							$customBody.=$row['label']." : ".$custom4."<br><br>";
					}
					
					/**************** Custom 4 End HERE ***********************/
					
					/**************** Custom 5 Start HERE ***********************/
					
					$custom5=$this->_request->getPost('custom5');					
					/*$ann_cust5=explode('_',$this->_request->getPost('announce_custom5'));						
					if($ann_cust5[0]=='1'){	 	
					$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust5[1]));	*/
					if($row['field_announce']=='yes' && $row['inquiry_table_field'] == 'custom5' && $custom5 != ''){
						$string_to_pass_to_twilio .=" the value entered for the field, ".$row['label'].", is ".$custom5.".";
						//$Body.=$forms_data['label']." : ".$custom5."<br><br>";				
					}
					if($custom5 != '' && $row['inquiry_table_field'] == 'custom5'){	 	
						//$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust5[1]));				
						$customBody.=$row['label']." : ".$custom5."<br><br>";				
					}
			}
        }	
			
        	$Body .= $customBody;
        
         
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
                        	
                                $mailBody = $customerHeader.$Body." Thanks <br><span style='color:black;'> ".$form_owner_data['companyname']."</span></div></div>";
                                $mail->setBodyHtml($mailBody);
                                $mail->setFrom(SITE_NO_REPLY_EMAIL, $form_owner_data['companyname']);//$form_owner_data['email']
                                $mail->addTo($email,$firstname.' '.$lastname);                                
                                $mail->setSubject('We have received your information');
                                $result=$mail->send();
                                
                               
                                
                        }
                            


			
                            $mail = new Zend_Mail();
                            $Body.=" Thanks <br><span style='color:black;'> ".WEBSITE_NAME."</span></div></div>";
                            $mail->setBodyHtml($ownerHeader.$Body);
                            $mail->setFrom(SITE_SUPPORT_EMAIL, WEBSITE_NAME);                            
                            //$mail->setSubject('A customer has submitted a Form on '.WEBSITE_NAME);
                            $mail->setSubject('You have received a new inquiry through FormActivate');
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
					
				//$this->_redirect('/web/connecttotwilio/rsession/'.md5($time).'/id/'.$last_insert_id.'/form_id/'.$form_id.'/user_phone/'.$user_phone.'/notification_email/'.$notification_email);	exit;
				
				$this->connecttotwilioAction(md5($time),$last_insert_id,$form_id,$user_phone,$notification_email,$authorizationkey,$format);
				
			}
			
			
			$nowww = ereg_replace('http://','',$redirect_url);
			$redirect_url='http://'.ereg_replace('www\.','',$nowww);
			
			
			
			/*if($redirect_type==1)
			{
				
			   //REAL SENE $this->_redirect($redirect_url);  exit;
                            echo "<script type='text/javascript'>window.top.location.href='{$redirect_url}'</script>"; exit;
//                            $runtime_session = new Zend_Session_Namespace('Zend_Auth');
//                            $runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
//                            $this->_redirect('/forms/thanks');   exit;
			}*/
			
			/*if($redirect_type==1)
			{*/
					
				
                            //$runtime_session = new Zend_Session_Namespace('Zend_Auth');
                            //$runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
                            $this->_redirect('/web/'.$last_insert_id.'/?auth_key='.$authorizationkey."-".$format);   exit;
                           // $this->processResponse();exit;
			//}
										 	
			//exit;
		 }else{
		 			 
		 /*$login_error = new Zend_Session_Namespace('Zend_Auth');
		 $login_error->loginError="<font color='black'><b>The Form you are submitting is not exist.</b></font>";
		 $this->_redirect('/customers/login'); */
		// echo "1088";//$this->_redirect($forms_data['redirect_url']);  
			//exit;
			$errorAutoKey = "abc";
		 $this->_redirect('/web/'.$this->inquaryId.'/?auth_key='.$errorAutoKey);   
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
    	 
    	 /*$rsession=$this->_request->getParam('rsession');	     
    	 $user_phone=$this->_request->getParam('user_phone');
         $notification_email = $this->_request->getParam('notification_email');*/
         
         $rsession= $ression;	     
    	 $user_phone= $user_phone;
         $notification_email = $notification_email;
         $authKey = $authorizationkey."-".$format;
    	 $this->_helper->layout->setLayout('layout_twilio');
    	 
    	// $id=$this->_request->getParam('id');
    	 //$form_id=$this->_request->getParam('form_id');	

    	 $id=$last_insert_id;
    	 $this->inquaryId = $id;
    	 $form_id=$form_id;	
    	 
    	 $runtime_session= new Zend_Session_Namespace('Zend_Auth');	    

    	 
         if($runtime_session->runtime_session==$rsession)
         {
         		
                $inquiryTable = new inquiry();
                $inquiryTableData=$inquiryTable->fetchRow($inquiryTable->select()->where('id='.$id));

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

                $CallerID = CALLER_ID;
                $to       = $client_number;
                
                
    		$client = new TwilioRestClient($AccountSid, $AuthToken);
                //validate user phone number

    		
		if(!empty($inquiryTableData['announced_data']) && $remaining_call > 0 ) // if number of call did not exceed 
		{
			$data=$inquiryTableData['announced_data'];
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
					
					$this->_redirect('/web/'.$this->inquaryId.'/?auth_key='.$authKey);   exit;
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
                        $mail->setSubject('Your FormActivate plan has run out of minutes.');
                        

                        $mail2->setBodyHtml($plan_mail_body);
                        $mail2->setFrom(SITE_SUPPORT_EMAIL, WEBSITE_NAME);
                        $mail2->addTo($plan_email, 'Admin');
                        $mail2->setSubject('Your FormActivate plan has run out of minutes.');
                        
                        
                        if($plan_id == 2 && $total_remaining_calls == 3)
                        {
                            $result=$mail->send(); $result2=$mail2->send();
                        }
                        else if($plan_id == 3 && $total_remaining_calls == 10)
                        {
                            $result=$mail->send(); $result2=$mail2->send();
                        }
                        else if($plan_id == 4 && $total_remaining_calls == 20)
                        {
                            $result=$mail->send(); $result2=$mail2->send();
                        }
                         /* END FOR PLAN RUN OUT EMAIL SENDING */

                        
                       // $runtime_session = new Zend_Session_Namespace('Zend_Auth');
                       // $runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
                    // save in DB this returns a unique id
                    /*if($redirect_type==1){
                    	
                    	
                            //$this->_redirect($redirect_url);  exit;
                                  echo "<script type='text/javascript'>window.top.location.href='{$redirect_url}'</script>"; exit;
//                                $runtime_session = new Zend_Session_Namespace('Zend_Auth');
//                                $runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
//                                $this->_redirect('/forms/thanks');   exit;
                            }*/
                          // if($redirect_type != 1)
                           //{
                            	
                            	
                               // $runtime_session = new Zend_Session_Namespace('Zend_Auth');
                            // $runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
                            $this->_redirect('/web/'.$this->inquaryId.'/?auth_key='.$authKey);   exit;
                               //$this->processResponse();exit;
                            	
                            	
                              /* $test_array = array (
							    'status' => 'success',
							    'statusCode' => '201',
							    'data' => array (
							        'call-time' => '1',
                                'announcedData' => 'hasan',
							    ),
							);*/
							
							//header('Content-type: application/json');
							 //json_encode($test_array );
							
							//RestUtils::array_to_xml($test_array, new SimpleXMLElement('<response/>'))->asXML();
							// RestUtils::sendResponse(200, json_encode($test_array ), 'application/json');
                            
							 
							 
                            //}
	    	
                }
	   
            }else{
            	
            				

			/* $test_array = array (
								    'status' => 'Your total remaining calls is finished ',
								    'statusCode' => '406',
								);
						$xml = RestUtils::array_to_xml($test_array, new SimpleXMLElement('<response/>'))->asXML();
						RestUtils::sendResponse(406, $xml, 'application/xml');
			*/
			 
			

			$this->_redirect('/customers/login'); 
		 }
        }
    }
    
}
?>
