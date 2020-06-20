Feature: Provide a consistent standard JSON API endpoint for USER.

  This test case are for User connexion only.

  Background:
    Given there are default users

    Scenario: I will login as user
      Given the request body is:
        """
        {
          "username": "user",
          "password": "user"
        }
        """
      When I request "/api/auth/login_check" using HTTP POST
      Then the response code is 200
      And the response body contains JSON:
      """
      {
        "token": "@regExp(/.*/)"
      }
      """
    
    Scenario: I will not login as user with a wrong password
      Given the request body is:
        """
        {
          "username": "user",
          "password": "wrong"
        }
        """
        When I request "api/auth/login_check" using HTTP POST
        Then the response code is 401
        And the response body contains JSON:
        """
        {
          "code": 401,
          "message": "Invalid credentials."
        }
        """
        And the response body has 2 fields
