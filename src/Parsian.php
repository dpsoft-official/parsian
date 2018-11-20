<?php namespace Dpsoft\Parsian;

use Dpsoft\Parsian\Exception\ParsianException;
use SoapClient;

class Parsian
{
    /**
     * @var string REQUEST_URL Url for initializing payment request
     */
    const REQUEST_URL = 'https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx?WSDL';

    /**
     * @var string GATE_URL Url for payment gateway
     */
    const GATE_URL = 'https://pec.shaparak.ir/NewIPG/?Token=';

    /**
     * @var string CONFIRM_URL Url for confirming transaction
     */
    const CONFIRM_URL = 'https://pec.shaparak.ir/NewIPGServices/Confirm/ConfirmService.asmx?WSDL';

    /**
     * @var string REVERSE_URL Url for reverse transaction
     */
    const REVERSE_URL = 'https://pec.shaparak.ir/NewIPGServices/Reverse/ReversalService.asmx?WSDL';

    /**
     * @var string $pin
     */
    public $pin;

    /**
     * @var string $token
     */
    public $token;

    /**
     * @var \SoapClient
     */
    private $client;

    /**
     * @param string $pin
     */
    public function __construct(string $pin)
    {
        $this->pin = $pin;
    }

    /**
     * Request token for generate payment gateway url
     *
     * @param int $amount in rial
     * @param string $callbackUrl Redirect url after payment
     * @param int $orderId = null
     * @param string $additionalData = null
     * @return string
     *
     * @throws ParsianException
     */
    public function payRequest(int $amount, string $callbackUrl, int $orderId = null, string $additionalData = null)
    {
        if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            throw new \Exception('callBackUrl نامعتبر است.');
        }

        if (!filter_var($amount, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1000)))) {
            throw new \Exception('مبلغ وارد شده نامعتبر است.');
        }

        if ($orderId === null) {
            $orderId = round(microtime(true) * 1000) + rand(0, 100000);
        }

        $params = array(
            'LoginAccount' => $this->pin,
            'Amount' => $amount,
            'OrderId' => $orderId,
            'CallBackUrl' => $callbackUrl,
            'AdditionalData' => $additionalData,
        );

        $client = $this->client ?? new SoapClient(self::REQUEST_URL);
        $result = $client->SalePaymentRequest(['requestData' => $params]);

        if (!empty($result)) {
            $token = !empty($result->SalePaymentRequestResult) ? $result->SalePaymentRequestResult->Token : null;
            $status = !empty($result->SalePaymentRequestResult) ? $result->SalePaymentRequestResult->Status : null;

            if ($token > 0 and $status === 0) {
                $this->token = $token;

                return compact('token', 'orderId', 'amount');
            }

            throw new ParsianException((int)$status);
        } else {
            throw new ParsianException();
        }
    }

    /**
     * Redirect to payment gateway
     */
    public function redirect()
    {
        header("Location: ".$this->getPaymentUrl());
        exit ();
    }

    /**
     * Return payment gate url for application
     *
     * @return string
     */
    public function getPaymentUrl()
    {
        return self::GATE_URL.$this->token;
    }

    /**
     * Verify transaction
     *
     * @param $token
     * @param $amount
     * @return array
     *
     * @throws ParsianException
     */
    public function verify($token, $amount)
    {
        if (!filter_var($_POST['status'], FILTER_VALIDATE_INT) && $_POST['status'] != 0) {
            throw new \Exception('status برگشتی از بانک نامعتبر می باشد.');
        }

        if(!$_POST['RRN'] || !$_POST['Amount'] && $_POST['status'] != 0)
        {
            throw new ParsianException($_POST['status']);
        }
        $args = array(
            'Token' => array(
                'filter' => FILTER_VALIDATE_INT,
                'flags' => FILTER_REQUIRE_SCALAR,
            ),
            'RRN' => array(
                'flags' => FILTER_REQUIRE_SCALAR,
            ),
            'Amount' => array(
                'flags' => FILTER_REQUIRE_SCALAR,
                'options' => array('min_range' => 1000),
            ),
            'TerminalNo' => array(
                'flags' => FILTER_REQUIRE_SCALAR,
            ),
        );

        $filters = filter_var_array($_POST, $args);

        if (is_array($filters)) {
            foreach ($filters as $key => $value) {
                if ($value === false || $value == null || empty($value)) {
                    throw new \Exception($key.'برگشتی از بانک معتبر نمی باشد.');
                }
            }
        }

        $gateToken = $_POST["Token"];
        $status = $_POST["status"];
        $RRN = $_POST["RRN"];
        $gateAmount = str_replace(',', '', $_POST["Amount"]);

        if ($status != 0 || !$RRN) {
            throw new ParsianException($status);
        }

        if ($token != $gateToken) {
            throw new ParsianException(-3);
        }

        if ($amount != $gateAmount) {
            throw new ParsianException(-5);
        }

        $params = array(
            'LoginAccount' => $this->pin,
            'Token' => $token,
        );

        $client = $this->client ?? new SoapClient(self::CONFIRM_URL);
        $result = $client->ConfirmPayment(['requestData' => $params]);

        if (empty($result) || !isset($result->ConfirmPaymentResult->Status)) {
            throw new ParsianException(-4);
        }

        $status = isset($result->ConfirmPaymentResult->Status) ? $result->ConfirmPaymentResult->Status : null;

        if ($status != 0 || !$RRN) {
            throw new ParsianException($status);
        }

        return array(
            'Token' => $token,
            'status' => $status,
            'OrderId' => $_POST["OrderId"],
            'TerminalNo' => $_POST["TerminalNo"],
            'RRN' => $RRN,
            'HashCardNumber' => $_POST["HashCardNumber"],
            'Amount' => $amount,
        );
    }

    /**
     * Reverse transaction
     *
     * @param string $token
     * @return bool
     *
     * @throws ParsianException
     */
    public function reverse(string $token)
    {
        $params = array(
            'LoginAccount' => $this->pin,
            'Token' => $token,
        );

        if ($token <= 0) {
            throw new ParsianException(-2);
        }
        $client = $this->client ?? new SoapClient(self::REVERSE_URL);
        $result = $client->ReversalRequest(['requestData' => $params]);

        if ($result === false || !isset($result->ReversalRequestResult->Status)) {
            throw new ParsianException(-4);
        }

        $status = !empty($result->ReversalRequestResult) ? $result->ReversalRequestResult->Status : null;

        if ($status != 0) {
            throw new ParsianException($status);
        }

        return true;
    }

    /**
     * Set client for testing
     *
     * @param \SoapClient $client
     *
     * @return Parsian
     */
    public function setClient(\SoapClient $client)
    {
        $this->client = $client;

        return $this;
    }

}
