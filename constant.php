<?php
define('CALLER_ID', '888-736-7633');
define("WEBSITE_NAME","FormActivate");
$host = $_SERVER['HTTP_HOST'];
$subDomain = substr($host, 0, strpos($host, '.'));

define("WEBSITE_URL","http://app.formactivate.com/");
define("PUBLIC_SITE_URL","http://www.formactivate.com/");
define("PROCESS_URL","http://process.formactivate.com/");
define("WEBROOT_PATH","/var/www/html/app.formactivate.com/");
define("__VIRTUAL_DIRECTORY__","/");

/**
 * 
 * SMTP Settings
 */
define("SMTP_HOST","smtp.sendgrid.net");
define("SMTP_USERNAME","bslavin");
define("SMTP_USER_PASSWORD","referredly");
define("SMTP_PORT",2525);

define("ADMIN_WEBSITE_NAME","FormActivate");
define("SITE_NO_REPLY_EMAIL","no-reply@formactivate.com");
define("DEMO_CONFIRMATION_EMAIL","demo@formactivate.com");
define("SIGNUP_CONFIRMATION_EMAIL","signup@formactivate.com");

define("ADMIN_EMAIL","support@formactivate.com");
define("SITE_SUPPORT_EMAIL","support@formactivate.com");

/**
 * 
 * DB Settings
 */
define("DB_HOST","db1.formactivate.com");
define("DB_USERNAME","formappuse");
define("DB_USER_PASSWORD","N01hOIAH13h4A");
define("DB_DBNAME","formactapp1");


/**
 * 
 * WEBSITE Settings
 */

define("WEBSITE_JS_URL",WEBSITE_URL."library/JS/");
define("WEBSITE_CSS_URL",WEBSITE_URL."css/");
define("WEBSITE_EXTERNALJS_URL",WEBSITE_URL."js/");
define("WEBSITE_IMG_URL",WEBSITE_URL."images/");


define("__JS_DIRECTORY__",__VIRTUAL_DIRECTORY__."library/JS/");
define("TODAY_DATE_TIME",date("Y-m-d H:i:s"));
define("TWILIO_API_VERSION","2010-04-01");
define("TWILIO_ACCOUNT_SID","AC2dbe8a176e89ff7f6641d4d03c047bca");
define("TWILIO_AUTH_TOKEN","1a0e256d656ebef49cbe84cd5e441501");
define("TWILIO_STATUS_CALL_BACK_URL",WEBSITE_URL."/status_call_back_url.php");

define("ENVIRONMENT","production"); //Environment options include production, staging, development

if(ENVIRONMENT == "production") {
	// Braintree Production
	define("MERCHANTID","4626g3h4g9vjc3rh");
	define("PUBLICKEY","qtz34s8xjv6kmwb4");
	define("PRIVATEKEY","vgssqbnv4g3ppb94");
}
else if(ENVIRONMENT == "staging") {
  // Braintree Sandbox
  define("MERCHANTID","4626g3h4g9vjc3rh");
	define("PUBLICKEY","yhdnqczdxrsp65c9");
	define("PRIVATEKEY","6ncvxb4vp4r9469p");
}
else if(ENVIRONMENT == "development") {
  // Braintree Dev
	define("MERCHANTID","4626g3h4g9vjc3rh");
	define("PUBLICKEY","yhdnqczdxrsp65c9");
	define("PRIVATEKEY","6ncvxb4vp4r9469p");
	
}
else if(ENVIRONMENT == "sandbox") {
  // Braintree Sandbox
	define("MERCHANTID","r5k644yrbbzyw7pv");
	define("PUBLICKEY","ctkqwstysgjx2ws6");
	define("PRIVATEKEY","zrm65dcgn7cg7xcv");
	
}
//////////////////////////////////////////////////////////////////////////////

