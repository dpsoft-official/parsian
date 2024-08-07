<?php

namespace Tests;

use Dpsoft\Parsian\Parsian;

class ParsianTests extends Base
{
    /**
     * @var Parsian
     */
    private $parsian;

    public function test_payRequest_will_return_token()
    {
        $this->parsian->setClient($this->requestMock('123456789', 0));
        $response = $this->parsian->request(1000, 'http://www.example.com', '123456', 'test');
        $this->assertEquals($response['token'], '123456789');
        $this->assertEquals($response['order_id'], '123456');
        $this->assertEquals($this->parsian->getPaymentUrl(), Parsian::GATE_URL.'123456789');
    }

    public function test_verify_transaction()
    {
        $_POST = array(
            "Token" => '123456789',
            "status" => 0,
            "RRN" => '123456789',
            "Amount" => 1000,
            "TerminalNo" => '123456789',
            "OrderId" => '1234567',
            "HashCardNumber" => '1234-1234-1234-1234',
        );

        $this->parsian->setClient($this->verifyMock(0));
        $response = $this->parsian->verify();
        $this->assertArrayHasKey('token', $response);
        $this->assertArrayHasKey('order_id', $response);
        $this->assertArrayHasKey('RRN', $response);
        $this->assertArrayHasKey('hash_card_number', $response);
    }

    public function test_reverse_transaction()
    {
        $this->parsian->setClient($this->reverseMock('0'));
        $this->assertTrue($this->parsian->reverse('123456789'));
    }

    public function setUp():void
    {
        $this->parsian = new Parsian('123456789');
    }
}
