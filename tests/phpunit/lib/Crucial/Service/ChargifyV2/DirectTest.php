<?php
use GuzzleHttp\Subscriber\Mock;


/**
 * Class Crucial_Service_ChargifyV2_DirectTest
 *
 */
class Crucial_Service_ChargifyV2_DirectTest extends PHPUnit_Framework_TestCase
{
    public function testAuthSuccess()
    {
        $mockFile = 'v2.authTest.success';
        $chargify = ClientV2Helper::getInstance();
        $direct   = $chargify->direct();

        // set mock on authtest http client before testing
        $authUtility = $direct->getAuthTestUtility();
        $mock        = new Mock([
            MockResponse::read($mockFile)
        ]);
        $authUtility->getHttpClient()->getEmitter()->attach($mock);

        $success = $direct->checkAuth();

        $this->assertTrue($success);
    }

    public function testAuthFailure()
    {
        $mockFile = 'v2.authTest.error';
        $chargify = ClientV2Helper::getInstance();
        $direct   = $chargify->direct();

        // set mock on authtest http client before testing
        $authUtility = $direct->getAuthTestUtility();
        $mock        = new Mock([
            MockResponse::read($mockFile)
        ]);
        $authUtility->getHttpClient()->getEmitter()->attach($mock);

        $success = $direct->checkAuth();

        $this->assertFalse($success);
    }

    public function testSetData()
    {
        $chargify = ClientV2Helper::getInstance();
        $direct   = $chargify->direct();

        // set redirect
        $redirect = 'http://example.local';
        $direct->setRedirect($redirect);

        // set data
        $data = [
            'signup' => [
                'product'  => [
                    'id' => 1234
                ],
                'customer' => [
                    'first_name' => 'Dan',
                    'last_name'  => 'Bowen',
                    'email'      => 'foo@mailinator.com'
                ]
            ]
        ];
        $direct->setData($data);

        // redirect_uri gets merged into data
        $expected = array_merge_recursive($data, ['redirect_uri' => $redirect]);

        $this->assertEquals($expected, $direct->getData());
        $this->assertEquals($redirect, $direct->getRedirect());
    }

    public function testGetRequestSignature()
    {
        $chargify = ClientV2Helper::getInstance();
        $direct   = $chargify->direct();

        $string = $direct->getApiId()
            . $direct->getTimeStamp()
            . $direct->getNonce()
            . $direct->getDataString();

        $signature = hash_hmac('sha1', $string, $chargify->getApiSecret());

        $this->assertEquals($signature, $direct->getRequestSignature());
    }

    public function testGetResponseSignature()
    {
        $chargify   = ClientV2Helper::getInstance();
        $direct     = $chargify->direct();
        $apiId      = $chargify->getApiId();
        $timeStamp  = $direct->getTimeStamp();
        $nonce      = $direct->getNonce();
        $statusCode = '200';
        $resultCode = '2000';
        $callId     = '1234';

        $signatureString = $apiId
            . $timeStamp
            . $nonce
            . $statusCode
            . $resultCode
            . $callId;

        $signature = hash_hmac('sha1', $signatureString, $chargify->getApiSecret());

        $this->assertEquals($signature, $direct->getResponseSignature($apiId, $timeStamp, $nonce, $statusCode, $resultCode, $callId));
    }

    public function testIsValidResponseSignature()
    {
        $chargify   = ClientV2Helper::getInstance();
        $direct     = $chargify->direct();
        $apiId      = $chargify->getApiId();
        $timeStamp  = $direct->getTimeStamp();
        $nonce      = $direct->getNonce();
        $statusCode = '200';
        $resultCode = '2000';
        $callId     = '1234';

        $validSignatureString = $apiId
            . $timeStamp
            . $nonce
            . $statusCode
            . $resultCode
            . $callId;

        $validSignature   = hash_hmac('sha1', $validSignatureString, $chargify->getApiSecret());
        $inValidSignature = str_repeat('x', 40);

        $this->assertTrue($direct->isValidResponseSignature($validSignature, $apiId, $timeStamp, $nonce, $statusCode, $resultCode, $callId));
        $this->assertFalse($direct->isValidResponseSignature($inValidSignature, $apiId, $timeStamp, $nonce, $statusCode, $resultCode, $callId));
    }

    public function testFormCreation()
    {
        $chargify = ClientV2Helper::getInstance();
        $direct   = $chargify->direct();

        // set redirect
        $direct->setRedirect('http://example.local');

        // set data
        $data = [
            'signup' => [
                'product'  => [
                    'id' => 1234
                ],
                'customer' => [
                    'first_name' => 'Dan',
                    'last_name'  => 'Bowen',
                    'email'      => 'foo@mailinator.com'
                ]
            ]
        ];
        $direct->setData($data);

        $signupAction = 'https://api.chargify.com/api/v2/signups';
        $this->assertEquals($signupAction, $direct->getSignupAction());

        // get hidden fields
        $hiddenFields   = $direct->getHiddenFields();
        $apiIdField     = '<input type="hidden" name="secure[api_id]"    value="' . $chargify->getApiId() . '" />';
        $timestampField = '<input type="hidden" name="secure[timestamp]" value="' . $direct->getTimeStamp() . '" />';
        $nonceField     = '<input type="hidden" name="secure[nonce]"     value="' . $direct->getNonce() . '" />';
        $dataField      = '<input type="hidden" name="secure[data]"      value="' . $direct->getDataStringEncoded() . '" />';
        $signatureField = '<input type="hidden" name="secure[signature]" value="' . $direct->getRequestSignature() . '" />';

        $this->assertContains($apiIdField, $hiddenFields);
        $this->assertContains($timestampField, $hiddenFields);
        $this->assertContains($nonceField, $hiddenFields);
        $this->assertContains($dataField, $hiddenFields);
        $this->assertContains($signatureField, $hiddenFields);
    }

    /**
     * @expectedException \Crucial\Service\ChargifyV2\Exception\BadMethodCallException
     */
    public function testSetRedirectAfterRequestSignatureThrowsException()
    {
        $chargify = ClientV2Helper::getInstance();
        $direct   = $chargify->direct();

        $signature = $direct->getRequestSignature();
        $direct->setRedirect('http://example.local');
    }

    /**
     * @expectedException \Crucial\Service\ChargifyV2\Exception\BadMethodCallException
     */
    public function testSetDataAfterRequestSignatureThrowsException()
    {
        $chargify = ClientV2Helper::getInstance();
        $direct   = $chargify->direct();

        $signature = $direct->getRequestSignature();
        $direct->setData([]);
    }

    public function testGetCardUpdateAction()
    {
        $chargify = ClientV2Helper::getInstance();
        $direct   = $chargify->direct();

        $this->assertEquals('https://api.chargify.com/api/v2/subscriptions/1234/card_update', $direct->getCardUpdateAction('1234'));
    }
}