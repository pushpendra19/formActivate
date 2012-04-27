<?php
/**
 * Customers Controller
 * Short description for file.
 *
 * This file is Customers controller file. Here is all methods of Customers 
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
require_once('braintree-php-2.3.0/lib/Braintree.php');
Braintree_Configuration::environment(ENVIRONMENT);
Braintree_Configuration::merchantId(MERCHANTID);
Braintree_Configuration::publicKey(PUBLICKEY);
Braintree_Configuration::privateKey(PRIVATEKEY);

//require_once('Zend/Session.php');

class CustomersController extends BaseController {

    /**
     * init() -Method for zend initialization
     *
	 * @access public
	 * @return void
	 */
	public function init() {
		$auth = Zend_Auth::getInstance();
		$session = new Zend_Session_Namespace('Zend_Auth');
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

			if($today >= $plan_end_date && $member_data['login_override'] != 'YES')
			{
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
	 * indexAction() - Method for Customer Index
	 *
	 * @access public
	 * @return void
	 */
	public function indexAction() {
		$auth = Zend_Auth::getInstance();
		$this->view->actionName = 'index';
		$this->_redirect('/customers/login');
	}




 



	/**
	 * thanksAction() - Method for Customer thanks after purchasing
	 *
	 * @access public
	 * @return void
	 */
	public function thanksAction() {
		$auth = Zend_Auth::getInstance();
		$this->view->actionName = 'thanks';
		$session = new Zend_Session_Namespace('Zend_Auth');

		if ($session->signup_session_id != '')
		{
			$id = $session->signup_session_id;
			$membersTable = new members();
			$members_data = $membersTable->fetchRow($membersTable->select()->where('id=' . $id));
			$subscriptions = new subscriptions();
			$subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id=' . $members_data['plan_id']));

			/* $this->view->card_name =  $members_data['card_name'];
			 $this->view->card_type =  $members_data['card_type'];
			 $this->view->card_num =  $members_data['card_num'];
			 $this->view->card_cvv =  $members_data['card_cvv'];
			 $this->view->expiry_month =  $members_data['expiry_month'];

			 $this->view->expiry_year =  $members_data['expiry_year']; */

			$mail = new Zend_Mail();

			$mail_view = new Zend_View();

			$mail_view->addScriptPath($this->view->getScriptPaths());
			$mail_view->firstname = $members_data['firstname'];
			$mail_view->lastname  = $members_data['lastname'];
			$mail_view->email     = $members_data['email'];
			$mail_view->companyname = $members_data['companyname'];
			$mail_view->price       =  $subscriptions_data['price'];
			$mail_view->address1    =  $members_data['address1'];
			$mail_view->address2  = $members_data['address2'];
			$mail_view->city    = $members_data['city'];
			$mail_view->zip    = $members_data['zip'];
			$mail_view->state  = $members_data['state'];
			$mail_view->phone  = $members_data['phone'];
			$mail_view->name  = $subscriptions_data['name'];
			$mail_view->reset_link = $members_data['reset_link'];

			$Body =$mail_view->render('signup_mail_template.phtml');

			//            $Body = "<div style='color:black;'>Hello " . $members_data['firstname'] . ",<br><br>";
			//            $Body.="Thanks for siging up in " . WEBSITE_NAME . "<br><br>";
			//            $Body.="Please find the details of your filled data.<br><br>";
			//            $Body.="<div style='color:black;'>First name: " . $members_data['firstname'] . "<br><br>";
			//            $Body.="<div style='color:black;'>Last name: " . $members_data['lastname'] . "<br><br>";
			//            $Body.="<div style='color:black;'>Email: " . $members_data['email'] . "<br><br>";
			//            if ($members_data['companyname'] != '') {
			//                $Body.="<div style='color:black;'>Company Name: " . $members_data['companyname'] . "<br><br>";
			//            }
			//
			//            if($subscriptions_data['price'] != 0)
			//            {
			//                $Body.="<div style='color:black;'>Address1: " . $members_data['address1'] . "<br><br>";
			//                if ($members_data['address2'] != '') {
			//                    $Body.="<div style='color:black;'>Address2: " . $members_data['address2'] . "<br><br>";
			//                }
			//                $Body.="<div style='color:black;'>City: " . $members_data['city'] . "<br><br>";
			//                $Body.="<div style='color:black;'>State: " . $members_data['state'] . "<br><br>";
			//                $Body.="<div style='color:black;'>Zip: " . $members_data['zip'] . "<br><br>";
			//                $Body.="<div style='color:black;'>Phone: " . $members_data['phone'] . "<br><br>";
			//            }
			//
			//            $Body.="<div style='color:black;'>Plan name: " . $subscriptions_data['name'] . "<br><br>";
			//            $Body.="<div style='color:black;'>Price: " . $subscriptions_data['price'] . "<br><br>";
			//
			//            $Body.="<a href=" . WEBSITE_URL . "customers/activate/link/" . $members_data['reset_link'] . ">Click here to activate your account.</a>";
			//            $Body.="<br><br> Thanks <br><span style='color:black;'> " . WEBSITE_NAME . "</span></div>";
			//echo $Body; exit;
			$mail->setBodyHtml($Body);
			$mail->setFrom(SITE_NO_REPLY_EMAIL, WEBSITE_NAME);
			$mail->addTo($members_data['email'], $members_data['firstname']);
			$mail->setSubject('Activate your Account on ' . WEBSITE_NAME);
			$result = $mail->send();
			$mail = new Zend_Mail();
			$mail->setBodyHtml($Body);
			$mail->setFrom(SITE_NO_REPLY_EMAIL, WEBSITE_NAME);
			$mail->addTo(SIGNUP_CONFIRMATION_EMAIL, $members_data['firstname']);
			$mail->setSubject('Activate your Account on ' . WEBSITE_NAME);
			$result = $mail->send();
			unset($session->signup_session_id);
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
	 * signupAction() - Method to Signup Step 1
	 *
	 * @access public
	 * @return void
	 */
	public function signupAction($id=null) 
	{
		$session = new Zend_Session_Namespace('Zend_Auth');
		if ($this->getRequest()->getParam('id') || $session->signup_session_id != '') {
			$members = new members();

			if ($this->getRequest()->getParam('id')) {
				$id = $this->getRequest()->getParam('id');
			} else if ($session->signup_session_id != '') {
				$id = $session->signup_session_id;            }

				$member_data = $members->fetchRow($members->select()->where('id=' . $id));
				$this->view->data = $member_data;
				$this->view->id = $id;
		}

		if ($this->getRequest()->getParam('plan')) {
			$session->plan_id = $this->getRequest()->getParam('plan');
		} else if ($session->plan_id) {
			$session->plan_id = $session->plan_id;
		}
		else
		$session->plan_id = 5;

		if($session->plan_id > 6 || $session->plan_id < 2)
		$session->plan_id = 5;

		$this->view->actionName = 'signup';

		if ($this->_request->isPost()) {
			$filter = new Zend_Filter_StripTags();
			$firstname = $filter->filter($this->_request->getPost('firstname'));
			$lastname = $filter->filter($this->_request->getPost('lastname'));
			$companyname = $filter->filter($this->_request->getPost('companyname'));
			$email = $filter->filter($this->_request->getPost('email'));
			$pass = $filter->filter($this->_request->getPost('pass'));

			$id = $filter->filter($this->_request->getPost('id'));
			$memberTable = new members();

			if (empty($firstname) || empty($lastname) || empty($email) || empty($pass)) {

				if (empty($firstname)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter first name.</b></font>";
				} else if (empty($lastname)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter username.</b></font>";
					$this->view->lastname = $lastname;
				} else if (empty($email)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter email.</b></font>";
					$this->view->email = $email;
				} else if (empty($pass)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter password.</b></font>";
					$this->view->lastname = $pass;
				}

				$data_email_check = $member->fetchRow(array('email = ?' => $email));

				if (count($data_email_check) > 0) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Email address already exists.</b></font>";
					$this->view->state = $phone;
				}
				//$this->_redirect('customers/signup');
			} else {
				$members_data['firstname'] = trim($firstname);
				$members_data['lastname'] = trim($lastname);
				$members_data['companyname'] = trim($companyname);
				$members_data['email'] = trim($email);
				$members_data['pass'] = md5($pass);
				$members_data['address1'] =  '';
				$members_data['address2'] =  '';
				$members_data['city'] =  '';
				$members_data['state'] =  '';
				$members_data['zip']=  '';
				$members_data['phone']=  '';
				$members_data['status'] = 'Inactive';
				$members_data['payment_status'] = 'Pending';
				$members_data['plan_status'] = '0';
				$members_data['reset_link'] = md5(time());
				$members_data['plan_start_date'] = date('Y-m-d,H:i:s');
				$members_data['plan_end_date'] = date('Y-m-d,H:i:s', strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " +1 month"));
				if ($id != '') {
					$members_data['api_key'] = strtoupper($this->mykeygen(15).dechex($id));  // used for api key
					$members->update($members_data, 'id=' . $id);
					$this->_redirect('customers/signupplan/id/' . $id);
					// exit;
				} else {
					$orders = new orders();
					$last_insert_id = $memberTable->insert($members_data);
					$session->signup_session_id = $last_insert_id;
					$members_order_data['member_id'] = $last_insert_id;
					$members_order_data['member_current_session_with_time'] = time() . $last_insert_id;
					$session->member_current_session_with_time = $members_order_data['member_current_session_with_time'];
					$last_insert_order_id = $orders->insert($members_order_data);
					$members_order_id['order_id'] = $last_insert_order_id;
					$members_order_id['api_key'] = strtoupper($this->mykeygen(15).dechex($last_insert_id)); // used for api key
					$memberTable->update($members_order_id, 'id=' . $last_insert_id);
					$this->_redirect('customers/signupplan/id/' . $last_insert_id);
				}
			}
		} else {
			//$this->_redirect('customers/');
		}
	}

	/**
	 * signupplanAction() - Method to Signup Step 2
	 *
	 * @access public
	 * @return void
	 */
	public function signupplanAction()
	{
		$session = new Zend_Session_Namespace('Zend_Auth');

		$id = $this->getRequest()->getParam('id');
		$this->view->id = $id;
		$this->view->actionName = 'signupplan';
		if ($id != $session->signup_session_id) {
			$this->_redirect(WEBSITE_URL . 'customers/signup');
		}

		$this->view->actionName = 'signup';

		$subscriptions = new subscriptions();
		$subscriptions_data = $subscriptions->fetchAll($subscriptions->select()->where("id!=1")->order('display_order asc'));
		$this->view->subscriptions = $subscriptions_data;
		 
		

		$members = new members();
		$member_data = $members->fetchRow($members->select()->where('id=' . $session->signup_session_id));
		$this->view->data = $member_data;
		//$this->view->card_num = $member_data->card_num;
		$this->view->data->plan_id = $session->plan_id;

	}

	/**
	 * signupplansaveAction() - Method to save Signup Save Data
	 *
	 * @access public
	 * @return void
	 */
	public function signupplansaveAction()
	{
	//echo "signupplansave"; exit;

		$this->view->actionName = 'signupplansave';
		if ($this->_request->isPost()) {
			$filter = new Zend_Filter_StripTags();

			$plan_id = $filter->filter($this->_request->getPost('plan_id'));


			$id = $filter->filter($this->_request->getPost('id'));

			if (empty($plan_id)) {
				$login_error = new Zend_Session_Namespace('Zend_Auth');
				$login_error->loginError = "<font color='red''><b>Please select plan.</b></font>";
				$this->_redirect('customers/signupplan/id/' . $id);
			}

			$session = new Zend_Session_Namespace('Zend_Auth');
			$session->plan_id = $plan_id;

			if($plan_id == 5)
			{
				$orders = new orders();
				$subscriptions = new subscriptions();
				$memberTable = new members();
				$subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id=' . $plan_id));

				$session = new Zend_Session_Namespace('Zend_Auth');
				$members_data['plan_id'] = $plan_id;
				$members_data['total_remaining_calls'] = $subscriptions_data->code;
				$members_data['total_my_credited_calls'] = $subscriptions_data->code;
				$members_data['plan_start_date'] = date('Y-m-d');
				$members_data['plan_end_date'] = date('Y-m-d,H:i:s', strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " +7 day"));
				$members_data['recurring_start_date'] = date('Y-m-d');
				$members_data['recurring_end_date'] = date('Y-m-d,H:i:s', strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " +7 day"));
				$memberTable->update($members_data, 'id=' . $id);
				$members_orders_data['plan_id'] = $plan_id;
				$members_orders_data['card_type'] = '';
				$members_orders_data['expiry_month'] = '';
				$members_orders_data['expiry_year'] = '';
				$members_orders_data['name_on_card'] = '';
				$members_orders_data['cvv_number'] = '';
				$members_orders_data['card_number'] = '';
				$members_orders_data['user_status'] = 'Inactive';
				$members_orders_data['order_status'] = '';
				$members_orders_data['created_on'] = date('Y-m-d,H:i:s');
				$members_orders_data['amount'] = trim($subscriptions_data['price']);
				$orders->update($members_orders_data, 'member_current_session_with_time=' . $session->member_current_session_with_time);
				$this->_redirect('customers/signupplanview/id/' . $id);
			}

			if($plan_id != 5)
			{
				$card_type = $filter->filter($this->_request->getPost('card_type'));
				$expiry_month = $filter->filter($this->_request->getPost('expiry_month'));
				$expiry_year = $filter->filter($this->_request->getPost('expiry_year'));
				$card_name = $filter->filter($this->_request->getPost('card_name'));
				$address1   = $filter->filter($this->_request->getPost('address1'));
				$address2   = $filter->filter($this->_request->getPost('address2'));
				$city   = $filter->filter($this->_request->getPost('city'));
				$state   = $filter->filter($this->_request->getPost('state'));
				$zip   = $filter->filter($this->_request->getPost('zip'));
				$phone   = $filter->filter($this->_request->getPost('phone'));

				if ($this->_request->getPost('card_cvv') != '') {
					$card_cvv = $filter->filter($this->_request->getPost('card_cvv'));
				}
				if ($this->_request->getPost('card_cvv4') != '') {
					$card_cvv = $filter->filter($this->_request->getPost('card_cvv4'));
				}

				if ($this->_request->getPost('card_num') != '') {
					$card_num = $this->encrypt($filter->filter($this->_request->getPost('card_num')));
				}
				if ($this->_request->getPost('card_num15') != '') {
					$card_num = $this->encrypt($filter->filter($this->_request->getPost('card_num15')));
				}

				$memberTable = new members();

				//if username or password is empty show an error message
				if (empty($plan_id) || empty($card_type) || empty($expiry_month) || empty($expiry_year) || empty($card_name) || empty($card_cvv)
				|| empty($address1) || empty($city) ||  empty($state) || empty($zip) || empty($phone)
				) {
					if (empty($plan_id)) {
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError = "<font color='red''><b>Please select plan.</b></font>";
					} else if (empty($card_type)) {
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError = "<font color='red''><b>Please select card type.</b></font>";
						$this->view->card_type = $card_type;
					} else if (empty($expiry_month)) {
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError = "<font color='red''><b>Please select expiry month.</b></font>";
						$this->view->expiry_month = $expiry_month;
					} else if (empty($expiry_year)) {
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError = "<font color='red''><b>Please enter $expiry_year.</b></font>";
						$this->view->$expiry_year = $expiry_year;
					} else if (empty($card_name)) {
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError = "<font color='red''><b>Please enter name on card.</b></font>";
						$this->view->card_name = $card_name;
					} else if (empty($card_cvv)) {
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError = "<font color='red''><b>Please enter card CVV number.</b></font>";
						$this->view->card_cvv = $card_cvv;
					}
					else if(empty($address1))
					{
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError="<font color='red''><b>Please enter company name.</b></font>";
						$this->view->address1 = $address1 ;
					}
					else if(empty($city))
					{
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError="<font color='red''><b>Please enter city.</b></font>";
						$this->view->city = $city ;
					}
					else if(empty($state))
					{
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError="<font color='red''><b>Please enter state.</b></font>";
						$this->view->state= $state;
					}
					else if(empty($zip))
					{
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError="<font color='red''><b>Please enter zip.</b></font>";
						$this->view->zip = $zip ;
					}
					else if(empty($phone))
					{
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError="<font color='red''><b>Please enter phone.</b></font>";
						$this->view->state= $phone;
					}

					$this->_redirect('customers/signupplan/id/' . $id);
				} else {
					$orders = new orders();
					$subscriptions = new subscriptions();
					$subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id=' . $plan_id));

					$session = new Zend_Session_Namespace('Zend_Auth');
					$members_data['plan_id'] = $plan_id;
					$members_data['card_type'] = $card_type;
					$members_data['expiry_month'] = $expiry_month;
					$members_data['expiry_year'] = $expiry_year;
					$members_data['card_name'] = trim($card_name);
					$members_data['card_cvv'] = trim($card_cvv);
					$members_data['card_num'] = trim($card_num);
					$members_data['address1'] =  $address1;
					$members_data['address2'] =  $address2;
					$members_data['city'] =  $city;
					$members_data['state'] =  $state;
					$members_data['zip']=  $zip;
					$members_data['phone']=  $phone;

					$memberTable->update($members_data, 'id=' . $id);
					$members_orders_data['plan_id'] = $plan_id;
					$members_orders_data['card_type'] = $card_type;
					$members_orders_data['expiry_month'] = $expiry_month;
					$members_orders_data['expiry_year'] = $expiry_year;
					$members_orders_data['name_on_card'] = trim($card_name);
					$members_orders_data['cvv_number'] = trim($card_cvv);
					$members_orders_data['card_number'] = trim($card_num);
					$members_orders_data['user_status'] = 'Inactive';
					$members_orders_data['order_status'] = 'Pending';
					$members_orders_data['created_on'] = date('Y-m-d,H:i:s');
					$members_orders_data['amount'] = trim($subscriptions_data['price']);
					$orders->update($members_orders_data, 'member_current_session_with_time=' . $session->member_current_session_with_time);
					$this->_redirect('customers/signupplanview/id/' . $id);
				}
			}
		}
		else {
			$this->_redirect('customers/');
		}
	}
	
	
	/**
	 * saveData() - Method to update Signup Save Data for Internol used
	 *
	 * @access public
	 * @return void
	 */
	
	public function updateData()
	{
		
		if ($this->_request->isPost()) 
		{
			$filter = new Zend_Filter_StripTags();

			$plan_id = $filter->filter($this->_request->getPost('plan_id'));


			$id = $filter->filter($this->_request->getPost('id'));

			if (empty($plan_id)) {
				$login_error = new Zend_Session_Namespace('Zend_Auth');
				$login_error->loginError = "<font color='red''><b>Please select plan.</b></font>";
				$this->_redirect('customers/signupplan/id/' . $id);
			}

			$session = new Zend_Session_Namespace('Zend_Auth');
			$session->plan_id = $plan_id;

			if($plan_id == 5)
			{
				$orders = new orders();
				$subscriptions = new subscriptions();
				$memberTable = new members();
				$subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id=' . $plan_id));

				$session = new Zend_Session_Namespace('Zend_Auth');
				$members_data['plan_id'] = $plan_id;
				$members_data['total_remaining_calls'] = $subscriptions_data->code;
				$members_data['total_my_credited_calls'] = $subscriptions_data->code;
				$members_data['plan_start_date'] = date('Y-m-d');
				$members_data['plan_end_date'] = date('Y-m-d,H:i:s', strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " +7 day"));
				$members_data['recurring_start_date'] = date('Y-m-d');
				$members_data['recurring_end_date'] = date('Y-m-d,H:i:s', strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " +7 day"));
				$memberTable->update($members_data, 'id=' . $id);
				$members_orders_data['plan_id'] = $plan_id;
				$members_orders_data['card_type'] = '';
				$members_orders_data['expiry_month'] = '';
				$members_orders_data['expiry_year'] = '';
				$members_orders_data['name_on_card'] = '';
				$members_orders_data['cvv_number'] = '';
				$members_orders_data['card_number'] = '';
				$members_orders_data['user_status'] = 'Inactive';
				$members_orders_data['order_status'] = '';
				$members_orders_data['created_on'] = date('Y-m-d,H:i:s');
				$members_orders_data['amount'] = trim($subscriptions_data['price']);
				$orders->update($members_orders_data, 'member_current_session_with_time=' . $session->member_current_session_with_time);
				//$this->_redirect('customers/signupplanview/id/' . $id);
			}

			if($plan_id != 5)
			{
				$card_type = $filter->filter($this->_request->getPost('card_type'));
				$expiry_month = $filter->filter($this->_request->getPost('expiry_month'));
				$expiry_year = $filter->filter($this->_request->getPost('expiry_year'));
				$card_name = $filter->filter($this->_request->getPost('card_name'));
				$address1   = $filter->filter($this->_request->getPost('address1'));
				$address2   = $filter->filter($this->_request->getPost('address2'));
				$city   = $filter->filter($this->_request->getPost('city'));
				$state   = $filter->filter($this->_request->getPost('state'));
				$zip   = $filter->filter($this->_request->getPost('zip'));
				$phone   = $filter->filter($this->_request->getPost('phone'));

				if ($this->_request->getPost('card_cvv') != '') {
					$card_cvv = $filter->filter($this->_request->getPost('card_cvv'));
				}
				if ($this->_request->getPost('card_cvv4') != '') {
					$card_cvv = $filter->filter($this->_request->getPost('card_cvv4'));
				}

				if ($this->_request->getPost('card_num') != '') {
					$card_num = $filter->filter($this->_request->getPost('card_num'));
				}
				if ($this->_request->getPost('card_num15') != '') {
					$card_num = $filter->filter($this->_request->getPost('card_num15'));
				}

				$memberTable = new members();

				//if username or password is empty show an error message
				if (empty($plan_id) || empty($card_type) || empty($expiry_month) || empty($expiry_year) || empty($card_name) || empty($card_cvv)
				|| empty($address1) || empty($city) ||  empty($state) || empty($zip) || empty($phone)
				) {
					if (empty($plan_id)) {
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError = "<font color='red''><b>Please select plan.</b></font>";
					} else if (empty($card_type)) {
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError = "<font color='red''><b>Please select card type.</b></font>";
						$this->view->card_type = $card_type;
					} else if (empty($expiry_month)) {
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError = "<font color='red''><b>Please select expiry month.</b></font>";
						$this->view->expiry_month = $expiry_month;
					} else if (empty($expiry_year)) {
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError = "<font color='red''><b>Please enter $expiry_year.</b></font>";
						$this->view->$expiry_year = $expiry_year;
					} else if (empty($card_name)) {
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError = "<font color='red''><b>Please enter name on card.</b></font>";
						$this->view->card_name = $card_name;
					} else if (empty($card_cvv)) {
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError = "<font color='red''><b>Please enter card CVV number.</b></font>";
						$this->view->card_cvv = $card_cvv;
					}
					else if(empty($address1))
					{
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError="<font color='red''><b>Please enter company name.</b></font>";
						$this->view->address1 = $address1 ;
					}
					else if(empty($city))
					{
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError="<font color='red''><b>Please enter city.</b></font>";
						$this->view->city = $city ;
					}
					else if(empty($state))
					{
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError="<font color='red''><b>Please enter state.</b></font>";
						$this->view->state= $state;
					}
					else if(empty($zip))
					{
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError="<font color='red''><b>Please enter zip.</b></font>";
						$this->view->zip = $zip ;
					}
					else if(empty($phone))
					{
						$login_error = new Zend_Session_Namespace('Zend_Auth');
						$login_error->loginError="<font color='red''><b>Please enter phone.</b></font>";
						$this->view->state= $phone;
					}

					$this->_redirect('customers/signupplan/id/' . $id);
				} 
				else {
					$orders = new orders();
					$subscriptions = new subscriptions();
					$subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id=' . $plan_id));

					$session = new Zend_Session_Namespace('Zend_Auth');
					$members_data['plan_id'] = $plan_id;
					$members_data['card_type'] = $card_type;
					$members_data['expiry_month'] = $expiry_month;
					$members_data['expiry_year'] = $expiry_year;
					$members_data['card_name'] = trim($card_name);
					$members_data['card_cvv'] = 0;
					$members_data['card_num'] = substr(trim($card_num),-4);
					$members_data['address1'] =  $address1;
					$members_data['address2'] =  $address2;
					$members_data['city'] =  $city;
					$members_data['state'] =  $state;
					$members_data['zip']=  $zip;
					$members_data['phone']=  $phone;

					$memberTable->update($members_data, 'id=' . $id);
					$members_orders_data['plan_id'] = $plan_id;
					$members_orders_data['card_type'] = $card_type;
					$members_orders_data['expiry_month'] = $expiry_month;
					$members_orders_data['expiry_year'] = $expiry_year;
					$members_orders_data['name_on_card'] = trim($card_name);
					$members_orders_data['cvv_number'] = 0;
					$members_orders_data['card_number'] = substr(trim($card_num),-4);
					$members_orders_data['user_status'] = 'Inactive';
					$members_orders_data['order_status'] = 'Pending';
					$members_orders_data['created_on'] = date('Y-m-d,H:i:s');
					$members_orders_data['amount'] = trim($subscriptions_data['price']);
					
					$orders->update($members_orders_data, 'member_current_session_with_time=' . $session->member_current_session_with_time);
					//$this->_redirect('customers/signupplanview/id/' . $id);
				}
			}
		}
		else {
			$this->_redirect('customers/');
		}
		
		return $id;
		
		
	}

    /**
	 * function to signupplanviewAction
	 */
	public function signupplanviewAction()
	{
		
		//      error_reporting(E_ALL);
		//      ini_set('display_errors', 'On');
		$id = $this->getRequest()->getParam('id');
		$status = $this->getRequest()->getParam('status');
		
		
		$filter = new Zend_Filter_StripTags();
		//$id = $this->getRequest()->getParam('id');
		if($this->_request->isPost() && $status == "confirm")
		{
			$id = $this->updateData();
		}
		$this->view->actionName = 'signupplanview';
		$membersTable = new members();
		$ordersTable = new orders();

		$session = new Zend_Session_Namespace('Zend_Auth');
		if ($id == $session->signup_session_id)
		 {

		 	
		 	
			$membersTable->select()->where('id=' . $id);
			$members_data = $membersTable->fetchRow($membersTable->select()->where('id=' . $id));
			$subscriptions = new subscriptions();

			//$this->view->signup_session_id =  $signup_session_id;
			$this->view->firstname = $members_data['firstname'];
			$this->view->id = $members_data['id'];
			$this->view->lastname = $members_data['lastname'];
			$this->view->email = $members_data['email'];
			$this->view->companyname = $members_data['companyname'];
			$this->view->address1 = $members_data['address1'];
			$this->view->address2 = $members_data['address2'];
			$this->view->city = $members_data['city'];
			$this->view->state = $members_data['state'];
			$this->view->zip = $members_data['zip'];
			$this->view->phone = $members_data['phone'];
			$this->view->plan_id = $plan_id = $members_data['plan_id'];
			$subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id=' . $members_data['plan_id']));
			$this->view->plan_name = $subscriptions_data['name'];
			$this->view->price = $subscriptions_data['price'];
			//$this->decrypt($members_data['card_num']);
			$this->view->card_name = $members_data['card_name'];
			$this->view->order_id = $members_data['order_id'];
			$this->view->card_type = $members_data['card_type'];
			$this->view->card_num = $members_data['card_num'];
			$this->view->card_num_orginal = $this->_request->getPost('card_num');
			$this->view->card_cvv = $this->_request->getPost('card_cvv');
			$this->view->card_cvv_original = $this->_request->getPost('card_cvv');
			$this->view->expiry_month = $members_data['expiry_month'];
			$this->view->expiry_year = $members_data['expiry_year'];

            

			if ($this->_request->isPost() && $status == "purchase" )
			{

				//* BRAINTREE USER CREATE AND PAYMENT START HERE *//

				$error_code_with_message = '';
				if ($members_data['braintree_customer_id'] == '')
				{
					$result = Braintree_Customer::create(array(
                            'firstName' => $members_data['firstname'],
                            'lastName' => $members_data['lastname'],
							'company' => $members_data['companyname'],
							'email' => $members_data['email'],
                            'creditCard' => array(
                                'number' => trim($this->_request->getPost('orginalCardNum')),
                                'expirationDate' => $members_data['expiry_month'] . '/' . $members_data['expiry_year'],
                                'cvv' => trim($this->_request->getPost('orginalCvv')),
								'cardholderName' => $members_data['card_name']
					)
					));

				               // echo '<pre>';
									//print_r($result); 
									// echo '</pre>';
								//	die();
						
					if ($result->success)
					{
						$cid = ($result->customer->id);
						$token = ($result->customer->creditCards[0]->token);

						$members_data_braintree['braintree_customer_id'] = $cid;
						$members_data_braintree['braintree_token'] = $token;
						$members_data_braintree['braintree_error_code_with_message'] = $error_code_with_message;
						 
						if($result->success == 1)
						{
							$result2 = Braintree_Subscription::create(array(
                                      'paymentMethodToken' => $token,
                                      'planId' => $plan_id
							));

							                      //  echo '<pre>';
					                       // print_r($result2);
					                       // print_r($result2->transaction->processorResponseText);
					                       //  echo '</pre>';
							                     //   die();

							if ($result2->success == 1)
							{
								$sub_info = $result2->subscription;
								$members_data_braintree['total_remaining_calls'] = $subscriptions_data->code;
								$members_data_braintree['total_my_credited_calls'] = $subscriptions_data->code;
								$members_data_braintree['braintree_subscription_id'] = $sub_info->id;
								$membersTable->update($members_data_braintree, 'id=' . $id);

								$transaction_id = $sub_info->transactions[0]->id;
								$members_data_update['payment_status'] = 'Confirmed';
								$orders_data['order_status'] = 'Confirmed';
								$orders_data['payment_type'] = 'Direct';
								$orders_data['transaction_id'] = $transaction_id;
								$ordersTable->update($orders_data, 'id=' . $members_data['order_id']);

								$members_data_update['plan_status'] = '1';
								$members_data_update['payment_due_for_coming_month'] = '1';
								$members_data_update['recurring_start_date'] = date('Y-m-d');
								$members_data_update['recurring_end_date'] = date('Y-m-d,H:i:s', strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " +1 month"));
								$members_data_update['card_num'] = $members_data['card_num'];
								$members_data_update['card_cvv'] = '0';
								$membersTable->update($members_data_update, 'id=' . $id);
								$this->_redirect('customers/thanks/');

							} else {
								$members_data_update['payment_status'] = 'Fail';
								$orders_data['order_status'] = 'Fail';
								$this->view->paymentError = $result2->transaction->processorResponseText;
							}
							$members_data_update['card_num'] = '';
							$members_data_update['card_cvv'] = '0';
							$members_data_update['plan_status'] = '1';
							$members_data_update['payment_due_for_coming_month'] = '1';
							$members_data_update['recurring_start_date'] = date('Y-m-d');
							$members_data_update['recurring_end_date'] = date('Y-m-d,H:i:s', strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " +1 month"));
							$membersTable->update($members_data_update, 'id=' . $id);

						}

					}
					else
					{
						$members_data_update['card_num'] = '';
						$members_data_update['card_cvv'] = '0';
						foreach ($result->errors->deepAll() AS $error)
						{
							$error_code_with_message = ($error->code . ": " . $error->message . "\n");
						}
						echo $error_code_with_message;
					}
				}
				//* BRAINTREE USER CREATE AND PAYMENT END HERE *//

			}
		} else {
			$this->_redirect('customers/signup/');
		}
	}

	/**
	 * function to emailvalidationAction    */
	public function emailvalidationAction() {
		if ($this->_request->isPost()) {
			$filter = new Zend_Filter_StripTags();
			$id = $filter->filter($this->_request->getPost('id'));
			$email = $filter->filter($this->_request->getPost('email'));
			$member = new members();
			if ($id > 0) {
				$data = $member->fetchRow(array('id != ?' => $id, 'email = ?' => $email));
			} else {
				$data = $member->fetchRow(array('email = ?' => $email));
			}
			//print_r($data['firstname']); exit;
			echo count($data);
			exit;
		}
	}

	/**
	 * login() -Method to login
	 *
	 * @access public
	 * @return void
	 */
	public function loginAction() {

		$this->view->actionName = 'login';
		$session = new Zend_Session_Namespace('Zend_Auth');
		if ($session->member_id > 0 && $session->user_type == 0) {
			$this->_redirect('/forms/');
		} else if ($session->member_id > 0 && $session->user_type == 1) {
			$this->_redirect('/admin/customers/');
		}
		if ($this->_request->isPost()) {
			$filter = new Zend_Filter_StripTags();
			$email = $filter->filter(trim($this->_request->getPost('email')));
			$pass = $filter->filter($this->_request->getPost('pass'));

			//if username or password is empty show an error message
			if (empty($email) || empty($pass)) {
				if (empty($pass) && empty($email)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					/* $login_error->loginError="<font color='red''><b>Please enter email and password.</b></font>"; */
					$login_error->loginError = "<font color='red''><b>Your email address or password was not recognized. Please re-enter this information below.</b></font>";
				} else if (empty($email)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					/* $login_error->loginError="<font color='red''><b>Please enter email.</b></font>"; */
					$login_error->loginError = "<font color='red''><b>Your email address or password was not recognized. Please re-enter this information below.</b></font>";
					$this->view->email = $email;
				} else if (empty($pass)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					/* $login_error->loginError="<font color='red''><b>Please enter password.</b></font>"; */
					$login_error->loginError = "<font color='red''><b>Your email address or password was not recognized. Please re-enter this information below.</b></font>";
					$this->view->$pass = $pass;
				}

				$this->_redirect('/customers/login');
			} else {
				$filter = new Zend_Filter_StripTags();
				//echo $pass;
				//echo $email;
				$member = new members();
				$data = $member->fetchRow(array('pass = ?' => md5($pass), 'email = ?' => $email));

				if (strtolower($data['status']) == 'inactive') {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = 'There is a problem in your account and you need to contact customer service.';
					$this->_redirect('/customers/login');
				}
				//echo "=inactive =".strtolower($data['status']); exit;

				if (count($data) > 0) 
				{
					$session->member_id = $data['id'];
					$session->email = $data['email'];
					$session->user_type = $data['user_type'];
					$apikey = $data['api_key'];
			        if(empty($data['api_key'])  || $data['api_key'] == "")
			        {
			        	$memberTable = new members();
			        	$members_order_id['api_key'] = strtoupper($this->mykeygen(15).dechex($last_insert_id)); // used for api key
						$memberTable->update($members_order_id, 'id=' . $session->member_id);
						$apikey=$members_order_id['api_key'];
			        }
					$session->api_key = $apikey;

					if ($session->member_id > 0 && $session->user_type == 0) {
						$this->_redirect('/forms/');
					} else if ($session->member_id > 0 && $session->user_type == 1) {
						$this->_redirect('/admin/customers/');
					}

				} else {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = 'Your email address or password was not recognized. Please re-enter this information below.';
				}
			}
		} else {
			;//$this->checklogin();
		}
	}

	/**
	 * logout() -Method to logout
	 *
	 * @Zend_Auth :: getInstance mehtods to get the instant of class and clear identity
	 * @return void
	 */
	function logoutAction() {
		Zend_Auth::getInstance()->clearIdentity();
		$session = new Zend_Session_Namespace('Zend_Auth');
		if ($session->member_id > 0) {
			unset($session->member_id);
			unset($session->signup_session_id);
			session_destroy();
			// Zend_Session::destroy();
			$login_error = new Zend_Session_Namespace('Zend_Auth');
			$login_error->loginError = "<font color='black'><b>You have successfully signed out.</b></font>";
		}
		$this->_redirect('/customers/login');
		exit;
	}

	/**
	 * myaccount() -Method after logged In this page will open where user can see their Account information
	 *
	 */
	function myaccountAction() {
		$filter = new Zend_Filter_StripTags();
		$session = new Zend_Session_Namespace('Zend_Auth');

		if(!empty($session->member_id) && ($session->user_type==0) )
		{
			if($this->limitTrialUserAccess($session->member_id))
			$this->_redirect('/customers/editplandetails/id/'.$session->member_id);
		}

		$members = new members();
        $member_data = $members->fetchRow($members->select()->where('id='.$session->member_id));
        $this->view->api_key = $member_data['api_key'];
		$session->loginError = NULL;
		$this->view->actionName = 'myaccount';
		$this->view->actionNameLeftPanel = 'left_customers';
		$this->view->loggedin_customer_id = $session->member_id;

		if ((($this->getRequest()->getParam('id') == $session->member_id) || ($this->getRequest()->getPost('id') != $session->member_id)) && ($session->user_type == 1)) {
			$id = $this->getRequest()->getParam('id');  // I AM ADMIN
		} else if ($session->member_id > 0) {
			$id = $session->member_id; // I AM CUSTOMER
		} else {
			$this->_redirect('/customers/logout');
		}
		if (($id > 0)) {
			$membersTable = new members();
			$members_data = $membersTable->fetchRow($membersTable->select()->where('id=' . $id));
			$subscriptions = new subscriptions();

			//$this->view->signup_session_id =  $signup_session_id;
			$this->view->firstname = $members_data['firstname'];
			$this->view->id = $members_data['id'];
			$this->view->lastname = $members_data['lastname'];
			$this->view->email = $members_data['email'];
			$this->view->companyname = $members_data['companyname'];
			$this->view->address1 = $members_data['address1'];
			$this->view->address2 = $members_data['address2'];
			$this->view->city = $members_data['city'];
			$this->view->state = $members_data['state'];
			$this->view->zip = $members_data['zip'];
			$this->view->phone = $members_data['phone'];
			$this->view->user_type = $members_data['user_type'];
			$this->view->start_date = date('F d', strtotime($members_data['plan_start_date']));
			$this->view->end_date = date('F d', strtotime($members_data['plan_end_date']));
			$subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id=' . $members_data['plan_id']));
			$this->view->plan_id = $subscriptions_data['name'];
			//$this->view->plan_price = $subscriptions_data['price'];

			$this->view->card_name = $members_data['card_name'];
			$this->view->card_type = $members_data['card_type'];
			$this->view->card_num = $members_data['card_num'];
			$this->view->card_cvv = $members_data['card_cvv'];
			$this->view->expiry_month = $members_data['expiry_month'];
			$this->view->expiry_year = $members_data['expiry_year'];
			$this->view->plan_status = $members_data['plan_status'];
			$this->view->total_remaining_calls = $members_data['total_remaining_calls'];



			if($members_data['override_price'] != null && $members_data['override_price'] != 0)
			{
				$this->view->plan_price = $members_data['override_price'];
			}
			else
			{
				$this->view->plan_price = $subscriptions_data['price'];
			}


			if($members_data['plan_id'] == 5)
			{
				$this->view->expiry_year  = date('y', strtotime($members_data['plan_end_date']));
				$this->view->expiry_month = date('m', strtotime($members_data['plan_end_date']));
			}


		} else {
			$this->_redirect('customers/login');
		}
	}

    /**
	 * editaccountdetails() -Method for Editing the Account information of User
	 *
	 */
	function editaccountdetailsAction() {
		$filter = new Zend_Filter_StripTags();
		$session = new Zend_Session_Namespace('Zend_Auth');
		$membersTable = new members();
		$this->view->actionName = 'customers';
		$this->view->actionNameLeftPanel = 'editaccountdetails';
		$this->view->loggedin_customer_id = $session->member_id;

		if (($this->getRequest()->getParam('id') != 1) && ($session->user_type == 1)) {
			$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}

		if ((($this->getRequest()->getParam('id') != $session->member_id) || ($this->getRequest()->getPost('id') != $session->member_id)) && ($session->user_type == 1)) {
			$id = $this->getRequest()->getParam('id');
		} else if ($session->member_id > 0) {
			$id = $session->member_id;
		} else {
			$this->_redirect('/customers/logout');
		}


		if ($this->_request->isPost() && $this->_request->getPost('id')) {

			$firstname = $filter->filter($this->_request->getPost('firstname'));
			$lastname = $filter->filter($this->_request->getPost('lastname'));
			$companyname = $filter->filter($this->_request->getPost('companyname'));
			$email = $filter->filter($this->_request->getPost('email'));
			$address1 = $filter->filter($this->_request->getPost('address1'));
			$address2 = $filter->filter($this->_request->getPost('address2'));
			$city = $filter->filter($this->_request->getPost('city'));
			$state = $filter->filter($this->_request->getPost('state'));
			$zip = $filter->filter($this->_request->getPost('zip'));
			$phone = $filter->filter($this->_request->getPost('phone'));
			$id = $filter->filter($this->_request->getPost('id'));


			if (empty($firstname) || empty($lastname) || empty($email) || empty($address1) || empty($city) || empty($state) || empty($zip) || empty($phone)) {

				if (empty($firstname)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter first name.</b></font>";
				} else if (empty($lastname)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter username.</b></font>";
					$this->view->lastname = $lastname;
				} else if (empty($email)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter email.</b></font>";
					$this->view->email = $email;
				} else if (empty($address1)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter company name.</b></font>";
					$this->view->address1 = $address1;
				} else if (empty($city)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter city.</b></font>";
					$this->view->city = $city;
				} else if (empty($state)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter state.</b></font>";
					$this->view->state = $state;
				} else if (empty($zip)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter zip.</b></font>";
					$this->view->zip = $zip;
				} else if (empty($phone)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter phone.</b></font>";
					$this->view->state = $phone;
				}
			} else {
				$members_data['firstname'] = trim($firstname);
				$members_data['lastname'] = trim($lastname);
				$members_data['companyname'] = trim($companyname);
				$members_data['email'] = trim($email);
				$members_data['address1'] = trim($address1);
				$members_data['address2'] = trim($address2);
				$members_data['city'] = trim($city);
				$members_data['state'] = trim($state);
				$members_data['zip'] = trim($zip);
				$members_data['phone'] = trim($phone);
				$login_error = new Zend_Session_Namespace('Zend_Auth');
				$login_error->loginError = "<font color='black''><b>Account information updated successfully.</b></font>";
				$this->view->email = $email;
				$membersTable->update($members_data, 'id=' . $id);

				if ($session->user_type == 1) {
					$this->_redirect('customers/edit/id/' . $id);
				} else {
					$this->_redirect('customers/myaccount/id/' . $id);
				}
			}
		}

		if ($id > 0) {
			$members_data = $membersTable->fetchRow($membersTable->select()->where('id=' . $id));
			$this->view->firstname = $members_data['firstname'];
			$this->view->id = $members_data['id'];
			$this->view->lastname = $members_data['lastname'];
			$this->view->email = $members_data['email'];
			$this->view->companyname = $members_data['companyname'];
			$this->view->address1 = $members_data['address1'];
			$this->view->address2 = $members_data['address2'];
			$this->view->city = $members_data['city'];
			$this->view->state = $members_data['state'];
			$this->view->zip = $members_data['zip'];
			$this->view->phone = $members_data['phone'];
			$this->view->start_date = date('F d', strtotime($members_data['plan_start_date']));
			$this->view->end_date = date('F d', strtotime($members_data['plan_end_date']));
		} else {
			$this->_redirect('customers/login');
		}
	}

	/**
	 * editpaymentdetails() -Method for Editing the Payment information of User
	 *
	 */
	function editpaymentdetailsAction() {
		//		error_reporting(E_ALL);
		//		ini_set('display_errors','On');

		$filter = new Zend_Filter_StripTags();
		$session = new Zend_Session_Namespace('Zend_Auth');
		$membersTable = new members();
		$this->view->actionName = 'customers';
		$this->view->actionNameLeftPanel = 'editpaymentdetails';
		$this->view->loggedin_customer_id = $session->member_id;
		$session->message = false;



		if (($this->getRequest()->getParam('id') != 1) && ($session->user_type == 1)) {
			$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}



		if ((($this->getRequest()->getParam('id') != $session->member_id) || ($this->getRequest()->getPost('id') != $session->member_id)) && ($session->user_type == 1)) {
			$id = $this->getRequest()->getParam('id');
		} else if ($session->member_id > 0) {
			$id = $session->member_id;
		} else {
			$this->_redirect('/customers/logout');
		}



		if ($this->_request->isPost() && $id > 0) {
			$filter = new Zend_Filter_StripTags();
			$plan_id = $filter->filter($this->_request->getPost('plan_id'));
			$card_type = $filter->filter($this->_request->getPost('card_type'));
			$expiry_month = $filter->filter($this->_request->getPost('expiry_month'));
			$expiry_year = $filter->filter($this->_request->getPost('expiry_year'));
			$card_name = $filter->filter($this->_request->getPost('card_name'));

			if ($this->_request->getPost('card_cvv') != '') {
				$card_cvv = $filter->filter($this->_request->getPost('card_cvv'));
			}
			if ($this->_request->getPost('card_cvv4') != '') {
				$card_cvv = $filter->filter($this->_request->getPost('card_cvv4'));
			}

			if ($this->_request->getPost('card_num') != '') {
				$card_num = $this->encrypt($filter->filter($this->_request->getPost('card_num')));
			}
			if ($this->_request->getPost('card_num15') != '') {
				$card_num = $this->encrypt($filter->filter($this->_request->getPost('card_num15')));
			}



			//if username or password is empty show an error message
			if (empty($card_type) || empty($expiry_month) || empty($expiry_year) || empty($card_name) || empty($card_cvv)) {
				if (empty($card_type)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please select card type.</b></font>";
					$this->view->card_type = $card_type;
				} else if (empty($expiry_month)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please select expiry month.</b></font>";
					$this->view->expiry_month = $expiry_month;
				} else if (empty($expiry_year)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter $expiry_year.</b></font>";
					$this->view->$expiry_year = $expiry_year;
				} else if (empty($card_name)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter name on card.</b></font>";
					$this->view->card_name = $card_name;
				} else if (empty($card_cvv)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter card CVV number.</b></font>";
					$this->view->card_cvv = $card_cvv;
				}

				$this->_redirect('customers/editpaymentdetails/id/' . $id);
			} else {
				$members_data['card_type'] = $card_type;
				$members_data['expiry_month'] = $expiry_month;
				$members_data['expiry_year'] = $expiry_year;
				$members_data['card_name'] = $card_name;
				$members_data['card_cvv'] = 0;
				$members_data['card_num'] = substr(trim($this->_request->getPost('card_num_org')), -4);
				$login_error = new Zend_Session_Namespace('Zend_Auth');
				$login_error->loginError = "<font color='black''><b>Payment information updated successfully.</b></font>";
				$membersTable->update($members_data, 'id=' . $id);
				$members_data = $membersTable->fetchRow($membersTable->select()->where('id=' . $id));

				//* BRAINTREE USER CREATE AND PAYMENT START HERE *//
				$error_code_with_message = '';

				//if ($card_num == $members_data['card_num']) {

					$membersTable = new Members();
					$subscriptions = new subscriptions();
					$ordersTable = new orders();

					if(!empty($members_data['braintree_token'])){
						try 
						{
							$result = Braintree_CreditCard::update(
							$members_data['braintree_token'],
							array(
                                'number' => $this->_request->getPost('card_num_org'),
                                'expirationDate' => $expiry_month . '/' . $expiry_year,
								'cvv' => $this->_request->getPost('card_cvv'),
                                'options' => array(
                                  'makeDefault' => true
								)
							)
							);
						} catch (Exception $exc) {
							$this->view->paymentError = "Invalid Credit Card information.";
							$noDeep = true;
						}

						if($result->success);
						else
						$this->view->paymentError = "Invalid Credit Card information.";
					}
					else
					{
						$result = Braintree_Customer::create(array(
                                    'firstName' => $members_data['firstname'],
                                    'lastName' => $members_data['lastname'],
                                    'creditCard' => array(
                                        'number' => $this->_request->getPost('card_num_org'),
                                        'expirationDate' => $members_data['expiry_month'] . '/' . $members_data['expiry_year'],
                                        'cvv' => $this->_request->getPost('card_cvv')
						)
						));

						if ($result->success)
						{
							$subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id=5'));

							$cid = ($result->customer->id);
							$token = ($result->customer->creditCards[0]->token);

							$members_data_braintree['braintree_customer_id'] = $cid;
							$members_data_braintree['braintree_token'] = $token;
							$members_data_braintree['braintree_error_code_with_message'] = $error_code_with_message;

							if($result->success == 1)
							{
								$result2 = Braintree_Subscription::create(array(
                                              'paymentMethodToken' => $token,
                                              'planId' => 5
								));

								if ($result2->success == 1)
								{
									$sub_info = $result2->subscription;
									//$members_data_braintree['total_remaining_calls'] = $subscriptions_data->code;
									//$members_data_braintree['total_my_credited_calls'] = $subscriptions_data->code;
									$members_data_braintree['braintree_subscription_id'] = $sub_info->id;
									$membersTable->update($members_data_braintree, 'id=' . $id);

									$transaction_id = $sub_info->transactions[0]->id;
									$members_data_update['payment_status'] = 'Confirmed';
									$orders_data['order_status'] = 'Confirmed';
									$orders_data['payment_type'] = 'Direct';
									$orders_data['transaction_id'] = $transaction_id;
									$ordersTable->update($orders_data, 'id=' . $members_data['order_id']);
								} else {
									$members_data_update['payment_status'] = 'Fail';
									$orders_data['order_status'] = 'Fail';
									$this->view->paymentError = $result2->transaction->processorResponseText;
								}

								$members_data_update['plan_status'] = '1';
								$members_data_update['payment_due_for_coming_month'] = '1';
								$members_data_update['recurring_start_date'] = date('Y-m-d');
								$members_data_update['recurring_end_date'] = date('Y-m-d,H:i:s', strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " +1 month"));
								
								$members_data_update['card_num'] = $members_data['card_num'];
								$members_data_update['card_cvv'] = '0';
								$membersTable->update($members_data_update, 'id=' . $id);

								$this->_redirect('customers/editplandetails/id/'.$id);
							}

						} 
						else 
						{
							foreach ($result->errors->deepAll() AS $error) {
								$error_code_with_message = ($error->code . ": " . $error->message . "\n");
							}
							echo $error_code_with_message;
						}

					}

					//print_r($result);


					if ($result->success) {
						$cid = ($result->customer->id);
						$token = ($result->customer->creditCards[0]->token);
						$session->message = true;
					} else {
						if(!isset ($noDeep)){
							foreach (@$result->errors->deepAll() AS $error) {
								$error_code_with_message = ($error->code . ": " . $error->message . "\n");
							}
						}
						$session->message = false;
					}
					//                    $members_data_braintree['braintree_customer_id'] = $cid;
					//                    $members_data_braintree['braintree_token'] = $token;
					//                    $members_data_braintree['braintree_error_code_with_message'] = $error_code_with_message;
					//                    $membersTable->update($members_data_braintree, 'id=' . $id);
				//}
				//* BRAINTREE USER CREATE AND PAYMENT END HERE *//
				if ($session->user_type == 1) {
					$this->_redirect('customers/edit/id/' . $id);
				} else {
					;//$this->_redirect('customers/myaccount/id/' . $id);
				}
			}
		}

		if (($id > 0) && !empty($id)) {
			$membersTable = new members();
			$members_data = $membersTable->fetchRow($membersTable->select()->where('id=' . $id));
			$this->view->id = $members_data['id'];
			 
			$this->view->firstname = $members_data['firstname'];
			$this->view->lastname = $members_data['lastname'];
			$this->view->card_name = $members_data['card_name'];
			$this->view->card_type = $members_data['card_type'];
			if($members_data['plan_id'] != 5)
			{
				$this->view->card_num = "";//"XXXXXXXXXXXX" . htmlspecialchars(stripslashes($members_data['card_num']));
				$this->view->card_num_org = '';
			}
			$this->view->card_cvv = '0';//$members_data['card_cvv'];
			$this->view->expiry_month = $members_data['expiry_month'];
			$this->view->expiry_year = $members_data['expiry_year'];

		} else {
			$this->_redirect('customers/login');
		}
	}

	/**
	 * editplandetailsAction() -Method for Editing the Plan information of User
	 *
	 */
	function editplandetailsAction() {

		$filter = new Zend_Filter_StripTags();
		$session = new Zend_Session_Namespace('Zend_Auth');
		$this->view->actionName = 'customers';
		$this->view->actionNameLeftPanel = 'editplandetails';
		$this->view->loggedin_customer_id = $session->member_id;

		if (($this->getRequest()->getParam('id') != 1) && ($session->user_type == 1)) {
			$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}

		if ((($this->getRequest()->getParam('id') != $session->member_id) || ($this->getRequest()->getPost('id') != $session->member_id)) && ($session->user_type == 1)) {
			$id = $this->getRequest()->getParam('id');
		} else if ($session->member_id > 0) {
			$id = $session->member_id;
		} else {
			$this->_redirect('/customers/logout');
		}
		$memberTable = new members();
		$membersdata1 = $memberTable->fetchRow($memberTable->select()->where("id=" . $id));
		$plan_end_date = date("F j, Y", strtotime($membersdata1['plan_end_date']));


		if ($this->_request->isPost()) {

			$filter = new Zend_Filter_StripTags();
			$plan_id = $filter->filter($this->_request->getPost('plan_id'));
			$org_plan_id = $filter->filter($this->_request->getPost('org_plan_id'));
			$org_price = $filter->filter($this->_request->getPost('org_price'));
			$old_plan_end_date = $filter->filter($this->_request->getPost('org_plan_end_date'));
			$_SESSION['old_plan_end_date'] = $old_plan_end_date;
			$_SESSION['org_price'] = $org_price;
			//$session->org_price=$org_price;

			$id = $filter->filter($this->_request->getPost('id'));
			$session->customer_id = $id;

			//$memberTable = new members();
			//if username or password is empty show an error message
			if (empty($plan_id)) {
				if (empty($plan_id)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please select plan.</b></font>";
				}
				$this->_redirect('customers/editplandetails/id/' . $id);
			} else {
				if ($plan_id == $org_plan_id) {

				} else {

					/* 	Get prorated data Start here */
					$subscriptions = new subscriptions();
					$subscriptions_new_data = $subscriptions->fetchRow($subscriptions->select()->where("id=" . $plan_id));
					$subscriptions_org_data = $subscriptions->fetchRow($subscriptions->select()->where("id=" . $org_plan_id));
					$membersdata = $memberTable->fetchRow($memberTable->select()->where("id=" . $id));
					$planname = $subscriptions_new_data['name'];
					$price = $subscriptions_new_data['price'];
					//echo "test";

					$daysremaining = (strtotime($membersdata ['plan_end_date']) - strtotime(date('Y-m-d'))) / 86400;

					$dayin_month = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));

					//echo $daysremaining=((strtotime($membersdata ['plan_end_date'])-strtotime($membersdata ['plan_start_date']))/(60*60*60)); exit;

					if (($subscriptions_new_data['price'] > $subscriptions_org_data['price']) && $daysremaining > 0)
					{
						$prorated_price = number_format((($subscriptions_new_data['price'] * $daysremaining) / $dayin_month) - (($subscriptions_org_data['price'] * $daysremaining) / $dayin_month), 2);
					} else if (($subscriptions_new_data['price'] > $subscriptions_org_data['price']) && $daysremaining <= 0) {
						$prorated_price = number_format($subscriptions_new_data['price'], 2);
					} else {
						$prorated_price = 0;
						// Prorate will not work here
					}
					/* Get prorated data End here */


					$orders = new orders();
					$members_order_data['plan_id'] = $plan_id;
					$members_order_data['member_id'] = $id;
					$members_order_data['created_on'] = date('Y-m-d,H:i:s');
					$members_order_data['order_status'] = 'Pending';
					$members_order_data['card_type'] = $membersdata['card_type'];
					$members_order_data['cvv_number'] = $membersdata['card_cvv'];
					$members_order_data['name_on_card'] = $membersdata['card_name'];
					$members_order_data['card_number'] = $membersdata['card_num'];
					$members_order_data['expiry_month'] = $membersdata['expiry_month'];
					$members_order_data['expiry_year'] = $membersdata['expiry_year'];
					$members_order_data['user_status'] = $membersdata['status'];
					$members_order_data['amount'] = $prorated_price;

					$last_insert_order_id = $orders->insert($members_order_data);
					if ($prorated_price == 0 && ($subscriptions_new_data['price'] < $subscriptions_org_data['price'])) {

						$result = Braintree_Subscription::update($membersdata['braintree_subscription_id'], array(
                            'paymentMethodToken' => $membersdata['braintree_token'],
                            'planId' => $plan_id,
                            'trialDuration' => 0
						));

						if ($result->success == 1) {
							$sub_info = $result->subscription;
							$members_data_update['total_remaining_calls'] = $subscriptions_new_data->code + $membersdata1['total_remaining_calls'];
							$members_data_update['total_my_credited_calls'] = $subscriptions_new_data->code + $membersdata1['total_my_credited_calls'];

							$members_data_update['payment_status'] = 'Confirmed';
							$orders_data['order_status'] = 'Confirmed';
						} else {
							$members_data_update['payment_status'] = 'Fail';
							$orders_data['order_status'] = 'Fail';
						}

						$members_data['plan_status'] = 4;
						if ($membersdata1['recurring_start_date'] == '' && $members_data['recurring_end_date'] == '') {
							$members_data_update['recurring_start_date'] = date('Y-m-d');
							$members_data_update['recurring_end_date'] = date('Y-m-d', strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " +1 month"));
						}

						$members_data_update['plan_id'] = $plan_id;
						$orders_data['payment_type'] = 'Direct';
						$orders_data['transaction_id'] = $sub_info->transactions[0]->id;
						$orders->update($orders_data, 'id=' . $last_insert_order_id);
						$memberTable->update($members_data_update, 'id=' . $id);

						$login_error = new Zend_Session_Namespace('Zend_Auth');
						//$login_error->loginError="<font color='black''><b>Your plan has been Downgraded successfully.</b></font>";
						$mail = new Zend_Mail();


						$mail_view = new Zend_View();

						$mail_view->addScriptPath($this->view->getScriptPaths());
						$mail_view->firstname = $membersdata ['firstname'];
						$mail_view->prorated_price  = $prorated_price;
						$mail_view->planname = $planname;
						$mail_view->price = $price;

						$Body =$mail_view->render('edit_plan_mail_template.phtml');


						//                        $Body = "<div style='color:black;'>Dear " . ucfirst($membersdata ['firstname']) . ",<br><br>";
						//                        $Body.="We have processed your request to downgrade your account.
						//								Your new subscription plan entitles you to " . $planname . " at a rate of $" . $price;
						//                        if ($prorated_price == 0) {
						//                            $Body.="Remember, you can upgrade and downgrade your account as frequently as you would like.<br><br>";
						//                        } else {
						//                            $Body.="The difference of $" . number_format($prorated_price, 2) . " has been charged to your account. Remember, you can upgrade and downgrade your account as frequently as you would like.<br><br>";
						//                        }
						//
						//                        $Body.="Thank you, <br><span style='color:black;'> " . WEBSITE_NAME . " Team</span></div>";
						$mail->setBodyHtml($Body);
						$mail->setFrom(SITE_SUPPORT_EMAIL, WEBSITE_NAME);
						$mail->addTo($membersdata ['email'], 'Admin');
						$mail->setSubject('Your subscription Downgraded on ' . WEBSITE_NAME);
						$result = $mail->send();
						if ($session->user_type == 1) {
							$this->_redirect('customers/edit/id/' . $id);
						} else {
							$this->_redirect('customers/myaccount/id/' . $id);
						}
					} else {
						// HERE WE WILL USE SIGNUPPLANVIEW PAGE AND THEN PAYMENT GATEWAY
						//echo "1006 afer clickin gon paid"; exit;

						$result = Braintree_Subscription::update($membersdata1['braintree_subscription_id'], array(
                            'paymentMethodToken' => $membersdata1['braintree_token'],
                            'planId' => $plan_id,
                            'trialDuration' => 0
						));

						if ($result->success == 1) {
							$sub_info = $result->subscription;
							$members_data_update['total_remaining_calls'] = $subscriptions_new_data->code + $membersdata1['total_remaining_calls'];
							$members_data_update['total_my_credited_calls'] = $subscriptions_new_data->code + $membersdata1['total_my_credited_calls'];

							$members_data_update['payment_status'] = 'Confirmed';
							$orders_data['order_status'] = 'Confirmed';
						} else {
							$members_data_update['payment_status'] = 'Fail';
							$orders_data['order_status'] = 'Fail';
						}

						$members_data_update['plan_status'] = '5';
						if ($membersdata1['recurring_start_date'] == '' && $members_data['recurring_end_date'] == '') {
							$members_data_update['recurring_start_date'] = date('Y-m-d');
							$members_data_update['recurring_end_date'] = date('Y-m-d', strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " +1 month"));
						}

						$members_data_update['plan_id'] = $plan_id;
						$orders_data['payment_type'] = 'Direct';
						$orders_data['transaction_id'] = $sub_info->transactions[0]->id;
						$orders->update($orders_data, 'id=' . $last_insert_order_id);
						$memberTable->update($members_data_update, 'id=' . $id);

						//$login_error->loginError="<font color='black''><b>Plan information updated successfully.</b></font>";
						$mail = new Zend_Mail();
						$Body = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'>
   						<img src='". WEBSITE_IMG_URL. "branding.png' /> <hr /><div style='color:black;'>Dear " . ucfirst($membersdata ['firstname']) . ",<br><br>";
						$Body.="We have processed your request to upgrade your account.
									Your new subscription plan entitles you to " . $planname . " at a rate of $" . $price;

						if ($prorated_price == 0) {
							$Body.="Remember, you can upgrade and downgrade your account as frequently as you would like.<br><br>";
						} else {
							$Body.="The difference of $" . number_format($prorated_price, 2) . " has been charged to your account. Remember, you can upgrade and downgrade your account as frequently as you would like.<br><br>";
						}
						$Body.="Thank you, <br><span style='color:black;'> " . WEBSITE_NAME . " Team</span></div></div>";
						$mail->setBodyHtml($Body);
						$mail->setFrom(SITE_SUPPORT_EMAIL, WEBSITE_NAME);
						$mail->addTo($membersdata ['email'], 'Admin');
						$mail->setSubject('Your subscription upgraded on ' . WEBSITE_NAME);
						$result = $mail->send();
						if ($session->user_type == 1) {
							$this->_redirect('customers/edit/id/' . $id);
						} else {
							$this->_redirect('customers/myaccount/id/' . $id);
						}
						//$this->_redirect('customers/paymentforupdatedplan/');

						/* 	$this->_redirect('customers/payment/status/1/order_id/'.$last_insert_order_id.'/plan_id/'.$plan_id.'/amount/'.$prorated_price);	 // in case of successfull
						 $this->_redirect('customers/payment/status/0/order_id/'.$last_insert_order_id.'/plan_id/'.$plan_id);	 // in case of fail */
					}

					/* here it will go for Payment Gateway, if payment is successfull
					 then return it to payment succssfull and update the orders table and
					 members table.
					 if payment is not successfull then delete the orders table last inserted row
					 */
					//$members_data['order_id']= $last_insert_order_id;
					//$members_data['plan_id'] = $plan_id;
					//$memberTable->update($members_data, 'id='.$id);
				}
			}
		}


		if (($id > 0) && !empty($id)) {
			$membersTable = new members();
			$subscriptions = new subscriptions();
			//$subscriptions_data = $subscriptions->fetchAll($subscriptions->select()->where("1"));
			$subscriptions_data = $subscriptions->fetchAll($subscriptions->select()->where("id!=5")->order("display_order asc"));
			$this->view->subscriptions = $subscriptions_data;

			$members_data = $membersTable->fetchRow($membersTable->select()->where('id=' . $id));
			$this->view->firstname = $members_data['firstname'];
			$this->view->lastname = $members_data['lastname'];
			$this->view->id = $id;
			$this->view->plan_id = $members_data['plan_id'];
			$this->view->plan_status = $members_data['plan_status'];
			$this->view->plan_end_date = $plan_end_date;

			if ($this->_request->isPost()) {

			} else {
				$login_error = new Zend_Session_Namespace('Zend_Auth');
				if ($members_data['plan_status'] == 2) {
					//$login_error->loginError="<font color='black''><b>Your plan will be cancelled at the end of the billing cycle.</b></font>";
				}
				if ($members_data['plan_status'] == 4) {
					//$login_error->loginError="<font color='black''><b>Your plan will be downgraded at the end of the billing cycle.</b></font>";
				}
				if ($members_data['plan_status'] == 5) {
					//$login_error->loginError="<font color='black''><b>Your plan has been upgraded to the $19.95 plan.</b></font>";
				}
			}
		} else {
			$this->_redirect('customers/login');
		}
	}

	/**
	 * payment() - method for updating payment information of user, it will reflect in members and orders table
	 * @loginError an variable to show message
	 * @access public
	 * @return void
	 */
	function paymentAction() {
		$filter = new Zend_Filter_StripTags();
		$this->view->actionName = 'payment';
		// NOTE:- WE ARE SENDING CUSTOMERID_LOGGEDINUSERID_USERTYPE IN THE CUSTOMER ID SO
		// WE USE HERE EXPLODE FUNCTION.
		$customer_information = explode('_', $filter->filter($this->_request->getPost('customer_id')));
		$customer_id = $customer_information['0'];
		//print_r($customer_information); exit;

		$order_id = $filter->filter($this->_request->getPost('order_id'));
		$transaction_id = $filter->filter($this->_request->getPost('transaction_id'));
		$status = $filter->filter($this->_request->getPost('status'));
		if ($transaction_id != '' && $status == 'authorized' && $customer_id > 0 && $order_id > 0) {
			$session = new Zend_Session_Namespace('Zend_Auth');   //
			$id = $customer_id; // Payment Gatway will return this value,

			$ordersTable = new orders();
			$orders_new_data = $ordersTable->fetchRow($ordersTable->select()->where("id=" . $order_id));
			$plan_id = $orders_new_data ['plan_id'];
			$prorated_price = $orders_new_data ['amount']; // Chargeable amount.
			/* print_r($orders_new_data);
			 echo "<br>"; */
			$memberTable = new members();
			$membersdata = $memberTable->fetchRow($memberTable->select()->where("id=" . $customer_id));
			/* print_r($membersdata);
			 exit; */
			$subscriptions = new subscriptions();
			$subscriptions_new_data = $subscriptions->fetchRow($subscriptions->select()->where("id=" . $plan_id));

			$planname = $subscriptions_new_data['name'];
			$price = $subscriptions_new_data['price'];

			if ($status == 'authorized' && $order_id != '') {
				$members_data['plan_id'] = $plan_id;

				if ($customer_information['2'] == '1') {

				} else {
					if ($membersdata['status'] == 'Inactive') {
						$members_data['plan_status'] = 1; // This is for New User Payement Case
						$first_time_user = '1';
					} else if ($membersdata['status'] == 'Active') {
						$members_data['plan_status'] = 5; // This is for Edit Case
						$first_time_user = '0';
					}
				}
				$members_data['order_id'] = $order_id;
				$members_data['payment_status'] = 'Confirmed';
				$memberTable->update($members_data, 'id=' . $customer_id);

				$orders_data['order_status'] = 'Confirmed';
				$orders_data['transaction_id'] = $transaction_id;
				$ordersTable->update($orders_data, 'id=' . $order_id);
				$login_error = new Zend_Session_Namespace('Zend_Auth');
				if ($first_time_user == 1) {
					$session = new Zend_Session_Namespace('Zend_Auth');
					$session->signup_session_id = $customer_id;
					$this->_redirect('/customers/thanks');
					exit;
				} else {

					$session->member_id = $customer_information['1'];
					$session->user_type = $customer_information['2'];

					//$login_error->loginError="<font color='black''><b>Plan information updated successfully.</b></font>";
					$mail = new Zend_Mail();
					$Body = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'>
    <img src='". WEBSITE_IMG_URL. "branding.png' /> <hr /><div style='color:black;'>Dear " . ucfirst($membersdata ['firstname']) . ",<br><br>";
					$Body.="We have processed your request to upgrade your account.
			Your new subscription plan entitles you to " . $planname . " at a rate of $" . $price;

					if ($prorated_price == 0) {
						$Body.="Remember, you can upgrade and downgrade your account as frequently as you would like.<br><br>";
					} else {
						$Body.="The difference of $" . number_format($prorated_price, 2) . " has been charged to your account. Remember, you can upgrade and downgrade your account as frequently as you would like.<br><br>";
					}
					$Body.="Thank you, <br><span style='color:black;'> " . WEBSITE_NAME . " Team</span></div></div>";
					$mail->setBodyHtml($Body);
					$mail->setFrom(SITE_SUPPORT_EMAIL, WEBSITE_NAME);
					$mail->addTo($membersdata ['email'], 'Admin');
					$mail->setSubject('Your subscription upgraded on ' . WEBSITE_NAME);
					$result = $mail->send();
					if ($session->user_type == 1) {
						$this->_redirect('customers/edit/id/' . $customer_information['0']);
					} else {
						$this->_redirect('customers/myaccount/id/' . $customer_information['1']);
					}
				}

				//$this->_redirect('/customers/editplandetails/');
				$this->_redirect('/customers/payments/payment/success/');
			} elseif ($this->getRequest()->getParam('status') == 0 && $order_id != '') {
				$login_error = new Zend_Session_Namespace('Zend_Auth');
				$login_error->loginError = "<font color='black''><b>Payment fail please try again.</b></font>";
				if ($session->user_type == 1) {
					$this->_redirect('customers/edit/id/' . $id);
				} else {
					$this->_redirect('customers/myaccount/id/' . $id);
				}
				//$this->_redirect('/customers/editplandetails/');
				$this->_redirect('/customers/payments/payment/fail/');
			} else {
				$this->_redirect('/customers/logout');
			}
		}
	}

	/**
	 * payments() - method for showing message to user that payment is confirmed or not
	 * @access public
	 * @return void
	 */
	function paymentsAction() {
		$filter = new Zend_Filter_StripTags();
		$this->view->actionName = 'payments';
		//$id=$session->member_id;
		//$order_id=$this->getRequest()->getParam('order_id');// Payment Gatway will return
		//this value , currently it is hard coded


		if ($this->getRequest()->getParam('payment') == 'fail') {
			$login_error = new Zend_Session_Namespace('Zend_Auth');
			$login_error->loginError = "<font color='red''><b>Your payment is failed. Please try again.</b></font>";
			$this->_redirect('/customers/logout');
		} elseif ($this->getRequest()->getParam('payment') == 'success') {
			$login_error = new Zend_Session_Namespace('Zend_Auth');
			$login_error->loginError = "<font color='red''><b>Your payment is successfull. Please login.</b></font>";
			$this->_redirect('/customers/logout');
		} else {
			$this->_redirect('/customers/logout');
		}
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
	public function forgotpasswordAction() {

		if ($this->_request->isPost()) {
			$filter = new Zend_Filter_StripTags();
			$email = $filter->filter($this->_request->getPost('email'));

			//if username or password is empty show an error message
			if (empty($email)) {
				if (empty($email)) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='red''><b>Please enter email.</b></font>";
					$this->view->email = $email;
				}
				$this->_redirect('/customers/forgotpassword');
			} else {
				$filter = new Zend_Filter_StripTags();
				$member = new members();
				$data = $member->fetchRow(array('email = ?' => $email));
				if (count($data) > 0) {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='black''><b>Please check your email for resetting password.</b></font>";
					$members_data['reset_link'] = md5(time());
					$member->update($members_data, 'id=' . $data['id']);
					$mail = new Zend_Mail();

					$mail_view = new Zend_View();

					$mail_view->addScriptPath($this->view->getScriptPaths());
					$mail_view->firstname = $data->firstname;
					$mail_view->reset_link = $members_data['reset_link'];

					$Body =$mail_view->render('password_reset_mail_template.phtml');

					//OLD MAIL STYLE .................... NOW WE ARE RENDERING MAIL FROM TEMPLATE;
					//                    $Body = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'>
					//                        <img src='".WEBSITE_IMG_URL."branding.png' /> <hr />";
					//                    $Body .="<div style='color:black;'>Hello " . $data->firstname . ",<br><br>";
					//                    $Body .="For resetting your password please click given URL.<br><br>";
					//                    $Body .="<a href=" . WEBSITE_URL . "customers/resetpassword/reset_link/" . $members_data['reset_link'] . ">Click here</a>";
					//                    $Body .="<br><br> Thanks <br><span style='color:black;'> " . WEBSITE_NAME . "</span></div>";
					//                    $Body .="</div>";

					$mail->setBodyHtml($Body);
					$mail->setFrom(SITE_SUPPORT_EMAIL, WEBSITE_NAME);
					$mail->addTo($data->email, 'Admin');
					$mail->setSubject('Reset your Password on ' . WEBSITE_NAME);
					$result = $mail->send();
					//echo "mail will be sent here"; exit;
					//$this->_redirect('/customers/home');
				} else {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = 'Your email address was not recognized. Please re-enter this information below.';
				}
			}
		}
		//$this->_redirect('/customers/forgotpassword');
	}

	/**
	 * activate() - method to Activate account
	 *
	 * @admin an object of Admin Model
	 * @mail an object of Zend_Mail class model
	 * @loginError an variable to show message
	 * @access public
	 * @return void
	 */
	public function activateAction() {
		$filter = new Zend_Filter_StripTags();
		$member = new members();
		$orders = new orders();
		$reset_link = $this->getRequest()->getParam('link');
		$data = $member->fetchRow(array('reset_link = ?' => $reset_link));

		if (count($data) > 0) {
			$session = new Zend_Session_Namespace('Zend_Auth');
			$members_data['reset_link'] = '';
			$members_data['status'] = 'Active';
			$member->update($members_data, 'id=' . $data['id']);
			$orders_data['user_status'] = 'Active';
			$orders->update($orders_data, 'id=' . $data['id']);
			$login_error = new Zend_Session_Namespace('Zend_Auth');
			$login_error->loginError = "<font color='black'><b>Your Account has been activated successfully.</b></font>";
			//$this->_redirect('/customers/resetpassword');
			$this->_redirect('/customers/login');
		} else {
			$login_error = new Zend_Session_Namespace('Zend_Auth');
			$login_error->loginError = "<font color='black'><b>Your link is either expired or already used.</b></font>";
			$this->_redirect('/customers/login');
		}
	}

	/**
	 * resetpassword() - method to send email with link for resetting the password
	 *
	 * @admin an object of Admin Model
	 * @mail an object of Zend_Mail class model
	 * @loginError an variable to show message
	 * @access public
	 * @return void
	 */
	public function resetpasswordAction() {
		$filter = new Zend_Filter_StripTags();
		$member = new members();
		$reset_link = $this->getRequest()->getParam('reset_link');
		$data = $member->fetchRow(array('reset_link = ?' => $reset_link));

		if ($this->_request->isPost()) {

			$filter = new Zend_Filter_StripTags();
			$pass = $filter->filter($this->_request->getPost('pass'));
			$re_pass = $filter->filter($this->_request->getPost('re_pass'));

			//if username or password is empty show an error message
			if (empty($pass)) {
				$login_error = new Zend_Session_Namespace('Zend_Auth');
				$login_error->loginError = "<font color='red''><b>Please enter password.</b></font>";
				$this->_redirect('/customers/resetpassword');
			} else {
				if (count($data) > 0) {
					$session = new Zend_Session_Namespace('Zend_Auth');
					$members_data['pass'] = md5($pass);
					$members_data['reset_link'] = '  ';
					$member->update($members_data, 'id=' . $data['id']);
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = "<font color='black''><b>Your password has been successfully changed.</b></font>";
					//$this->_redirect('/customers/resetpassword');
					$this->_redirect('/customers/login');
				} else {
					$login_error = new Zend_Session_Namespace('Zend_Auth');
					$login_error->loginError = 'Your link is either expired or already used.';
				}
			}
		}

		if (count($data) > 0) {

		} else {
			$login_error = new Zend_Session_Namespace('Zend_Auth');
			$login_error->loginError = 'Your link is either expired or already used.';
			$this->_redirect('/customers/forgotpassword');
		}

		if (strlen($reset_link) == 32) {
			$this->view->reset_link = $this->getRequest()->getParam('reset_link');
		} else {
			$this->_redirect('/customers/login');
		}
	}

	// Delete customer.
	public function deleteAction() {

		if ($this->getRequest()->getParam('id')) {
			$member = new members();
			$forms = new Forms();
			$emailnotifications = new emailnotification();
			$customerruless = new customerrules();
			$formreports = new formreport();
			$formsStds = new FormsStd();
			$inquirys = new inquiry();
			$opthrss = new opthrs();
			$orders = new orders();

			$orders->delete("member_id=" . $this->getRequest()->getParam('id'));
			$customerruless->delete("customer_id=" . $this->getRequest()->getParam('id'));
			$emailnotifications->delete("user_id=" . $this->getRequest()->getParam('id'));
			$formsStds->delete("customer_id=" . $this->getRequest()->getParam('id'));
			$forms->delete("customer_id=" . $this->getRequest()->getParam('id'));
			$inquirys->delete("customer_id=" . $this->getRequest()->getParam('id'));
			$opthrss->delete("customer_id=" . $this->getRequest()->getParam('id'));
			$formreports->delete("customer_id=" . $this->getRequest()->getParam('id'));
			$member->delete("id=" . $this->getRequest()->getParam('id'));
			$deleteError = new Zend_Session_Namespace('Zend_Auth');
			$deleteError->deleteError = "<font color='black'><b>Customer has been deleted successfully!</b></font>";
			$this->_redirect('/admin/customers');
			exit;
		}
	}

	/**
	 * customerchangepasswordAction() - method to change customer password by admin
	 *
	 * @admin an object of Admin Model
	 * @Errormsg an variable to show message
	 * @access  admin user
	 * @return void
	 */
	public function customerchangepasswordAction() {
		$member = new members();
		$filter = new Zend_Filter_StripTags();
		$session = new Zend_Session_Namespace('Zend_Auth');
		$this->view->actionNameLeftPanel = 'changepassword';
		$this->view->loggedin_customer_id = $session->member_id;
		$this->view->user_type = $session->user_type;
		$i_m_admin = 0;
		if (($this->getRequest()->getParam('id') != 1) && ($session->user_type == 1)) {
			$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}

		if ((($this->getRequest()->getParam('id') != $session->member_id) || ($this->getRequest()->getPost('id') != $session->member_id)) && ($session->user_type == 1)) {
			if ($this->getRequest()->getParam('id') != '') {
				$id = $this->getRequest()->getParam('id');
			} else {
				$id = $this->getRequest()->getPost('id');
			}

			$i_m_admin = 1;
			$this->view->i_m_admin = 1;
		} else if ($session->member_id > 0) {
			$id = $session->member_id;
		} else {
			$this->_redirect('/customers/logout');
		}

		if (($this->getRequest()->getParam('id') != 1) && ($session->user_type == 1)) {
			$this->view->actionName = 'customers';
		}

		$this->view->id = $id;

		$members_data = $member->fetchRow($member->select()->where('id=' . $id));
		$this->view->firstname = $members_data['firstname'];
		$this->view->lastname = $members_data['lastname'];

		if ($this->getRequest()->isPost()) {

			$old_password = $this->getRequest()->getParam('password');
			$new_password = $this->getRequest()->getParam('newpassword');
			$conpassword = $this->getRequest()->getParam('conpassword');

			$this->view->old_password = $old_password;
			$this->view->new_password = $new_password;
			$this->view->conpassword = $conpassword;

			if (empty($new_password) || empty($conpassword) || empty($old_password)) {

				$pError = "";
				if (empty($old_password)) {

					if ($pError != "") {
						$pError .= "<br /> Please enter current password";
					}


					if ($pError == "") {
						$pError = "Please enter current password";
					}
				}

				if (empty($new_password)) {

					if ($pError != "") {
						$pError .= "<br /> Please enter new password";
					}

					if ($pError == "") {
						$pError = "Please enter new password";
					}
				}

				if (empty($conpassword)) {


					if ($pError != "") {
						$pError .= "<br /> Please enter confirm password";
					}

					if ($pError == "") {
						$pError = "Please enter confirm password";
					}
				}
				//$login_error = new Zend_Session_Namespace('Zend_Auth');
				///$login_error->loginError=$pError;
				//$this->_redirect('/customers/forgotpassword');
				$this->view->Errormsg = $pError;
			} else if ($new_password != $conpassword) {
				$this->view->Errormsg = "New password and confirm password do not match.";
			} else {
				$old_password = md5($old_password);

				//$userDetails  = $member->fetchRow( $member->select()->where('id = ?',$id) );
				if ($i_m_admin == 1) {
					$userDetails = $member->fetchRow(array('id = ?' => $id));
				} else {
					$userDetails = $member->fetchRow(array('id = ?' => $id, 'pass = ?' => $old_password));
				}

				//$old_password

				if (isset($userDetails->id)) {
					$data['pass'] = md5($new_password);
					$n = $member->update($data, "id = " . $id);
					$this->view->old_password = '';
					$this->view->new_password = '';
					$this->view->conpassword = '';
					$this->view->Errormsg = "Password has been updated successfully.";
				} else {
					$this->view->old_password = '';
					$this->view->new_password = '';
					$this->view->conpassword = '';
					$this->view->Errormsg = "Please enter valid current password.";
				}
			}
		}
	}

	/**
	 * changepassword() - method to change the password of admin
	 *
	 * @admin an object of Admin Model
	 * @Errormsg an variable to show message
	 * @access  admin user
	 * @return void
	 */
	public function changepasswordAction() {
		$member = new members();
		$filter = new Zend_Filter_StripTags();
		$session = new Zend_Session_Namespace('Zend_Auth');
		$this->view->actionNameLeftPanel = 'changepassword';
		$this->view->loggedin_customer_id = $session->member_id;
		$this->view->user_type = $session->user_type;
		$i_m_admin = 0;
		if (($this->getRequest()->getParam('id') != 1) && ($session->user_type == 1)) {
			$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}

		if ((($this->getRequest()->getParam('id') != $session->member_id) || ($this->getRequest()->getPost('id') != $session->member_id)) && ($session->user_type == 1)) {
			if ($this->getRequest()->getParam('id') != '') {
				$id = $this->getRequest()->getParam('id');
			} else {
				$id = $this->getRequest()->getPost('id');
			}

			$i_m_admin = 1;
			$this->view->i_m_admin = 1;
		} else if ($session->member_id > 0) {
			$id = $session->member_id;
		} else {
			$this->_redirect('/customers/logout');
		}

		if (($this->getRequest()->getParam('id') != 1) && ($session->user_type == 1)) {
			$this->view->actionName = 'customers';
		}

		$this->view->id = $id;

		$members_data = $member->fetchRow($member->select()->where('id=' . $id));
		$this->view->firstname = $members_data['firstname'];
		$this->view->lastname = $members_data['lastname'];

		if ($this->getRequest()->isPost()) {

			$old_password = $this->getRequest()->getParam('password');
			$new_password = $this->getRequest()->getParam('newpassword');
			$conpassword = $this->getRequest()->getParam('conpassword');

			$this->view->old_password = $old_password;
			$this->view->new_password = $new_password;
			$this->view->conpassword = $conpassword;

			if (empty($new_password) || empty($conpassword) || empty($old_password)) {

				$pError = "";
				if (empty($old_password)) {

					if ($pError != "") {
						$pError .= "<br /> Please enter current password";
					}


					if ($pError == "") {
						$pError = "Please enter current password";
					}
				}

				if (empty($new_password)) {

					if ($pError != "") {
						$pError .= "<br /> Please enter new password";
					}

					if ($pError == "") {
						$pError = "Please enter new password";
					}
				}

				if (empty($conpassword)) {


					if ($pError != "") {
						$pError .= "<br /> Please enter confirm password";
					}

					if ($pError == "") {
						$pError = "Please enter confirm password";
					}
				}
				//$login_error = new Zend_Session_Namespace('Zend_Auth');
				///$login_error->loginError=$pError;
				//$this->_redirect('/customers/forgotpassword');
				$this->view->Errormsg = $pError;
			} else if ($new_password != $conpassword) {
				$this->view->Errormsg = "New password and confirm password do not match.";
			} else {
				$old_password = md5($old_password);

				//$userDetails  = $member->fetchRow( $member->select()->where('id = ?',$id) );
				if ($i_m_admin == 1) {
					$userDetails = $member->fetchRow(array('id = ?' => $id));
				} else {
					$userDetails = $member->fetchRow(array('id = ?' => $id, 'pass = ?' => $old_password));
				}

				//$old_password

				if (isset($userDetails->id)) {
					$data['pass'] = md5($new_password);
					$n = $member->update($data, "id = " . $id);
					$this->view->old_password = '';
					$this->view->new_password = '';
					$this->view->conpassword = '';
					$this->view->Errormsg = "Password has been updated successfully.";
				} else {
					$this->view->old_password = '';
					$this->view->new_password = '';
					$this->view->conpassword = '';
					$this->view->Errormsg = "Please enter valid current password.";
				}
			}
		}
	}

	/**
	 * billinghistoryAction() - Method to List all the Billing Information
	 *
	 * @access public
	 * @return void
	 */
	public function billinghistoryAction() {
		$filter = new Zend_Filter_StripTags();
		$session = new Zend_Session_Namespace('Zend_Auth');

		$member = new members();
		$select = $member->select()->setIntegrityCheck(false);

		$orders = new orders();
		$this->view->actionName = 'customers';
		$this->view->actionNameLeftPanel = 'billinghistory';
		$this->view->loggedin_customer_id = $session->member_id;

		if (($this->getRequest()->getParam('id') != 1) && ($session->user_type == 1)) {
			$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}

		if ((($this->getRequest()->getParam('id') != $session->member_id) || ($this->getRequest()->getPost('id') != $session->member_id)) && ($session->user_type == 1)) {
			if ($this->getRequest()->getParam('id') != '') {
				$id = $this->getRequest()->getParam('id');
			} else {
				$id = $this->getRequest()->getPost('id');
			}
		} else if ($session->member_id > 0) {
			$id = $session->member_id;
		} else {
			$this->_redirect('/customers/logout');
		}

		$this->view->id = $id;
		$members_data = $member->fetchRow($member->select()->where('id=' . $id));
		$this->view->firstname = $members_data['firstname'];
		$this->view->lastname = $members_data['lastname'];

		/* $orders = new orders();
		 $orders_data =$orders->fetchRow(array('member_id = ?' =>$id,'order_status = ?' => 'Confirmed'));
		 $this->view->orders = $orders_data; */

		$session_billing_sorting = new Zend_Session_Namespace('Zend_Auth');

		if ($this->_getParam('page') != '') {
			$page_number = $this->_getParam('page');
		} elseif ($session_billing_sorting->page != '') {
			$page_number = $session_billing_sorting->page;
		} else {
			$page_number = 1;
		}
		$this->view->page_number = $page_number;


		$field_name = $this->getRequest()->getParam('name');
		$sort = $this->getRequest()->getParam('sort');

		if (!empty($field_name) && !empty($sort)) {
			if ($field_name == 'date') {
				$order_by = "id " . $sort;
			} else {
				$order_by = $field_name . " " . $sort;
				$session_billing_sorting->billing_sorting = $order_by;
				$session_billing_sorting->field_name = $field_name;
				$session_billing_sorting->sort = $sort;
				$session_billing_sorting->page = $page_number;
			}
		} elseif (trim($session_billing_sorting->billing_sorting) != '') {
			$order_by = $session_billing_sorting->billing_sorting;
			$this->view->columnname = $session_billing_sorting->field_name;
			$this->view->sortby = $session_billing_sorting->sort;
			$session_billing_sorting->page = $page_number;
		} else {
			$order_by = 'id desc';
		}

		//echo $order_by; exit;

		if (empty($condition)) {             // if no conditiona has been set due to search
			$condition = $select->from($orders, array('orders.*'))
			->joinInner('subscriptions', 'orders.plan_id=subscriptions.id and orders.member_id=' . $id, array('name'))
			->order($order_by);
		}
		//echo $condition; exit;
		$result_billing = $orders->fetchAll($condition);
		//echo "<pre>";
		//print_r($result_billing);
		//exit;
		$this->view->total_billing = count($result_billing);

		if (count($result_billing)) {
			$paginator = Zend_Paginator::factory($result_billing);
			$view = Zend_View_Helper_PaginationControl::setDefaultViewPartial('partials/my_pagination_control.phtml');
			$paginator->setItemCountPerPage(10)
			->setPageRange(10)
			->setCurrentPageNumber($page_number);
			$paginator->setDefaultScrollingStyle('Sliding');
			$paginator->setView($view);

			$this->view->paginator = $paginator;
			$this->view->membertypes = $paginator;
			$this->view->pageno = $this->_getParam('page');
			$this->view->recordPerPage = 10;
		}
		return;
	}

	/**
	 * cancelsubscriptionAction() - Method to Cancel subscription
	 *
	 * @access public
	 * @return void
	 */
	public function cancelsubscriptionAction() {
		$filter = new Zend_Filter_StripTags();
		$session = new Zend_Session_Namespace('Zend_Auth');
		$this->view->actionName = 'customers';
		$this->view->actionNameLeftPanel = 'cancelsubscription';
		$this->view->loggedin_customer_id = $session->member_id;

		if (($this->getRequest()->getParam('id') != 1) && ($session->user_type == 1)) {
			$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}

		if ($this->_request->isPost() && ($session->member_id == $this->_request->getPost('id'))) {

			$filter = new Zend_Filter_StripTags();
			$id = $filter->filter($this->_request->getPost('id'));

			$membersTable = new members();
			$members_data = $membersTable->fetchRow($membersTable->select()->where('id=' . $id));
			$braintree_subscription_id = $members_data['braintree_subscription_id'];

			$result = Braintree_Subscription::cancel($braintree_subscription_id);

			$session = new Zend_Session_Namespace('Zend_Auth');

			if($result->success == 1)
			{
				$member = new members();

				$cancel_type = $filter->filter($this->_request->getPost('cancel_type'));
				$cancel_subscription_data['plan_status'] = $cancel_type;  // 2 is for cancel plan
				$cancel_subscription_data['plan_id'] = 5;  //
				$cancel_subscription_data['braintree_subscription_id'] = '';
				$cancel_subscription_data['braintree_customer_id'] = '';
				$cancel_subscription_data['braintree_token'] = '';

				//update members set plan_status=1 where id=1
				$member->update($cancel_subscription_data, 'id=' . $session->member_id);
				$login_error = new Zend_Session_Namespace('Zend_Auth');
				if ($cancel_type == 2) {
					$login_error->loginError = "<font color='black''><b>Your plan will be reinstated at the end of the billing cycle.</b></font>";
				} else {
					$login_error->loginError = "<font color='black''><b>Your plan has been reinstated.</b></font>";
				}
			}
			else
			$login_error->loginError = "<font color='black''><b>Your plan can not be canceled, try again.</b></font>";
		}

		if ((($this->getRequest()->getParam('id') != $session->member_id) || ($this->getRequest()->getPost('id') != $session->member_id)) && ($session->user_type == 1)) {
			if ($this->getRequest()->getParam('id') != '') {
				$id = $this->getRequest()->getParam('id');
			} else {
				$id = $this->getRequest()->getPost('id');
			}
		} else if ($session->member_id > 0) {
			$id = $session->member_id;
		} else {
			$this->_redirect('/customers/logout');
		}
		$this->view->id = $id;
		$member = new members();
		$members_data = $member->fetchRow($member->select()->where('id=' . $id));
		$this->view->firstname = $members_data['firstname'];
		$this->view->lastname = $members_data['lastname'];
		$this->view->plan_status = $members_data['plan_status'];
		$this->view->plan_id = $members_data['plan_id'];

		if ($members_data['plan_status'] == 1) {
			$this->view->plan_message = "<font color='black''><b>Your plan has been reinstated.</b></font>";
		}

		if ($members_data['plan_status'] == 2) {
			$this->view->plan_message = "<font color='black''><b>Your plan will be reinstated at the end of the billing cycle.</b></font>";
		}
	}

	/**
	 * editAction() -Method for editing customer information
	 */
	function editAction()
	{
		 
		$filter = new Zend_Filter_StripTags();
		$session = new Zend_Session_Namespace('Zend_Auth');
		$this->view->actionName = 'customers';
		$this->view->actionNameLeftPanel = 'left_customers';
		$this->view->loggedin_customer_id = $session->member_id;


		if ($this->_request->isPost())
		{
			$member_id = $this->getRequest()->getParam('id');

			if (!empty($member_id))
			{
				$membersTable = new members();
				$member_update_data = array();

				$members_data = $membersTable->fetchRow($membersTable->select()->where('id=' . $member_id));


				$member_update_data['override_plan_minutes']= $_POST['MinutesPerMonth'];
				$member_update_data['override_price']= $_POST['MonthlyCost'];
				$member_update_data['plan_id']= $_POST['planId'];
				$member_update_data['total_remaining_calls'] = $_POST['CurrentMinutesRemaining'];
				$member_update_data['login_override'] = $_POST['LoginOveride'];
				$bool = true;
				if($_POST['planId'] != '' && $_POST['MonthlyCost'] != 0 && $_POST['MonthlyCost'] != "" && !empty($members_data['braintree_token']))
				{
						
					$orders = new orders();
					$members_order_data['plan_id'] = trim($_POST['planId']);
					$members_order_data['member_id'] = $member_id;
					$members_order_data['created_on'] = date('Y-m-d,H:i:s');
					$members_order_data['order_status'] = 'Pending';
					$members_order_data['card_type'] = $members_data['card_type'];
					$members_order_data['cvv_number'] = $members_data['card_cvv'];
					$members_order_data['name_on_card'] = $members_data['card_name'];
					$members_order_data['card_number'] = $members_data['card_num'];
					$members_order_data['expiry_month'] = $members_data['expiry_month'];
					$members_order_data['expiry_year'] = $members_data['expiry_year'];
					$members_order_data['user_status'] = $members_data['status'];
					$members_order_data['amount'] = trim($_POST['MonthlyCost']);
					$members_order_data['payment_type'] = "OVERRIDDEN";

					$last_insert_order_id = $orders->insert($members_order_data);
						
						
					$BrainTreeResult = Braintree_Subscription::update($members_data['braintree_subscription_id'], array(
                            'paymentMethodToken' => $members_data['braintree_token'],
                            'planId' => trim($_POST['planId']),
                            'price' => trim($_POST['MonthlyCost'])
					));


					if ($BrainTreeResult->success == 1)
					{
							
						$sub_info = $BrainTreeResult->subscription;
						$orders_data = array();
						$member_update_data['payment_status'] = 'Confirmed';
						$member_update_data['payment_due_for_coming_month'] = '1';
						$orders_data['order_status'] = 'Confirmed';
					}
					else
					{
						$bool = false;
						$orders_data = array();
						$member_update_data = array();
						$member_update_data['payment_status'] = 'Fail';
						$orders_data['order_status'] = 'Fail';
					}

					$orders->update($orders_data, 'id=' . $last_insert_order_id);
				}
				else if(empty($members_data['braintree_token']) && intval(trim($_POST['MonthlyCost'])) > 0 )
				{
					$bool = false;
					$this->view->errorMessage ="<font color='red''><b>Error: Braintree token is empty for this user in our database, So its price can't be override!</b></font>";
				}
				
				if($bool)
					$membersTable->update($member_update_data,'id='.$member_id);

			}
		}
		if (($this->getRequest()->getParam('id') != 1) && ($session->user_type == 1)) {
			$this->view->admin_left_tab = 'admin_left_tab_for_customer';
		}



		if ((($this->getRequest()->getParam('id') != $session->member_id) || ($this->getRequest()->getPost('id') != $session->member_id)) && ($session->user_type == 1)) {
			if ($this->getRequest()->getParam('id') != '') {
				$id = $this->getRequest()->getParam('id');
			} else {
				$id = $this->getRequest()->getPost('id');
			}
		} else if ($session->member_id > 0) {
			$id = $session->member_id;
		} else {
			$this->_redirect('/customers/logout');
		}


		if (($id > 0) && !empty($id)) {
			$membersTable = new members();
			$members_data = $membersTable->fetchRow($membersTable->select()->where('id=' . $id));
			$subscriptions = new subscriptions();

			//$this->view->signup_session_id =  $signup_session_id;
			$this->view->member_id = $id;
			$this->view->firstname = $members_data['firstname'];
			$this->view->id = $members_data['id'];
			$this->view->lastname = $members_data['lastname'];
			$this->view->email = $members_data['email'];
			$this->view->companyname = $members_data['companyname'];
			$this->view->address1 = $members_data['address1'];
			$this->view->address2 = $members_data['address2'];
			$this->view->city = $members_data['city'];
			$this->view->state = $members_data['state'];
			$this->view->zip = $members_data['zip'];
			$this->view->phone = $members_data['phone'];
			$this->view->plan_id = $members_data['plan_id'];
			$this->view->login_override = $members_data['login_override'];
			$this->view->total_remaining_calls = $members_data['total_remaining_calls'];

			$subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id=' . $members_data['plan_id']));

			if($members_data['override_plan_minutes'] != null && $members_data['override_plan_minutes'] != '')
			{
				$this->view->plan_code = $members_data['override_plan_minutes'];
			}
			else
			{
				$this->view->plan_code = $subscriptions_data['code'];
				 
			}

			if($members_data['override_price'] != null || $members_data['override_price'] != 0)
			{
				$this->view->plan_price = $members_data['override_price'];
			}
			else
			{
				$this->view->plan_price = $subscriptions_data['price'];
			}

			 
			$this->view->plan_name = $subscriptions_data['name'];


			$code = 0;

			$this->view->subscriptions_all_data = $subscriptions->fetchAll($subscriptions->select()->where('code !=' . $code));

			$this->view->card_name = $members_data['card_name'];
			$this->view->card_type = $members_data['card_type'];
			$this->view->card_num = $members_data['card_num'];
			$this->view->card_cvv = $members_data['card_cvv'];
			$this->view->expiry_month = $members_data['expiry_month'];
			$this->view->expiry_year = $members_data['expiry_year'];
			$this->view->start_date = date('F d', strtotime($members_data['plan_start_date']));
			$this->view->end_date = date('F d', strtotime($members_data['plan_end_date']));
		} else {
			$this->_redirect('customers/login');
		}
	}

	public function  overrideplaneAction()
	{
		 
		if ($this->_request->isPost())
		{
			$filter = new Zend_Filter_StripTags();
			$planId = $filter->filter($this->_request->getPost('planId'));
			$subscriptions = new subscriptions();

			$subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id=' . $planId));
				
			$this->view->minates_per_month = $subscriptions_data['code'];
			$this->view->price = $subscriptions_data['price'];

			 
			echo $data =  $this->view->minates_per_month."-".$this->view->price;
			exit;
		}
	}

	/**
	 * reportingAction() - Method to show Reporting List
	 *
	 * @access public
	 * @return void
	 */
	public function reportingAction() {
		$session = new Zend_Session_Namespace('Zend_Auth');
		$this->view->loggedinuser = $session->member_id;
		$customer_id = $session->member_id;

		$inquiries = new inquiry();
		$formstdfield = new FormsStd();
		$select = $inquiries->select()->setIntegrityCheck(false);

		$name = $this->getRequest()->getParam('name');
		$orderby = $this->getRequest()->getParam('sort');

		$fromdate = $this->getRequest()->getParam('fromdate');
		$todate = $this->getRequest()->getParam('todate');

		$this->view->fromdates = $this->getRequest()->getParam('fromdate');
		$this->view->todates = $this->getRequest()->getParam('todate');

		$this->view->columnname = $name;
		$this->view->actionName = 'reporting';

		$membersTable = new members();
		/* $members_data = $membersTable->fetchAll($membersTable->select()->where("status='Active' and user_type=0"));	    				$this->view->members_data = $members_data;	 */

		$this->view->select_billing_ajax = 'fromdate/' . $fromdate . '/todate/' . $todate;

		$formsTable = new Forms();

		$session_customer_sorting = new Zend_Session_Namespace('Zend_Auth');

		if ($this->_getParam('page') != '') {
			$page_number = $this->_getParam('page');
		} elseif ($session_customer_sorting->page != '') {
			$page_number = $session_customer_sorting->page;
		} else {
			$page_number = 1;
		}
		$this->view->page_number = (int) $page_number;


		$field_name = $this->getRequest()->getParam('name');
		$sort = $this->getRequest()->getParam('sort');

		$form_id = $this->getRequest()->getParam('form_id');
		$allforms = $this->getRequest()->getParam('allforms');
		$forms_data = $formsTable->fetchAll($formsTable->select()->where("customer_id='" . $customer_id . "'"));
		$this->view->forms_data = $forms_data;
		$queryfield = '';
		$querylabel = '';

		if ($form_id > 0) {
			$formreport = new formreport();
			$formreport_data = $formreport->fetchRow($formreport->select()->where("form_id=" . $form_id . " and customer_id=" . $customer_id));
			$sortby = $formreport_data->field_sort;
			$formstdfield->select()->where("id=" . $sortby);
			$form_fieldid_sort_data = $formstdfield->fetchRow($formstdfield->select()->where("id=" . $sortby));
			$field_name = $form_fieldid_sort_data->inquiry_table_field;
			if ($formreport_data->selected_field_id != '') {
				$form_fieldid_data = $formstdfield->fetchAll($formstdfield->select()->where("id in ($formreport_data->selected_field_id)"));
				foreach ($form_fieldid_data as $form_fieldid_datas) {
					$queryfield.=$form_fieldid_datas->inquiry_table_field . ',';
					$querylabel.=$form_fieldid_datas->label . ',';
				}
			}
		}

		$this->view->queryfield_array = explode(',', $queryfield);
		$this->view->querylabel_array = explode(',', $querylabel);



		if (!empty($field_name) && !empty($sort)) {
			if ($field_name == 'date') {
				$order_by = "id " . $sort;
			} else {
				$order_by = $field_name . " " . $sort;
				$session_customer_sorting->customers_sorting = $order_by;
				$session_customer_sorting->field_name = $field_name;
				$session_customer_sorting->sort = $sort;
				$session_customer_sorting->page = $page_number;
			}
		} elseif (trim($session_customer_sorting->customers_sorting) != '') {
			$order_by = $session_customer_sorting->customers_sorting;
			$this->view->columnname = $session_customer_sorting->field_name;
			$this->view->sortby = $session_customer_sorting->sort;
			$session_customer_sorting->page = $page_number;
		} else {
			$order_by = 'id desc';
		}



		// if search by Customer name, Form name form has been posted


		if (($allforms == '1' || $allforms == '2' || $allforms == '3' || $allforms == '4' || $allforms == '5') && $form_id != '') {
			if ($allforms == '1') {
				$condition = $select->from($inquiries, array('inquiry.*'))
				->where("customer_id ='" . $customer_id . "'")
				->order($order_by);
			} else {
				$inquiry_type_value = '';
				if ($allforms == '5') {
					$inquiry_type_value = 'Completed';
				}
				if ($allforms == '2') {
					$inquiry_type_value = 'Incomplete';
				}
				if ($allforms == '3') {
					$inquiry_type_value = 'After hours';
				}
				if ($allforms == '4') {
					$inquiry_type_value = 'Hang up';
				}
				if ($inquiry_type_value == '') {
					$inquiry_type_value = 'Completed';
				}

				if ($form_id > 0) {
					$condition = $select->from($inquiries, array('inquiry.*'))
					->where("customer_id ='" . $customer_id . "' and inquiry_type='" . $inquiry_type_value . "' and form_id=" . $form_id)
					->order($order_by);
				} else {
					$condition = $select->from($inquiries, array('inquiry.*'))
					->where("customer_id ='" . $customer_id . "' and inquiry_type='" . $inquiry_type_value . "'")
					->order($order_by);
				}
			}
		} else if ($customer_id > 0 && $form_id > 0 && $fromdate != '' && $todate != '') {
			$dateformdate = explode('-', $fromdate);
			$datetodate = explode('-', $todate);
			$fromdate = strtotime($dateformdate[1] . '-' . $dateformdate[0] . '-' . $dateformdate[2]);
			$todate = strtotime($datetodate[1] . '-' . $datetodate[0] . '-' . $datetodate[2]);
			$condition = $select->from($inquiries, array('inquiry.*'))
			->where("form_id='" . $form_id . "' and customer_id='" . $customer_id . "' and unix_timestamp(date_created) between '" . $fromdate . "' and '" . $todate . "'")
			->order($order_by);
		} else if ($customer_id > 0 && $fromdate != '' && $todate != '') {
			$dateformdate = explode('-', $fromdate);
			$datetodate = explode('-', $todate);
			$fromdate = strtotime($dateformdate[1] . '-' . $dateformdate[0] . '-' . $dateformdate[2]);
			$todate = strtotime($datetodate[1] . '-' . $datetodate[0] . '-' . $datetodate[2]);
			$condition = $select->from($inquiries, array('inquiry.*'))
			->where("customer_id='" . $customer_id . "' and unix_timestamp(date_created) between '" . $fromdate . "' and '" . $todate . "'")
			->order($order_by);
		} else if ($customer_id > 0 && $form_id > 0) {
			$condition = $select->from($inquiries, array('inquiry.*'))
			->where("form_id='" . $form_id . "' and customer_id='" . $customer_id . "'")
			->order($order_by);
		} else {
			$condition = $select->from($inquiries, array('inquiry.*'))
			->where("customer_id ='" . $customer_id . "'")
			->order($order_by);
		}
		$this->view->drop_down_customer_id = $customer_id;
		$this->view->drop_down_form_id = $form_id;
		//echo $condition;

		/* if($allforms=='0')
		 {
		 $condition='false';
		 } */

		if (empty($condition)) {             // if no conditiona has been set due to search
			$condition = $select->from($inquiries, array('inquiry.*'))
			->order($order_by);
		}

		$result_inquiry = $inquiries->fetchAll($condition);
		$this->view->total_calls = count($result_inquiry);



		// total connections
		$condition_1 = $inquiries->select()->where("inquiry_type='Completed'");
		$result_inquiry_1 = $inquiries->fetchAll($condition_1);
		$this->view->total_connections = count($result_inquiry_1);
		$total_minutes = 0;
		$min = 0;

		foreach($result_inquiry as  $data_key => $data_val)
		{
			$duration = 0;
			if(!empty($data_val->call_duration))
			{
				$call_val = intval($data_val->call_duration);
				$duration = $call_val;
			}
			$sec = $duration;
			$min = $sec/60;
			$min = ceil ( $min );
			$total_minutes += $min;
		}

		$this->view->total_minutes = $total_minutes;

		//$this->view->total_connections =0;// Later we will implement
		//$this->view->total_calls =$total_calls;
		//$this->view->total_connections =$total_connections;
		//*MONTHLY CALLS *//
		$month_start_date = strtotime(date('Y') . '-' . date('m') . '-1');

		if (date('m') == 1 || date('m') == 3 || date('m') == 5 || date('m') == 7 || date('m') == 8 || date('m') == 10 || date('m') == 12) {
			$month_last_date = strtotime(date('Y') . '-' . date('m') . '-31');
		}
		if (date('m') == 4 || date('m') == 6 || date('m') == 9 || date('m') == 11) {
			$month_last_date = strtotime(date('Y') . '-' . date('m') . '-30');
		}
		if (date('m') == 2 && date('m') % 4 == 0) {
			$month_last_date = strtotime(date('Y') . '-' . date('m') . '-29');
		}
		if (date('m') == 2 && date('m') % 4 != 0) {
			$month_last_date = strtotime(date('Y') . '-' . date('m') . '-28');
		}

		$condition1 = $inquiries->select()->where("unix_timestamp(date_created) between '" . $month_start_date . "' and '" . $month_last_date . "' and inquiry_type='Completed'");
		$result_inquiry1 = $inquiries->fetchAll($condition1);
		$this->view->total_inquires = count($result_inquiry1);
		//*MONTHLY CALLS END HERE *//

		if ($allforms == '1' || $allforms == '2' || $allforms == '3' || $allforms == '4' || $allforms == '5') {
			if ($allforms == '1') {
				$page_limit = count($result_inquiry);
			} else {
				$page_limit = 20;
			}
			$this->view->selectform = $allforms; //
		} else {
			$page_limit = 20;
		}

		// Billing period Date in case of Customer Start here
		if ($customer_id > 0) {
			$members_billing_period = $membersTable->fetchAll($membersTable->select()->where("id=" . $customer_id));

			//print_r($members_billing_period);
			if (count($members_billing_period) > 0) {

				$this->view->members_billing_period = $members_billing_period;
			}
		}
		// Billing period Date in case of Customer End here

		if (count($result_inquiry)) {
			$paginator = Zend_Paginator::factory($result_inquiry);
			$view = Zend_View_Helper_PaginationControl::setDefaultViewPartial('partials/my_pagination_control.phtml');
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
		//exit;
		return;
	}

	/**
	 * reportingAction() - Method to show Reporting List
	 *
	 * @access public
	 * @return void
	 */
	public function reportingpopupAction() {
		$session = new Zend_Session_Namespace('Zend_Auth');
		if ($session->member_id > 0) {
			$id = $session->member_id;
		} else {
			$this->_redirect('/customers/logout');
		}

		$inquiries = new inquiry();
		//$id= $this->_request->getPost('id');
		$topposition = $this->_request->getPost('topposition');
		if ($id > 0) {
			$inquiries_data = $inquiries->fetchAll($inquiries->select()->where("customer_id=" . $id));

			if (count($inquiries_data) > 0) {
				?>
<!-- <div id="formUserDetails" class="hr1b1" style="position: absolute; left: 30%;top: <?= $topposition ?>px;" onmouseout="hideme('previewhiddendiv')"> -->
<div id="formUserDetails" class="hr1b1" style="position: absolute; left: 30%;top: <?= $topposition ?>px;">
<!-- <a onclick="hideme('previewhiddendiv')" class="buttonsNext" href="javascript:;" style="float:right;">Close</a> -->
                    <img onclick="hideme('previewhiddendiv')" src="<?= WEBSITE_URL ?>images/Close.png" style="float:right;">
                    <h3>Inquiry <span>Details</span></h3>
                    <fieldset>
<?php
                if (count($inquiries_data) > 0) {
                    foreach ($inquiries_data as $data_key => $data_val) {
?> 
<?php if ($data_val->date_created != '') { ?>
                                    <span class="popupmsg" style="width:100px;">Date:</span><span class="popupmsg"><?php echo date('m/d/Y', strtotime($data_val->date_created)); ?></span><br>
<? } ?>
<?php if ($data_val->date_created != '') { ?>
                                    <span  class="popupmsg" style="width:100px;">Time:</span><span class="popupmsg"><?php echo stripslashes($data_val->time); ?></span><br>
<? } ?>
<?php if ($data_val->firstname != '') { ?>
                                    <span  class="popupmsg" style="width:100px;">First Name:</span><span class="popupmsg"><?php echo stripslashes(ucfirst($data_val->firstname)); ?></span><br>
<? } ?>
<?php if ($data_val->lastname != '') { ?>
                                    <span  class="popupmsg" style="width:100px;">Last Name:</span><span class="popupmsg"><?php echo stripslashes(ucfirst($data_val->lastname)); ?></span><br>
<? } ?>
<?php if ($data_val->email != '') { ?>
                                    <span  class="popupmsg" style="width:100px;">Email:</span><span class="popupmsg"><?php echo stripslashes($data_val->email); ?></span><br>
<? } ?>
<?php if ($data_val->streetaddress != '') { ?>
                                    <span  class="popupmsg" style="width:100px;">Address:</span><span class="popupmsg"><?php echo stripslashes(ucfirst($data_val->streetaddress)); ?></span><br>
<? } ?>
<?php if ($data_val->city != '') { ?>
                                    <span  class="popupmsg" style="width:100px;">City:</span><span class="popupmsg"><?php echo stripslashes(ucfirst($data_val->city)); ?></span><br>
<? } ?>
<?php if ($data_val->state != '') { ?>
                                    <span class="popupmsg" style="width:100px;">State:</span><span class="popupmsg"><?php echo stripslashes(ucfirst($data_val->state)); ?></span><br>
<? } ?>
<? }
                } else {
                    echo "No Details Found.";
                } ?>
                    </fieldset>
                </div>
<?php
                exit;
			}
		}
	}

	function count_days($a, $b) {
		#
		echo $a;
		echo"<br>" . $b;
		echo"<br>";
		echo"<br>";
		echo"<br>";
		exit;
		#
		// First we need to break these dates into their constituent parts:
		#
		$gd_a = getdate($a);
		#
		$gd_b = getdate($b);
		#
		#
		// Now recreate these timestamps, based upon noon on each day
		#
		// The specific time doesn't matter but it must be the same each day
		#
		$a_new = mktime(12, 0, 0, $gd_a['mon'], $gd_a['mday'], $gd_a['year']);
		#
		$b_new = mktime(12, 0, 0, $gd_b['mon'], $gd_b['mday'], $gd_b['year']);
		#
		#
		// Subtract these two numbers and divide by the number of seconds in a
		#
		// day. Round the result since crossing over a daylight savings time
		#
		// barrier will cause this time to be off by an hour or two.
		#
		return round(abs($a_new - $b_new) / 86400);
		#
	}

	function calculatproratedpriceAction() {
		/*         * **** */

		$session = new Zend_Session_Namespace('Zend_Auth');

		if(!empty($session->member_id) && ($session->user_type==0) )
		{
			if($this->limitTrialUserAccess($session->member_id)){
				//                $this->_redirect('/customers/editpaymentdetails/id/'.$session->member_id);
				echo "<script type='text/javascript'> window.location ='/customers/editpaymentdetails/id/".$session->member_id ."'</script>";
				exit;
			}
		}

		if ($this->_request->isPost()) {
			$filter = new Zend_Filter_StripTags();
			$plan_id = $filter->filter($this->_request->getPost('plan_id'));
			$org_plan_id = $filter->filter($this->_request->getPost('org_plan_id'));
			$id = $filter->filter($this->_request->getPost('id'));

			$memberTable = new members();

			/*             * ****************************** */

			/* 	Get prorated data Start here */
			$subscriptions = new subscriptions();
			$subscriptions_new_data = $subscriptions->fetchRow($subscriptions->select()->where("id=" . $plan_id));
			$subscriptions_org_data = $subscriptions->fetchRow($subscriptions->select()->where("id=" . $org_plan_id));
			$membersdata = $memberTable->fetchRow($memberTable->select()->where("id=" . $id));
			$planname = $subscriptions_new_data['name'];
			$price = $subscriptions_new_data['price'];
			$daysremaining = (strtotime($membersdata ['plan_end_date']) - strtotime(date('Y-m-d'))) / 86400;
			$dayin_month = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));

			if (($subscriptions_new_data['price'] > $subscriptions_org_data['price']) && $daysremaining > 0) {
				$prorated_price = number_format((($subscriptions_new_data['price'] * $daysremaining) / $dayin_month) - (($subscriptions_org_data['price'] * $daysremaining) / $dayin_month), 2);
			} else if (($subscriptions_new_data['price'] > $subscriptions_org_data['price']) && $daysremaining <= 0) {
				$prorated_price = number_format($subscriptions_new_data['price'], 2);
			} else {
				$prorated_price = 0;
				// Prorate will not work here
			}
			?>
<div id="formUserDetails" class="hr1b1"
	style="position: absolute; left: 37%; top: 258px;"><img
	onclick="hideme('div_prorate')"
	src="<?= WEBSITE_URL ?>images/Close.png" style="float: right;">
<fieldset><span class="popupmsg" style="width: 275px;"> <?php
//                           echo "subscriptions_new_data_price=".$subscriptions_new_data['price'];
//			echo "<br/>";
//			echo "subscriptions_org_data_price=".$subscriptions_org_data['price'];
//			echo "<br/>";
//			echo "daysremaining=".$daysremaining;
//			echo "<br/>";
//			echo "plan end date=".$membersdata ['plan_end_date'];
//			echo "<br/>";
?> <?php

if(!empty ($membersdata ['braintree_customer_id'])) {

	if ($subscriptions_org_data['price'] > $subscriptions_new_data['price']) {
		?> By clicking the confirm button, you acknowledge that you will
downgrade your FormActivate subscription plan from the $<?= number_format($subscriptions_org_data['price'], 2, '.', '') ?>
plan to the $<?= number_format($subscriptions_new_data['price'], 2, '.', '') ?>
plan. This will automatically take effect in your next billing cycle.
Thank you for using FormActivate <?php } else { ?> You have chosen to go
from the $<?= number_format($subscriptions_org_data['price'], 2, '.', '') ?>
plan to the $<?= number_format($subscriptions_new_data['price'], 2, '.', '') ?>
Your plan will be updated immediately and you will be charged a prorated
rate for the remainder of the current billing cycle. This amounts to $<?=
number_format($prorated_price, 2, '.', '');
}

?> <br>
<br>
</span> <input class="buttonsNext" type="button" value="Cancel"
	onclick="hideme('div_prorate')" style="width: 78px;" />&nbsp;<input
	class="buttonsNext" type="submit" value="Update" style="width: 78px;" /></fieldset>
</div>
<?php
}
else{
	?>

You are free user, please give us your payment details.
<a
	href="<?php echo WEBSITE_URL.'customers/editpaymentdetails/id/'.$id; ?>">Pay
Now</a>

<br>
<br>
</span>
<input
	class="buttonsNext" type="button" value="Cancel"
	onclick="hideme('div_prorate')" style="width: 78px;" />
&nbsp;

	<?php   } exit;
		}

	}

	// ON THIS PAGE THE FORM IS SUBMITTED FOR PAYMENT
	function paymentforupdatedplanAction() {

		$filter = new Zend_Filter_StripTags();
		$this->view->actionName = 'paymentforupdatedplan';
		$session = new Zend_Session_Namespace('Zend_Auth');
		$this->_helper->layout->setLayout('layout_api');

		if ($session->customer_id != '') {
			$id = $session->customer_id;
			$membersTable = new members();
			$members_data = $membersTable->fetchRow($membersTable->select()->where('id=' . $id));
			$subscriptions = new subscriptions();

			//$this->view->signup_session_id =  $signup_session_id;
			$this->view->firstname = $members_data['firstname'];
			$this->view->id = $members_data['id'];
			$this->view->lastname = $members_data['lastname'];
			$this->view->email = $members_data['email'];

			$subscriptions_data = $subscriptions->fetchRow($subscriptions->select()->where('id=' . $session->plan_id));
			$this->view->plan_id = $subscriptions_data['name'];
			$this->view->price = $session->prorated_price;
			$this->view->card_name = $members_data['card_name'];
			$this->view->order_id = $session->last_insert_order_id;
			$this->view->card_type = $members_data['card_type'];
			$this->view->card_num = $members_data['card_num'];
			$this->view->card_cvv = $members_data['card_cvv'];
			$this->view->expiry_month = $members_data['expiry_month'];
			$this->view->expiry_year = $members_data['expiry_year'];
			$this->view->loggedin_member_id_and_user_type = $session->member_id . '_' . $session->user_type;
		} else {
			$this->_redirect('customers/logout/');
		}
	}

	/*     * * function to getbillingperiodAction    */

	public function getbillingperiodAction() {
		if ($this->_request->isPost()) {
			$filter = new Zend_Filter_StripTags();
			$id = $filter->filter($this->_request->getPost('customer_id'));

			$membersTable = new members();
			if ($id > 0) {
				$members_data = $membersTable->fetchRow($membersTable->select()->where('id=' . $id));

				//echo $members_data['plan_start_date'];
				$thisusermonth = abs((time() - (strtotime($members_data['plan_start_date']))) / (60 * 60 * 24 * 30));

				$plan_start_date = explode('-', $members_data['plan_start_date']);
				$plan_start_month = $plan_start_date[1];
				$plan_start_months = $plan_start_date[1];
				$plan_start_day = explode(' ', abs($plan_start_date[2]));
				$plan_start_day = abs($plan_start_day[0]);
				$plan_start_days = $plan_start_day;
				$current_year = date('Y');

				//********START DATE FOR BILLING *********************************//
				$start_date_value[0] = strtotime($current_year . '-' . (date('m') - 1) . '-' . $plan_start_day);
				$plan_start_month = (date('m') - 1);
				//$plan_start_month=(date('m')-1);

				$end_date_value[0] = strtotime(date('Y-m-d')) - (24 * 60 * 60);
				// STARTING DATE OR BILLING DATE FOR CURRENT MONTH.
				for ($i = 1; $i < $thisusermonth; $i++) {
					$plan_start_month--;
					if ($plan_start_month < 1) {
						if ($plan_start_month == 0) {
							$plan_start_month_year_changed = 12;

							$start_date_value[$i] = strtotime(($current_year - 1) . '-' . ($plan_start_month_year_changed) . '-' . $plan_start_day);
						} else {

							$start_date_value[$i] = strtotime(($current_year - 1) . '-' . ($plan_start_month_year_changed - (abs($plan_start_month))) . '-' . $plan_start_day);
						}
					} else {
						$start_date_value[$i] = strtotime(($current_year) . '-' . ($plan_start_month) . '-' . $plan_start_day);
					}
				}

				//print_r($start_date_value); exit;
				//********END DATE FOR BILLING *********************************//
				// END DATE OR BILLING DATE FOR CURRENT MONTH.
				$plan_start_months = (date('m') - 1);

				for ($i = 1; $i < $thisusermonth; $i++) {

					if ($plan_start_months < 1) {
						if ($plan_start_months == 0) {
							$plan_start_month_year_changed = 12;
							$end_date_value[$i] = strtotime(($current_year - 1) . '-' . ($plan_start_month_year_changed) . '-' . $plan_start_days) - (24 * 60 * 60);
							;
						} else {
							$end_date_value[$i] = strtotime(($current_year - 1) . '-' . ($plan_start_month_year_changed - (abs($plan_start_months))) . '-' . $plan_start_days) - (24 * 60 * 60);
						}
					} else {
						$end_date_value[$i] = strtotime(($current_year) . '-' . $plan_start_months . '-' . $plan_start_days) - (24 * 60 * 60);
					}
					$plan_start_months--;
				}
				//print_r($end_date_value);
				//********END DATE FOR BILLING *********************************//
				?>
<option value="0">Select</option>
				<? for ($report_date = 0; $report_date < $thisusermonth; $report_date++) {
					?>
                    <option value='fromdate/<?php echo date('m-d-Y', $start_date_value[$report_date]); ?>/todate/<?php echo date('m-d-Y', $end_date_value[$report_date]) ?>'><?php echo date('M j, Y', $start_date_value[$report_date]); ?> - <?php echo date('M j, Y', $end_date_value[$report_date]); ?></option>
					<? } ?>
					<?
			}
		}
		exit;
	}

	/*     * * function to getbillingperiodAction    */

	public function getbillingperiodselectedAction() {
		if ($this->_request->isPost()) {
			$filter = new Zend_Filter_StripTags();
			$id = $filter->filter($this->_request->getPost('customer_id'));
			$selected_option = $filter->filter($this->_request->getPost('selected_option'));

			$membersTable = new members();
			if ($id > 0) {
				$members_data = $membersTable->fetchRow($membersTable->select()->where('id=' . $id));

				//echo $members_data['plan_start_date'];

				$plan_start_date = explode('-', $members_data['plan_start_date']);

				$thisusermonth = abs((time() - (strtotime($members_data['plan_start_date']))) / (60 * 60 * 24 * 30));


				$plan_start_month = $plan_start_date[1];

				$plan_start_months = $plan_start_date[1];
				$plan_start_day = explode(' ', abs($plan_start_date[2]));
				$plan_start_day = abs($plan_start_day[0]);
				$plan_start_days = $plan_start_day;
				$current_year = date('Y');

				//********START DATE FOR BILLING *********************************//
				$start_date_value[0] = strtotime($current_year . '-' . (date('m') - 1) . '-' . $plan_start_day);
				$plan_start_month = (date('m') - 1);
				//$plan_start_month=(date('m')-1);

				$end_date_value[0] = strtotime(date('Y-m-d')) - (24 * 60 * 60);
				// STARTING DATE OR BILLING DATE FOR CURRENT MONTH.
				for ($i = 1; $i < $thisusermonth; $i++) {
					$plan_start_month--;
					if ($plan_start_month < 1) {
						if ($plan_start_month == 0) {
							$plan_start_month_year_changed = 12;

							$start_date_value[$i] = strtotime(($current_year - 1) . '-' . ($plan_start_month_year_changed) . '-' . $plan_start_day);
						} else {

							$start_date_value[$i] = strtotime(($current_year - 1) . '-' . ($plan_start_month_year_changed - (abs($plan_start_month))) . '-' . $plan_start_day);
						}
					} else {
						$start_date_value[$i] = strtotime(($current_year) . '-' . ($plan_start_month) . '-' . $plan_start_day);
					}
				}

				//print_r($start_date_value); exit;
				//********END DATE FOR BILLING *********************************//
				// END DATE OR BILLING DATE FOR CURRENT MONTH.
				$plan_start_months = (date('m') - 1);

				for ($i = 1; $i < $thisusermonth; $i++) {

					if ($plan_start_months < 1) {
						if ($plan_start_months == 0) {
							$plan_start_month_year_changed = 12;
							$end_date_value[$i] = strtotime(($current_year - 1) . '-' . ($plan_start_month_year_changed) . '-' . $plan_start_days) - (24 * 60 * 60);
							;
						} else {
							$end_date_value[$i] = strtotime(($current_year - 1) . '-' . ($plan_start_month_year_changed - (abs($plan_start_months))) . '-' . $plan_start_days) - (24 * 60 * 60);
						}
					} else {
						$end_date_value[$i] = strtotime(($current_year) . '-' . $plan_start_months . '-' . $plan_start_days) - (24 * 60 * 60);
					}
					$plan_start_months--;
				}
				//print_r($end_date_value);
				//********END DATE FOR BILLING *********************************//
				?>
<!--<option value="0">Select</option>	-->
				<? for ($report_date = 0; $report_date < $thisusermonth; $report_date++) {
					?>
<option
	value='fromdate/<?php echo date('m-d-Y', $start_date_value[$report_date]); ?>/todate/<?php echo date('m-d-Y', $end_date_value[$report_date]) ?>'
	<?php if ('fromdate/' . date('m-d-Y', $start_date_value[$report_date]) . '/todate/' . date('m-d-Y', $end_date_value[$report_date]) == $selected_option) {
		echo "selected";
	} ?>><?php echo date('M j, Y', $start_date_value[$report_date]); ?> - <?php echo date('M j, Y', $end_date_value[$report_date]); ?></option>
	<? } ?>
	<?
			}
		}
		exit;
	}

	/**
	 * popuppreviewAction() -Method for Popup for visual editor
	 *
	 * @access public
	 * @return void
	 */
	public function popupinquirypreviewAction() {
		$session = new Zend_Session_Namespace('Zend_Auth');
		$this->view->loggedin_customer_id = $session->member_id;
		$this->view->inquiry_id = $this->getRequest()->getParam('inquiry_id');
		$inquiry_id = $this->getRequest()->getParam('inquiry_id');
		$divposition = $this->getRequest()->getParam('position');
		//exit;
		if (empty($inquiry_id)) {
			$this->_redirect('/customers/reporting');
		}

		if ($session->member_id > 0) {
			$id = $session->member_id;
		} else {
			$this->_redirect('/customers/login');
		}
		$inquiry = new inquiry();
		$inquiry_data = $inquiry->fetchRow($inquiry->select()->where('id=' . $inquiry_id));
		?>
<div id="formUserDetails" style="background: none repeat scroll 0 0 #FFFFFF;border: 5px solid #666666;font: 12px Arial,Helvetica,sans-serif;
             left: 610px;padding: 8px;text-align: justify;width: 286px;position: absolute; left: 21%;z-index:10;top:<?= $divposition ?>px;">

<img onclick="hideme('previewhiddendiv')" style="float: right"
	src="<?php echo WEBSITE_IMG_URL.'Close.png' ?>" />
<h3>Inquiry <span> Details </span></h3>
<fieldset style="color: #000; padding-left: 40px"><b>Date: </b> <?php echo date('m/d/Y', strtotime($inquiry_data['date_created'])); ?><br />
<br />
<b>Time: </b> <?php echo $inquiry_data['time'] ?><br />
<br />
<b>First Name: </b> <?php echo $inquiry_data['firstname'] ?><br />
<br />
<b>Last Name: </b> <?php if (empty($inquiry_data['lastname']))
echo "N/A"; else
echo $inquiry_data['lastname'] ?><br />
<br />
<b>Email: </b> <?php echo $inquiry_data['email'] ?><br />
<br />
<b>Address: </b> <?php if (empty($inquiry_data['streetaddress']))
echo "N/A"; else
echo $inquiry_data['streetaddress'] ?><br />
<br />
<b>City: </b> <?php if (empty($inquiry_data['city']))
echo "N/A"; else
echo $inquiry_data['city'] ?><br />
<br />
<b>State: </b> <?php if (empty($inquiry_data['state']))
echo "N/A"; else
echo $inquiry_data['state'] ?><br />
<br />
<b>Call Duration: </b> <?php
if ($inquiry_data['call_duration'] > 60) {
	$call_duration = $inquiry_data['call_duration'] % 60;
	if ($call_duration < 10) {
		$call_duration = '0' . $call_duration;
	}

	$time = floor($inquiry_data['call_duration'] / 60);
	echo $time . ":" . $call_duration;
} else {
	$call_duration = $inquiry_data['call_duration'];
	if ($call_duration < 10) {
		$call_duration = '0' . $call_duration;
	}
	echo "0:" . $call_duration;
} ?><br />
<br />
</fieldset>
</div>
<?php
exit;
	}

	function encrypt($data)
	{
		$key[] = '';
		$box[] = '';
		$cipher = '';

		$data_length = strlen($data);
		if($data_length == 0)
		return'';
		for ($i = 0; $i < 256; $i++) {
			$key[$i] = ($i % $data_length);
			$box[$i] = $i;
		}


		for ($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $key[$i]) % 256;
			$box[$i] ^= $box[$j];
			$box[$j] ^= $box[$i];
			$box[$i] ^= $box[$j];
		}

		for ($a = $j = $i = 0; $i < $data_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;

			$box[$a] ^= $box[$j];
			$box[$j] ^= $box[$a];
			$box[$a] ^= $box[$j];

			$k = $box[(($box[$a] + $box[$j]) % 256)];
			$cipher .= chr(ord($data[$i]) ^ $k);
		}

		return $cipher;
	}

	function decrypt($data) {
		return $this->encrypt($data);
	}

}
?>
