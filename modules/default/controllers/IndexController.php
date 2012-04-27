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
class IndexController extends Zend_Controller_Action {

    public function init() {
        $auth = Zend_Auth::getInstance();
        $session = new Zend_Session_Namespace('Zend_Auth');
        $this->view->loggedin_customer_id = $session->member_id;
    }

    public function indexAction()
    {
        $session = new Zend_Session_Namespace('Zend_Auth');

        if ($session->member_id > 0 && $session->user_type == 0) {
            $this->_redirect('/customers/myaccount');
        } else if ($session->member_id > 0 && $session->user_type == 1) {
            $this->_redirect('/admin/customers/');
        }
        $this->_redirect('/customers/login');
    }

    function testEmailAction()
    {
        $config = array(
            'username' => SMTP_USERNAME,
            'password' => SMTP_USER_PASSWORD,
            'port' => SMTP_PORT,
            'auth' => 'plain'

        );

        $transport = new Zend_Mail_Transport_Smtp(SMTP_HOST, $config); 

        $mail = new Zend_Mail();
        $mail->setFrom(SITE_NO_REPLY_EMAIL, WEBSITE_NAME);
        $mail->addTo(SITE_NO_REPLY_EMAIL, WEBSITE_NAME);
        $mail->setSubject('Email Test');
        $mail->setBodyHtml('<p>Test</p>');
        $mail->send($transport);
        
    }

    
    public function howItWorksAction() {

        $this->view->actionName = 'how-it-works';
        $this->_helper->layout->setLayout('layout');
    }

    public function pricingAction() {

        $subscriptions = new subscriptions();
        $subscriptions_data = $subscriptions->fetchAll();

        $this->view->data = $subscriptions_data;

        $this->view->actionName = 'pricing';
        $this->_helper->layout->setLayout('layout');
    }

    public function supportAction() {

        $this->view->actionName = 'support';
        $this->_helper->layout->setLayout('layout');
    }

    

    function demoConfirmAction() {
        $this->view->actionName = 'demo-confirm';
    }

