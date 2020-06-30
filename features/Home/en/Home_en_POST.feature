Feature: Test Brand JSON API endpoint

    In order to build interchangeable front ends
    As a JSON API developer
    I need to allow Create functionality to initialise the project.

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
        And the response body contains JSON:
        """
        {
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
            }
        }
        """
        And the response body has 2 fields
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204

    Scenario: I cannot create an home page if I am connected as user
        Given I am login as user
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
        Then the response code is 403



    Scenario: Cannot create an home page with wrong values
        Given I am login as merchant
        Given the request body is:
        """
        {
            "background": "wrong value",
            "name": "www.wrong_value.fr",
            "separator": {
                "description": {"en": "Wrong value", "fr": "Mauvaise valeur"},
                "image": "data:application/pdf;base64,JVBERi0xLjUKJYCBgoMKMSAwIG9iago8PC9GaWx0ZXIvRmxhdGVEZWNvZGUvRmlyc3QgMTQxL04gMjAvTGVuZ3=="
            }
        }
        """
        When I request "/api/en/home" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Error.",
            "message": "Validation error.",
            "errors": [{
                "errors": [ "This form should not contain extra fields."],
                "children": {
                    "background": { "errors": [ "This value is not valid." ] },
                    "separator": { 
                        "children": {
                            "image": { "errors": ["This is not an image in base64."]},
                            "description": []
                        }
                    }

                }
            }]
        }
        """

    Scenario: Cannot create a home with empty json
        Given I am login as merchant
        Given the request body is:
        """
        {
        }
        """
        When I request "/api/en/home" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Error.",
            "message": "Validation error.",
            "errors": "The body of the request does not contain a valid JSon."
        }
        """

    Scenario: POST two homes page. Impossible
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
        Then the response code is 409
        And the response body contains JSON:
        """
        {
            "status": "Error.",
            "message": "Conflict error.",
            "errors": "There is already an home page."
        }
        """
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204
    
    Scenario: Add an home page using an existing image for background - POST
        Given I am login as merchant
        Then the request body is:
        """
        {
            "description": { "en": "Background", "fr": "Arrière plan" },
            "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
        }
        """
        When I request "/api/en/mediaobject" using HTTP POST
        Then the response code is 201
        And I save the "id"
        Then the request body is with "id" for ":id":
        """
        {
            "background": { "id": ":id", "description": { "en": "TOTO", "fr": "TOTO" } },
            "separator": {
                "description": { "en": "Separator", "fr": "Séparateur" },
                "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
            }
        }
        """
        When I request "/api/en/home" using HTTP POST
        Then the response code is 201
        And the response body contains JSON:
        """
        {
            "background": {
                "id": "@regExp(/.*/)",
                "filePath": "@regExp(/.+/)",
                "description": "TOTO"
            },
            "separator": {
                "id": "@regExp(/.*/)",
                "filePath": "@regExp(/.+/)",
                "description": "Separator"
            }
        }
        """
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204

    Scenario: Add an home page using an existing image for separator - POST
        Given I am login as merchant
        Then the request body is:
        """
        {
            "description": { "en": "Separator", "fr": "Séparator" },
            "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
        }
        """
        When I request "/api/en/mediaobject" using HTTP POST
        Then the response code is 201
        And I save the "id"
        Then the request body is with "id" for ":id":
        """
        {
            "separator": { "id": ":id", "description": { "en": "TOTO", "fr": "TOTO" } },
            "background": {
                "description": { "en": "Background", "fr": "Arrière plan" },
                "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
            }
        }
        """
        When I request "/api/en/home" using HTTP POST
        Then the response code is 201
        And the response body contains JSON:
        """
        {
            "background": {
                "id": "@regExp(/.*/)",
                "filePath": "@regExp(/.+/)",
                "description": "Background"
            },
            "separator": {
                "id": "@regExp(/.*/)",
                "filePath": "@regExp(/.+/)",
                "description": "TOTO"
            }
        }
        """
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204

    Scenario: Add an home page using an existing image for separator and background - POST
        Given I am login as merchant
        Then the request body is:
        """
        {
            "description": { "en": "Separator", "fr": "Séparator" },
            "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
        }
        """
        When I request "/api/en/mediaobject" using HTTP POST
        Then the response code is 201
        And I save the "id" as "separator"
        Then the request body is:
        """
        {
            "description": { "en": "Background", "fr": "Arrière plan" },
            "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
        }
        """
        When I request "/api/en/mediaobject" using HTTP POST
        Then the response code is 201
        And I save the "id" as "background"
        Then the request body is with "separator" for ":separator" and with "background" for ":background":
        """
        {
            "separator": { "id": ":separator", "description": { "en": "TOTO", "fr": "TOTO" } },
            "background": { "id": ":background", "description": { "en": "Background 2" } }
        }
        """
        When I request "/api/en/home" using HTTP POST
        Then the response code is 201
        And the response body contains JSON:
        """
        {
            "background": {
                "id": "@regExp(/.*/)",
                "filePath": "@regExp(/.+/)",
                "description": "Background 2"
            },
            "separator": {
                "id": "@regExp(/.*/)",
                "filePath": "@regExp(/.+/)",
                "description": "TOTO"
            }
        }
        """
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204
    
    Scenario: I can create an home page if I am connected as merchant with only background
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
        And the response body contains JSON:
        """
        {
            "background": {
                "id": "@regExp(/.*/)",
                "filePath": "@regExp(/.+/)",
                "description": "Home page background"
            }
        }
        """
        And the response body has 1 fields
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204
    
    Scenario: I can create an home page if I am connected as merchant with only separator
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
        And the response body contains JSON:
        """
        {
            "separator": {
                "id": "@regExp(/.*/)",
                "filePath": "@regExp(/.+/)",
                "description": "Home page separator"
            }
        }
        """
        And the response body has 1 fields
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204



    