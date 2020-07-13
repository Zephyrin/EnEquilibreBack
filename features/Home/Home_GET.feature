Feature: Test Brand JSON API endpoint GET

    Background:
        Given there are default users
    
    Scenario: I cannot get the home page if I am not connected and I get everything if I am a merchant
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
        When I request "/api/home" using HTTP GET
        Then the response code is 401
        Given I am login as user
        When I request "/api/home" using HTTP GET
        Then the response code is 403
        Given I am login as merchant
        When I request "/api/home" using HTTP GET
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
            "subtitle": "English subtitle",
            "translations": {
                "en": {
                    "background": { "description": "Home page background" },
                    "separator": { "description": "Home page separator" },
                    "title": "English title",
                    "subtitle": "English subtitle"
                },
                "fr": {
                    "background": {"description": "Fond d'écran de la page d'acceuil"},
                    "separator": {"description": "Séparateur de la page d'acceuil" }
                    "title": "Titre en français",
                    "subtitle": "Sous titre en français"
                }
            }
        }
        """
        And the response body has 4 fields
        Given I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 2
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204
        Given I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 2

    Scenario: I get the home page with only the background
        Given I am login as merchant
        Then the request body is:
        """
        {
            "background": {
                "description": {"en": "Home page background", "fr": "Fond d'écran de la page d'acceuil"},
                "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
            }
        }
        """
        When I request "/api/en/home" using HTTP POST
        Then the response code is 201
        Given I logout
        When I request "/api/home" using HTTP GET
        Then the response code is 401
        Given I am login as user
        When I request "/api/home" using HTTP GET
        Then the response code is 403
        Given I am login as merchant
        When I request "/api/home" using HTTP GET
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
            "translations": {
                "en": {
                    "background": { "description": "Home page background" }
                },
                "fr": {
                    "background": {"description": "Fond d'écran de la page d'acceuil"}
                }
            }
        }
        """
        And the response body has 3 fields
        Given I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 1
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204
        Given I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 1

    Scenario: I get the separator only information.
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
        Given I logout
        When I request "/api/home" using HTTP GET
        Then the response code is 401
        Given I am login as user
        When I request "/api/home" using HTTP GET
        Then the response code is 403
        Given I am login as merchant
        When I request "/api/home" using HTTP GET
        Then the response body contains JSON:
        """
        {
            "id": "@regExp(/[0-9]+/)",
            "separator": {
                "id": "@regExp(/[0-9]+/)",
                "description": "Home page separator",
                "filePath": "@regExp(/.*\\.svg/)",
                "createdBy": {
                    "id": "@regExp(/[0-9]+/)"
                }
            },
            "translations": {
                "en": {
                    "separator": { "description": "Home page separator" }
                },
                "fr": {
                    "separator": {"description": "Séparateur de la page d'acceuil" }
                }
            }
        }
        """
        And the response body has 3 fields
        Given I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 1
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204
        Given I request "/api/en/mediaobjects" using HTTP GET
        Then the response body is a JSON array of length 1