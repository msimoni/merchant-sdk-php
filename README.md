# SysPay PHP Merchant SDK

## Installation
This library requires php 5.2+ along with the `json` and the `curl` extensions.

### Using composer

The easiest way to start using our SDK and stay up to date is by adding it to your composer dependencies:
```
$ composer.phar require syspay/merchant-sdk-php dev-master
```

### Manual

Obtain a copy of this SDK:
```
$ git clone https://github.com/syspay/merchant-sdk-php.git
```

Then, include our loader.php:
```php
<?php
require_once('/path/to/loader.php');
```

## Requesting the API
Implementation reference: [SysPay Processing API](https://app.syspay.com/docs/api/merchant_api.html)

### Create a client
All operations are requested via an instance of a [Client object](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Client.html).

```php
<?php
$client = new Syspay_Merchant_Client($username, $secret[, $baseUrl]);
```

To call against the sandbox environment, the `$baseUrl` can be set to `Syspay_Merchant_Client::BASE_URL_SANDBOX`.

### Creditcard payment request (server to server)

Request class: [Syspay\_Merchant\_PaymentRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_PaymentRequest.html)

You request a [payment](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Payment.html) for a [customer](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Customer.html) on a given [creditcard](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Creditcard.html):

```php
<?php
$paymentRequest = new Syspay_Merchant_PaymentRequest(Syspay_Merchant_PaymentRequest::FLOW_API);
$paymentRequest->setPaymentMethod(Syspay_Merchant_PaymentRequest::METHOD_CREDITCARD);
$paymentRequest->setBillingAgreement(true); // true means you want to be able to rebill this customer later. Defaults to false

$customer = new Syspay_Merchant_Entity_Customer();
$customer->setEmail('foo@bar.baz'); // Customer's email
$customer->setLanguage('en'); // Optional, used to send notifications in the correct language
$customer->setIp('1.2.3.4'); // Customer IP address
$paymentRequest->setCustomer($customer);

$creditcard = new Syspay_Merchant_Entity_Creditcard();
$creditcard->setHolder('John Doe');
$creditcard->setNumber('4111111111111111');
$creditcard->setCvc('123');
$creditcard->setExpMonth('01');
$creditcard->setExpYear('2014');
$paymentRequest->setCreditcard($creditcard);

$payment = new Syspay_Merchant_Entity_Payment();
$payment->setReference('1234567'); // Your own reference for this payment
$payment->setPreauth(true); // By default, we will process a DIRECT payment. Set this to true to PREAUTH only, it will then need to be confirmed later
$payment->setAmount(1000); // Amount in *cents*
$payment->setCurrency('EUR'); // Currency
$payment->setDescription('some description'); // An optional description
$payment->setExtra(json_encode($someInformation)); // An optional information that will given back to you on notifications
$paymentRequest->setPayment($payment);

$payment = $client->request($paymentRequest);
// $payment is an instance of Syspay_Merchant_Entity_Payment
```

### Hosted payment request

Request class: [Syspay\_Merchant\_PaymentRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_PaymentRequest.html)

```php
<?php
$paymentRequest = new Syspay_Merchant_PaymentRequest(Syspay_Merchant_PaymentRequest::FLOW_BUYER);
$paymentRequest->setMode(Syspay_Merchant_PaymentRequest::MODE_ONLINE); // Refer to the documentation for the 'terminal' mode
$paymentRequest->setBillingAgreement(true); // true means you want to be able to rebill this customer later. Defaults to false

// Assigning a customer to the request is optional on the hosted flow but recommended to pre-fill the payment form
$customer = new Syspay_Merchant_Entity_Customer();
$customer->setEmail('foo@bar.baz'); // Customer's email
$customer->setLanguage('en'); // Used to send notifications in the correct language and overwrite the payment page language (by default it tries to accomodate the browser settings)
$paymentRequest->setCustomer($customer);

$payment = new Syspay_Merchant_Entity_Payment();
$payment->setReference('1234567'); // Your own reference for this payment
$payment->setAmount(1000); // Amount in *cents*
$payment->setCurrency('EUR'); // Currency
$payment->setDescription('some description'); // An optional description
$payment->setExtra(json_encode($someInformation)); // An optional information that will given back to you on notifications
$paymentRequest->setPayment($payment);

$payment = $client->request($paymentRequest);
// $payment is an instance of Syspay_Merchant_Entity_Payment, you can now redirect your customer to $payment->getRedirect()
```

### Confirm an AUTHORIZED payment

Request class: [Syspay\_Merchant\_ConfirmRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_ConfirmRequest.html)

```php
<?php
$confirmRequest = new Syspay_Merchant_ConfirmRequest();
$confirmRequest->setPaymentId($originalPaymentId); // Returned to you on the initial payment request

$confirm = $client->request($confirmRequest);
// $confirm is an instance of Syspay_Merchant_Entity_Payment
```

### Void an AUTHORIZED payment

Request class: [Syspay\_Merchant\_VoidRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_VoidRequest.html)

```php
<?php
$voidRequest = new Syspay_Merchant_VoidRequest();
$voidRequest->setPaymentId($originalPaymentId); // Returned to you on the initial payment request

$void = $client->request($voidRequest);
// $void is an instance of Syspay_Merchant_Entity_Payment
```

### Get information about a payment

Request class: [Syspay\_Merchant\_PaymentInfoRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_PaymentInfoRequest.html)

```php
<?php
$infoRequest = new Syspay_Merchant_PaymentInfoRequest($paymentId);

$payment = $client->request($infoRequest);
// $payment is an instance of Syspay_Merchant_Entity_Payment
```

### Export a list of payments

Request class: [Syspay\_Merchant\_PaymentListRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_PaymentListRequest.html)
The list of available filters can be found in our [api documentation](https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-a-list-of-payments)

```php
<?php
$paymentListRequest = new Syspay_Merchant_PaymentListRequest();
// Optionally set filters (refer to the API documentation for an exhaustive list)
$paymentListRequest->addFilter('start_date', $someTimestamp);
$paymentListRequest->addFilter('end_date', $someOtherTimestamp);

$payments = $client->request($paymentListRequest);
// $payments is an array of Syspay_Merchant_Entity_Payment
```

### Refund a payment

Request class: [Syspay\_Merchant\_RefundRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_RefundRequest.html)

You request a [refund](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Refund.html) on a given *payment id*:

```php
<?php
$refund = new Syspay_Merchant_Entity\Refund();
$refund->setReference('1234567'); // Your own reference for this refund
$refund->setAmount(1000); // The amount to refund in *cents*
$refund->setCurrency('EUR'); // The currency of the refund. It must match the one of the original payment
$refund->setDescription('some description'); // An optional description for this refund
$refund->setExtra(json_encode($someInformation)); // An optional information that will be given back to you on notifications

$refundRequest = new Syspay_Merchant_RefundRequest();
$refundRequest->setPaymentId($paymentId); // The payment id to refund
$refundRequest->setRefund($refund);

$refund = $client->request($refundRequest);
// $refund is an instance of Syspay_Merchant_Entity_Refund
```

### Get information about a refund

Request class: [Syspay\_Merchant\_RefundInfoRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_RefundInfoRequest.html)

```php
<?php
$infoRequest = new Syspay_Merchant_RefundInfoRequest($refundId);

$refund = $client->request($infoRequest);
// $refund is an instance of Syspay_Merchant_Entity_Refund
```

### Export a list of refunds

Request class: [Syspay\_Merchant\_RefundListRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_RefundListRequest.html)
The list of available filters can be found in our [api documentation](https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-a-list-of-refunds)

```php
<?php
$refundListRequest = new Syspay_Merchant_RefundListRequest();
// Optionally set filters (refer to the API document for an exhaustive list)
$paymentListRequest->addFilter('status', 'SUCCESS');

$refunds = $client->request($refundListRequest);
// $refunds is an array of Syspay_Merchant_Entity_Refund
```

### Rebill on a given billing agreement

Request class: [Syspay\_Merchant\_RebillRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_RebillRequest.html)

```php
<?php
// The billing agreement id returned from the initial payment request must be used
$rebillRequest = new Syspay_Merchant_RebillRequest($billingAgreementId);
$rebillRequest->setAmount(1000); // Amount in *cents*
$rebillRequest->setCurrency('EUR'); // This is used as security and must match the currency that was used to create the billing agreement
$rebillRequest->setReference('123456'); // Your own reference for this payment
$rebillRequest->setDescription('some description'); // An optional description
$rebillRequest->setExtra(json_encode($someInformation)); // An optional information that will given back to you on notifications
$rebillRequest->setEmsUrl('https://foo.bar/baz'); // An optional EMS url the notifications will be posted to if you don't want to use the default one

$payment = $client->request($rebillRequest);
// $payment is an instance of Syspay_Merchant_Entity_Payment
```

### Get information about a billing agreement

Request class: [Syspay\_Merchant\_BillingAgreementInfoRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_BillingAgreementInfoRequest.html)

```php
<?php
$infoRequest = new Syspay_Merchant_BillingAgreementInfoRequest($billingAgreementId);

$billingAgreement = $client->request($infoRequest);
// $billingAgreement is an instance of Syspay_Merchant_Entity_BillingAgreement
```

### Cancel a billing agreement

Request class: [Syspay\_Merchant\_BillingAgreementCancellationRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_BillingAgreementCancellationRequest.html)

```php
<?php
$cancellationRequest = new Syspay_Merchant_BillingAgreementCancellationRequest($billingAgreemntId);

$billingAgreement = $client->request($cancellationRequest);
// $billingAgreement is an instance of Syspay_Merchant_Entity_BillingAgreement
```

### Export a list of billing agreements

Request class: [Syspay\_Merchant\_BillingAgreementListRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_BillingAgreementListRequest.html)
The list of available filters can be found in our [api documentation](https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-a-list-of-billing-agreements)

```php
<?php
$billingAgreementsRequest = new Syspay_Merchant_BillingAgreementListRequest();
// Optionally set filters (refer to the API document for an exhaustive list)
$billingAgreementsRequest->addFilter('status', 'ACTIVE');

$billingAgreements = $client->request($billingAgreementsRequest);
// $billingAgreements is an array of Syspay_Merchant_Entity_BillingAgreement
```

### Get information about a chargeback

Request class: [Syspay\_Merchant\_ChargebackInfoRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_ChargebackInfoRequest.html)

```php
<?php
$infoRequest = new Syspay_Merchant_ChargebackInfoRequest($chargebackId);

$chargeback = $client->request($infoRequest);
// $chargeback is an instance of Syspay_Merchant_Entity_Chargeback
```

### Export a list of chargebacks

Request class: [Syspay\_Merchant\_ChargebackListRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_ChargebackListRequest.html)
The list of available filters can be found in our [api documentation](https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-a-list-of-chargebacks)

```php
<?php
$chargebackListRequest = new Syspay_Merchant_ChargebackListRequest();
// Optionally set filters (refer to the API document for an exhaustive list)
$paymentListRequest->addFilter('email', 'foo@bar.baz');

$chargebacks = $client->request($chargebackListRequest);
// $chargebacks is an array of Syspay_Merchant_Entity_Chargeback
```

### Get the Syspay IP addresses

Request class: [Syspay\_Merchant\_IpAddressesRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_IpAddressesRequest.html)

```php
<?php
$ipRequest = new Syspay_Merchant_IpAddressesRequest();

$ips = $client->request($ipRequest);
// $ips is an array of strings (ips)
```

### Creating a subscription or instalment plan

Request class: [Syspay\_Merchant\_PlanRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_PlanRequest.html)

The main differences between a subscription and an instalment plan are:

* For instalment plan, you give a total amount and a number of cycles, we then take care of splitting the total payment.
* Instalment plans do not support trial periods nor distinct initial amounts. If you need these you will have to use subscriptions.
* Instalment plans are ENDED when the last payment is taken, while a subscription will be ENDED one 'cycle' after the last payment.

#### Example 1: 2-week trial at 1 EUR, followed by a recurring monthly payment of 30 EUR until unsubscription

```php
<?php
$plan = new Syspay_Merchant_Entity_Plan();
$plan->setType(Syspay_Merchant_Entity_Plan::TYPE_SUBSCRIPTION);
$plan->setName('Product 01');
$plan->setDescription('2 weeks for 1EUR / 30EUR monthly');
$plan->setCurrency('EUR');

// Trial is 1 time 15 minutes at 1 EUR
$plan->setTrialAmount(100); // Amounts are in cents
$plan->setTrialCycles(1);
$plan->setTrialPeriod(2);
$plan->setTrialPeriodUnit(Syspay_Merchant_Entity_Plan::UNIT_WEEK);
// Billing is until unsubscription (0 cycles), every 1 month
$plan->setBillingCycles(0);
$plan->setBillingAmount(3000);
$plan->setBillingPeriod(1);
$plan->setBillingPeriodUnit(Syspay_Merchant_Entity_Plan::UNIT_MONTH);

$planRequest = new Syspay_Merchant_PlanRequest();
$planRequest->setPlan($plan);
$plan = $client->request($planRequest);
// $plan is an instance of Syspay_Merchant_Entity_Plan
```

#### Example 2: No trial, but an initial payment of 45 EUR followed by 3 payments of 20 EUR every 2 months

In this case there are 4 billing cycles, including the initial one which has a different amount.

```php
<?php
$plan = new Syspay_Merchant_Entity_Plan();
$plan->setType(Syspay_Merchant_Entity_Plan::TYPE_SUBSCRIPTION);
$plan->setName('Product 02');
$plan->setDescription('45 initial / 3 * 20 EUR');
$plan->setCurrency('EUR');

// 4 Billing cycles of 20 EUR, except for the initial one
$plan->setBillingCycles(4);
$plan->setInitialAmount(4500);
$plan->setBillingAmount(2000);
$plan->setBillingPeriod(2);
$plan->setBillingPeriodUnit(Syspay_Merchant_Entity_Plan::UNIT_MONTH);

$planRequest = new Syspay_Merchant_PlanRequest();
$planRequest->setPlan($plan);
$plan = $client->request($planRequest);
// $plan is an instance of Syspay_Merchant_Entity_Plan
```

#### Example 3: Instalment plan, take 100 EUR in 3 times, once per month

```php
<?php
$plan = new Syspay_Merchant_Entity_Plan();
$plan->setType(Syspay_Merchant_Entity_Plan::TYPE_INSTALMENT);
$plan->setName('Product 03');
$plan->setDescription('100 EUR in 3 months');
$plan->setCurrency('EUR');

// 3 billing cycles of 1 month, 100 EUR to take
// Note: This will create 1 payment of 33.34 and 2 payments of 33.33 EUR
$plan->setBillingCycles(3);
$plan->setTotalAmount(10000);
$plan->setBillingPeriod(1);
$plan->setBillingPeriodUnit(Syspay_Merchant_Entity_Plan::UNIT_MONTH);

$planRequest = new Syspay_Merchant_PlanRequest();
$planRequest->setPlan($plan);
$plan = $client->request($planRequest);
// $plan is an instance of Syspay_Merchant_Entity_Plan
```

### Subscribing your customer to a plan

Request class: [Syspay\_Merchant\_SubscriptionRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_SubscriptionRequest.html)

As with standard payments, there are 2 ways to subscribe a user to a plan:

* Hosted: In this case a link will be given back to you. You need to redirect the customer to that link for him to complete the payment.
* Server-2-server: You send us the customer and payment information along with the subscription data and the payment will be processed on the fly. If a redirection is needed to complete the payment (e.g 3DS), this URL will be given back to you so you can redirect the customer for him to complete the payment.

In both cases, the subscription starts (becomes ACTIVE) when the initial payment is complete or is cancelled if it failed.

#### Example 1: Hosted payment page

```php
<?php
// You must at least provide the customer's email
$customer = new Syspay_Merchant_Entity_Customer();
$customer->setEmail('your@customer.com');
$customer->setLanguage('fr');

$subscription = new Syspay_Merchant_Entity_Subscription();
$subscription->setPlanId(61); // The id of the plan to subscribe to
$subscription->setReference('123456'); // Your own reference for this subscription (and all the payments that will be linked to it)
$subscription->setExtra(json_encode($someInformation)); // An optional information that will be given back to you on notifications

$subscriptionRequest = new Syspay_Merchant_SubscriptionRequest();
$subscriptionRequest->setSubscription($subscription);
$subscriptionRequest->setFlow(Syspay_Merchant_SubscriptionRequest::FLOW_BUYER); // Hosted payment page
$subscriptionRequest->setCustomer($customer);

$subscription = $client->request($subscriptionRequest);
// $subscription is an instance of Syspay_Merchant_Entity_Subscription
// The link to redirect the customer to will be available in $subscription->getRedirect();
```

#### Example 2: Server-2-server

```php
<?php
// For server-2-server we require you to send the customer's IP
$customer = new Syspay_Merchant_Entity_Customer();
$customer->setEmail('your@customer.com');
$customer->setLanguage('fr'); // Defaults to english
$customer->setIp('1.2.3.4'); // Mandatory for server-2-server flow

$creditcard = new Syspay_Merchant_Entity_Creditcard();
$creditcard->setHolder('John Doe');
$creditcard->setNumber('4111111111111111');
$creditcard->setCvc('123');
$creditcard->setExpMonth('01');
$creditcard->setExpYear('2015');

$subscription = new Syspay_Merchant_Entity_Subscription();
$subscription->setPlanId(61); // The id of the plan to subscribe to
$subscription->setReference(uniqid()); // Your own reference for this subscription (and all the payments that will be linked to it)
$subscription->setExtra(json_encode($someInformation)); // An optional information that will be given back to you on notifications

$subscriptionRequest = new Syspay_Merchant_SubscriptionRequest();
$subscriptionRequest->setSubscription($subscription);
$subscriptionRequest->setFlow(Syspay_Merchant_SubscriptionRequest::FLOW_API); // server-2-server flow
$subscriptionRequest->setCustomer($customer);

// Use the created creditcard to pay
$subscriptionRequest->setPaymentMethod(Syspay_Merchant_Entity_PaymentMethod::TYPE_CREDITCARD);
$subscriptionRequest->setCreditcard($creditcard);

$subscription = $client->request($subscriptionRequest);
// $subscription is an instance of Syspay_Merchant_Entity_Subscription
// The subscription will be ACTIVE on success, CANCELLED on failure, or PENDING if there is a redirect to do
```

### Cancel a subsription or instalment plan

Request class: [Syspay\_Merchant\_SubscriptionCancellationRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_SubscriptionCancellationRequest.html)

```php
<?php
$cancelRequest = new Syspay_Merchant_SubscriptionCancellationRequest($subscriptionId);
$subscription = $client->request($cancelRequest);
// $subscription is an instance of Syspay_Merchant_Entity_Subscription
```

### Get information about a plan

Request class: [Syspay\_Merchant\_PlanInfoRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_PlanInfoRequest.html)

```php
<?php
$planInfoRequest = new Syspay_Merchant_PlanInfoRequest($planId);
$plan = $client->request($planInfoRequest);
// $plan is an instance of Syspay_Merchant_Entity_Plan
```


### Get information about a subscription

Request class: [Syspay\_Merchant\_SubscriptionInfoRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_SubscriptionInfoRequest.html)

```php
<?php
$subInfoRequest = new Syspay_Merchant_SubscriptionInfoRequest($subscriptionId);
$subscription = $client->request($subInfoRequest);
// $subscription is an instance of Syspay_Merchant_Entity_Subscription
```

## Handling hosted payment pages and 3DS redirections

When a payment requires a redirection (either during a server-to-server payment that needs a 3DS verification, or when using the hosted payment page flow), you will not know synchronously the result of the transaction.

Instead, once the transaction will be processed, the customer will be redirected back to your site (either to your default redirect url, or to the one you set upon request) along with extra parameters that will inform you about the result of the transaction.

To make it easy for you to validate these extra parameters and extract the information, you can use the [Syspay\_Merchant\_Redirect](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Redirect.html) handler. It will check that the parameters haven't been tampered with and return a [payment](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Payment.html) object.

NOTE: Should the customer not come back to your site (e.g, he closes his browser on the hosted payment confirmation page), you will still be notified about the payment status using our EMS system (described in the [next chapter](#receiving-ems-notifications))

### Sample handler

```php
<?php

require '/path/to/merchant-sdk-php/loader.php';

// You might have multiple merchant credentials that all point to the same EMS url
$secrets = array(
    'login_1' => 'secret_1',
    'login_2' => 'secret_2'
);

try {
    $redirect = new Syspay_Merchant_Redirect($secrets);
    // The getResult method takes an array as input. This array must contain the 'result', 'merchant' and 'checksum' request parameters
    $payment = $redirect->getResult($_REQUEST);
} catch (Syspay_Merchant_RedirectException $e) {
    // If an error status is sent, syspay will try again to deliver the message several times.
    header(':', true, 500);
    printf("Something went wrong while processing the message: (%d) %s\n",
                $e->getCode(), $e->getMessage());
}
```


## Receiving EMS notifications

Implementation reference: [SysPay Event Messaging System](https://app.syspay.com/bundles/emiuser/doc/merchant_ems.html)

The [Syspay\_Merchant\_EMS](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_EMS.html) class will automatically validate the HTTP headers and parse the event to return the relevant object.

The currently supported events are:

* Payments ([Syspay\_Merchant\_Entity\_Payment](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Payment.html))
* Refunds ([Syspay\_Merchant\_Entity\_Refund](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Refund.html))
* Billing agreements ([Syspay\_Merchant\_Entity\_BillingAgreement](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_BillingAgreement.html))
* Chargebacks ([Syspay\_Merchant\_Entity\_Chargeback](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Chargeback.html))
* Subscription ([Syspay\_Merchant\_Entity\_Subscription](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Subscription.html))

If an error occurs, a [Syspay\_Merchant\_EMSException](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_EMSException.html) will be thrown and will contain one of the following codes:

* `Syspay_Merchant_EMSException::CODE_MISSING_HEADER`: The *X-Merchant* and/or the *X-Checksum* headers could not be found
* `Syspay_Merchant_EMSException::CODE_INVALID_CHECKSUM`: The *X-Checksum* header doesn't validate against the provided keys
* `Syspay_Merchant_EMSException::CODE_INVALID_CONTENT`: The request content could not be parsed
* `Syspay_Merchant_EMSException::CODE_UNKNOWN_MERCHANT`: The *X-Merchant* header is there but cannot be found in the array passed to the constructor

### Sample listener

```php
<?php

require '/path/to/merchant-sdk-php/loader.php';

// You might have multiple merchant credentials that all point to the same EMS url
$secrets = array(
    'login_1' => 'secret_1',
    'login_2' => 'secret_2'
);

$ems = new Syspay_Merchant_EMS($secrets);

try {
    $event = $ems->getEvent();
    switch ($event->getType()) {
        case 'payment':
            printf("Payment %d received, status: %s\n", $event->getId(), $event->getStatus());
            break;
        case 'refund':
            printf("Refund %d received, status: %s\n", $event->getId(), $event->getStatus());
            break;
        case 'chargeback':
            printf("Chargeback %d received, status: %s\n", $event->getId(), $event->getStatus());
            break;
        case 'billing_agreement':
            printf("Billing Agreement %d received, status: %s\n", $event->getId(), $event->getStatus());
            break;
        case 'subscription':
            printf("Subscription %d received, status: %s, phase: %s\n", $event->getId(), $event->getStatus(), $event->getPhase());
            break;
    }
} catch (Syspay_Merchant_EMSException $e) {
    // If an error status is sent, syspay will try again to deliver the message several times
    header(':', true, 500);
    printf("Something went wrong while processing the message: (%d) %s",
                $e->getCode(), $e->getMessage());
}
```
