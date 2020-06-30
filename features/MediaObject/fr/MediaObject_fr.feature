Feature: Test MediaObject with french result.

  Scenario:
    Given there are default users
    Given there are objects to post to "/api/en/mediaobject" with the following details:
      | #description  | image |
      | en: Katadyn's Logo, fr: Logo Katadyn | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQImWNgYGD4DwABBAEAfbLI3wAAAABJRU5ErkJggg== |
      | en: MSR's Logo,  fr: Logo MSR | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQImWNgYGD4DwABBAEAfbLI3wAAAABJRU5ErkJggg== |

  Scenario: Can get a single MediaObject if I am connected - GET
    Given I am login as admin
    Then I request "/api/fr/mediaobject/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "description": "Logo Katadyn",
      "filePath": "@regExp(/.+\\.png/)",
      "createdBy": {
            "id": "@regExp(/[0-9]+/)",
            "username": "@regExp(/.*/)",
            "roles": [
                "@regExp(/(ROLE_USER|ROLE_ADMIN|ROLE_SUPERADMIN)/)"
            ],
            "email": "@regExp(/.*@.*/)",
            "lastLogin": "@regExp(/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}.*/)",
            "created": "@regExp(/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}.*/)"
        }
    }
    """
    And the response body has 4 fields

  Scenario: Cannot get a single MediaObject if I am not connected - GET
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

  Scenario: Can get a collection of MediaObject - GET
    Given I am login as admin 
    Then I request "/api/fr/mediaobjects" using HTTP GET
    Then the response code is 200
    And the response body is a JSON array of length 2
    And the response body contains JSON:
    """
    [
      {
        "id": 1,
        "description": "Logo Katadyn",
        "filePath": "@regExp(/.+\\.png/)"
      },
      {
        "id": 2,
        "description": "Logo MSR",
        "filePath": "@regExp(/.+\\.png/)"
      }
    ]
    """

  Scenario: Can add a new MediaObject - POST
    Given I am login as admin
    Then the request body is:
      """
      {
        "description": {"en": "Rab's Logo", "fr": "Logo Rab"},
        "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
      }
      """
    When I request "/api/fr/mediaobject" using HTTP POST
    Then the response code is 201
    And the response body contains JSON:
    """
      {
        "id": 3,
        "description": "Logo Rab",
        "filePath": "@regExp(/.+\\.svg/)"
      }
    """
  
  Scenario: Can update an existing MediaObject
    Given I am login as admin
    Then the request body is:
      """
      {
        "description": { "en": "Logo of Katadyn", "fr": "Logo de Katadyn"},
        "image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQImWNgYGD4DwABBAEAfbLI3wAAAABJRU5ErkJggg=="
      }
      """
    When I request "/api/fr/mediaobject/1" using HTTP PUT
    Then the response code is 204

  Scenario: Cannot update an unknown MediaObject - PUT
    Given I am login as admin
    Then the request body is:
      """
      {
        "description": { "en": "Logo of Katadyn", "fr": "Logo de Katadyn"},
        "image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQImWNgYGD4DwABBAEAfbLI3wAAAABJRU5ErkJggg=="
      }
      """
    When I request "api/fr/mediaobject/4" using HTTP PUT
    Then the response code is 404

  Scenario: Can update an existing MediaObject - PATCH
    Given I am login as admin
    Then the request body is:
      """
      {
        "description": { "fr": "Logo de Katadyn" }
      }
      """
    When I request "/api/fr/mediaobject/1" using HTTP PATCH
    Then the response code is 204
    When I request "/api/fr/mediaobject/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
      {
        "id": 1,
        "description": "Logo de Katadyn",
        "filePath": "@regExp(/.+\\.png/)"
      }
    """
    When I request "/api/en/mediaobject/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "description": "Logo of Katadyn",
      "filePath": "@regExp(/.+\\.png/)"
    }
    """
  
  Scenario: Can update an existing MediaObject - PUT
    Given I am login as admin
    Then the request body is:
      """
      {
        "description": { "fr": "Logo de Katadyn" }
      }
      """
    When I request "/api/fr/mediaobject/1" using HTTP PUT
    Then the response code is 204
    When I request "/api/fr/mediaobject/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
      {
        "id": 1,
        "description": "Logo de Katadyn",
        "filePath": "@regExp(/.+\\.png/)"
      }
    """
    When I request "/api/en/mediaobject/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "filePath": "@regExp(/.+\\.png/)",
      "createdBy": {
            "id": "@regExp(/[0-9]+/)",
            "username": "@regExp(/.*/)",
            "roles": [
                "@regExp(/(ROLE_USER|ROLE_ADMIN|ROLE_SUPERADMIN)/)"
            ],
            "email": "@regExp(/.*@.*/)",
            "lastLogin": "@regExp(/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}.*/)",
            "created": "@regExp(/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}.*/)"
        }
    }
    """
    And the response body has 3 fields

  
  Scenario: Can delete an MediaObject
    Given I am login as admin
    When I request "/api/fr/mediaobject/2" using HTTP GET
    Then the response code is 200
    When I request "/api/fr/mediaobject/2" using HTTP DELETE
    Then the response code is 204
    When I request "/api/fr/mediaobject/2" using HTTP GET
    Then the response code is 404
    When I request "/api/fr/mediaobject/2" using HTTP GET
    Then the response code is 404

  Scenario: Can update extension of a MediaObject PATCH
    Given I am login as admin
    When I request "/api/fr/mediaobject/1" using HTTP GET
    Then the response code is 200
    And I save the "filePath"
    Given the request body is:
      """
      {
        "image": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8-"
      }
      """
    When I request "/api/fr/mediaobject/1" using HTTP PATCH
    Then the response code is 204
    When I request "/api/fr/mediaobject/1" using HTTP GET
    Then the response body contains JSON:
    """
    {
      "id": 1,
      "description": "@regExp(/.+/)",
      "filePath": "@regExp(/.+\\.svg/)"
    }
    """
    And The previous filename should not exists

  Scenario: Cannot do anything with an empty JSON
    Given I am login as admin
    Given the request body is:
    """
    """
    Then I request "/api/fr/mediaobject" using HTTP POST
    Then the response code is 422
    Then the response body contains JSON:
    """
    {
      "status": "Erreur.",
      "message": "Erreur de validation.",
      "errors": "Le body de la requête ne contient pas de JSon valide."
    }
    """
  
  Scenario: Cannot do anything with a JSON that contains invalid data
    Given I am login as admin
    Given the request body is:
    """
    {
      "invalid": "invalid data"
    }
    """
    Then I request "/api/fr/mediaobject" using HTTP POST
    Then the response code is 422
    Then the response body contains JSON:
    """
    {                                                                                                                                                      
      "status": "Erreur.",                                                                                                                                   
      "message": "Erreur de validation.",                                                                                                                       
      "errors": [
        {
          "errors": [
            "Ce formulaire ne doit pas contenir des champs supplémentaires."
          ],
          "children": {
            "description": [],
            "image": []
          }
        }
      ]
    }
    """