Feature: Test ViewTranslate JSON API endpoint GET

    Background:
        Given there are default users
    
    Scenario: I can get a view translate if I am not connected
        Given I am login as merchant
        Then the request body is:
        """
        {
            "key": "menu",
            "translate": { "en": "English menu", "fr": "Menu français" }
        }
        """
        When I request "/api/en/viewtranslate" using HTTP POST
        Then the response code is 201
        Given I logout
        When I request "/api/en/viewtranslate/menu" using HTTP GET
        Then the response body contains JSON:
        """
        {
            "key": "menu",
            "translate": "English menu"
        }
        """
        And the response body has 2 fields
        When I request "/api/en/viewtranslates" using HTTP GET
        Then the response body is a JSON array of length 1
        And the response body contains JSON:
        """
        [{
            "key": "menu",
            "translate": "English menu"
        }]
        """
        Given I am login as merchant
        When I request "/api/en/viewtranslate/menu" using HTTP DELETE
        Then the response code is 204
        Given I request "/api/en/viewtranslates" using HTTP GET
        Then the response body is a JSON array of length 0

    Scenario: I can get all translations if I am connected as Merchant
        Given I am login as merchant
        Then the request body is:
        """
        {
            "key": "menu",
            "translate": { "en": "English menu", "fr": "Menu français" }
        }
        """
        When I request "/api/en/viewtranslate" using HTTP POST
        Then the response code is 201
        Given I logout
        When I request "/api/viewtranslate/menu" using HTTP GET
        Then the response code is 401
        When I request "/api/viewtranslates" using HTTP GET
        Then the response code is 401
        Given I am login as merchant
        When I request "/api/viewtranslate/menu" using HTTP GET
        Then the response body contains JSON:
        """
        {
            "key": "menu",
            "translate": "English menu",
            "translations": {
                "en": { "translate" : { "translate": "English menu"} },
                "fr": { "translate" : { "translate": "Menu français" } }
            }
        }
        """
        And the response body has 3 fields
        When I request "/api/viewtranslates" using HTTP GET
        Then the response body is a JSON array of length 1
        And the response body contains JSON:
        """
        [{
            "key": "menu",
            "translate": "English menu",
            "translations": {
                "en": { "translate" : { "translate": "English menu"} },
                "fr": { "translate" : { "translate": "Menu français" } }
            }
        }]
        """
        Given I am login as merchant
        When I request "/api/en/viewtranslate/menu" using HTTP DELETE
        Then the response code is 204
        Given I request "/api/en/viewtranslates" using HTTP GET
        Then the response body is a JSON array of length 0