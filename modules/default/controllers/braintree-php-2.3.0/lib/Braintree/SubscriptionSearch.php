<?php
class Braintree_SubscriptionSearch
{
    static function planId()
    {
        return new Braintree_TextNode("plan_id");
    }

    static function daysPastDue()
    {
        return new Braintree_TextNode("days_past_due");
    }

    static function status()
    {
        return new Braintree_MultipleValueNode("status");
    }

    static function ids()
    {
        return new Braintree_MultipleValueNode("ids");
    }
}
?>
