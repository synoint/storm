# Syno Survey
### `Next generation survey tool for Syno International.`
## Prerequisites
Before starting the development server, you need to start the Traefik for HTTP routing, alongside Redis, Mongodb and MySQL instances.
## Starting Docker instances
To start the instances (PHP+Nginx and frontend watcher):
```shell
make start
```
To install the dependencies (Composer and Yarn), run:
```shell
make install-deps
```

## Available Makefile Commands

The following commands are available for managing the project using `make`:

| Command             | Description                                                                      |
|---------------------|----------------------------------------------------------------------------------|
| `make install-deps` | Installs PHP dependencies using Composer and JavaScript dependencies using Yarn. |
| `make start`        | Starts the Docker containers in detached mode.                                   |
| `make stop`         | Stops and removes the Docker containers.                                         |
| `make restart`      | Restarts the Docker containers.                                                  |
| `make build`        | Builds and starts the Docker containers.                                         |
| `make cache-clear`  | Clears the Symfony cache.                                                        |
| `make logs`         | Displays real-time logs from Docker containers.                                  |
| `make php-bash`     | Opens a Bash shell inside the PHP container.                                     |
| `make node-bash`    | Opens a Bash shell inside the Node container.                                    |


