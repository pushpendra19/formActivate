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

class AdminformsController extends BaseController
{
	
   /**
     * init() -Method for zend initialization
     *
     * @access public
	 * @return void
     */	
    public function init()
    {
    	$auth=Zend_Auth::getInstance();	
    	$session = new Zend_Session_Namespace('Zend_Auth'); 
    	//allowed actions without login
		//$this->_redirect(WEBSITE_URL.'admin/index');	
		
		$string=$_SERVER['REQUEST_URI'];
		
		
		$authorizationkey=$this->getRequest()->getPost('authorizationkey');
		$form_id=$this->getRequest()->getPost('fid');
		$customer_id= $this->getRequest()->getParam('cid');
		
		$runtime_session= new Zend_Session_Namespace('Zend_Auth');	
		    	 	 		 
		if(($authorizationkey!='' && $form_id!='') || $runtime_session->runtime_session!=''){
			
		}else{
			$find='inquiry';
			$pos=strpos($string,$find);		
			if($pos===false){				 
			 if(empty($session->member_id)) {$this->_redirect('/customers/login'); 		 }
			}
		}
		
		if(!empty($customer_id) || ($customer_id>0)){}else{				 
			 $this->_redirect('/customers/logout'); exit;		
		  }
		  
		$membersTable= new members();  
		$members_data=$membersTable->fetchRow($membersTable->select()->where('id='.$customer_id));	
		$this->view->firstname =  $members_data['firstname'];
		$this->view->lastname =  $members_data['lastname'];
		//echo "49"; exit;
    }
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
		
		if(($this->getRequest()->getParam('cid')!=1) &&  ($session->user_type==1)){
				$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}
		
