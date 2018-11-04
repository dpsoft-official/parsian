<?php

namespace Tests;



use Dpsoft\Parsian\Parsian;
use PHPUnit\Framework\TestCase;

class Base extends TestCase
{
    public function test()
    {
        self::assertTrue(true);
    }

    public function requestMock($token, $status)
    {
        $fromWsdl = $this->getMockFromWsdl(Parsian::REQUEST_URL);

        $result = new \stdClass();
        $result->SalePaymentRequestResult = new \stdClass();
        $result->SalePaymentRequestResult->Token = $token;
        $result->SalePaymentRequestResult->Status = $status;

        $fromWsdl->method('SalePaymentRequest')->will($this->returnValue($result));

        return $fromWsdl;
    }

    public function verifyMock($status)
    {
        $fromWsdl = $this->getMockFromWsdl(Parsian::CONFIRM_URL);
        $result = new \stdClass();
        $result->ConfirmPaymentResult = new \stdClass();
        $result->ConfirmPaymentResult->Status = $status;
        $fromWsdl->method('ConfirmPayment')->will($this->returnValue($result));

        return $fromWsdl;
    }

    public function reverseMock($status)
    {
        $fromWsdl = $this->getMockFromWsdl(Parsian::REVERSE_URL);

        $result = new \stdClass();
        $result->ReversalRequestResult = new \stdClass();
        $result->ReversalRequestResult->Status = $status;

        $fromWsdl->method('ReversalRequest')->will($this->returnValue($result));

        return $fromWsdl;
    }

    public function wsdlMock($resultCode = 0)
    {
        $fromWsdl = $this->getMockFromWsdl(Request::WSDL_REQUEST);
        $result = new \stdClass();
        $return = new \stdClass();
        $return->result = $resultCode;
        $return->token = uniqid();
        $return->signature = 'yyyxxxxyyy';
        $result->return = $return;
        $fromWsdl->method('reservation')->will($this->returnValue($result));

        return $fromWsdl;
    }

    public function wsdlVerifyMock($resultCode = 0, $successful = false)
    {
        $fromWsdl = $this->getMockFromWsdl(Request::WSDL_VERIFY);

        $conf = new \stdClass();
        $conf->RESCODE = $resultCode;
        $conf->REPETETIVE = 'xx';
        $conf->AMOUNT = 'xx';
        $conf->DATE = 'xx';
        $conf->TIME = 'xx';
        $conf->TRN = 'xx';
        $conf->STAN = 'xx';
        $conf->successful = $successful;
        $conf->SIGNATURE = 'xx';
        $result = new \stdClass();
        $result->return = $conf;
        $fromWsdl->method('sendConfirmation')->will($this->returnValue($result));

        return $fromWsdl;
    }

    public function verifyData($result = '00')
    {
        return ['RESCODE' => $result, 'CRN' => 'xxx', 'TRN' => 'yyy'];
    }
}
