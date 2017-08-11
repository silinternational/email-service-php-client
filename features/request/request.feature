Feature: Formatting requests for sending to the Email Service API

  Scenario: Checking site status without validating the email service ip address
    Given I am using a baseUri of "https://api.example.com/"
      And I have indicated not to validate the email service ip
    When I call getSiteStatus
    Then the method should be "GET"
      And the url should be "https://api.example.com/site/status"

  Scenario: Checking site status with trusted email-service
   Given I am using a trusted baseUri
     And I have indicated that I want the email service ip to be validated
    When I call getSiteStatus
    Then the method should be "GET"
    And the url should be "https://trusted_host.org/site/status"

  Scenario: Checking client with untrusted email-service
    Given I am using an untrusted baseUri
    When I create the emailServiceClient
    Then an exception will be thrown

  Scenario: Checking client with a single trusted ip block value
    Given I have not indicated whether the email service ip should be validated
      And I am using a single value for a trusted ip block
    When I create the emailServiceClient
    Then an exception will be thrown

  Scenario: Checking client with the assert_valid_ip not given and the trusted_ip_ranges value missing
    Given I am using a baseUri of "https://api.example.com/"
      And I have not indicated whether the email service ip should be validated
    When I create the emailServiceClient
    Then an exception will be thrown

  Scenario: Creating a email
    Given I am using a baseUri of "https://api.example.com/"
      And I have indicated not to validate the email service ip
      And I provide a "to_address" of "test@domain.com"
      And I provide a "cc_address" of "other@domain.com"
      And I provide a "subject" of "Test Subject"
      And I provide a "text_body" of "this is text"
      And I provide a "html_body" of "<b>this is html</b>"
    When I call email
    Then the method should be "POST"
      And the url should be "https://api.example.com/email"
      And an authorization header should be present
      And the body should equal the following:
        """
        {
          "to_address": "test@domain.com",
          "cc_address": "other@domain.com",
          "subject": "Test Subject",
          "text_body": "this is text",
          "html_body": "<b>this is html</b>"
        }
        """
