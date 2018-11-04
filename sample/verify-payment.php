<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Saderat Payment</title>
</head>
<body>
<h1>Parsian Bank Payment Test Page with Verify Payment</h1>
<form method="post" action="/parsian/sample/request-payment.php">
    <fieldset>
        <legend>Sendeing Data</legend>
        <input type="hidden" name="callbackurl"
               value="Your website url/verify-payment.php"/>
        <div>
            <label for="Amount">Amount:</label>
            <input type="text" name="amount" value="1000"/>
        </div>
        <div>
            <label for="Payload">Payload(Description):</label>
            <input type="text" name="payload" value="test"/>
        </div>
        <br>
        <div>
            <label>&nbsp;</label>
            <input type="submit" value="Send" class="submit"/>
        </div>
    </fieldset>
</form>

<br>

</body>
</html>

<?php

use Dpsoft\Parsian\Parsian;

require "../vendor/autoload.php";

echo $_REQUEST["test"];
var_dump($_POST);

if (isset($_POST['Token'])) {
    echo 'post set';
}
try {
    $response = new Parsian('xxx');

    if ($_POST) {
        /**
         * @method $verify return array of all response value
         */
        $results = $response->verify($token, $amount);

        print_r($results);

        echo "<br>Successful payment ...";
    }
} catch (Exception $exception) {
    echo $exception->getMessage();
}

?>
