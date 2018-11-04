<?php

namespace Tests;

use Dpsoft\Parsian\Parsian;
use phpDocumentor\Reflection\Types\This;

class ParsianTests extends Base
{
    /**
     * @var Parsian
     */
    private $parsian;

    public function test_payRequest_return_amount_exception()
    {
        $this->expectException(\Exception::class);
        $this->parsian->setClient($this->requestMock('123456789', 0));
        $this->parsian->payRequest(100, 'http://example.com/callback');
    }

    public function test_payRequest_return_callback_url_exception()
    {
        $this->expectException(\Exception::class);
        $this->parsian->setClient($this->requestMock('123456789', 0));
        $this->parsian->payRequest(10000, 'example.com/callback');
    }

    public function test_payRequest_will_return_token()
    {
        $this->parsian->setClient($this->requestMock('123456789', 0));
        $response = $this->parsian->payRequest(1000, 'http://www.example.com', '123456', 'test');
        $this->assertEquals($response['token'], '123456789');
        $this->assertEquals($response['orderId'], '123456');
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
        $response = $this->parsian->verify(123456789, 1000);
        $this->assertArrayHasKey('Token', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('OrderId', $response);
        $this->assertArrayHasKey('TerminalNo', $response);
        $this->assertArrayHasKey('RRN', $response);
        $this->assertArrayHasKey('HashCardNumber', $response);
    }

    public function test_reverse_transaction()
    {
        $this->parsian->setClient($this->reverseMock('0'));
        $this->assertTrue($this->parsian->reverse('123456789'));
    }

    public function setUp()
    {
        $this->parsian = new Parsian('123456789');
    }
}
