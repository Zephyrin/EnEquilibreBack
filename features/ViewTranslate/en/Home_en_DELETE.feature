Feature: Test Home JSON API DELETE - Admin only interface.

    Background:
        Given there are default users

    Scenario: I can create an home page if I am connected as merchant
        Given I am login as merchant
        Then the request body is:
        """
        {
            "background": {
                "description": {"en": "Home page background", "fr": "Fond d'écran de la page d'acceuil"},
                "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
            },
            "separator": {
                "description": {"en": "Home page separator", "fr": "Séparateur de la page d'acceuil"},
                "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
            }
        }
        """
        When I request "/api/en/home" using HTTP POST
        Then the response code is 201
        Given I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 2
        Given I am login as user
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 403
        Given I am login as merchant
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 403
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204
        When I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 0

    Scenario: I can create an home page if I am connected as merchant
        Given I am login as merchant
        Then the request body is:
        """
        {
            "separator": {
                "description": {"en": "Home page separator", "fr": "Séparateur de la page d'acceuil"},
                "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
            }
        }
        """
        When I request "/api/en/home" using HTTP POST
        Then the response code is 201
        Given I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 1
        Given I am login as user
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 403
        Given I am login as merchant
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 403
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204
        When I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 0

    Scenario: I can create an home page if I am connected as merchant
        Given I am login as merchant
        Then the request body is:
        """
        {
            "background": {
                "description": {"en": "Home page background", "fr": "Arrière plan de la page d'acceuil"},
                "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
            }
        }
        """
        When I request "/api/en/home" using HTTP POST
        Then the response code is 201
        Given I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 1
        Given I am login as user
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 403
        Given I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 1
        Given I am login as merchant
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 403
        Given I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 1
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204
        When I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 0