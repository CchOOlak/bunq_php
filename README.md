# Bunq php test

In this project, I learned to code PHP pure :smile:

I know about OOP concepts, but I'd like make a simple project.

## Requirements
  - php-sqlite


## Run

    php -S localhost:8000


Send message:
  - path: `/send`
  - method: `POST`
  - body:
    ```jsonc
    {
        "username": <string>,
        "message": <string>,
    }
    ```

Refresh:
  - path: `/refresh`
  - method: `GET`
  - paremeters: `curser` // optional (last recieved message_id)
