Feature: Test Brand JSON API endpoint GET

    Background:
        Given there are default users
    
    Scenario: I can get the home page if I am not connected
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
            }, 
            "title": { "en": "English title", "fr": "Titre en français" },
            "subtitle": { "en": "English subtitle", "fr": "Sous titre en français" }
        }
        """
        When I request "/api/en/home" using HTTP POST
        Then the response code is 201
        Given I logout
        When I request "/api/en/home" using HTTP GET
        Then the response body contains JSON:
        """
        {
            "id": "@regExp(/[0-9]+/)",
            "background": {
                "id": "@regExp(/[0-9]+/)",
                "description": "Home page background",
                "filePath": "@regExp(/.*\\.svg/)",
                "createdBy": {
                    "id": "@regExp(/[0-9]+/)"
                }
            },
            "separator": {
                "id": "@regExp(/[0-9]+/)",
                "description": "Home page separator",
                "filePath": "@regExp(/.*\\.svg/)",
                "createdBy": {
                    "id": "@regExp(/[0-9]+/)"
                }
            },
            "title": "English title",
            "subtitle": "English subtitle"
        }
        """
        And the response body has 5 fields
        Given I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 2
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204
        Given I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 2