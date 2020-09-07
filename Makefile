all: build

build: build-api build-app

build-api:
	(cd api; $(MAKE) build)

build-app:
	(cd app; $(MAKE) build)

init-api-db:
	(cd api; $(MAKE) init-db)

start: start-api start-app

start-api:
	(cd api; $(MAKE) start)

start-app:
	(cd app; $(MAKE) start)

stop: stop-api stop-app

stop-api:
	(cd api; $(MAKE) stop)

stop-app:
	(cd app; $(MAKE) stop)

clean: clean-api clean-app

clean-api:
	(cd api; $(MAKE) clean)

clean-app:
	(cd app; $(MAKE) clean)

purge: purge-api purge-app

purge-api:
	(cd api; $(MAKE) purge)

purge-app:
	(cd app: $(MAKE) purge)

.PHONY: all build build-api build-app init-api-db start start-api start-app \
  stop stop-api stop-app clean clean-api clean-app purge purge-api purge-app
