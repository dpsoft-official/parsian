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
     * @param  string  $pin
     */
    public function __construct(string $pin)
    {
        $this->pin = $pin;
    }

    /**
     * Request token for generate payment gateway url
     *
     * @param  int  $amount  in rial
     * @param  string  $callbackUrl  Redirect url after payment
     * @param  int  $orderId  = null
     * @param  string  $additionalData  = null
     * @return array
     * @throws ParsianException
     * @throws \SoapFault
     * @throws \Exception
     */
    public function request(int $amount, string $callbackUrl, int $orderId = null, string $additionalData = null)
    {
        $orderId = $orderId ? $orderId : $this->uniqueNumber();
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

                return ['token' => $token, 'order_id' => $orderId];
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
     * @return array
     * @throws ParsianException
     * @throws \SoapFault
     */
    public function verify()
    {
        $token = $_POST["Token"] ?? null;
        $status = $_POST["status"] ?? null;
        $RRN = $_POST["RRN"] ?? null;

        if (empty($token) or !is_numeric($status)) {
            throw new ParsianException(-3);
        }
        if ($status != 0 || !$RRN) {
            throw new ParsianException($status);
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

        if ($status != 0) {
            throw new ParsianException($status);
        }

        return array(
            'token' => $token,
            'order_id' => $_POST["OrderId"],
            'RRN' => $RRN,
            'hash_card_number' => $_POST["HashCardNumber"],
        );
    }

    /**
     * Reverse transaction
     *
     * @param  string  $token
     * @return bool
     * @throws ParsianException
     * @throws \SoapFault
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
     * @param  \SoapClient  $client
     *
     * @return Parsian
     */
    public function setClient(\SoapClient $client)
    {
        $this->client = $client;

        return $this;
    }

    public function uniqueNumber()
    {
        return hexdec(uniqid());
    }

}
