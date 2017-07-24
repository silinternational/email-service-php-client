Feature: Handling responses from the email service API

  Scenario: Handling a successful siteStatus call
    Given a call to "getSiteStatus" will return a 204 response
    When I call getSiteStatus
    Then an exception should NOT have been thrown

  Scenario: Handling a getSiteStatus call that triggers an exception
    Given a call to "getSiteStatus" will return a 500 with the following data:
      """
      {
        "name": "Internal Server Error",
        "message": "Some error message.",
        "code": 0,
        "status": 500
      }
      """
    When I call getSiteStatus
    Then an exception should have been thrown

  Scenario: Handling a successful email call
    Given a call to "email" will return a 200 response
    When I call email with the necessary data
    Then an exception should NOT have been thrown
      And the result should be an array

