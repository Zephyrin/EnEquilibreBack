Feature: Test Event

    Background:
        Given there are default users

    Scenario: Create an event
        Given I am login as admin
        Then the request body is:
        """
        {
            "title": {"en": "First event", "fr": "Premier évènement"},
            "subTitle": {"en": "Sub first event", "fr": "Premier sous évènement"},
            "description": {"en": "Event's description", "fr": "description de l'événement"},
            "order": 1,
            "image": "data:application/pdf;base64,JVBERi0xLjUKJYCBgoMKMSAwIG9iago8PC9GaWx0ZXIvRmxhdGVEZWNvZGUvRmlyc3QgMTQxL04gMjAvTGVuZ3=="
        }
        """
        When I request "/api/fr/event" using HTTP POST
        Then the response code is 201
        Then I save the id as id
        When I request "/api/fr/event/" with id using HTTP GET
        Then the response code is 200