Feature: Test Home JSON API endpoint PATCH
 
    Background:
        Given there are default users

    Scenario: I can create and udpate an home page if I am connected as merchant using PATCH
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
                "id": "@regExp(/[0-9]+/)",
                "description": "Home page background",
                "filePath": "@regExp(/.*\\.svg/)",
                "createdBy": {
                    "id": "@regExp(/[0-9]+/)"
                }
            }
        }
        """
        And the response body has 1 fields
        Given the request body is:
        """
        {
            "background": 1,
            "separator": {
                "description": {"en": "Home page separator", "fr": "Séparateur de la page d'acceuil"},
                "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
            }
        }
        """
        When I request "/api/en/home" using HTTP PATCH
        Then the response code is 204
        When I request "/api/en/home" using HTTP GET
        Then the response body contains JSON:
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

    Scenario: I can create and udpate an home page if I am connected as merchant using PATCH
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
                "id": "@regExp(/[0-9]+/)",
                "description": "Home page background",
                "filePath": "@regExp(/.*\\.svg/)",
                "createdBy": {
                    "id": "@regExp(/[0-9]+/)"
                }
            }
        }
        """
        And the response body has 1 fields
        Given the request body is:
        """
        {
            "separator": {
                "description": {"en": "Home page separator", "fr": "Séparateur de la page d'acceuil"},
                "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
            }
        }
        """
        When I request "/api/en/home" using HTTP PATCH
        Then the response code is 204
        When I request "/api/en/home" using HTTP GET
        Then the response body contains JSON:
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

    Scenario: I cannot udpate an home page with empty json if I am connected as merchant using PATCH
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
                "id": "@regExp(/[0-9]+/)",
                "description": "Home page background",
                "filePath": "@regExp(/.*\\.svg/)",
                "createdBy": {
                    "id": "@regExp(/[0-9]+/)"
                }
            }
        }
        """
        And the response body has 1 fields
        Given the request body is:
        """
        {
        }
        """
        When I request "/api/en/home" using HTTP PATCH
        Then the response code is 422
        When I request "/api/en/home" using HTTP GET
        Then the response body contains JSON:
        """
        {
            "background": {
                "id": "@regExp(/[0-9]+/)",
                "description": "Home page background",
                "filePath": "@regExp(/.*\\.svg/)",
                "createdBy": {
                    "id": "@regExp(/[0-9]+/)"
                }
            }
        }
        """
        And the response body has 1 fields
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204

    Scenario: I cannot udpate an home page with wrong value if I am connected as merchant using PATCH
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
                "id": "@regExp(/[0-9]+/)",
                "description": "Home page background",
                "filePath": "@regExp(/.*\\.svg/)",
                "createdBy": {
                    "id": "@regExp(/[0-9]+/)"
                }
            }
        }
        """
        And the response body has 1 fields
        Given the request body is:
        """
        {
            "background": "wrong value",
            "new": "new",
            "separator": {
                "description": {"en": "Home page separator", "fr": "Séparateur de la page d'acceuil"},
                "image": "data:application/pdf;base64,JVBERi0xLjUKJYCBgoMKMSAwIG9iago8PC9GaWx0ZXIvRmxhdGVEZWNvZGUvRmlyc3QgMTQxL04gMjAvTGVuZ3=="
            }
        }
        """
        When I request "/api/en/home" using HTTP PATCH
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
        When I request "/api/en/home" using HTTP GET
        Then the response body contains JSON:
        """
        {
            "background": {
                "id": "@regExp(/[0-9]+/)",
                "description": "Home page background",
                "filePath": "@regExp(/.*\\.svg/)",
                "createdBy": {
                    "id": "@regExp(/[0-9]+/)"
                }
            }
        }
        """
        And the response body has 1 fields
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204
    
    Scenario: I cannot udpate an home page if I am connected as user using PATCH
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
                "id": "@regExp(/[0-9]+/)",
                "description": "Home page background",
                "filePath": "@regExp(/.*\\.svg/)",
                "createdBy": {
                    "id": "@regExp(/[0-9]+/)"
                }
            }
        }
        """
        And the response body has 1 fields
        Given I am login as user
        Given the request body is:
        """
        {
            "background": 1,
            "separator": {
                "description": {"en": "Home page separator", "fr": "Séparateur de la page d'acceuil"},
                "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
            }
        }
        """
        When I request "/api/en/home" using HTTP PATCH
        Then the response code is 403
        When I request "/api/en/home" using HTTP GET
        Then the response body contains JSON:
        """
        {
            "background": {
                "id": "@regExp(/[0-9]+/)",
                "description": "Home page background",
                "filePath": "@regExp(/.*\\.svg/)",
                "createdBy": {
                    "id": "@regExp(/[0-9]+/)"
                }
            }
        }
        """
        And the response body has 1 fields
        Given I am login as admin
        When I request "/api/en/home" using HTTP DELETE
        Then the response code is 204
        