<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Saderat Payment</title>
</head>
<body>

</body>
</html>

<?php

use Dpsoft\Parsian\Parsian;

require "../vendor/autoload.php";
try {
    $parsian = new Parsian('xxx');
    $response = $parsian->payRequest($_POST['amount'], $_POST['callbackurl'], null, $_POST['payload']);

    //browser
    $parsian->redirect();

    //for app we can  get payment url
    //echo $parsian->getPaymentUrl();

} catch (Exception $exception) {
    echo $exception->getMessage();
}


?>
