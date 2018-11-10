# Parsian bank payment package

[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl.html)

Easily integrate PHP application with parsian bank payment.

# Installation
``` bash
$ composer require dpsoft/parsian-payment
```

# Implementation
Attention: The Parsian Bank webservice just available with IP that allowed with Parsian Bank.
<br><br>[استفاده از درگاه بانک پارسیان در زبان Php](https://dpsoft.ir/%D9%88%D8%A8%D9%84%D8%A7%DA%AF/26/%D8%A7%D8%B3%D8%AA%D9%81%D8%A7%D8%AF%D9%87-%D8%A7%D8%B2-%D8%AF%D8%B1%DA%AF%D8%A7%D9%87-%D9%BE%D8%B1%D8%AF%D8%A7%D8%AE%D8%AA-%D8%A8%D8%A7%D9%86%DA%A9-%D9%BE%D8%A7%D8%B1%D8%B3%DB%8C%D8%A7%D9%86-%D8%AF%D8%B1-%D8%B2%D8%A8%D8%A7%D9%86-PHP)
#### Request payment
```php
<?php 
use Dpsoft\Parsian\Parsian;

try{
    /**
    * @param int $pin (required) The Parsian gateway pin code 
    */
    $parsian = new Parsian($pin);
	
    /**
     * @param int $amount (required) The amount that customer must pay
     * @param string $callbackUrl (required) The url that customer redirect to after payment
     * @param int $orderId (optional) The unique order id, generate by package if value passed null
     * @param int $additionalData (optional) addition data
	 *
	 * @method payRequest Return array contain transaction `token` and `orderId` and you cat save.
     * $token = $response['token'];
     * $orderId = $response['orderId'];
     *     
     */
    $response = $parsian->payRequest($amount, $callbackUrl, $orderId, $additionalData);
    
    /**
     * Redirect user to payment gateway
     */
     echo $parsian->redirect();
    
    /**
     * If you need to get payment url for application or another reason you can use this method  
     * 
     * $paymentUrl = $parsian->getPaymentUrl();
     * 
     */
}catch (\Throwable $exception){
    echo $exception->getMessage();
}
```
#### Verify transaction or maybe reverse transaction in conditions
Customer redirect to callback url with all transaction data and you must verify or rollback transaction.

#### verify:
```php
<?php
use Dpsoft\Parsian\Parsian;

try{
        /**
         * @param int $pin (required) The Parsian gateway pin code 
         */
        $parsian = new Parsian($pin);
	
        /**
          * @var $token (required) Your transaction token you need to verify
          * @var $amount (required) Your consider amount for compare with payment amount
          * 
          * @method $verify return array of  transaction data.
          * $token = $response['Token'];
          * $orderId = $response['OrderId'];
          * $terminalNo = $response['TerminalNo'];
          * $RRN = $response['RRN'];
          * $hashCardNumber = $response['HashCardNumber'];
          * $amount = $response['Amount'];
          */
        $response = $parsian->verify($token, $amount);
        
        echo "Successful payment ...";
}catch (\Throwable $exception){
    echo $exception->getMessage();
}
```
#### Reverse transaction
If you don't verify transaction you can reverse it.

```php
<?php
use Dpsoft\Parsian\Parsian;

try{
        /**
         * @param int $pin (required) The Parsian gateway pin code 
         */
        $parsian = new Parsian($pin);
	
        /**
         * @param int $pin (required) The Parsian gateway pin code 
         */
        $parsian->reverse($token);
    	
        echo "Transaction reverse successful ...";
       
}catch (\Throwable $exception){
    echo $exception->getMessage();
}
```

## License
[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl.html)

Copyright (c) 2018 [dpsoft.ir](http://dpsoft.ir)

  


