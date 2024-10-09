# ChatApp API Documentation

## SetUp

To setup and run the app, clone the repository and run the command

```json
composer install
```

Then run the command to start the local server

```json
php -S localhost:8080 -t public/
```

## Overview

This document provides an overview of the endpoints available in the ChatApp API. The API follows REST principles and returns responses in a consistent format.

### Generic Response Structure

All responses from the API adhere to the following structure:

```json
{
    "status": "string",
    "data": [],
    "message": "string"
}
```

### Authentication Endpoints

#### 1\. User Registration

-   **Endpoint**: `POST /api/v1/register`
-   **Description**: Registers a new user in the system.
-   **Request Body**:

    ```json

    {
        "username": "string",
        "password": "string"
    }
    ```

-   **Response**:
    -   **Status Code**: `201 Created`
    -   **Response Body**:

    ```json


    {
        "status": "success",
        "data": {
            "id": "integer",     // The ID of the newly created user
            "username": "string" // The username of the newly created user
        },
        "message": "User registered successfully."
    }
    ```

    -   **Error Response**:

    ```json

    {
        "status": "error",
        "data": [],
        "message": "User already exists."
    }
    ```

#### 2\. User Login

-   **Endpoint**: `POST /api/v1/login`
-   **Description**: Authenticates a user and returns a token for further requests.
-   **Request Body**:

    ```json

    {
        "username": "string", // Username of the user
        "password": "string"  // Password of the user
    }
    ```

-   **Response**:
    -   **Status Code**: `200 OK`
    -   **Response Body**:

    ```json

    {
        "status": "success",
        "data": {
            "token": "string" // JWT token for authentication
        },
        "message": "User logged in successfully."
    }
    ```

    -   **Error Response**:

    ```json

    {
        "status": "error",
        "data": [],
        "message": "Invalid credentials."
    }
    ```

### Group Management Endpoints

#### 3\. Create Group

-   **Endpoint**: `POST /api/v1/groups`
-   **Description**: Creates a new group.
-   **Request Body**:

    ```json

    {
        "name": "string" // The name of the new group
    }
    ```

-   **Response**:
    -   **Status Code**: `201 Created`
    -   **Response Body**:

    ```json

    {
        "status": "success",
        "data": {
            "id": "integer", // The ID of the newly created group
            "name": "string" // The name of the newly created group
        },
        "message": "Group created successfully."
    }
    ```

    -   **Error Response**:

    ```json

    {
        "status": "error",
        "data": [],
        "message": "A group with this name already exists."
    }
    ```

#### 4\. Join Group

-   **Endpoint**: `POST /api/v1/groups/{groupId}/join`
-   **Description**: Adds a user to an existing group.
-   **Request Body**:

    ```json

    {
        "user_id": "integer" // ID of the user to be added to the group
    }
    ```

-   **Response**:
    -   **Status Code**: `200 OK`
    -   **Response Body**:

    ```json

    {
        "status": "success",
        "data": [],
        "message": "User joined the group successfully."
    }
    ```

    -   **Error Response**:

    ```json

    {
        "status": "error",
        "data": [],
        "message": "Group does not exist."
    }
    ```

### Message Management Endpoints

#### 5\. Send Message

-   **Endpoint**: `POST /api/v1/messages`
-   **Description**: Sends a message to a group.
-   **Request Body**:

    ```json

    {
        "group_id": "integer", // ID of the group to which the message is sent
        "content": "string"    // The content of the message
    }
    ```

-   **Response**:
    -   **Status Code**: `201 Created`
    -   **Response Body**:

    ```json

    {
        "status": "success",
        "data": {
            "id": "integer", // The ID of the sent message
            "content": "string" // The content of the sent message
        },
        "message": "Message sent successfully."
    }
    ```

    -   **Error Response**:

    ```json

    {
        "status": "error",
        "data": [],
        "message": "Group does not exist."
    }
    ```

#### 6\. Get Messages

-   **Endpoint**: `GET /api/v1/messages/{groupId}`
-   **Description**: Retrieves all messages for a specific group.
-   **Response**:
    -   **Status Code**: `200 OK`
    -   **Response Body**:

    ```json

    {
        "status": "success",
        "data": [
            {
                "id": "integer", // The ID of the message
                "content": "string", // The content of the message
            },
            ...
        ],
        "message": "Messages retrieved successfully."
    }
    ```