//----------------------------------------
// Sign Up
//----------------------------------------
define("SIGNUP_FIRST_NAME","Please enter your first name");
define("SIGNUP_LAST_NAME","Please enter your last name");
define("SIGNUP_EMAIL","Please enter a valid email address");
define("SIGNUP_PASS1","Please specify a password that contains a combination of letters and numbers");
define("SIGNUP_PASS2","Please re-enter the password again");

define("SIGNUP_CREDIT_CARD_TYPE","Please select your credit card type");
define("SIGNUP_CREDIT_CARD_NUMBER","Please enter your credit card number without any spaces or special characters");
define("SIGNUP_CREDIT_CARD_EXPIRE","Please select your credit card expriation month and year");
define("SIGNUP_CREDIT_CARD_NAME_ON_CARD","Please enter the name as is appears on your credit card");
define("SIGNUP_CVV","Please enter the verification code on your credit card. Visa and Mastercard have a 3-digit number on the back of the card. American Express has a 4-digit number on the front of the card");

define("SIGNUP_ADDRESS1","Please enter your billing address");
define("SIGNUP_CITY","Please enter your city");
define("SIGNUP_STATE","Please enter your state");
define("SIGNUP_ZIP","Please enter your zip code");
define("SIGNUP_PHONE","Please enter your billing phone number");

//----------------------------------------
// Form Creation
//----------------------------------------

define("OVERVIEW_FORM_NAME","The form name is a label that is used internally to idenfiy the form. It will not be displayed on the web page");
define("OVERVIEW_FORM_BUSNESPH","This is the connection number that will ring when a form is submitted during office hours");
define("OVERVIEW_FORM_HOMEPH","This is the connection number that will ring when a form is submitted after hours");
define("OVERVIEW_FORM_CALLER_ID","At least one phone number must be validated. This number will be shown as a caller id to the prospective lead");

define("STDFIELD_PREVIEW","Once the mandatory fields have been specified, you may preview the rendered form");

define("STDFIELD_HELP","Standard form fields are commonly used on web forms. Please remember to select at least one field to be announced to the business owner when a form is submitted.");
define("WUFOOSTDFIELD_HELP","These form fields are commonly used on web forms. Please remember to select at least one field to be announced to the business owner when a form is submitted.");
define("CUSTOME_FORM_HELP","Forms may be customized with any user-selected form field. The Label is shown on the web page and any of these fields may be announced to the business owner when a form is submited.");

define("BUSINES_HOUR_HELP","The Hours of Operation determine whether calls are placed outside business hours. If an after hours connection number is specified in the overview section, this number is dialed when a form is submitted after hours");

define("BISNESRULE_RULE_NAME","Provide a descriptive name for the business rule");
define("BISNESRULE_STATUS","Select whether the busienss rule is currently active or inactive");
define("BISNESRULE_CONDITION","Select the most appropriate rule condition from the list of available options");
define("BISNESRULE_TIME","Specify the time based on the rule condition above");
define("BISNESRULE_CON_NUMBER","Specify a connection number to override the default value stored on the overview section");
define("BISNESRULE_CUSTOM_FIELD","Select from the list of included standard or custom fields");
define("BISNESRULE_CUSTOM_FIELD_VALUE","Enter a value for the standard or custom rule");

define("INCOMPLETE_CALL","Send email notifications for calls that are incomplete");
define("AFTER_HOUR_CALL","Send email notifications for calls that are placed after hours");
define("UNANSWER_CALL","Send email notifications for calls that are not answered");
define("ANSWER_CALL","Send email notifications for calls that are answered");
define("CONNECTIONS","Send email notifications for calls that are successfully connected to the prospective lead");
define("PROS_EMAIL","Select whether to include the prospective lead on the confirmation email from the form submission");

define("REDIRECT_TYPE","Determine whether the form should be redirected to a specific URL after it has been submitted");
define("REDIRECT_URL","Please enter the full URL as http://www.somewhere.com");

define("OLD_PASSWORD","Please enter your current password");

?>
