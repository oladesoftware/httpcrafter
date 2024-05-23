<?php

use Oladesoftware\Httpcrafter\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testBuildHtmlResponse()
    {
        $htmlResponse = new Response("It works!");
        $this->assertEquals("It works!", $htmlResponse->send());
    }

    public function testBuildHtmlResponseWithoutConstructor()
    {
        $htmlResponse = new Response();
        $htmlResponse
            ->setBody("It works!");
        $this->assertEquals("It works!", $htmlResponse->send());
    }

    public function testBuildJsonResponse()
    {
        $jsonResponse = new Response("It works!", Response::JSON);
        $this->assertEquals("\"It works!\"", $jsonResponse->send());
    }

    public function testBuildJsonResponseWithoutConstructor()
    {
        $jsonResponse = new Response();
        $jsonResponse
            ->setContentType(Response::JSON)
            ->setBody("It works!");
        $this->assertEquals("\"It works!\"", $jsonResponse->send());
    }
}