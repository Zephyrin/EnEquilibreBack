Feature: Test View Translate JSON API endpoint POST

    Background:
        Given there are default users

    Scenario: I can create a new translate if I am connected as merchant
        Given I am login as merchant
        Then the request body is:
        """
        {
            "key": "menu", 
            "translate": { "en": "English menu", "fr": "Menu en français" }
        }
        """
        When I request "/api/en/viewtranslate" using HTTP POST
        Then the response code is 201
        And the response body contains JSON:
        """
        {
            "key": "menu",
            "translate": "English menu"
        }
        """
        And the response body has 2 fields
        Given I am login as admin
        When I request "/api/en/viewtranslate/menu" using HTTP DELETE
        Then the response code is 204

    Scenario: I cannot create a view translate if I am connected as user
        Given I am login as user
        Then the request body is:
        """
        {
            "key": "menu", 
            "translate": { "en": "English menu", "fr": "Menu en français" }
        }
        """
        When I request "/api/en/viewtranslate" using HTTP POST
        Then the response code is 403

    Scenario: Cannot create a view translate with wrong values
        Given I am login as merchant
        Given the request body is:
        """
        {
            "key": "", 
            "translate": "English menu",
            "fr": "Menu en français"
        }
        """
        When I request "/api/en/viewtranslate" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Error.",
            "message": "Validation error.",
            "errors": [{
                "errors": [ "This form should not contain extra fields."],
                "children": {
                    "key": { "errors": [ "This value should not be null." ] },
                    "translate": []
                }
            }]
        }
        """

    Scenario: Cannot create a view translate with wrong values
        Given I am login as merchant
        Given the request body is:
        """
        {
            "key": "test", 
            "translate": "English test"
        }
        """
        When I request "/api/en/viewtranslate" using HTTP POST
        Then the response code is 201
        And the response body contains JSON:
        """
        {
            "key": "test"
        }
        """
        And the response body has 1 fields

    Scenario: Cannot create a view translate with empty json
        Given I am login as merchant
        Given the request body is:
        """
        {
        }
        """
        When I request "/api/en/viewtranslate" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Error.",
            "message": "Validation error.",
            "errors": "The body of the request does not contain a valid JSon."
        }
        """

    Scenario: POST two view translate page with same key. Impossible
        Given I am login as merchant
        Then the request body is: 
        """
        {
            "key": "menu", 
            "translate": { "en": "English menu", "fr": "Menu en français" }
        }
        """
        When I request "/api/en/viewtranslate" using HTTP POST
        Then the response code is 201
        Then the request body is: 
        """
        {
            "key": "menu", 
            "translate": { "en": "English menu", "fr": "Menu en français" }
        }
        """
        When I request "/api/en/viewtranslate" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Error.",
            "message": "Validation error.",
            "errors": [{ 
                "children": {
                    "key": { "errors": [ "This value exists." ] },
                    "translate": []
                }
            }]
        }
        """
        Given I am login as admin
        When I request "/api/en/viewtranslate/menu" using HTTP DELETE
        Then the response code is 204
