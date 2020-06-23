Feature: Test token message in english
    
    Scenario: Set default value.
        Given there are default users

    Scenario: The token is expired
        Given I am login with expired token
        Given I request "/api/fr/mediaobject/1" using HTTP GET
        Then the response code is 401
        Then the response body contains JSON:
        """
        {
            "code": 401,
            "message": "Ton jeton d'authentification est expiré."
        }
        """