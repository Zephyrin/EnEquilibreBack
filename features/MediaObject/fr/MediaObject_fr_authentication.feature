Feature: Authorization test MediaObject with french result

    Background: Default settings.
        Given there are default users
        Given there are objects to post to "/api/fr/mediaobject" with the following details:
            | #description  | image |
            | en: Katadyn's Logo, fr: Logo Katadyn |  data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQImWNgYGD4DwABBAEAfbLI3wAAAABJRU5ErkJggg== |
            | en: MSR's Logo,  fr: Logo MSR |  data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQImWNgYGD4DwABBAEAfbLI3wAAAABJRU5ErkJggg== |
     
    Scenario: I created a mediaobject as user and admin cannot delete it - DELETE
        Given I am login as user
        Then the request body is:
        """
        {
            "description": {"en": "Rab's Logo", "fr": "Logo Rab"},
            "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
        }
        """
        When I request "/api/fr/mediaobject" using HTTP POST
        Then the response code is 201
        When I logout
        When I am login as merchant
        Then I request "/api/fr/mediaobject/3" using HTTP DELETE
        Then the response code is 204

    Scenario: I can get a list of MediaObject if I am not connected - GET
        Given I request "/api/fr/mediaobjects" using HTTP GET
        Then the response code is 200
        And the response body contains JSON:
        """
        [{
            "id": 1,
            "description": "Logo Katadyn",
            "filePath": "@regExp(/.+\\.png/)"
        },
        {
            "id": 2,
            "description": "Logo MSR",
            "filePath": "@regExp(/.+\\.png/)"
        }]
        """
    
    Scenario: I can get a single MediaObject if I am not connected - GET
        Given I request "/api/fr/mediaobject/1" using HTTP GET
        Then the response code is 200
        And the response body contains JSON:
        """
        {
            "id": 1,
            "description": "Logo Katadyn",
            "filePath": "@regExp(/.+\\.png/)"
        }
        """
    
    Scenario: I cannot post a MediaObject if I am not connected - POST
        Given the request body is:
        """
        {
            "description": {"en": "Rab's Logo", "fr": "Logo Rab"},
            "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
        }
        """
        When I request "/api/fr/mediaobject" using HTTP POST
        Then the response code is 401
        And the response body contains JSON:
        """
        {
            "status": 401,
            "message": "Tu n'es pas connecté."
        }
        """

    Scenario: I cannot update a MediaObject if I am not connected - PUT
        Given the request body is:
        """
        {
            "id": 1,
            "description": {"en": "Rab's Logo", "fr": "Logo Rab"},
            "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
        }
        """
        When I request "/api/fr/mediaobject/1" using HTTP PUT
        Then the response code is 401
        And the response body contains JSON:
        """
        {
            "status": 401,
            "message": "Tu n'es pas connecté."
        }
        """

    Scenario: I cannot update a MediaObject if I am not connected - PATCH
        Given the request body is:
        """
        {
            "id": 1,
            "description": {"en": "Rab's Logo", "fr": "Logo Rab"},
            "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
        }
        """
        When I request "/api/fr/mediaobject/1" using HTTP PATCH
        Then the response code is 401
        And the response body contains JSON:
        """
        {
            "status": 401,
            "message": "Tu n'es pas connecté."
        }
        """
    
    Scenario: I cannot delete a MediaObject if am not connected - DELETE
        Given I request "/api/fr/mediaobject/1" using HTTP GET
        Then the response code is 200
        Then I request "/api/fr/mediaobject/1" using HTTP DELETE
        Then the response code is 401
        And the response body contains JSON:
        """
        {
            "status": 401,
            "message": "Tu n'es pas connecté."
        }
        """
    

    
