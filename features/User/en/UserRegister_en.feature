Feature: Register an user using english language

    These tests cases are for user registration in english only.

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
        When I request "api/en/auth/register" using HTTP POST
        Then the response code is 200
        And the response body contains JSON:
        """
        {
            "token": "@regExp(/.*/)"
        }
        """

    Scenario: I will register with an empty username, password and email and get the information in english
        Given the request body is:
        """
        {
            "username": "",
            "password": "",
            "email": ""
        }
        """
        When I request "api/en/auth/register" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Error.",
            "message": "Validation error.",
            "errors": [
                {
                    "children": {
                        "username": {
                            "errors": [
                                "This value should not be blank."
                            ]
                        },
                        "password": {
                            "errors": [
                                "This value should not be blank."
                            ]
                        },
                        "email": {
                            "errors": [
                                "This value should not be blank."
                            ]
                        }
                    }
                }
            ]
        }
        """
    
    Scenario: I will register with a taken username and email and get error information in english
        Given the request body is:
        """
        {
            "username": "user",
            "password": "u",
            "email": "user@way_b.com"
        }
        """
        When I request "api/en/auth/register" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Error.",
            "message": "Validation error.",
            "errors": [ 
                {
                    "children": {
                        "username": {
                            "errors": [ "This value is already used." ]
                        },
                        "password": {
                            "errors": [ "This value must be at least 6 characters long." ]
                        },
                        "email": {
                            "errors": [ "This value is already used." ]
                        }
                    }
                }
            ]
        }
        """

    Scenario: I will register with a too long password get error information in english
        Given the request body is:
        """
        {
            "username": "user_new2",
            "password": "bépoèdjzwauiectsrnmçàyxkqgbépoèdjzwauiectsrnmçàyxkqgbépoèdjzwauieei",
            "email": "user_new2@way_b.com"
        }
        """
        When I request "api/en/auth/register" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Error.",
            "message": "Validation error.",
            "errors": [
                {
                    "children": {
                        "username": [],
                        "password": {
                            "errors": [
                                "This value cannot be longer than 64 characters."
                            ]
                        },
                        "email": []
                    }
                }
            ]
        }
        """

    Scenario: I will register with a too long username, password and email get error information in english
        Given the request body is:
        """
        {
            "username": "user_new2user_new2user_new2user_new2user_new2user_new2user_new2user_new2",
            "password": "bépoèdjzwauiectsrnmçàyxkqgbépoèdjzwauiectsrnmçàyxkqgbépoèdjzwauieei",
            "email": "user_new2@en_equilibre.comuser_new2@en_equilibre.comuser_new2@en_equilibre.com"
        }
        """
        When I request "api/en/auth/register" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Error.",
            "message": "Validation error.",
            "errors": [
                {
                    "children": {
                        "username": {
                            "errors": [
                                "This value cannot be longer than 64 characters."
                            ]
                        },
                        "password": {
                            "errors": [
                                "This value cannot be longer than 64 characters."
                            ]
                        },
                        "email": {
                            "errors": [
                                "This value cannot be longer than 64 characters."
                            ]
                        }
                    }
                }
            ]
        }
        """