		if((($this->getRequest()->getParam('cid')!=$session->member_id) || ($this->getRequest()->getPost('cid')!=$session->member_id)) &&  ($session->user_type==1)){
			$customer_id = $this->getRequest()->getParam('cid');
		}else if($session->member_id>0){
			$customer_id= $this->getRequest()->getParam('cid');
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
		
		$this->view->cid= $customer_id;	
		 
		
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
			$forms_data=$formTable->fetchAll($formTable->select()->where('customer_id="'.$customer_id.'"')->order($order_by));
		
			$session_form_sorting = new Zend_Session_Namespace('Zend_Auth'); 
			
			if($this->_getParam('page')!=''){
			$page_number=$this->_getParam('page');
			}elseif($session_form_sorting->page!=''){
			$page_number=$session_form_sorting->page;		
			}else{
			$page_number=1;
			}			
			$this->view->page_number = $page_number;
			
		$this->view->total_forms =count($forms_data);
		
		$total_active_forms=$formTable->fetchAll($formTable->select()->where('status=1 and customer_id="'.$customer_id.'"'));				
		
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
		//$this->view->forms_data=$forms_data;
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
    	 
		if(($this->getRequest()->getParam('cid')!=1) &&  ($session->user_type==1)){
				$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}
    	
		if((($this->getRequest()->getParam('cid')!=$session->member_id) || ($this->getRequest()->getPost('cid')!=$session->member_id)) &&  ($session->user_type==1)){
			$customer_id = $this->getRequest()->getParam('cid');
		}else if($session->member_id>0){
			$customer_id= $this->getRequest()->getParam('cid');
		}else{
		$this->_redirect('/customers/login'); 
		}
			
		$form_id=$this->getRequest()->getParam('form_id');		
		$this->view->cid = $this->getRequest()->getParam('cid');

                if(empty ($form_id))
                {
                    $members = new members();
                    $member_data = $members->fetchRow($members->select()->where('id='.$customer_id));
                    $plan_id = $member_data['plan_id'];

                    $totalForms = $this->getTotalCustomerFormsByCustomerID($customer_id);
                     if($plan_id == 5 && $totalForms >= 1)
                     {
                        $session->deleteError="<font color='black'><b>You have already created ".$totalForms." Form!</b></font>";
                        $this->_redirect('/adminforms/index/cid/'.$customer_id);
                     }

                     if($plan_id == 2 && $totalForms >= 1)
                     {
                        $session->deleteError="<font color='black'><b>You have already created ".$totalForms." Form!</b></font>";
                        $this->_redirect('/adminforms/index/cid/'.$customer_id);
                     }

                     if($plan_id == 3 && $totalForms >= 3)
                     {
                        $session->deleteError="<font color='black'><b>You have already created ".$totalForms." Forms!</b></font>";
                        $this->_redirect('/adminforms/index/cid/'.$customer_id);
                     }

                     if($plan_id == 4 && $totalForms >= 5)
                     {
                        $session->deleteError="<font color='black'><b>You have already created ".$totalForms." Forms!</b></font>";
                        $this->_redirect('/adminforms/index/cid/'.$customer_id);
                     }

                     if($plan_id == 6 && $totalForms >= 10)
                     {
                        $session->deleteError="<font color='black'><b>You have already created ".$totalForms." Forms!</b></font>";
                        $this->_redirect('/forms');
                     }

                    $authorizationkey = sha1(md5('a1b2c3d4e5fg6h7i8jklmnop9qrs0tuvwxz'.time()));
			// END SIX DIGIT ALPHANUMERIC AUTHORIZATION KEY

                    $form_data['authorizationkey']=$authorizationkey;
                    $form_data['customer_id']=$customer_id;
                    $session = new Zend_Session_Namespace('Zend_Auth');
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
			 	
			
			$form_id = $filter->filter($this->_request->getPost('form_id'));
			$formTable = new Forms();
			$form_data = array();
			//$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	

			
			$form_data['form_name']=$formname;
			$form_data['status']=$status;
			$form_data['business_phone'] = $business_phone_status == 1 ? trim($business_phone) : '';
			$form_data['home_phone']= $home_phone_status == 1 ? trim($home_phone) : '';
			$form_data['caller_id']= ($business_phone_status == 1 || $home_phone_status == 1) ? $caller_id : '';

                        $form_data['business_phone_validated'] = $business_phone_status;
                        $form_data['home_phone_validated']     = $home_phone_status;
			
			if($form_id!='')
			{
				 $formTable->update($form_data,'id='.$form_id);					
				 $this->_redirect('adminforms/stdfield/form_id/'.$form_id.'/cid/'.$customer_id);
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
			
				
			    $this->_redirect('adminforms/stdfield/form_id/'.$last_insert_id.'/cid/'.$customer_id);	
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
        //error_reporting(E_ALL);
 		
    	$session = new Zend_Session_Namespace('Zend_Auth');
    	$this->view->loggedin_customer_id=$session->member_id;
    	$this->view->form_id=$this->getRequest()->getParam('form_id');
    	$this->view->preview_hidden_value=$this->getRequest()->getParam('pre');
    	
		$form_id=$this->getRequest()->getParam('form_id');

                if(!empty ($form_id))
                {
                    $this->formCompletionStatus($form_id);
                }

		$this->view->actionName = 'forms';		
		
		if(($this->getRequest()->getParam('cid')!=1) &&  ($session->user_type==1)){
				$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}
		//exit;		
		
		if((($this->getRequest()->getParam('cid')!=$session->member_id) || ($this->getRequest()->getPost('cid')!=$session->member_id)) &&  ($session->user_type==1)){
			$customer_id = $this->getRequest()->getParam('cid');
		}else if($session->member_id>0){
			$customer_id= $this->getRequest()->getParam('cid');
		}else{
		//$this->_redirect('/customers/login'); 
		}
		
		if(empty($form_id))
		{
			$this->_redirect('/adminforms/overview/cid/'.$customer_id); 
		}
	
		$this->view->cid=$this->getRequest()->getParam('cid'); 
    	
    	$form_std_fields=new FormsStd();
 		$condition = $form_std_fields->select()->where('customer_id=0 and field_status="standard" and status="1"')->order('form_std_field.id asc');		
 		$this->view->fields = $form_std_fields->fetchAll($condition);   
 						
		// chk for saved standard fields data filled by user
		
		if(!empty($form_id))
		{
			$customer_id = $customer_id;
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
				$form_fields['customer_id']=$customer_id;
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
			$this->_redirect('adminforms/customfield/form_id/'.$form_id.'/cid/'.$customer_id.'/pre/2/');
			}else{
				
					if($this->_request->getPost('form_action')=='Previous'){
						
						$this->_redirect('adminforms/overview/form_id/'.$form_id.'/cid/'.$customer_id.'/pre/1/');
					}					
					if($this->_request->getPost('form_action')=='Next'){
					
				$this->_redirect('adminforms/customfield/form_id/'.$form_id.'/cid/'.$customer_id);
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
		$form_std_fields=new FormsStd();		
		$this->view->form_id=$this->getRequest()->getParam('form_id');
		$form_id=$this->getRequest()->getParam('form_id');

                if(!empty ($form_id))
                {
                    $this->formCompletionStatus($form_id);
                }

		$this->view->actionName = 'forms';
		$this->view->preview_hidden_value=$this->getRequest()->getParam('pre');
		//exit;	
		if(($this->getRequest()->getParam('cid')!=1) &&  ($session->user_type==1)){
				$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}
		
		if((($this->getRequest()->getParam('cid')!=$session->member_id) || ($this->getRequest()->getPost('cid')!=$session->member_id)) &&  ($session->user_type==1)){
			$customer_id = $this->getRequest()->getParam('cid');
		}else if($session->member_id>0){
			$customer_id= $this->getRequest()->getParam('cid');
		}else{
		$this->_redirect('/customers/login'); 
		}
		if(empty($form_id))
		{
			$this->_redirect('/adminforms/overview/cid/'.$customer_id); 
		}
		
		$this->view->cid=$this->getRequest()->getParam('cid');
		// view previously saved data
		if(!empty($form_id))
		{
			$condition_customer = $form_std_fields->select()->where('customer_id='.$customer_id.' and form_id='.$form_id.' and field_status="custom" and status="1"')->order('form_std_field.id asc');			
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
					$custom_data['customer_id']=$customer_id;
					$custom_data['field_type']=$val;
					$custom_data['label']=$val;
					$custom_data['data_field']=str_replace(' ','',$val);
					$custom_data['inquiry_table_field']='custom'.$i;
					$custom_data['field_required']=$field_required_arr[$key];
					$custom_data['field_validate']=$field_valdidate_arr[$key];
					$custom_data['validate_class']=$field_valdidate_arr[$key];
					$custom_data['field_announce']=$field_announce_arr[$key];	
					$custom_data['field_status']='custom';
					$custom_data['status']='1';
					$form_std_fields->insert($custom_data);		
					$i++;
				}
			}
			
			//form_id/'.$form_id.'/cid/'.$customer_id
			
			if($this->_request->getPost('preview_hidden_value')==1){
			//$this->view->preview_hidden_value=2; // Two will indicate that value saved and show preview.
			$this->_redirect('adminforms/opthrs/form_id/'.$form_id.'/cid/'.$customer_id.'/pre/2/');
			}else{
				
				
				if($this->_request->getPost('form_action')=='Previous'){
					$this->_redirect('adminforms/stdfield/form_id/'.$form_id.'/cid/'.$customer_id);
				}
				if($this->_request->getPost('form_action')=='Next'){
					$this->_redirect('adminforms/opthrs/form_id/'.$form_id.'/cid/'.$customer_id);	
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
		$this->view->form_id=$this->getRequest()->getParam('form_id');		
		$form_id=$this->getRequest()->getParam('form_id');

                if(!empty ($form_id))
                {
                    $this->formCompletionStatus($form_id);
                }

		$this->view->actionName = 'forms';
		$timetable = new timetable();
		
		if(($this->getRequest()->getParam('cid')!=1) &&  ($session->user_type==1)){
				$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}
		
		if((($this->getRequest()->getParam('cid')!=$session->member_id) || ($this->getRequest()->getPost('cid')!=$session->member_id)) &&  ($session->user_type==1)){
			$customer_id = $this->getRequest()->getParam('cid');
		}else if($session->member_id>0){
			$customer_id= $this->getRequest()->getParam('cid');
		}else{
		$this->_redirect('/customers/login'); 
		}
		$this->view->cid=$this->getRequest()->getParam('cid');		
		//exit;
		if(empty($form_id))
		{
			$this->_redirect('/adminforms/overview/cid/'.$customer_id); 
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
		
		if((($this->getRequest()->getParam('id')!=$customer_id) || ($this->getRequest()->getPost('id')!=$customer_id)) &&  ($session->user_type==1)){
			$id = $this->getRequest()->getParam('id');
		}else if($customer_id>0){
			$id = $customer_id;
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
					$this->_redirect('adminforms/customfield/form_id/'.$form_id.'/cid/'.$customer_id);
				}
			if($this->_request->getPost('form_action')=='Next'){
					$this->_redirect('adminforms/businessrule/form_id/'.$form_id.'/cid/'.$customer_id);	
				}
			
			
		}
    }
    
    
    // Business Rules listing
    public function businessruleAction()
    {
    	$session = new Zend_Session_Namespace('Zend_Auth'); 
    	$this->view->loggedin_customer_id=$session->member_id;
		$this->view->form_id=$this->getRequest()->getParam('form_id');
		$this->view->actionName = 'forms';
		$this->view->cid=$this->getRequest()->getParam('cid');		
		if(empty($session->member_id))
		{
			$this->_redirect('/customers/login'); 
		}
		
		if(($this->getRequest()->getParam('cid')!=1) &&  ($session->user_type==1)){
				$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}
		
		$form_id=$this->getRequest()->getParam('form_id');

                if(!empty ($form_id))
                {
                    $this->formCompletionStatus($form_id);
                }
    	
		
		if((($this->getRequest()->getParam('cid')!=$session->member_id) || ($this->getRequest()->getPost('cid')!=$session->member_id)) &&  ($session->user_type==1)){
			$customer_id = $this->getRequest()->getParam('cid');
		}else if($session->member_id>0){
			$customer_id= $this->getRequest()->getParam('cid');
		}else{
		$this->_redirect('/customers/login'); 
		}	
		
		if(empty($form_id))
		{
			$this->_redirect('/adminforms/overview/cid/'.$customer_id); 
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
        	$form_id=$this->getRequest()->getParam('id');
                $cid  = $this->getRequest()->getParam('cid');
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
        	$formreports->delete("form_id=".$this->getRequest()->getParam('id'));
        	$formsStds->delete("form_id=".$this->getRequest()->getParam('id'));
        	$inquirys->delete("form_id=".$this->getRequest()->getParam('id'));
        	$opthrss->delete("form_id=".$this->getRequest()->getParam('id'));
		
			$session = new Zend_Session_Namespace('Zend_Auth');
			$session->deleteError="<font color='black'><b>Your form, ".$form_data['form_name'].",  has been deleted successfully!</b></font>";
			$this->_redirect('/adminforms/index/cid/'.$cid);
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
			$this->_redirect('/adminforms/index/page/'.$page);
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

			$this->_redirect('/adminforms/businessrule/form_id/'.$form_id.'/cid/'.$customer_id);
			exit;
		}
	}
	
	// delete customer rule
	   // Delete customer.
	public function deleteruleAction()
	{
            $rule_id = $this->getRequest()->getParam('id');
            $form_id=$this->getRequest()->getParam('form_id');
            $customer_id = $this->getRequest()->getParam('cid');
		
		if($rule_id != '' && $form_id != '' && $customer_id != '')
                {
                    $rules = new customerrules();
                    
                    $rule_data=$rules->fetchRow($rules->select()->where('id='.$this->getRequest()->getParam('id')));
                    $rules->delete("id=".$this->getRequest()->getParam('id'));
                    $deleteError = new Zend_Session_Namespace('Zend_Auth');

                    $deleteError->deleteError="<font color='black'><b>Your rule, ".$rule_data['rule_name'].", has been deleted successfully!</b></font>";
                    $this->_redirect('/adminforms/businessrule/form_id/'.$form_id.'/cid/'.$customer_id);
                    exit;
		}
	}
	
	// create new rule
	public function newbusinessruleAction()
	{
		$session = new Zend_Session_Namespace('Zend_Auth');
    	$this->view->loggedin_customer_id=$session->member_id;		
		
		$form_id=$this->getRequest()->getParam('form_id');
		$this->view->form_id =$form_id;
		$rule_data=array();
		$admin_rule=new Businessrule();
		$customer_rule=new customerrules();
		$timetable = new timetable();
		$form_std_fields = new FormsStd();	
		$this->view->actionName = 'forms';
		
		if(($this->getRequest()->getParam('cid')!=1) &&  ($session->user_type==1)){
				$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}
		
				
		if((($this->getRequest()->getParam('cid')!=$session->member_id) || ($this->getRequest()->getPost('cid')!=$session->member_id)) &&  ($session->user_type==1)){
			$customer_id = $this->getRequest()->getParam('cid');
		}else if($session->member_id>0){
			$customer_id= $this->getRequest()->getParam('cid');
		}else{
		$this->_redirect('/customers/login'); 
		}	
		$this->view->cid=$this->getRequest()->getParam('cid');
				
		if(($this->getRequest()->getParam('cid')!=1) &&  ($session->user_type==1)){
				$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}
		if(empty($form_id))
		{
			$this->_redirect('/adminforms/overview/cid/'.$customer_id); 
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
		$member_data = $members->fetchRow($members->select()->where('id='.$customer_id));
		
		$subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id='.$member_data['plan_id']));

                $totalCustomerRules = $this->getTotalCustomerRulesByCustomerID($customer_id);
                $plan_id = $member_data['plan_id'];
                 if($plan_id == 5 && $totalCustomerRules >= 1)
                 {
                    $session->deleteError="<font color='black'><b>You have already created ".$totalCustomerRules." rules!</b></font>";
                    $this->_redirect('/adminforms/businessrule/form_id/'.$form_id.'/cid/'.$customer_id);
                 }

                 if($plan_id == 2 && $totalCustomerRules >= 1)
                 {
                    $session->deleteError="<font color='black'><b>You have already created ".$totalCustomerRules." rules!</b></font>";
                    $this->_redirect('/adminforms/businessrule/form_id/'.$form_id.'/cid/'.$customer_id);
                 }

                 if($plan_id == 3 && $totalCustomerRules >= 3)
                 {
                    $session->deleteError="<font color='black'><b>You have already created ".$totalCustomerRules." rules!</b></font>";
                    $this->_redirect('/adminforms/businessrule/form_id/'.$form_id.'/cid/'.$customer_id);
                 }

                 if($plan_id == 4 && $totalCustomerRules >= 10)
                 {
                    $session->deleteError="<font color='black'><b>You have already created ".$totalCustomerRules." rules!</b></font>";
                    $this->_redirect('/adminforms/businessrule/form_id/'.$form_id.'/cid/'.$customer_id);
                 }

		/*
		$customerrules_data = $customerrules->fetchAll($customerrules->select()->where('customer_id='.$customer_id.' and form_id ='.$form_id));
		if($form_id!='' && $id!=''){}else{			
		if(($subscriptions_data['available_rule']>count($customerrules_data)) || ($subscriptions_data['available_rule']=='unlimited')){
		
		}else{
		$deleteError = new Zend_Session_Namespace('Zend_Auth');
		$deleteError->deleteError="<font color='black'><b>You have already created ".count($customerrules_data)." rules!</b></font>";
		$this->_redirect('/adminforms/businessrule/form_id/'.$form_id.'/cid/'.$customer_id); 
		}
		} */
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
			$this->_redirect('adminforms/businessrule/form_id/'.$form_id.'/cid/'.$customer_id);
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
    	     $form_id=$this->getRequest()->getParam('form_id');

            if(!empty ($form_id))
            {
                $this->formCompletionStatus($form_id);
            }
    	     
    	    if(($this->getRequest()->getParam('cid')!=1) &&  ($session->user_type==1)){
				$this->view->admin_left_tab = 'admin_left_tab_for_customer';
			}
    	     
    	     $this->view->form_id=$form_id;
    	     
    	      		 
    		 if((($this->getRequest()->getParam('cid')!=$session->member_id) || ($this->getRequest()->getPost('cid')!=$session->member_id)) &&  ($session->user_type==1)){
			$customer_id = $this->getRequest()->getParam('cid');
		}else if($session->member_id>0){
			$customer_id= $this->getRequest()->getParam('cid');
		}else{
		$this->_redirect('/customers/login'); 
		}
		
		if(empty($form_id))
		{
			$this->_redirect('/adminforms/overview/cid/'.$customer_id); 
		}
		$this->view->cid=$this->getRequest()->getParam('cid');
    	     	
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
							$this->_redirect('adminforms/businessrule/form_id/'.$form_id.'/cid/'.$customer_id);
						}
						if($this->_request->getPost('form_action')=='Next'){
							$this->_redirect('adminforms/formredirect/form_id/'.$form_id.'/cid/'.$customer_id);	
						}
				// $this->_redirect('adminforms/formredirect/form_id/'.$form_id.'/cid/'.$customer_id);
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
							$this->_redirect('adminforms/businessrule/form_id/'.$form_id.'/cid/'.$customer_id);
						}
						if($this->_request->getPost('form_action')=='Next'){
							$this->_redirect('adminforms/formredirect/form_id/'.$form_id.'/cid/'.$customer_id);	
						}
					// $this->_redirect('adminforms/formredirect/form_id/'.$form_id.'/cid/'.$customer_id);						
					}
					else{			
					$session = new Zend_Session_Namespace('Zend_Auth'); 
				    $last_insert_id=$emailnotifications->insert($notification_data);
				    	if($this->_request->getPost('form_action')=='Previous'){
							$this->_redirect('adminforms/businessrule/form_id/'.$form_id.'/cid/'.$customer_id);
						}
						if($this->_request->getPost('form_action')=='Next'){
							$this->_redirect('adminforms/formredirect/form_id/'.$form_id.'/cid/'.$customer_id);	
						}
				    
				    
				    
				   //	$this->_redirect('adminforms/formredirect/form_id/'.$form_id.'/cid/'.$customer_id);	
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
    	$form_id=$this->getRequest()->getParam('form_id');   	
       	
    	$session = new Zend_Session_Namespace('Zend_Auth');
    	$customer_id=$this->getRequest()->getParam('cid');


        if(!empty ($form_id))
        {
            $this->formCompletionStatus($form_id);
        }

    	if(($this->getRequest()->getParam('cid')!=1) &&  ($session->user_type==1)){
			$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}
    	
		if(empty($form_id))
		{
			$this->_redirect('/adminforms/overview/cid/'.$customer_id); 
		}
		
		$this->view->cid=$this->getRequest()->getParam('cid');
		
    	$this->view->actionName = 'forms';
    	
    	if($this->getRequest()->getParam('form_id'))
    	{
    		$form_id = $this->getRequest()->getParam('form_id'); 
    		$this->view->form_id=$form_id;
    		$session = new Zend_Session_Namespace('Zend_Auth');
    		$customer_id=$customer_id;
    		$forms=new Forms();
    		$forms_data=$forms->fetchRow($forms->select()->where("id=".$form_id));
    		$this->view->url_type=$forms_data['redirect_type'];
    		$this->view->url=$forms_data['redirect_url'];
    		if($this->_request->isPost())
			{
				$filter=new Zend_Filter_StripTags();
				$redirect_type=$filter->filter($this->_request->getPost('redirect_type'));
				$redirect_url=$filter->filter($this->_request->getPost('url'));
				$data['redirect_type']=$redirect_type;
				$data['redirect_url']=$redirect_url;
				if($form_id)
				{
					 $forms->update($data,'id='.$form_id);
				}
				
				if($this->_request->getPost('form_action')=='Previous'){
					$this->_redirect('adminforms/emailnotification/form_id/'.$form_id.'/cid/'.$customer_id);
				}
				if($this->_request->getPost('form_action')=='Next'){
					$this->_redirect('adminforms/reporting/form_id/'.$form_id.'/cid/'.$customer_id);	
				}
				
				//$this->_redirect('adminforms/reporting/form_id/'.$form_id.'/cid/'.$customer_id);
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
    	//echo $session->member_id; 
		
		if($session->member_id>0 && $session->user_type==1){
			$id = $session->member_id;
		}else if($session->member_id>0 && $session->user_type==0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}
		
		$customer_id = $this->getRequest()->getParam('cid');
		
		
		
    	if(($this->getRequest()->getParam('cid')!=1) &&  ($session->user_type==1)){
			$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}	
		$this->view->cid=$this->getRequest()->getParam('cid');
				
		$form_id=$this->getRequest()->getParam('form_id');

                if(!empty ($form_id))
                {
                    $this->formCompletionStatus($form_id);
                }

                if($session->overview_done == false)
                {
                    $this->_redirect('adminforms/overview/form_id/'.$form_id.'/cid/'.$customer_id);
                }		
		
		$this->view->form_id=$form_id;
		if($form_id!='')
		{
			
		$form_std_fields = new FormsStd();	
		
		if($customer_id>0 && $session->user_type==1){
			$id = $customer_id;
			$form_std_fields_datas=$form_std_fields->fetchAll($form_std_fields->select()->where('form_id='.$form_id));	
			// This is for Admin Section 
		}else if($customer_id>0 && $session->user_type==0){
			$id = $customer_id;
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
				$form_report_data['customer_id']=$customer_id; 				
			    $last_insert_id=$form_report_table->insert($form_report_data);
			    $session->form_session_id =  $last_insert_id;
				$deleteError = new Zend_Session_Namespace('Zend_Auth');
				$deleteError->deleteError="<font color='black'><b>Your report has been saved successfully!</b></font>";					
			   // $this->_redirect('adminforms/stdfield/form_id/'.$last_insert_id);	
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
					$this->_redirect('adminforms/formredirect/form_id/'.$form_id.'/cid/'.$customer_id);
				}
				if($this->_request->getPost('form_action')=='Next'){
					$this->_redirect('adminforms/preview/form_id/'.$form_id.'/cid/'.$customer_id);
				}
		 		
			}else{
			 $this->_redirect('adminforms/stdfield/form_id/'.$form_id.'/cid/'.$customer_id);	
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
		$cid=$this->getRequest()->getParam('cid');
		$this->view->actionName = 'forms';
		//exit;

                if(!empty ($form_id))
                {
                    $this->formCompletionStatus($form_id);
                }

		if(empty($form_id))
		{
			$this->_redirect('/adminforms/overview/cid/'.$cid); 
		}
		
		if($session->member_id>0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}
		
		$customer_id = $this->getRequest()->getParam('cid');    	 
		
		if(($this->getRequest()->getParam('cid')!=1) &&  ($session->user_type==1)){
				$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}
		$this->view->cid=$this->getRequest()->getParam('cid');	
    	
    	$form_std_fields=new FormsStd();
 		$condition = $form_std_fields->select()->where('status="1" and customer_id='.$customer_id.' and form_id='.$form_id)->order('form_std_field.id asc');		
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
		
		$cid=$this->getRequest()->getParam('cid');
		
		//exit;
		if(empty($form_id))
		{
			$this->_redirect('/adminforms/overview/cid/'.$cid); 
		}
		
		if($session->member_id>0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
		
		
		$formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
		    	
    	$form_std_fields=new FormsStd();
 		$condition = $form_std_fields->select()->where('status="1" and customer_id='.$customer_id.' and form_id='.$form_id)->order('form_std_field.id asc');		
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
	
		if($session->member_id>0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
		
		$customer_id=$this->getRequest()->getParam('cid');	
		
		if(empty($form_id))
		{
			$this->_redirect('/adminforms/overview/cid/'.$customer_id); 
		}	
		
		$formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
		    	
    	$form_std_fields=new FormsStd();
 		/*$condition = $form_std_fields->select()->where('status="1" and customer_id='.$customer_id.' and form_id='.$form_id. ' and field_status="custom"')->order('form_std_field.id asc');		*/
 		$condition = $form_std_fields->select()->where(' customer_id='.$customer_id.' and form_id='.$form_id)->order('form_std_field.id asc');		
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
                            	 <input name="<?php echo stripslashes($data_val['data_field']); ?>" id="<?php echo stripslashes($data_val['data_field']); ?>" <?php if(($data_val['field_required']=='yes') && $data_val['field_validate']!='none'){?> class="validate[required,custom[<?php echo $data_val['validate_class'];?>]]" <?php } else if($data_val['field_required']=='yes'){ ?> class="validate[required],custom[<?php echo $data_val['validate_class'];?>]" <? }?> value="" maxlength="50"/>                                </label>                     
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
			$this->_redirect('/adminforms/overview'); 
		}
		
		if($session->member_id>0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
		$customer_id=$this->getRequest()->getParam('cid');	
		
		$formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
		    	
    	$form_std_fields=new FormsStd();
 		/*$condition = $form_std_fields->select()->where('status="1" and customer_id='.$customer_id.' and form_id='.$form_id.' and field_status="standard"')->order('form_std_field.id asc');*/		
 		$condition = $form_std_fields->select()->where('customer_id='.$customer_id.' and form_id='.$form_id)->order('form_std_field.id asc');
 		
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
                            	 <input name="<?php echo stripslashes($data_val['data_field']); ?>" id="<?php echo stripslashes($data_val['data_field']); ?>" <?php if(($data_val['field_required']=='yes') && $data_val['field_validate']!='none'){?> class="validate[required,custom[<?php echo $data_val['validate_class'];?>]]" <?php } else if($data_val['field_required']=='yes'){ ?> class="validate[required],custom[<?php echo $data_val['validate_class'];?>]" <? }?> value="" maxlength="50"/>                                </label>                       
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
    public function formhtmlAction()
    {
        
                $session = new Zend_Session_Namespace('Zend_Auth');
                $this->view->loggedin_customer_id=$session->member_id;
                $this->view->form_id=$this->getRequest()->getParam('form_id');
                $form_id=$this->getRequest()->getParam('form_id');
                $customer_id=$this->getRequest()->getParam('cid');

                if(empty($form_id))
		{
                    $this->_redirect('/adminforms/overview/cid/'.$customer_id);
		}

                $form_std_fields=new FormsStd();
                $condition = $form_std_fields->select()->where('status="1" and customer_id='.$customer_id.' and form_id='.$form_id)->order('form_std_field.id asc');

                $fields = $form_std_fields->fetchAll($condition);

                if(count($fields) == 0)
                {
                    $this->_redirect('/adminforms/preview/form_id/'.$form_id.'/cid/'.$customer_id); exit;
                }
	
		if(($this->getRequest()->getParam('cid')!=1) &&  ($session->user_type==1)){
                    $this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}
		
		$this->view->cid=$this->getRequest()->getParam('cid');
			
		
		$formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
		$this->view->formname=$forms_data['form_name'];
		$this->view->authorizationkey=$forms_data['authorizationkey'];
		if($session->member_id>0){
			$id = $session->member_id;
                        $this->view->member_id = $id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
		
		$formTable = new Forms();
		$forms_data=$formTable->fetchRow($formTable->select()->where('id='.$form_id));	
		$this->view->formname=$forms_data['form_name'];
		$this->view->authorizationkey=$forms_data['authorizationkey'];
		if($customer_id>0){
			$id = $customer_id;
                        $this->view->member_id = $id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
    	
//    	$form_std_fields=new FormsStd();
// 		$condition = $form_std_fields->select()->where('status="1" and customer_id='.$customer_id.' and form_id='.$form_id)->order('form_std_field.id asc');
// 		$this->view->fields = $form_std_fields->fetchAll($condition);  	
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
				$this->_redirect('/adminforms/overview'); 
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
			
			$operation_hours_data=$opthrsTable->fetchRow($opthrsTable->select()->where('form_id='.$form_id.' and week_day="'.$today_day.'"'));		 
						
			/* it will return gmt value in seconds*/
			$time_zone_customer=$operation_hours_data['time_zone'];
			$start_time_with_id=explode("_",$operation_hours_data['start_time']);  // THIS WILL BE USED FOR HOURS OF OPERATION
			$start_time=$start_time_with_id[1];
			$end_time_with_id=explode("_",$operation_hours_data['end_time']);  // THIS WILL BE USED FOR HOURS OF OPERATION
			$end_time=$end_time_with_id[1];
			// CUSTOMER (FORM OWNER ) CURRENT TIME  IN SECOANDS
			
			date_default_timezone_set($operation_hours_data['time_zone_code']);
		
						$UTCtime = new DateTime('now', new DateTimeZone($operation_hours_data['time_zone_code']));
						$customer_current_time = strtotime($UTCtime->format("Y-m-d H:i:s"));
						$tm = date("H:i:s", $customer_current_time);
						$customer_current_time = $this->time2seconds($tm);
				
			/* SERVER IS BASED IN INDIA SO SERVER TIMEZONE IST WHICH IS 5:30 =19800 SECONDS*/			//$servertimezone=19800;  				 
			/* SERVER IS BASED IN US SO SERVER TIMEZONE IST WHICH IS -8:00 =51600 SECONDS*/
			//$servertimezone=51600;  				 		
		 	
		 	
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
			
				  
			  /* NOTE THESE RULES ARE HARD CODED IN THE ADMIN PANEL, IT WILL BE MANAGED BY THE ADMIN*/
			  			  			  
			  if($customerrules_data['rule_id']==1){
			   $inquiry_form_std_custom_data=$this->_request->getPost($customerrules_data['field_data']);
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
			   $inquiry_form_std_custom_data=$this->_request->getPost($customerrules_data['field_data']);
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
			   $inquiry_form_std_custom_data=$this->_request->getPost($customerrules_data['field_data']);
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
			   $inquiry_form_std_custom_data=$this->_request->getPost($customerrules_data['field_data']);
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
			   $inquiry_form_std_custom_data=$this->_request->getPost($customerrules_data['field_data']);
			   // Rule:- If the inquiry contains a data field field with a value that is empty, set the connection number to [phone number]
			   
				   if(trim($inquiry_form_std_custom_data)==''){
				   // SET THE PHONE NUMBER FOR CALLING
				   	 $phone_flag=1;
					 $email_flag=1;	
					  $customerRuleFlag = true;
				   }
			  }
			  
			  if($customerrules_data['rule_id']==6){
			   $inquiry_form_std_custom_data=$this->_request->getPost($customerrules_data['field_data']);
			   // Rule:-If the inquiry contains a data field field with a value that is empty, do not place the call
				   if(trim($inquiry_form_std_custom_data)==''){
				   // DON'T SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=0;
					   $email_flag=1;	
					    $customerRuleFlag = true;
				   }
			  }
			  
			  if($customerrules_data['rule_id']==7){
			   $inquiry_form_std_custom_data=$this->_request->getPost($customerrules_data['field_data']);
			   // Rule:-If the inquiry contains a data field field with a value that is NOT empty, set the connection number to [phone number]
				   if(trim($inquiry_form_std_custom_data)!=''){
				   // SET THE PHONE NUMBER FOR CALLING
					   $phone_flag=1;
					   $email_flag=1;
					    $customerRuleFlag = true;
				   }			  
			  }
			  
			  if($customerrules_data['rule_id']==8){
			   $inquiry_form_std_custom_data=$this->_request->getPost($customerrules_data['field_data']);
				// Rule:-If the inquiry contains a data field field with a value that is NOT empty, do not place the call
			   if(trim($inquiry_form_std_custom_data)!=''){
				   // DON'T PLACE THE CALL 
   					   $phone_flag=0;
					   $email_flag=1;	
					    $customerRuleFlag = true;
				   }
			  }
			  
			  if($customerrules_data['rule_id']==9){
			   $inquiry_form_std_custom_data=$this->_request->getPost($customerrules_data['field_data']);
			   // Rule:-If the inquiry contains a data field field with a value equal to [value], set the connection number to [phone number]
				   if(trim($inquiry_form_std_custom_data)==$customerrules_data['field_data_value']){
				   // SET THE PHONE NUMBER FOR CALLING
   					   $phone_flag=1;
					   $email_flag=1;	
					    $customerRuleFlag = true;

				   }
			  }
			  
			   if($customerrules_data['rule_id']==10){
			   $inquiry_form_std_custom_data=$this->_request->getPost($customerrules_data['field_data']);
			   // Rule:-If the inquiry contains a data field field with a value equal to [value], do not place the call
				   if(trim($inquiry_form_std_custom_data)==$customerrules_data['field_data_value']){
				   // DON'T PLACE THE CALL
					   $phone_flag=0;
					   $email_flag=1;					   
					    $customerRuleFlag = true;					   
				   }
			  }
			  
			   if($customerrules_data['rule_id']==11){
			   $inquiry_form_std_custom_data=$this->_request->getPost($customerrules_data['field_data']);
			 // Rule:-If the inquiry contains a data field field with a value NOT equal to [value], set the connection number to [phone number]
			   if(trim($inquiry_form_std_custom_data)!=$customerrules_data['field_data_value']){
				   // SET THE CALL
					   $phone_flag=1;
					   $email_flag=1;					   
					    $customerRuleFlag = true;				   
				   }
			  }
			  
			   if($customerrules_data['rule_id']==12){
			   $inquiry_form_std_custom_data=$this->_request->getPost($customerrules_data['field_data']);
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
			     
      		
			$string_to_pass_to_twilio=''; 	
		 	$firstname=$this->_request->getPost('1');			 	
			$lastname=$this->_request->getPost('2');
		 	
		 	if($this->_request->getPost('announce_1')=='1' && $this->_request->getPost('announce_2')=='1'){	
		 		$string_to_pass_to_twilio .="Their Name is ".$firstname." ".$lastname.".";
		 	}else if($this->_request->getPost('announce_1')=='1'){	 			
		 		$string_to_pass_to_twilio .="Their Name is ".$firstname.".";
		 	}else if($this->_request->getPost('announce_2')=='1'){	 			
		 		$string_to_pass_to_twilio .="Their Name is ".$lastname.".";
		 	}
		 	 
		 	$Body="<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'>
    <img src='". WEBSITE_IMG_URL. "branding.png' /> <hr /><div style='color:black;'>Dear ".$firstname .",<br><br>";
		 	$Body.="Thank you for your interest in '".$form_owner_data['companyname']."'.  We have received your information and will be in touch shortly. Please keep a copy of this email for your reference.<br><br>Sincerely, '".$form_owner_data['companyname']."'<br><br>";
		 			
		 	/**************** EMAIL START HERE ********************************/
		 	
			$email=$this->_request->getPost('3');
			if($this->_request->getPost('announce_3')=='1'){	 	
				$string_to_pass_to_twilio .=" Their Email is ".$email.".";				
			}
			
			if($email!=''){	 					
				$Body.=" Email : ".$email."<br><br>";
			}			
			/**************** EMAIL END HERE ********************************/
			
			/**************** COMPANY START HERE ********************************/
			$company=$this->_request->getPost('5');
			if($this->_request->getPost('announce_5')=='1'){
					$string_to_pass_to_twilio .=" Their Company Name is ".$company.".";
			}
			if($company!=''){	 					
				$Body.=" Company : ".$company."<br><br>";
			}
			/**************** COMPANY END HERE ********************************/
			
			/**************** Street Address START HERE ***********************/
			$streetaddress=$this->_request->getPost('6');
			if($this->_request->getPost('announce_6')=='1'){
				$string_to_pass_to_twilio .=" Their Street Address is ".$streetaddress.".";
				
			}
			if($streetaddress!=''){	 					
				$Body.=" Street Address : ".$streetaddress."<br><br>";
			}
			/**************** Street Address END HERE ********************************/
			
			/**************** City START HERE ***********************/
			$city=$this->_request->getPost('7');
			if($this->_request->getPost('announce_7')=='1'){
				$string_to_pass_to_twilio .=" Their City is ".$city.".";
				
			}

			if($city!=''){	 					
				$Body.=" City : ".$city."<br><br>";
			}
			/**************** City END HERE ****************/
			
			/**************** State START HERE ***********************/
			$state=$this->_request->getPost('8');
			if($this->_request->getPost('announce_8')=='1'){
				$string_to_pass_to_twilio .=" Their State is ".$state.".";
			}
			
			if($state!=''){	 					
				$Body.=" State : ".$state."<br><br>";
			}
			/**************** Zip START HERE ***********************/
			$zip=$this->_request->getPost('9');
			if($this->_request->getPost('announce_9')=='1'){
					$string_to_pass_to_twilio .=" Their Zip is ".$zip.".";
			}
			
			if($zip!=''){	 					
				$Body.=" Zip : ".$zip."<br><br>";
			}
			/**************** Zip End HERE ***********************/
			
			/**************** Country START HERE ***********************/
			$country=$this->_request->getPost('10');
			if($this->_request->getPost('announce_10')=='1'){
				$string_to_pass_to_twilio .=" Their Country is ".$country.".";
				
			}
			if($country!=''){	 					
				$Body.=" Country : ".$country."<br><br>";
			}
			/**************** Country End HERE ***********************/
			
			/**************** Phone number START HERE ***********************/
			$homephone=$this->_request->getPost('4');
			if($this->_request->getPost('announce_4')=='1'){
				$string_to_pass_to_twilio .=" Their Phone Number is ".$homephone.".";
				
			}
			if($homephone!=''){	 					
				$Body.=" Phone Number is : ".$homephone."<br><br>";
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
			$custom1=$this->_request->getPost('custom1');			
			$ann_cust=explode('_',$this->_request->getPost('announce_custom1'));		
			if($ann_cust[0]=='1'){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust[1]));		
			$string_to_pass_to_twilio .=" the value entered for the field, the value entered for the field, ".$forms_data['label'].", is ".$custom1.".";
			$Body.=$forms_data['label']." : ".$custom1."<br><br>";
			}
			if($ann_cust[0]=='0'){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust[1]));					
			$Body.=$forms_data['label']." : ".$custom1."<br><br>";
			}
			/**************** Custom 1 End HERE ***********************/
			
			/**************** Custom 2 Start HERE ***********************/
			$custom2=$this->_request->getPost('custom2');
			$ann_cust2=explode('_',$this->_request->getPost('announce_custom2'));						
			if($ann_cust2[0]=='1'){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust2[1]));	
				$string_to_pass_to_twilio .=" the value entered for the field, ".$forms_data['label'].", is ".$custom2.".";
				$Body.=$forms_data['label']." : ".$custom2."<br><br>";
			}
			
			if($ann_cust2[0]=='0'){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust2[1]));					
				$Body.=$forms_data['label']." : ".$custom2."<br><br>";
			}			
			/**************** Custom 2 End HERE ***********************/
			
			/**************** Custom 3 Start HERE ***********************/
			$custom3=$this->_request->getPost('custom3');			
			$ann_cust3=explode('_',$this->_request->getPost('announce_custom3'));						
			if($ann_cust3[0]=='1'){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust3[1]));	
				$string_to_pass_to_twilio .=" the value entered for the field, ".$forms_data['label'].", is ".$custom3.".";
				$Body.=$forms_data['label']." : ".$custom3."<br><br>";
			}
			if($ann_cust3[0]=='0'){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust3[1]));					
				$Body.=$forms_data['label']." : ".$custom3."<br><br>";
			}
			
			/**************** Custom 3 End HERE ***********************/
			
			/**************** Custom 4 Start HERE ***********************/
			$custom4=$this->_request->getPost('custom4');
			$ann_cust4=explode('_',$this->_request->getPost('announce_custom4'));						
			if($ann_cust4[0]=='1'){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust4[1]));	
					$string_to_pass_to_twilio .=" the value entered for the field, ".$forms_data['label'].", is ".$custom4.".";
					$Body.=$forms_data['label']." : ".$custom4."<br><br>";
			}
			
			if($ann_cust4[0]=='0'){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust4[1]));						
					$Body.=$forms_data['label']." : ".$custom4."<br><br>";
			}
			
			/**************** Custom 4 End HERE ***********************/
			
			/**************** Custom 5 Start HERE ***********************/
			
			$custom5=$this->_request->getPost('custom5');					
			$ann_cust5=explode('_',$this->_request->getPost('announce_custom5'));						
			if($ann_cust5[0]=='1'){	 	
			$forms_data=$form_std_fields->fetchRow($form_std_fields->select()->where('id='.$ann_cust5[1]));	
				$string_to_pass_to_twilio .=" the value entered for the field, ".$forms_data['label'].", is ".$custom5.".";
				$Body.=$forms_data['label']." : ".$custom5."<br><br>";				
			}
			if($ann_cust5[0]=='0'){	 	
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
                                $string_to_pass_to_twilio .=", To forward the message, press the 4 key ";
                                $string_to_pass_to_twilio .=", To call back after five minuites the message, press the 6 key";

				$string_to_pass_to_twilio .=" @#@#@@#".$user_phone;
				// NEED TO CONNECT TO NUMBER USER HAS ENTERED IN THE FORM
			}
			
			
			$form_submitted_data = array();				
			$form_submitted_data['form_id']=$form_id;
			$form_submitted_data['customer_id']=$forms_data['customer_id'];	
			$form_submitted_data['caller_id']=$caller_id;		
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
			$form_submitted_data['date_created']=date('Y-m-d',$customer_current_time);			
			$form_submitted_data['time']=date('h:i A',$customer_current_time);  
			$form_submitted_data['announced_data']=$string_to_pass_to_twilio;
			$last_insert_id=$inquiryTable->insert($form_submitted_data);
			$time=time()-rand(0, 9999999999); // I used this for secure encryption logic.
			$runtime_session_for_form_submitter = new Zend_Session_Namespace('Zend_Auth');
			$runtime_session_for_form_submitter->runtime_session=md5($time);
					
			/* EMAIL CONSTRUCTION FOR OWNER  START HERE */
			//echo $string_to_pass_to_twilio;  exit;
			
			if($notification_email!=''){			
			$mail = new Zend_Mail();			
			$Body.=" Thanks <br><span style='color:black;'> ".WEBSITE_NAME."</span></div></div>";
			$mail->setBodyHtml($Body);
			$mail->setFrom(SITE_SUPPORT_EMAIL, WEBSITE_NAME);		
			$mail->addTo($notification_email,'Admin');
			//$mail->setSubject('A customer has submitted a Form on '.WEBSITE_NAME);
			$mail->setSubject('We have received your information');			
			$result=$mail->send(); 			 
			// SEND EMAIL TO PROSPECTIVE LEAD
			if($emailnotification_data['send_email_notification_pros_leads']=='yes'){
			$mail->addTo($form_owner_data['email'],'Owner');
			//$mail->setSubject('We have received your information');			
			$result=$mail->send(); 
			}
			}
			
			/* EMAIL SEND END HERE */	
			/*echo "connect to phone ";
			echo $phone_flag; echo "connect to twilio";
			echo $connecttotwilio;	*/
			
			/*echo '/adminforms/connecttotwilio/rsession/'.md5($time).'/id/'.$last_insert_id.'/form_id/'.$form_id.'/cid/'.$customer_id.'/user_phone/'.$phone; exit;*/
						
			if($connecttotwilio==1){				
				$this->_redirect('/forms/connecttotwilio/rsession/'.md5($time).'/id/'.$last_insert_id.'/form_id/'.$form_id.'/user_phone/'.$user_phone.'/notification_email/'.$notification_email);	exit;
			}
			
			if($redirect_type==1){
			$this->_redirect($redirect_url);  exit;			
			}else{
			$runtime_session = new Zend_Session_Namespace('Zend_Auth');
			$runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
			$this->_redirect('/adminforms/thanks');   exit;
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

         if($runtime_session->runtime_session==$rsession){
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

                /* Twilio REST API version */

                // need to check remaining credits before making the call.

                $members = new members();
                $callInfoData=$members->fetchRow($members->select()->where('id='.$customer_id));
                $remaining_call = $callInfoData['total_remaining_calls'];


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
                $to  = $customer_phone;//$client_number; Changed by Hasan


    		$client = new TwilioRestClient($AccountSid, $AuthToken);
                //validate user phone number

		if(!empty($inquiryTableData['announced_data']) && $remaining_call > 0 && !empty($to))
		{
			$data=$inquiryTableData['announced_data']."@@@@".$formsTableData['to_repeat_the_announcement']; // to add by hasan ."%".$formsTableData['to_repeat_the_announcement']
			/* Instantiate a new Twilio Rest Client */


                /* Initiate a new outbound call by POST'ing to the Calls resource */
               $response = $client->request("/$ApiVersion/Accounts/$AccountSid/Calls",
                    "POST", array(
                        "From" => $CallerID,
                        "To"   => $to,
                        "Url"  => WEBSITE_URL."hello.php?data=".base64_encode($data),
                        "StatusCallback" => WEBSITE_URL."twilio_call_time_update.php?notification_email={$notification_email}"
                    ));


//               echo '<pre>';
//               print_r($response);
//               die();

                $inquiryTable = new inquiry();
                $inquiry_update_data = array();

                if( !empty ($response->IsError)) {
                    //echo "Error: {$response->ErrorMessage}";
			$inquiry_update_data['response_error']=$response->ResponseText;
			$inquiryTable->update($inquiry_update_data,'id='.$id);

//                        print $this->db->getProfiler()->getLastQueryProfile()->getQuery();
//
//                        die();


			// save in db instead of echo
			$runtime_session = new Zend_Session_Namespace('Zend_Auth');
			$runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";

			//$redirect_type=$formsTableData['redirect_type'];
		 	//$redirect_url=$formsTableData['redirect_url'];
		 	if($redirect_type==1){
			 $redirect_url = $redirect_url.'?msg='.urlencode($response->ErrorMessage);
			$this->_redirect($redirect_url);  exit;
			}else{
			$this->_redirect('/adminforms/thanks');   exit;
			}
                }
                else{
                        $inquiry_update_data['response_error']=$response->ResponseText;

                        $inquiryTable->update($inquiry_update_data,'id='.$id);

                        $members = new members();

                        $members->update(array('total_remaining_calls' => new Zend_Db_Expr( 'total_remaining_calls-1')), 'id='.$customer_id);

                        $runtime_session = new Zend_Session_Namespace('Zend_Auth');
                        $runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
                    // save in DB this returns a unique id
                    if($redirect_type==1){
                            //$this->_redirect($redirect_url);  exit;
                                $runtime_session = new Zend_Session_Namespace('Zend_Auth');
                                $runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
                                $this->_redirect('/adminforms/thanks');   exit;
                            }else{
                                $runtime_session = new Zend_Session_Namespace('Zend_Auth');
                                $runtime_session->runtime_session="<font color='black'><b>Your form is submitted successfully!</b></font>";
                                $this->_redirect('/adminforms/thanks');   exit;
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

        if(!empty ($form_id))
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