Feature: Register an user using french language

    These tests cases are for user registration in french only.

    Background:
        #Given there are default users

    Scenario: I will register a new account
        Given clean up database
        Given there are default users
        Given the request body is:
        """
        {
            "username": "user_new",
            "password": "user_new",
            "email": "user.new@new.com"
        }
        """
        When I request "api/fr/auth/register" using HTTP POST
        Then the response code is 200
        And the response body contains JSON:
        """
        {
            "token": "@regExp(/.*/)"
        }
        """

    Scenario: I will register with an empty username, password and email and get the information in french
        Given the request body is:
        """
        {
            "username": "",
            "password": "",
            "email": ""
        }
        """
        When I request "api/fr/auth/register" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Erreur.",
            "message": "Erreur de validation.",
            "errors": [
            {
                "children": {
                    "username": {
                        "errors": [
                            "Cette valeur ne doit pas être vide."
                        ]
                    },
                    "password": {
                        "errors": [
                            "Cette valeur ne doit pas être vide."
                        ]
                    },
                    "email": {
                        "errors": [
                            "Cette valeur ne doit pas être vide."
                        ]
                    }
                }
            }
            ]
        }
        """

    Scenario: I will register with a taken username and email and get error information in french
        Given the request body is:
        """
        {
            "username": "user",
            "password": "u",
            "email": "user@way_b.com"
        }
        """
        When I request "api/fr/auth/register" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Erreur.",
            "message": "Erreur de validation.",
            "errors": [
                {
                    "children": {
                        "username": {
                            "errors": [ "Cette valeur est déjà utilisée." ]
                        },
                        "password": {
                            "errors": [ "Cette valeur doit-être d'au moins 6 charactères de long." ]
                        },
                        "email": {
                            "errors": [ "Cette valeur est déjà utilisée." ]
                        }
                    }
                }
            ]
        }
        """
        
    Scenario: I will register with a too long password get error information in french
        Given the request body is:
        """
        {
            "username": "user_new2",
            "password": "bépoèdjzwauiectsrnmçàyxkqgbépoèdjzwauiectsrnmçàyxkqgbépoèdjzwauieei",
            "email": "user_new2@way_b.com"
        }
        """
        When I request "api/fr/auth/register" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Erreur.",
            "message": "Erreur de validation.",
            "errors": [
                {
                    "children": {
                        "username": [],
                        "password": {
                            "errors": [
                                "Cette valeur ne doit pas dépasser les 64 charactères de long."
                            ]
                        },
                        "email": []
                    }
                }
            ]
        }
        """
    
    Scenario: I will register with a too long username, password and email get error information in french
        Given the request body is:
        """
        {
            "username": "user_new2user_new2user_new2user_new2user_new2user_new2user_new2user_new2",
            "password": "bépoèdjzwauiectsrnmçàyxkqgbépoèdjzwauiectsrnmçàyxkqgbépoèdjzwauieei",
            "email": "user_new2@way_b.comuser_new2@way_b.comuser_new2@way_b.comuser_new2@way_b.com"
        }
        """
        When I request "api/fr/auth/register" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Erreur.",
            "message": "Erreur de validation.",
            "errors": [
                {
                    "children": {
                        "username": {
                            "errors": [
                                "Cette valeur ne doit pas dépasser les 64 charactères de long."
                            ]
                        },
                        "password": {
                            "errors": [
                                "Cette valeur ne doit pas dépasser les 64 charactères de long."
                            ]
                        },
                        "email": {
                            "errors": [
                                "Cette valeur ne doit pas dépasser les 64 charactères de long."
                            ]
                        }
                    }
                }
            ]
        }
        """