    public function demoAction() {

        $error = "";
        $this->view->actionName = 'demo';

        $formats = array(
                    '##########',
                    '###-###-####',
                    '(###)###-####',
                    '(###)#######',
                    '(###) ###-####',
                    '(###) #######',
                    '###.###.####'
                );

        if ($this->_request->isPost()) {
            $filter = new Zend_Filter_StripTags();

            $this->view->name    = $name          = stripcslashes($filter->filter($this->_request->getPost('name')));
            $this->view->email   = $email         = stripcslashes($filter->filter($this->_request->getPost('email')));
            $this->view->phone   = $user_phone    = stripcslashes($filter->filter($this->_request->getPost('phone')));
            $this->view->inquiry = $inquiry       = stripcslashes($filter->filter($this->_request->getPost('inquiry')));
            $this->view->phone2  = $to            = stripcslashes($filter->filter($this->_request->getPost('phone2')));


            if ($this->_request->getPost('name') == '') {
                $error = "* Please give your name<br />";
            }

            if ($this->_request->getPost('email') == '') {
                $error .= "* Please give your email address<br />";
            }

            if ($this->_request->getPost('phone') == '') {
                $error .= "* Please give your phone number<br />";
            } else {
                $user_phone = $filter->filter($this->_request->getPost('phone'));
                $user_phone = str_replace(array('(', ')', ' ', '.', '-'), '', $user_phone);
                $user_phone = substr($user_phone,0,10);
                $temp_user_phone = trim(preg_replace('/[0-9]/', '#', $user_phone));

                if (in_array($temp_user_phone, $formats)) {
                    $user_phone = substr($user_phone, 0, 3) . '-' . substr($user_phone, 3, 3) . '-'. substr($user_phone, 6, 4);
                } else {
                    $error .= "* Please enter valid phone number<br />";
                }
            }

            if ($this->_request->getPost('inquiry') == '') {
                $error .= "* Please write about your inquiry<br />";
            }

            if ($this->_request->getPost('phone2') == '') {
                $error .= "* Please give your announcement phone/mobile number<br />";
            } else {
                $to = $filter->filter($this->_request->getPost('phone2'));
                $to = str_replace(array('(', ')', ' ', '.', '-'), '', $to);
                $to = substr($to,0,10);
                $tempto = trim(preg_replace('/[0-9]/', '#', $to));

                if (in_array($tempto, $formats)) {
                    $to = substr($to, 0, 3) . '-' . substr($to, 3, 3) . '-'. substr($to, 6, 4);
                } else {
                    $error .= "* Please enter valid phone number<br />";
                }
            }
            
            //echo "TEMP $temp_user_phone == $tempto ::CALLER : {$user_phone} , TO: $to";

            if ($error == '') {
                require "twilio.php";

                $ApiVersion = TWILIO_API_VERSION; // config variable
                $AccountSid = TWILIO_ACCOUNT_SID; // take from config file
                $AuthToken  = TWILIO_AUTH_TOKEN;

                $data = '';

                $client = new TwilioRestClient($AccountSid, $AuthToken);                
                $CallerID = CALLER_ID;

                // Demo announcement message
                if (empty($inquiry)) {$inquiry = "Nothing in particular";}
                
                $data = "Their name is {$name}. They are inquiring about {$inquiry}. To connect to this customer press the 1 key, to announce the message again press the 0 key @#@#@@#{$user_phone}";
                
                $response = $client->request("/$ApiVersion/Accounts/$AccountSid/Calls",
                                "POST", array(
                                    "From" => $CallerID,
                                    "To"   => $to,
                                    "Url"  => WEBSITE_URL . "hello.php?data=" . base64_encode($data)
                                ));


                if (empty($response->IsError)) 
                {
                	$ownerHeader = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'><img src='". WEBSITE_IMG_URL. "branding.png' /> <hr />You have received a new inquiry through FormActivate. Details of the inquiry are included below <br><br>";
		 			
                	if($name!='')	 					
						$Body.=" Name : ".$name."<br><br>";
                	
                	if($email!='')	 					
						$Body.=" Email : ".$email."<br><br>";
                	
					if($user_phone!='')
						$Body.=" Phone Number is : ".$user_phone."<br><br>";
						
					if($inquiry!='')	 					
						$Body.=" Inquiry : ".$inquiry."<br><br>";
							
		 			$Body.=" Thanks <br><span style='color:black;'> ".WEBSITE_NAME."</span></div></div>";
		 			
                	$mail = new Zend_Mail();;
					$mail->setBodyHtml($ownerHeader.$Body);
					$mail->setFrom(SITE_NO_REPLY_EMAIL, WEBSITE_NAME);
					$mail->setSubject('You have received a new inquiry through '. WEBSITE_NAME);
					$mail->addTo(DEMO_CONFIRMATION_EMAIL, $name);
					$result = $mail->send();
                	
                    $this->_redirect('/demo-confirm');
                    exit;
                } else {
                    $error .= $response->ErrorMessage;
                }
            }

            $this->view->error = $error;
        } else {
            $this->view->error = $error;
            $this->view->actionName = 'demo';
            $this->_helper->layout->setLayout('layout');
        }
    }

    public function servicesAction() {
        $staticpage = new staticpage();
        $staticpage_data = $staticpage->fetchRow($staticpage->select()->where('id=2'));
        $this->view->staticpage_data = $staticpage_data;
        $this->view->actionName = 'services';
        $this->_helper->layout->setLayout('layout_services');
    }

    public function officesearchAction() {
        $staticpage = new staticpage();
        $staticpage_data = $staticpage->fetchRow($staticpage->select()->where('id=3'));
        $this->view->staticpage_data = $staticpage_data;
        $this->view->actionName = 'officesearch';
        $this->_helper->layout->setLayout('layout_services');
    }

    public function meetingroomsAction() {
        $staticpage = new staticpage();
        $staticpage_data = $staticpage->fetchRow($staticpage->select()->where('id=4'));
        $this->view->staticpage_data = $staticpage_data;
        $this->view->actionName = 'meetingrooms';
        $this->_helper->layout->setLayout('layout_services');
    }

    public function contactsAction() {
        $staticpage = new staticpage();
        $staticpage_data = $staticpage->fetchRow($staticpage->select()->where('id=5'));
        $this->view->staticpage_data = $staticpage_data;
        $this->view->actionName = 'contacts';
        $this->_helper->layout->setLayout('layout');
    }

    public function virtualofficeAction() {
        $staticpage = new staticpage();
        $staticpage_data = $staticpage->fetchRow($staticpage->select()->where('id=6'));
        $this->view->staticpage_data = $staticpage_data;
        $this->view->actionName = 'virtualoffice';
        $this->_helper->layout->setLayout('layout_services');
    }
}
?>
