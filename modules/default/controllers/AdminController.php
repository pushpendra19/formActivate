<?php
/**
 * Admin Controller
 * Short description for file.
 *
 * This file is Admin Controller file. Here is all methods of back end section 
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
//require_once('Zend/Session.php');

class AdminController extends BaseController
{

   public function init()
    {
    	date_default_timezone_set('America/Los_Angeles');
    	$auth=Zend_Auth::getInstance();	
    	$session = new Zend_Session_Namespace('Zend_Auth'); 
		//$this->view->actionName = 'index';	
		$this->view->loggedin_customer_id=$session->member_id;	
		if(($session->member_id>0) &&($session->user_type==1)){			
		}else{
		$this->_redirect(WEBSITE_URL.'customers/logout');
		}
    }

       /**
     * indexAction() - Method to show home of admin area
     *
     * @access public
	 * @return void
     */

    public function indexAction()
    {
    	/*$session = new Zend_Session_Namespace('Zend_Auth'); 
		$this->view->actionName = 'index';	
		$this->view->loggedin_customer_id=$session->member_id;	*/
    	$this->view->actionName = 'myaccount';
    }
    /**
     * customersAction() - Method to show Customer List
     *
     * @access public
	 * @return void
     */

    public function customersAction()
    {
			$member = new members();
			$orders = new orders();
			$session_customer_sorting = new Zend_Session_Namespace('Zend_Auth'); 
		
			$select = $member->select()->setIntegrityCheck(false);			
			$name = $this->getRequest()->getParam('name');
			$orderby = $this->getRequest()->getParam('sort');
			$page_limit=20;
			/*
			if(empty($orderby))
			{
				$orderby="firstname desc";
			}*/
			
			$this->view->columnname = $name;	
			$this->view->sortby = $orderby;	
			$this->view->actionName = 'customers';
			
			// FOR GETTING START DATE AND END DATE OF MONTH START HERE
			 $var=explode('-',date('Y-m-d'));

			if($var['1']==1 || $var['1']==3 || $var['1']==5 || $var['1']==7 || $var['1']==8 || $var['1']==10  || $var['1']==12){
			$start_date_for_month=$var['0'].'-'.$var['1'].'-1';
			$end_date_for_month=$var['0'].'-'.$var['1'].'-31';
			}
			
			// For the case february
			if($var['1']==2 && $var['1']%2==0){
			$start_date_for_month=$var['0'].'-'.$var['1'].'-1';
			$end_date_for_month=$var['0'].'-'.$var['1'].'-28';
			}else if($var['1']==2){
			$start_date_for_month=$var['0'].'-'.$var['1'].'-1';
			$end_date_for_month=$var['0'].'-'.$var['1'].'-29';
			}
			
			
			if($var['1']==4 || $var['1']==6 || $var['1']==9 || $var['1']==11){
			$start_date_for_month=$var['0'].'-'.$var['1'].'-1';
			$end_date_for_month=$var['0'].'-'.$var['1'].'-30';
			}
			// FOR GETTING START DATE AND END DATE OF MONTH END HERE
			
		/*		$condition2 = $select->from($orders,array('orders.*'))
					->joinInner('members','orders.member_id=members.id',array('firstname'))
					->where("orders.created_on between '$start_date_for_month' and '$end_date_for_month' and orders.order_status!='Pending' and members.status !='Inactive'");	
				//	$sql = $condition2->__toString(); echo $sql;die;
			$total_monthly_income_result = $orders->fetchAll($condition2);	*/
		
			 $orders->select()->where("created_on between '$start_date_for_month' and '$end_date_for_month' and user_status='Active' and order_status!='Pending'");
		    $total_monthly_income_result = $orders->fetchAll($orders->select()->where("created_on between '$start_date_for_month' and '$end_date_for_month' and user_status='Active' and order_status!='Pending'"));		  
		    
		    $this->view->total_monthly_income=$total_monthly_income_result;  
			
			$session_customer_sorting = new Zend_Session_Namespace('Zend_Auth'); 
			if(!empty($session_customer_sorting->customers_sorting))
			{
				$session_customer_sorting->customers_sorting='';
				unset($session_customer_sorting->customers_sorting);
			}
			
			if($this->_getParam('page')!=''){
			$page_number=$this->_getParam('page');
			}elseif($session_customer_sorting->page!=''){
			$page_number=$session_customer_sorting->page;		
			}else{
			$page_number=1;
			}			
			$this->view->page_number = $page_number;					
			
			$field_name=$this->getRequest()->getParam('name');
			$sort=$this->getRequest()->getParam('sort');
			$field_id=$this->getRequest()->getParam('id');
			
			if($field_id=='' && $field_name==''){
			$field_id='2';
			$field_name='limit';
			}
			
			if(!empty($field_name) && !empty($sort))
			{
				if($field_name=='date')
				{
					$order_by="id ".$sort;
				}
				else
				{
					$order_by=$field_name." ".$sort;
					$session_customer_sorting->customers_sorting=$order_by;
					$session_customer_sorting->field_name=$field_name;
					$session_customer_sorting->sort=$sort;
					$session_customer_sorting->page=$page_number;
				}
			}elseif(trim($session_customer_sorting->customers_sorting)!=''){
			$order_by=$session_customer_sorting->customers_sorting;	
			$this->view->columnname = $session_customer_sorting->field_name;	
			$this->view->sortby = $session_customer_sorting->sort;	
			$session_customer_sorting->page=$page_number;						
			}else {
			$order_by='firstname desc';
			}	
				if($order_by=='call_duration desc')
				{
					$order_by='firstname desc';
				}
			//echo $order_by; exit;
					
			// if search form has been posted		

			if($this->getRequest()->getParam('search')) 
			{ 
				$username = addslashes(trim($this->getRequest()->getParam('search')));
				if(!empty($username))
				{
				
					$condition = $select->from($member,array('customers.*'))
					->joinInner('subscriptions','customers.plan_id=subscriptions.id',array('price'))
					->order($order_by)
					->where("customers.username like '$username%'");					
					$_GET['search'] = $username;
					$this->view->username = $username;	
					
				}	
			}
			
			//$field_status
			if(($field_name=='limit') && ($field_id==2 || $field_id==3)) 
			{ 			
				if($field_id==2)	
					$field_status='Active';
				if($field_id==3)	
					$field_status='Inactive';
				$this->view->selectcustomer =$field_id;//
				}else{
				$field_status='';
				
			}
			
			if(empty($condition) && $field_status!='')// if no conditiona has been set due to search
			{
				$condition = $select->from($member, array('customers.*'))				
				 ->joinInner('subscriptions','customers.plan_id=subscriptions.id and customers.user_type=0',array('price'))
				 ->where("customers.status='$field_status'")
				->order($order_by);						
			}else if(empty($condition))// if no conditiona has been set due to search
			{
				$condition = $select->from($member, array('customers.*'))				
				 ->joinInner('subscriptions','customers.plan_id=subscriptions.id and customers.user_type=0',array('price'))				
				->order($order_by);						
			}
						
			$result_member = $member->fetchAll($condition);			
			$this->view->total_customer =count($result_member);
			
			$total_active_customer=$member->fetchAll(array('status= ?' =>'Active','user_type = ?' => '0'));	
			$this->view->total_active_customer =count($total_active_customer);
			
			$result_total_customer=$member->fetchAll(array('user_type = ?' => '0','plan_id >?' => '0'));	
			$this->view->total_customer =count($result_total_customer);
			
			/*date('Y-m-d'); echo "<br>";
			echo date('Y-m-d',strtotime("next Month"));				*/
			//$orders
			
			
			
			if(($field_name=='limit') && $field_id==1) 
			{ 			
			//$page_limit=count($result_member);
			$this->view->selectcustomer =1;//
			}
			

			if(count($result_member))
			{
				$paginator  = Zend_Paginator::factory($result_member); 
				$view=Zend_View_Helper_PaginationControl::setDefaultViewPartial('partials/my_pagination_control.phtml');   
				$paginator->setItemCountPerPage($page_limit) 
						  ->setPageRange($page_limit) 
						  ->setCurrentPageNumber($page_number); 
				$paginator->setDefaultScrollingStyle('Sliding');
				$paginator->setView($view);

				$this->view->paginator = $paginator;
				$this->view->membertypes = $paginator;
				$this->view->pageno = $this->_getParam('page');
				$this->view->recordPerPage = $page_limit;
			}
			
			
			$conditions = $select->from($orders,array('orders.*'))
			->joinInner('customers','customers.id=orders.member_id')
			->where("customers.status='Active'");			 			
			return;    
	
	}
	
	 /**
     * businessruleAction() - Method to show Business Rule Listing 
     *
     * @access public
	 * @return void
     */
    
    public function businessruleAction()
    {	
    	$businessrule = new Businessrule();	
    	$businessrule_data = $businessrule->fetchAll($businessrule->select()->where("1"));		
		$this->view->businessrules = $businessrule_data;	
    }
    

    // Change Customer Status.
	public function changecustomerstatusAction()
	{
		
		$member = new members();
		$orders = new orders();

		if($this->getRequest()->getParam('id'))
        {
		 
			$userId = $this->getRequest()->getParam('id');
			$data['status']=$this->getRequest()->getParam('status');
			$data1['user_status']=$this->getRequest()->getParam('status');
			$n = $member->update($data, "id =".$userId); 
			$orders->update($data1, "member_id=".$userId);   
			
			$member_data=$member->fetchRow($member->select()->where('id='.$userId));		
			$deleteError = new Zend_Session_Namespace('Zend_Auth');
			$deleteError->deleteError="<font color='black'><b>".$member_data['firstname']." ".$member_data['lastname']." has been set to ".$data['status'].".</b></font>";
			$this->_redirect('/admin/customers');
			exit;
		}
	}
    
	// Change Business Rule Status.
	public function changestatusAction()
	{		
		/*$session = new Zend_Session_Namespace('Zend_Auth'); 
		$this->view->actionName = 'index';	
		$this->view->loggedin_customer_id=$session->member_id;	*/
		
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
     * signupAction() - Method to Signup Step 1 
     *
     * @access public
	 * @return void
     */
    public function editpageAction($id=null)
    {
    	
    	$staticpage = new staticpage();		
    	$this->view->actionName = 'editstaticpage';
    	
		if($this->_request->isPost())
		{
			$page_name = $this->_request->getPost('page_name');
			$page_title  = $this->_request->getPost('page_title');
			$page_body   =$this->_request->getPost('page_body');
			$modified_date   = date('Y-m-d');
			$id= $this->_request->getPost('id');			
			
			if( empty($page_name) || empty($page_title) || empty($page_body))
			{				
				
				if(empty($page_name))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='red'><b>Please enter page name.</b></font>";
					$this->view->page_name = $page_name ;
				}
				else if(empty($page_title))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='red'><b>Please enter page title.</b></font>";
					$this->view->page_title = $page_title ;
				}
				else if(empty($page_body))
				{
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='red'><b>Please enter page body.</b></font>";
					$this->view->page_body = $page_body ;
				}
				//$this->_redirect('customers/signup'); 
			}	
			else
			{
					$modified_date   = date('Y-m-d');
					$id= $this->_request->getPost('id');
					$static_data['page_title'] = $page_title;
					$static_data['page_name'] = $page_title;
					$static_data['page_body'] = $page_body;
					$static_data['modified_date'] = $modified_date;
					$staticpage->update($static_data, 'id='.$id);	
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError="<font color='black'><b>Page update successfully.</b></font>";
					$this->view->page_name = $page_name ;				
					$this->_redirect('admin/editpage/id/'.$id);
					// exit;
			}
			
		}

		 if($this->getRequest()->getParam('id')){ 
				$staticpage_data = $staticpage->fetchRow($staticpage->select()->where('id='.$this->getRequest()->getParam('id')));
				//print_r($staticpage_data); exit;
				$this->view->data = $staticpage_data;				
				//print_r($member_data);exit;								
				$this->view->id=$this->getRequest()->getParam('id');				
			}			
    }
    
     /**
     * pagesAction() - Method to show Business Rule Listing 
     *
     * @access public
	 * @return void
     */
    
    public function pagesAction()
    {
		$staticpages = new staticpage();	
    	$staticpages_data = $staticpages->fetchAll($staticpages->select()->where("1"));		
		$this->view->staticpages_data = $staticpages_data;	
		//$this->render();	
    }  
    
     /**
     * inquiryAction() - Method to show Inquiry List
     *
     * @access public
	 * @return void
     */

    public function inquiryAction()
    {
    	
			$inquiries = new inquiry();
			$select = $inquiries->select()->setIntegrityCheck(false);
			
			$name = $this->getRequest()->getParam('name');
			$orderby = $this->getRequest()->getParam('sort');			
			
			$fromdate = $this->getRequest()->getParam('fromdate'); 
			$todate = $this->getRequest()->getParam('todate');
			
			$this->view->fromdates=$this->getRequest()->getParam('fromdate'); 
			$this->view->todates= $this->getRequest()->getParam('todate');
			
			// if search by Customer name, Form name form has been posted		
			$customer_id=$this->getRequest()->getParam('customer_id');
			$form_id=$this->getRequest()->getParam('form_id');
			$allforms=$this->getRequest()->getParam('allforms');
			$allcustomers=$this->getRequest()->getParam('allcustomers');
			$field_name=$this->getRequest()->getParam('name');
			
			
			$this->view->columnname = $name;	
			$this->view->sortby = $orderby;	
			$this->view->actionName = 'inquiry';
			$condition_for_monthly_calls='';			
			
			$membersTable = new members();	
			$formsTable = new Forms();	
			$formstdfield = new FormsStd();	 
			
		//	echo $membersTable->select()->where("status='Active' and user_type=0 order by firstname asc");
    		$members_data = $membersTable->fetchAll($membersTable->select()->where("status='Active' and user_type=0")->order('firstname asc'));	    		

    		$this->view->members_data = $members_data;	
			
			$this->view->select_billing_ajax='fromdate/'.$fromdate.'/todate/'.$todate;
			
						
			$session_customer_sorting = new Zend_Session_Namespace('Zend_Auth'); 
                        $session_customer_sorting->deleteError = NULL;
			
			$queryfield='';
			$querylabel='';
						
			if($form_id>0){	
				$formreport = new formreport();	
				$formreport_data = $formreport->fetchRow($formreport->select()->where("form_id=".$form_id." and customer_id=".$customer_id));				
				$sortby=$formreport_data->field_sort;	
				$formstdfield->select()->where("id=".$sortby);
				$form_fieldid_sort_data = $formstdfield->fetchRow($formstdfield->select()->where("id=".$sortby));								
				$field_name=$form_fieldid_sort_data->inquiry_table_field;
				if($formreport_data->selected_field_id!=''){
				$form_fieldid_data = $formstdfield->fetchAll($formstdfield->select()->where("id in ($formreport_data->selected_field_id)"));				
				foreach($form_fieldid_data as $form_fieldid_datas){
					$queryfield.=$form_fieldid_datas->inquiry_table_field.',';
					$querylabel.=$form_fieldid_datas->label.',';
					 
				}				
				}
			}
			
			$this->view->queryfield_array=explode(',',$queryfield);
			$this->view->querylabel_array=explode(',',$querylabel);		
			
			
			
			if($this->_getParam('page')!=''){
			$page_number=$this->_getParam('page');
			}elseif($session_customer_sorting->page!=''){
			$page_number=$session_customer_sorting->page;		
			}else{
			$page_number=1;
			}			
			$this->view->page_number = $page_number;	
			
				
			if(!empty($field_name) && !empty($sort))
			{
				if($field_name=='date')
				{
						$order_by="id ".$sort;
				}
				else
				{
					$order_by=$field_name." ".$sort; 
					$session_customer_sorting->customers_sorting=$order_by;
					$session_customer_sorting->field_name=$field_name;
					$session_customer_sorting->sort=$sort;
					$session_customer_sorting->page=$page_number;
				}
			}elseif(trim($session_customer_sorting->customers_sorting)!=''){
			$order_by=$session_customer_sorting->customers_sorting;	
			$this->view->columnname = $session_customer_sorting->field_name;	
			$this->view->sortby = $session_customer_sorting->sort;	
			$session_customer_sorting->page=$page_number;						
			}else {				
			$order_by='firstname desc';
			}					
							
			if($customer_id!=''){
			$forms_data = $formsTable->fetchAll($formsTable->select()->where("customer_id='".$customer_id."'"));		
			$this->view->forms_data = $forms_data;	
			}
			
			
			if(($allforms=='1' || $allforms=='2' || $allforms=='3' || $allforms=='4' || $allforms=='5') &&  $form_id!='') 
			{ 				
				if($allforms=='1'){
					$condition = $select->from($inquiries,array('inquiry.*'))					
					->where("customer_id ='".$customer_id."'")
					->order($order_by);		
									
				}else{
					$inquiry_type_value='';
					if($allforms=='5'){$inquiry_type_value='Completed';}
					if($allforms=='2'){$inquiry_type_value='Incomplete';}
					if($allforms=='3'){$inquiry_type_value='After hours';}	
					if($allforms=='4'){$inquiry_type_value='Hang up';}				
					if($inquiry_type_value==''){$inquiry_type_value='Completed';}	
								
					if($form_id>0){
					$condition = $select->from($inquiries,array('inquiry.*'))					
					->where("customer_id ='".$customer_id."' and inquiry_type='".$inquiry_type_value."' and form_id=".$form_id)
					->order($order_by);	 }else{
					$condition = $select->from($inquiries,array('inquiry.*'))					
					->where("customer_id ='".$customer_id."' and inquiry_type='".$inquiry_type_value."'")
					->order($order_by);
					}	
				}
			}else if($customer_id>0 &&  $form_id>0 && $fromdate!='' &&  $todate!='') 
			{ 	
				$forms_data = $formsTable->fetchAll($formsTable->select()->where("customer_id='".$customer_id."'"));		
				$this->view->forms_data = $forms_data;						
				$dateformdate=explode('-',$fromdate);
				$datetodate=explode('-',$todate);				
				$fromdate=strtotime($dateformdate[1].'-'.$dateformdate[0].'-'.$dateformdate[2]);				
				$todate=strtotime($datetodate[1].'-'.$datetodate[0].'-'.$datetodate[2]);				
				$condition = $select->from($inquiries,array('inquiry.*'))					
				->where("form_id='".$form_id."' and customer_id='".$customer_id."' and unix_timestamp(date_created) between '".$fromdate."' and '".$todate."'")
				->order($order_by);	
								
				$condition_1 = $inquiries->select()->where("form_id='".$form_id."' and customer_id='".$customer_id."' 
				and unix_timestamp(date_created) between '".$fromdate."' and '".$todate."' and inquiry_type='completed'");	
						
				
				$condition_for_monthly_calls="form_id='".$form_id."' and customer_id='$customer_id'";
							
			}else if($customer_id>0 && $fromdate!='' &&  $todate!='') 
			{ 	
				$forms_data = $formsTable->fetchAll($formsTable->select()->where("customer_id='".$customer_id."'"));		
				$this->view->forms_data = $forms_data;						
				$dateformdate=explode('-',$fromdate);
				$datetodate=explode('-',$todate);				
				$fromdate=strtotime($dateformdate[1].'-'.$dateformdate[0].'-'.$dateformdate[2]);				
				$todate=strtotime($datetodate[1].'-'.$datetodate[0].'-'.$datetodate[2]);				
				$condition = $select->from($inquiries,array('inquiry.*'))					
				->where("customer_id='".$customer_id."' and unix_timestamp(date_created) between '".$fromdate."' and '".$todate."'")
				->order($order_by);		
				
				$condition_1 = $inquiries->select()->where("customer_id='".$customer_id."' 
				and unix_timestamp(date_created) between '".$fromdate."' and '".$todate."' and inquiry_type='completed'");	
				$condition_for_monthly_calls="customer_id='$customer_id'";
				//$this->view->drop_down_form_id=$form_id;								
			}else if($customer_id>0 &&  $form_id>0) 
			{ 	
				$forms_data = $formsTable->fetchAll($formsTable->select()->where("customer_id='".$customer_id."'"));		
				$this->view->forms_data = $forms_data;						
				$condition = $select->from($inquiries,array('inquiry.*'))									
				->where("form_id='".$form_id."' and customer_id='".$customer_id."'")
				->order($order_by);									
				$condition_1 = $inquiries->select()->where("customer_id='".$customer_id."' and form_id='".$form_id."' and inquiry_type='completed'");									
				$this->view->drop_down_customer_id=$customer_id;
				$condition_for_monthly_calls="customer_id='".$customer_id."' and form_id='$form_id'";				
			}else if($customer_id=='allcustomers' && $fromdate!='' &&  $todate!='') 
			{ 	
				$dateformdate=explode('-',$fromdate);
				$datetodate=explode('-',$todate);				
				$fromdate=strtotime($dateformdate[1].'-'.$dateformdate[0].'-'.$dateformdate[2]);				
				$todate=strtotime($datetodate[1].'-'.$datetodate[0].'-'.$datetodate[2]);				
				$condition = $select->from($inquiries,array('inquiry.*'))					
				->where("unix_timestamp(date_created) between '".$fromdate."' and '".$todate."'")
				->order($order_by);							
				$condition_1 = $inquiries->select()->where("inquiry_type='completed'");	
				$condition_for_monthly_calls='';				
			}
			
			
			if($allforms=='0' || $allcustomers=='0') 
			{
				$condition='false';
			}
			

			if(empty($condition))												 // if no conditiona has been set due to search
			{
				$condition = $select->from($inquiries, array('inquiry.*'))						   
				->order($order_by);						
			}
			if(empty($condition_1))												 // if no conditiona has been set due to search
			{
				$condition_1 = $inquiries->select()->where("inquiry_type='completed'");						
			}
			
			
			$this->view->drop_down_customer_id=$customer_id;
			$this->view->drop_down_form_id=$form_id;	
		//	echo $condition; exit;			
			$result_inquiry = $inquiries->fetchAll($condition);			
			$this->view->total_calls=count($result_inquiry);	
			// total connections
				
 			$result_inquiry_1= $inquiries->fetchAll($condition_1);	
 			$this->view->total_connections = count($result_inquiry_1);
 			
 			/* Changed by Pushpendra to add minutes in admin site START*/
 			$call_duration = 0;
 			foreach($result_inquiry_1 as $res)
 			{
					$call_duration += $res->call_duration;
 			}
 			$min = $call_duration/60;
			$min = ceil ( $min );
 			$this->view->total_minutes = $min;
			
 			/* Changed by Pushpendra to add minutes in admin site END*/
			//*MONTHLY CALLS *//
			 $month_start_date=strtotime(date('Y').'-'.date('m').'-1');
			
			if(date('m')==1 || date('m')==3 || date('m')==5 || date('m')==7 || date('m')==8 || date('m')==10 || date('m')==12){
			$month_last_date =strtotime(date('Y').'-'.date('m').'-31');  
			}
			if(date('m')==4 || date('m')==6 || date('m')==9 || date('m')==11){
			$month_last_date =strtotime(date('Y').'-'.date('m').'-30');  
			}
			if(date('m')==2 && date('m')%4==0){
			$month_last_date =strtotime(date('Y').'-'.date('m').'-29');  
			}
			if(date('m')==2 && date('m')%4!=0){
			$month_last_date =strtotime(date('Y').'-'.date('m').'-28');  
			}
			
			if($condition_for_monthly_calls==''){			
				$condition1 = $inquiries->select()->where("unix_timestamp(date_created) between '".$month_start_date."' and '".$month_last_date."'");		
			}else{				
				 $condition1 = $inquiries->select()->where("unix_timestamp(date_created) between '".$month_start_date."' and '".$month_last_date."' and ".$condition_for_monthly_calls);		
			}
			
 			$result_inquiry1= $inquiries->fetchAll($condition1);	
 			$this->view->total_inquires =count($result_inquiry1);
 			//*MONTHLY CALLS END HERE *//
 			
 			if($allforms=='1' || $allforms=='2' || $allforms=='3' || $allforms=='4' || $allforms=='5') 
			{ 					
					$this->view->selectform =$allforms;//
			}else if($allcustomers=='1') 
			{ 						
			$this->view->selectcustomer =1;//
			}
			$page_limit=20;
			
			
			// Billing period Date in case of Customer Start here
			if($customer_id>0){
			$members_billing_period = $membersTable->fetchAll($membersTable->select()->where("id=".$customer_id));	
			
			//print_r($members_billing_period);
			if(count($members_billing_period)>0){

				$this->view->members_billing_period=$members_billing_period;			
			 }
			}	
			// Billing period Date in case of Customer End here
		
			
			if(count($result_inquiry))
			{
				$paginator  = Zend_Paginator::factory($result_inquiry); 
				$view=Zend_View_Helper_PaginationControl::setDefaultViewPartial('partials/my_pagination_control.phtml');   
				$paginator->setItemCountPerPage($page_limit) 
						  ->setPageRange($page_limit) 
						  ->setCurrentPageNumber($page_number); 
				$paginator->setDefaultScrollingStyle('Sliding');
				$paginator->setView($view);

				$this->view->paginator = $paginator;
				$this->view->membertypes = $paginator;
				$this->view->pageno = $this->_getParam('page');
				$this->view->recordPerPage = $page_limit;
			}			
			return;    
	
	}

	 /**
    * function to getformsusingcustomeridAction    */   
    
   public function getformsusingcustomeridAction()
	{		
		if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();			
			$id= $filter->filter($this->_request->getPost('customer_id'));					 
		 	
			$formsTable = new Forms();	    		
			if($id>0){
			$forms_data = $formsTable->fetchAll($formsTable->select()->where("status=1 and customer_id=".$id));		
			if(count($forms_data)>0){
				echo "<option value='0'>Select</option>";
			foreach($forms_data as $forms_data_key => $forms_data_val){?>
			<option	value="<?php echo $forms_data_val['id'];?>"><?php echo $forms_data_val['form_name'];?></option>
			 <? }}else{
			 echo "<option value='0'>Select</option>";
			 }
			}			
			exit;	
		}
	}
	
	
	/*** function to getbillingperiodAction    */   
    
   public function getbillingperiodAction()
	{		
		if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();			
			$id= $filter->filter($this->_request->getPost('customer_id'));					 
		 	
			$membersTable = new members();	    		
			if($id>0){
			$members_data=$membersTable->fetchRow($membersTable->select()->where('id='.$id));

			//echo $members_data['plan_start_date']; 
			$thisusermonth=abs((time()-(strtotime($members_data['plan_start_date'])))/(60*60*24*30));
		
					
			$plan_start_date =  explode('-',$members_data['plan_start_date']);			
			$plan_start_month=$plan_start_date[1];
			$plan_start_months=$plan_start_date[1];
			$plan_start_day=explode(' ',abs($plan_start_date[2]));
			$plan_start_day=abs($plan_start_day[0]);		
			$plan_start_days=$plan_start_day;				
			$current_year=date('Y');
								
			//********START DATE FOR BILLING *********************************//
			$start_date_value[0]=strtotime($current_year.'-'.(date('m')-1).'-'.$plan_start_day); 
			$plan_start_month=(date('m')-1);
			//$plan_start_month=(date('m')-1);
			
			$end_date_value[0]=strtotime(date('Y-m-d'))-(24*60*60);
			 // STARTING DATE OR BILLING DATE FOR CURRENT MONTH.				
			for($i=1;$i<$thisusermonth;$i++){				
					$plan_start_month--;
					if($plan_start_month < 1){											
						if($plan_start_month==0){
							$plan_start_month_year_changed=12;
							
						$start_date_value[$i]=strtotime(($current_year-1).'-'.($plan_start_month_year_changed).'-'.$plan_start_day);					
						}else{
						
						$start_date_value[$i]=strtotime(($current_year-1).'-'.($plan_start_month_year_changed-(abs($plan_start_month))).'-'.$plan_start_day);					
						}
					}else{				
					$start_date_value[$i]=strtotime(($current_year).'-'.($plan_start_month).'-'.$plan_start_day);			
					}
					
				}
				
				//print_r($start_date_value); exit;
				
				//********END DATE FOR BILLING *********************************//
				
				
				 // END DATE OR BILLING DATE FOR CURRENT MONTH.	
				 $plan_start_months=(date('m')-1);
									 	
			for($i=1;$i<$thisusermonth;$i++){				
					
					if($plan_start_months < 1){											
						if($plan_start_months==0){
							$plan_start_month_year_changed=12;							
						    $end_date_value[$i]=strtotime(($current_year-1).'-'.($plan_start_month_year_changed).'-'.$plan_start_days)-(24*60*60);;					
						}else{						
						    $end_date_value[$i]=strtotime(($current_year-1).'-'.($plan_start_month_year_changed-(abs($plan_start_months))).'-'.$plan_start_days)-(24*60*60);					
						}
					}else{				
					        $end_date_value[$i]=strtotime(($current_year).'-'.$plan_start_months.'-'.$plan_start_days)-(24*60*60);
					        
					}
					$plan_start_months--;
				}
				//print_r($end_date_value); 
				//********END DATE FOR BILLING *********************************//
				?>	
				<option value="0">Select</option>				
				<?
				for($report_date=0;$report_date<$thisusermonth;$report_date++){?>
				<option value='fromdate/<?php echo date('m-d-Y',$start_date_value[$report_date]); ?>/todate/<?php echo date('m-d-Y',$end_date_value[$report_date]) ?>'><?php echo date('M j, Y',$start_date_value[$report_date]); ?> - <?php echo date('M j, Y',$end_date_value[$report_date]); ?></option>				
				<? }?>
			 <? }
		}			
			exit;			
	}
	
	
	/*** function to getbillingperiodAction    */   
    
   public function getbillingperiodselectedAction()
	{		
		if($this->_request->isPost())
		{
			$filter=new Zend_Filter_StripTags();			
			$id= $filter->filter($this->_request->getPost('customer_id'));
			$selected_option= $filter->filter($this->_request->getPost('selected_option'));									 
		 	
			$membersTable = new members();	    		
			if($id>0){
			$members_data=$membersTable->fetchRow($membersTable->select()->where('id='.$id));
			
			$thisusermonth=abs((time()-(strtotime($members_data['plan_start_date'])))/(60*60*24*30));
		

			$plan_start_date =  explode('-',$members_data['plan_start_date']);			
			$plan_start_month=$plan_start_date[1];
			$plan_start_months=$plan_start_date[1];
			$plan_start_day=explode(' ',abs($plan_start_date[2]));
			$plan_start_day=abs($plan_start_day[0]);		
			$plan_start_days=$plan_start_day;				
			$current_year=date('Y');
								
			//********START DATE FOR BILLING *********************************//
			$start_date_value[0]=strtotime($current_year.'-'.(date('m')-1).'-'.$plan_start_day); 
			$plan_start_month=(date('m')-1);
			//$plan_start_month=(date('m')-1);
			
			$end_date_value[0]=strtotime(date('Y-m-d'))-(24*60*60);
			 // STARTING DATE OR BILLING DATE FOR CURRENT MONTH.				
			for($i=1;$i<$thisusermonth;$i++){				
					$plan_start_month--;
					if($plan_start_month < 1){											
						if($plan_start_month==0){
							$plan_start_month_year_changed=12;
							
						$start_date_value[$i]=strtotime(($current_year-1).'-'.($plan_start_month_year_changed).'-'.$plan_start_day);					
						}else{
						
						$start_date_value[$i]=strtotime(($current_year-1).'-'.($plan_start_month_year_changed-(abs($plan_start_month))).'-'.$plan_start_day);					
						}
					}else{				
					$start_date_value[$i]=strtotime(($current_year).'-'.($plan_start_month).'-'.$plan_start_day);			
					}
					
				}
				
				//print_r($start_date_value); exit;
				
				//********END DATE FOR BILLING *********************************//
				
				
				 // END DATE OR BILLING DATE FOR CURRENT MONTH.	
				 $plan_start_months=(date('m')-1);
									 	
			for($i=1;$i<$thisusermonth;$i++){				
					
					if($plan_start_months < 1){											
						if($plan_start_months==0){
							$plan_start_month_year_changed=12;							
						    $end_date_value[$i]=strtotime(($current_year-1).'-'.($plan_start_month_year_changed).'-'.$plan_start_days)-(24*60*60);;					
						}else{						
						    $end_date_value[$i]=strtotime(($current_year-1).'-'.($plan_start_month_year_changed-(abs($plan_start_months))).'-'.$plan_start_days)-(24*60*60);					
						}
					}else{				
					        $end_date_value[$i]=strtotime(($current_year).'-'.$plan_start_months.'-'.$plan_start_days)-(24*60*60);
					        
					}
					$plan_start_months--;
				}
				//print_r($end_date_value); 
				//********END DATE FOR BILLING *********************************//
				?>	
				<option value="0">Select</option>				
				<?
				for($report_date=0;$report_date<$thisusermonth;$report_date++){?>
				<option value='fromdate/<?php echo date('m-d-Y',$start_date_value[$report_date]); ?>/todate/<?php echo date('m-d-Y',$end_date_value[$report_date]) ?>'  <?php if('fromdate/'.date('m-d-Y',$start_date_value[$report_date]).'/todate/'.date('m-d-Y',$end_date_value[$report_date])==$selected_option) { echo "selected";}?>><?php echo date('M j, Y',$start_date_value[$report_date]); ?> - <?php echo date('M j, Y',$end_date_value[$report_date]); ?></option>				
				<? }?>
			 <? }
		}			
			exit;			
	}
	
	  public function popupinquirypreviewAction()
  	 {
 		$session = new Zend_Session_Namespace('Zend_Auth');
    	$this->view->loggedin_customer_id=$session->member_id;
    	$this->view->inquiry_id=$this->getRequest()->getParam('inquiry_id');
		$inquiry_id=$this->getRequest()->getParam('inquiry_id');
		$divposition=$this->getRequest()->getParam('position');		
		//exit;
		if(empty($inquiry_id))
		{
			$this->_redirect('/admin/inquiry'); 
		}
		
		if($session->member_id>0){
			$id = $session->member_id;
		}else{
		$this->_redirect('/customers/login'); 
		}	
		
		$inquiry = new inquiry();
		$inquiry_data=$inquiry->fetchRow($inquiry->select()->where('id='.$inquiry_id));	
    	 ?>
                     
       <div id="formUserDetails" style="background: none repeat scroll 0 0 #FFFFFF;border: 5px solid #666666;font: 12px Verdana,Helvetica,sans-serif;
    left: 610px;padding: 8px;text-align: justify;width: 286px;position: absolute; left: 20%;z-index:10;top:<?=$divposition?>px;">                        
                        <img  onclick="hideme('previewhiddendiv')" style="float:right;" src="<?php echo WEBSITE_IMG_URL.'Close.png' ?>"/>
                         <h3>Inquiry <span> Details</span></h3>
                           <fieldset style="color:#000;padding-left:40px;">
                            
                           <b>Date: </b> <?php echo date('m/d/Y',strtotime($inquiry_data['date_created'])); ?><br/><br/>
                           <b>Time: </b> <?php echo $inquiry_data['time'] ?><br/><br/>
                           <b>First Name: </b> <?php echo $inquiry_data['firstname'] ?><br/><br/>
                           <b>Last Name: </b> <?php if(empty($inquiry_data['lastname'])) echo "N/A"; else echo $inquiry_data['lastname'] ?><br/><br/>
                           <b>Email: </b> <?php echo $inquiry_data['email'] ?><br/><br/>
                           <b>Address: </b> <?php if(empty($inquiry_data['streetaddress'])) echo "N/A"; else echo $inquiry_data['streetaddress'] ?><br/><br/>
                           <b>City: </b> <?php if(empty($inquiry_data['city'])) echo "N/A"; else echo $inquiry_data['city'] ?><br/><br/>
                           <b>State: </b> <?php if(empty($inquiry_data['state'])) echo "N/A"; else echo $inquiry_data['state'] ?><br/><br/>
                      <b>Call Duration: </b> <?php if($inquiry_data['call_duration']>60)
									{
										$call_duration=$inquiry_data['call_duration']%60;
										if($call_duration<10)
										{
											$call_duration='0'.$call_duration;
										}
										
										$time=floor($inquiry_data['call_duration']/60);
										echo $time.":".$call_duration;
									}
									else{
										$call_duration=$inquiry_data['call_duration'];
										if($call_duration<10)
										{
											$call_duration='0'.$call_duration;
										}
										echo "0:".$call_duration;
									} ?><br/><br/>
                            </fieldset>
                           </div>     
                             
                            
 		<?php
 		 exit; 		
}   
	
	
	
}
?>