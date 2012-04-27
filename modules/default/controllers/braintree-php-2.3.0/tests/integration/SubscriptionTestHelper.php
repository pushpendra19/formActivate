<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_SubscriptionTestHelper extends PHPUnit_Framework_TestCase
{
    static function trialPlan()
    {
        return array(
            'description' => 'Plan for integration tests -- with trial',
            'id' => 'integration_trial_plan',
            'price' => '43.21',
            'trial_period' => true,
            'trial_duration' => 2,
            'trial_duration_unit' => 'day' // Braintree::Subscription::TrialDurationUnit::Day
        );
    }

    static function triallessPlan()
    {
        return array(
            'description' => 'Plan for integration tests -- without a trial',
            'id' => 'integration_trialless_plan',
            'price' => '12.34',
            'trial_period' => false
        );
    }

    static function createCreditCard()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/2010'
            )
        ));
        return $customer->creditCards[0];
    }

    static function createSubscription()
    {
        $plan = Braintree_SubscriptionTestHelper::triallessPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => Braintree_SubscriptionTestHelper::createCreditCard()->token,
            'price' => '54.99',
            'planId' => $plan['id']
        ));
        return $result->subscription;
    }
}

?>
