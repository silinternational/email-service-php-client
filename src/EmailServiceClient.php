<?php
namespace Sil\EmailService\Client;

use GuzzleHttp\Command\Result;
use IPLib\Range\RangeInterface;
use IPLib\Factory;

/**
 * Email Service API client implemented with Guzzle.
 */
class EmailServiceClient extends BaseClient
{
    /**
     * The key for the constructor's config parameter that refers
     * to the trusted IP ranges.
     */
    const TRUSTED_IPS_CONFIG = 'trusted_ip_ranges';
    const ASSERT_VALID_IP_CONFIG = 'assert_valid_ip';

    /**
     * The list of trusted IP address ranges (aka. blocks).
     *
     * @var RangeInterface[]
     */
    private $trustedIpRanges = [];

    private $assertValidIp = true;

    private $serviceUri;

    /**
     * Constructor.
     *
     * @param string $baseUri - The base of the API's URL.
     *     Example: 'https://api.example.com/'.
     * @param string $accessToken - Your authorization access (bearer) token.
     * @param array $config - Any other configuration settings.
     * @throws EmailServiceClientException
     */
    public function __construct(
        string $baseUri,
        string $accessToken,
        array $config = []
    ) {
        if (empty($baseUri)) {
            throw new EmailServiceClientException(
                'Please provide a base URI for the Email Service.',
                1503511656
            );
        }

        $this->serviceUri = $baseUri;

        if (empty($accessToken)) {
            throw new EmailServiceClientException(
                'Please provide an access token for the Email Service.',
                1503511657
            );
        }

        $this->initializeConfig($config);

        // Create the client (applying some defaults).
        parent::__construct(array_replace_recursive([
            'description_path' => \realpath(
                __DIR__ . '/descriptions/email-service-api.php'
            ),
            'description_override' => [
                'baseUri' => $baseUri,
            ],
            'access_token' => $accessToken,
            'http_client_options' => [
                'timeout' => 30,
            ],
        ], $config));
    }

    /*
     * Validates the config values for ASSERT_VALID_IP_CONFIG and
     *   TRUSTED_IPS_CONFIG
     *
     *
     *
     *


     * Uses them to set $this->assertValidIp and $this->trustedIpRanges
     *
     * @param array the config values for the client
     *
     * @return null
     * @throws EmailServiceClientException
     */
    private function initializeConfig($config)
    {

        if (isset($config[self::ASSERT_VALID_IP_CONFIG])) {
            $this->assertValidIp = $config[self::ASSERT_VALID_IP_CONFIG];
        }

        // If we don't need to validate the service Ip, we're done here
        if ( ! $this->assertValidIp) {
            return;
        }

        /*
         *  If we should validate the service IP but there aren't
         *  any trusted IPs, throw an exception
         */
        if (empty($config[self::TRUSTED_IPS_CONFIG])) {
            throw new EmailServiceClientException(
                'The config entry for ' . self::TRUSTED_IPS_CONFIG .
                ' must be set (as an array) when ' .
                self::ASSERT_VALID_IP_CONFIG .
                ' is not set or is set to True.',
                1503511658
            );
        }

        /*
         * At this point, we need to validate the service Ip and we know
         * that the TRUSTED_IPS_CONFIG is not empty
         */
        $newTrustedIpRanges = $config[self::TRUSTED_IPS_CONFIG];
        if ( ! is_array($newTrustedIpRanges)) {
            throw new EmailServiceClientException(
                'The config entry for ' . self::TRUSTED_IPS_CONFIG .
                ' must be an array.',
                1503511659
            );
        }

        foreach ($newTrustedIpRanges as $nextIpRange) {
            $this->trustedIpRanges[] = Factory::rangeFromString($nextIpRange);
        }

        $this->assertTrustedIp();
    }

    /**
     * Create an email with the given information.
     *
     * @param array $config An array key/value pairs of attributes for the new
     *     email.
     * @return array An array of information about the email.
     * @throws EmailServiceClientException
     */
    public function email(array $config = [])
    {
        $result = $this->emailInternal($config);
        $statusCode = (int)$result['statusCode'];

        if ($statusCode >= 200 && $statusCode < 300) {
            return $this->getResultAsArrayWithoutStatusCode($result);
        }

        $this->reportUnexpectedResponse($result, 1503511660);
    }

    /**
     * Convert the result of the Guzzle call to an array without a status code.
     *
     * @param Result $result The result of a Guzzle call.
     * @return array
     */
    protected function getResultAsArrayWithoutStatusCode($result)
    {
        unset($result['statusCode']);
        return $result->toArray();
    }

    /**
     * Ping the /site/status url
     *
     * @return string "OK".
     * @throws Exception
     */
    public function getSiteStatus()
    {
        $result = $this->getSiteStatusInternal();
        $statusCode = (int)$result['statusCode'];

        if (($statusCode >= 200) && ($statusCode < 300)) {
            return 'OK';
        }

        $this->reportUnexpectedResponse($result, 1503511661);
    }

    protected function reportUnexpectedResponse($response, $uniqueErrorCode)
    {
        throw new EmailServiceClientException(
            sprintf(
                'Unexpected response: %s',
                var_export($response, true)
            ),
            $uniqueErrorCode
        );
    }

    /**
     * Determine whether any of the service's IPs are not in the
     * trusted ranges
     *
     * @throws Exception
     */
    private function assertTrustedIp()
    {
        $baseHost = parse_url($this->serviceUri, PHP_URL_HOST);
        if ($baseHost == ($serviceIp = gethostbyname($baseHost))) {
            throw new Exception(
                'DNS lookup failure on email service host ' . $baseHost,
                1687147214
            );
        }

        if ( ! $this->isTrustedIpAddress($serviceIp)) {
            throw new EmailServiceClientException(
                'The service has an IP that is not trusted ... ' . $serviceIp,
                1503511662
            );
        }
    }

    /**
     * Determine whether the service's IP address is in a trusted range.
     *
     * @param string $ipAddress The IP address in question.
     * @return bool
     */
    private function isTrustedIpAddress($ipAddress)
    {
        foreach ($this->trustedIpRanges as $trustedIpBlock) {
            $addr = Factory::addressFromString($ipAddress);
            if ($addr !== null && $trustedIpBlock->contains($addr)) {
                return true;
            }
        }

        return false;
    }

}
