Feature: Test MediaObject with french result.

  Background:
    Given there are default users

  Scenario: Cannot post a pdf into media file
    Given I am login as admin
    Then the request body is:
      """
      {
        "description": {"en": "Wrong file.", "fr": "Mauvais fichier."},
        "image": "data:application/pdf;base64,JVBERi0xLjUKJYCBgoMKMSAwIG9iago8PC9GaWx0ZXIvRmxhdGVEZWNvZGUvRmlyc3QgMTQxL04gMjAvTGVuZ3=="
      }
      """
    When I request "/api/fr/mediaobject" using HTTP POST
    Then the response code is 422
    And the response body contains JSON:
    """
      {
        "status": "Erreur.",
        "message": "Erreur de validation.",
        "errors": [{
            "children": {
                "image": { "errors": ["Ce n'est pas une image en base64."]},
                "description": []
            }
        }]
      }
    """

  Scenario: Cannot post an icon into media file
    Given I am login as admin
    Then the request body is:
      """
      {
        "description": {"en": "Wrong file.", "fr": "Mauvais fichier."},
        "image": "data:image/icon;base64,AAABAAEAAQEAAAEAIAAwAAAAFgAAACgAAAABAAAAAgAAAAEAIAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAA=="
      }
      """
    When I request "/api/fr/mediaobject" using HTTP POST
    Then the response code is 422
    And the response body contains JSON:
    """
      {
        "status": "Erreur.",
        "message": "Erreur de validation.",
        "errors": [{
            "children": {
                "image": { "errors": ["Ce type d'image n'est pas supporté. Les formtas supportés sont le jpg, jpeg, png ou svg."]},
                "description": []
            }
        }]
      }
    """

  Scenario: Cannot post an icon into media file
    Given I am login as admin
    Then the request body is:
      """
      {
        "description": {"en": "Wrong file.", "fr": "Mauvais fichier."},
        "image": "data:image/png;base64,"
      }
      """
    When I request "/api/fr/mediaobject" using HTTP POST
    Then the response code is 422
    And the response body contains JSON:
    """
      {
        "status": "Erreur.",
        "message": "Erreur de validation.",
        "errors": [{
            "children": {
                "image": { "errors": ["Erreur lors du décodage du base64."]},
                "description": []
            }
        }]
      }
    """
  Scenario: Cannot write a new image into media folder
    Given I am login as admin
    Given the media folder is unwritable
    Then the request body is:
      """
      {
        "description": {"en": "Wrong file.", "fr": "Mauvais fichier."},
        "image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADElEQVR42mP4z8AAAAMBAQD3A0FDAAAAAElFTkSuQmCC"
      }
      """
    When I request "/api/fr/mediaobject" using HTTP POST
    Then the response code is 422
    And the response body contains JSON:
    """
      {
        "status": "Erreur.",
        "message": "Erreur de validation.",
        "errors": [{
            "children": {
                "image": { "errors": ["Impossible de sauvegarder l'image."]},
                "description": []
            }
        }]
      }
    """
    Given the media folder is writable
