<?php
namespace Sil\EmailService\Client\features\request;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Context\Context;
use Exception;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use Sil\EmailService\Client\EmailServiceClient;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * Defines application features from the specific context.
 */
class RequestContext implements Context
{
    private $baseUri;
    private $requestData = [];
    private $requestHistory = [];
    private $config = [];
    private $exceptionThrown = null;
    public $trustedHost = 'https://trusted_host.org/';
    public $untrustedHost = 'https://untrusted_host.org/';

    public $trustedIpRanges = ['10.0.1.1/32', '10.1.1.1/32'];

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }
    
    protected function assertSame($expected, $actual)
    {
        Assert::same(
            $actual,
            $expected,
            sprintf(
                "Expected %s,\n     not %s.",
                var_export($expected, true),
                var_export($actual, true)
            )
        );
    }
    
    protected function getHttpClientHandlerForTests()
    {
        $mockHandler = new MockHandler([
            new Response(),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);

        // Add a history middleware to the handler stack.
        $historyMiddleware = Middleware::history($this->requestHistory);
        $handlerStack->push($historyMiddleware);
        
        return $handlerStack;
    }

    /**
     * @return EmailServiceClient
     */
    protected function getEmailServiceClient()
    {
        $startConfig = [
            'http_client_options' => [
                'handler' => $this->getHttpClientHandlerForTests(),
            ],
        ];

        $finalConfig = array_merge($startConfig, $this->config);

        return new EmailServiceClient(
            $this->baseUri,
            'DummyAccessToken',
            $finalConfig
        );
    }
    
    /**
     * @return Request
     */
    protected function getRequestFromHistory()
    {
        return $this->requestHistory[0]['request'];
    }

    /**
     * @Given I am using a baseUri of :baseUri
     */
    public function iAmUsingABaseuriOf($baseUri)
    {
        $this->baseUri = $baseUri;
    }

    /**
     * @Then the url should be :expectedUri
     */
    public function theUrlShouldBe($expectedUri)
    {
        $request = $this->getRequestFromHistory();
        $actualUri = (string)$request->getUri();
        $this->assertSame($expectedUri, $actualUri);
    }

    /**
     * @Then the body should be :expectedBodyText
     */
    public function theBodyShouldBe($expectedBodyText)
    {
        $request = $this->getRequestFromHistory();
        $actualBodyText = (string)$request->getBody();
        $this->assertSame($expectedBodyText, $actualBodyText);
    }

    /**
     * @Then the body should contain json
     */
    public function theBodyShouldContainJson()
    {
        $request = $this->getRequestFromHistory();
        $bodyText = (string)$request->getBody();
        $isJson = $this->isJson($bodyText);
        Assert::true($isJson, 'Was expecting body to be JSON');
    }
    
    /**
     * @Then the method should be :expectedMethod
     */
    public function theMethodShouldBe($expectedMethod)
    {
        $request = $this->getRequestFromHistory();
        $actualMethod = $request->getMethod();
        $this->assertSame($expectedMethod, $actualMethod);
    }

    /**
     * @Then the body should not contain a :fieldName field
     */
    public function theBodyShouldNotContainAField($fieldName)
    {
        $request = $this->getRequestFromHistory();
        $bodyText = (string)$request->getBody();
        Assert::notContains(
            $bodyText,
            $fieldName,
            sprintf('The body should not contain %s', $fieldName)
        );
    }

    /**
     * @Given I provide a(n) :fieldName of :fieldValue
     */
    public function iProvideAOf($fieldName, $fieldValue)
    {
        $this->requestData[$fieldName] = $fieldValue;
    }


    /**
     * @Given I am using a trusted baseUri
     */
    public function iAmUsingATrustedBaseuri()
    {
        $this->baseUri = $this->trustedHost;
        $this->config['trusted_ip_ranges'] = $this->trustedIpRanges;
    }

    /**
     * @Given I am using an untrusted baseUri
     */
    public function iAmUsingAnUnTrustedBaseuri()
    {
        $this->baseUri = $this->untrustedHost;
        $this->config['trusted_ip_ranges'] = $this->trustedIpRanges;
    }

    /**
     * @Given I am using a single value for a trusted ip block
     */
    public function iAmUsingASingleValueForATrustedIpBlock()
    {
        $this->baseUri = $this->trustedHost;
        $this->config['trusted_ip_ranges'] = $this->trustedIpRanges[0];
    }

    /**
     * @Given I have indicated not to validate the email service ip
     */
    public function iHaveIndicatedNotToValidateTheEmailServiceIp()
    {
        $this->config[EmailServiceClient::ASSERT_VALID_IP_CONFIG] = false;
    }

    /**
     * @Given I have indicated that I want the email service ip to be validated
     */
    public function iHaveIndicatedThatIWantTheEmailServiceIpToBeValidated()
    {
        $this->config[EmailServiceClient::ASSERT_VALID_IP_CONFIG] = true;
    }

    /**
     * @Given I have not indicated whether the email service ip should be validated
     */
    public function iHaveNotIndicatedWhetherTheEmailServiceIpShouldBeValidated()
    {
        unset($this->config[EmailServiceClient::ASSERT_VALID_IP_CONFIG]);
    }

    /**
     * @Then an authorization header should be present
     */
    public function anAuthorizationHeaderShouldBePresent()
    {
        $request = $this->getRequestFromHistory();
        $headerLine = $request->getHeaderLine('Authorization');
        Assert::contains($headerLine, 'Bearer ');
    }

    /**
     * @Then the body should equal the following:
     */
    public function theBodyShouldEqualTheFollowing(PyStringNode $expectedBodyText)
    {
        $isJson = $this->isJson((string)$expectedBodyText);
        Assert::true($isJson, 'Expected body text should be JSON');

        $request = $this->getRequestFromHistory();
        $actualBodyText = $request->getBody();
        $isJson = $this->isJson((string)$actualBodyText);
        Assert::true($isJson, 'Actual body text should be JSON');

        $prettyExpectedBodyText = json_encode(json_decode($expectedBodyText, true));
        $prettyActualBodyText = json_encode(json_decode($actualBodyText, true));
        Assert::same(
            $prettyExpectedBodyText,
            $prettyActualBodyText,
            sprintf(
                'The body text (%s) is not the expected body text',
                $prettyActualBodyText
            )
        );
    }

    /**
     * @When I call getSiteStatus
     */
    public function iCallGetsitestatus()
    {
        $this->getEmailServiceClient()->getSiteStatus();
    }

    /**
     * @When I create the emailServiceClient
     */
    public function iCreateTheEmailServiceclient()
    {
        $this->exceptionThrown = null;
        try {
            $this->getEmailServiceClient();
        } catch (Exception $e) {
            $this->exceptionThrown = $e;
        }

    }

    /**
     * @Then an exception will be thrown
     */
    public function anExceptionWillBeThrown()
    {
        Assert::isInstanceOf($this->exceptionThrown, Exception::class);
    }

    /**
     * @When I call email
     */
    public function iCallEmail()
    {
        $this->getEmailServiceClient()->email($this->requestData);
    }


    /**
     * @Given I have provided a baseUri
     */
    public function iHaveProvidedABaseuri()
    {
        $this->baseUri = 'https://api.example.com/';
    }

    /**
     * @Given I have not provided a list of trusted IP ranges
     */
    public function iHaveNotProvidedAListOfTrustedIpRanges()
    {
        unset($this->config['trusted_ip_ranges']);
    }

    private function isJson(string $value): bool
    {
        try {
            $isJson = true;
            $decoded = json_decode($value, true);
            if ($decoded === null) {
                $isJson = false;
            }
        } catch (Throwable $throwable) {
            $isJson = false;
        }
        return $isJson;
    }
}
