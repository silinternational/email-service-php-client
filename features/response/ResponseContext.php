<?php
namespace Sil\EmailService\Client\features\response;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Context\Context;
use Exception;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use Sil\EmailService\Client\EmailServiceClient;

/**
 * Defines application features from the specific context.
 */
class ResponseContext implements Context
{
    private $methodName;
    private $response = null;
    private $result;
    private $exceptionThrown = null;
    
    protected function getHttpClientHandlerForTests()
    {
        Assert::assertNotEmpty(
            $this->response,
            'You need to define the response before you can pretend to call the API.'
        );
        $mockHandler = new MockHandler([$this->response]);
        return HandlerStack::create($mockHandler);
    }
    
    /**
     * @return EmailServiceClient
     */
    protected function getEmailServiceClient()
    {
        return new EmailServiceClient('https://api.example.com/', 'DummyAccessToken', [
            'http_client_options' => [
                'handler' => $this->getHttpClientHandlerForTests(),
            ],
            EmailServiceClient::ASSERT_VALID_IP_CONFIG => false,
        ]);
    }
    
    /**
     * @Given a call to :methodName will return a :statusCode with the following data:
     */
    public function aCallToWillReturnAWithTheFollowingData(
        $methodName,
        $statusCode,
        PyStringNode $responseData
    ) {
        $this->methodName = $methodName;
        $this->response = new Response($statusCode, [], (string)$responseData);
    }
    
    /**
     * @Given a call to :methodName will return a :statusCode response
     */
    public function aCallToWillReturnAResponse($methodName, $statusCode)
    {
        $this->methodName = $methodName;
        $this->response = new Response($statusCode);
    }

    /**
     * @When I call getSiteStatus
     */
    public function iCallGetsitestatus()
    {
        try {
            $this->getEmailServiceClient()->getSiteStatus();
        } catch (Exception $e) {
            $this->exceptionThrown = $e;
        }
    }

    
    /**
     * @Then the result should NOT contain user information
     */
    public function theResultShouldNotContainUserInformation()
    {
        if (is_array($this->result)) {
            foreach ($this->userInfoFields as $fieldName) {
                Assert::assertArrayNotHasKey($fieldName, $this->result);
            }
        }
    }
    
    /**
     * @Then the result SHOULD contain user information
     */
    public function theResultShouldContainUserInformation()
    {
        foreach ($this->userInfoFields as $fieldName) {
            Assert::assertArrayHasKey($fieldName, $this->result);
        }
    }

    /**
     * @Then the result should NOT contain an error message
     */
    public function theResultShouldNotContainAnErrorMessage()
    {
        Assert::assertArrayNotHasKey('message', $this->result);
    }
    
    /**
     * @Then the result SHOULD contain an error message
     */
    public function theResultShouldContainAnErrorMessage()
    {
        Assert::assertArrayHasKey('message', $this->result);
    }

    /**
     * @When I call email with the necessary data
     */
    public function iCallEmailWithTheNecessaryData()
    {
        try {
            $this->result = $this->getEmailServiceClient()->email([
                "to_address" => "test@domain.com",
                "cc_address" => "other@domain.com",
                "subject" => "Test Subject",
                "text_body" => "this is text",
                "html_body" => "<b>this is html</b>",
            ]);
        } catch (Exception $e) {
            $this->exceptionThrown = $e;
        }
    }

    /**
     * @Then an exception should NOT have been thrown
     */
    public function anExceptionShouldNotHaveBeenThrown()
    {
        Assert::assertNull($this->exceptionThrown);
    }

    /**
     * @Then an exception SHOULD have been thrown
     */
    public function anExceptionShouldHaveBeenThrown()
    {
        Assert::assertInstanceOf(Exception::class, $this->exceptionThrown);
    }

    /**
     * @Then the result should be an array
     */
    public function theResultShouldBeAnArray()
    {
        Assert::assertInternalType('array', $this->result);
    }
}
