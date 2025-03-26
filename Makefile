PROJECT_NAME=syno_survey

DCOMPOSE=docker compose
EXEC_PHP=$(DCOMPOSE) exec survey-php
EXEC_FE=$(DCOMPOSE) exec survey-frontend

.PHONY: start stop restart build install-deps cache-clear logs php-bash node-bash

install-deps:
	$(EXEC_PHP) composer install
	$(EXEC_FE) yarn

start:
	$(DCOMPOSE) up -d

stop:
	$(DCOMPOSE) down

restart:
	$(DCOMPOSE) down && $(DCOMPOSE) up -d

build:
	$(DCOMPOSE) up --build -d

cache-clear:
	$(EXEC_PHP) bin/console cache:clear

logs:
	$(DCOMPOSE) logs -f

php-bash:
	$(EXEC_PHP) bash

node-bash:
	$(EXEC_FE) bash