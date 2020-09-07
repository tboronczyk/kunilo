all: build

build: build-api

build-api:
	(cd api; $(MAKE) build)

init-api-db:
	(cd api; $(MAKE) init-db)

start: start-api

start-api:
	(cd api; $(MAKE) start)

stop: stop-api

stop-api:
	(cd api; $(MAKE) stop)

clean: clean-api

clean-api:
	(cd api; $(MAKE) clean)

purge: purge-api

purge-api:
	(cd api; $(MAKE) purge)

.PHONY: all build build-api init-api-db start start-api stop stop-api clean \
  clean-api purge purge-api
