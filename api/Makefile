all: build

build: dist/public/index.php

MYSQL_RUNNING := $(shell docker-compose top | grep mysql)
init-db: dist/db/schema.sql
ifeq (${MYSQL_RUNNING},)
	$(error MySQL is not running)
endif
	docker-compose exec mysql sh -c \
	  'mysql -u root -ppassword -e "CREATE DATABASE kunilo" && \
	   mysql -u root -ppassword kunilo < /kunilo/schema.sql'

start: dist/.env dist/public/index.php
	docker-compose up -d

stop:
	docker-compose stop

dist/public/index.php: dist/vendor/autoload.php $(shell find src)
	cp -R src/{env.example,db,include,public,templates,*.php} dist

dist/vendor/autoload.php: dist src/composer.json
	cp src/composer.* dist
	docker-compose run --rm -v "$(PWD)/dist":/api -w /api php composer install -v

dist:
	mkdir dist

clean:
	rm -Rf dist

purge: clean
	docker-compose down -v

.PHONY: all build init-db start stop clean purge
