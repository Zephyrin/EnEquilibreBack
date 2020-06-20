Feature: Login an user using french language

  These tests cases are for user connexion in english only.

  Background:
    Given there are default users

    Scenario: I will login as user
      Given the request body is:
        """
        {
            "username": "user",
            "password": "user_user"
        }
        """
      When I request "/api/fr/auth/login_check" using HTTP POST
      Then the response code is 200
      And the response body contains JSON:
      """
      {
        "token": "@regExp(/.*/)"
      }
      """
    
    Scenario: I will not login as user with a wrong password and I get error message in french
      Given the request body is:
        """
        {
            "username": "user",
            "password": "wrong_user"
        }
        """
      When I request "api/fr/auth/login_check" using HTTP POST
      Then the response code is 401
      And the response body contains JSON:
        """
        {
            "code": 401,
            "message": "Les informations d'identification sont invalides."
        }
        """
      And the response body has 2 fields
    
    Scenario: I will not login with unknown username and a known password.
      Given the request body is:
        """
        {
            "username": "user_unknown",
            "password": "user_user"
        }
        """
      When I request "api/fr/auth/login_check" using HTTP POST
      Then the response code is 401
      And the response body contains JSON:
        """
        {
            "code": 401,
            "message": "Les informations d'identification sont invalides."
        }
        """
      And the response body has 2 fields