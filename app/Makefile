all: build

build: build-browser

build-browser: platforms/browser/www/index.html

start: platforms/browser/www/index.html
	(docker-compose run --rm --name=cordova-serve -T -v "$(PWD)":/app -w /app \
	  -p 8000:8000 node npx cordova --no-telemetry serve 8000) &

CORDOVA_PID := $(shell docker ps -q -f name=cordova-serve)
stop:
ifneq (${CORDOVA_PID},)
	docker kill ${CORDOVA_PID}
endif

platforms/browser/www/index.html: www/index.html www/js/app.js www/css/styles.css platforms/browser/browser.json
	docker-compose run --rm -v "$(PWD)":/app -w /app \
 	  node npx cordova --no-telemetry build browser

platforms/browser/browser.json:
	docker-compose run --rm -v "$(PWD)":/app -w /app \
	  node npx cordova --no-telemetry prepare browser

www/index.html: www src/index.html $(shell find src/img) 
	cp src/index.html www
	cp -R src/img www

www/js/app.js: node_modules www $(shell find src \( -name '*.js' -o -name '*.vue' \)) src/config.js
	docker-compose run --rm -v "$(PWD)":/app -w /app node npm run-script build-js

www/css/styles.css: node_modules www $(shell find src/css -name '*css')
	docker-compose run --rm -v "$(PWD)":/app -w /app node npm run-script build-css
	cp -R node_modules/onsenui/css/{font_awesome,ionicons,material-design-iconic-font} www/css

node_modules: package.json
	docker-compose run --rm -v "$(PWD)":/app -w /app node sh -c \
	  'npm install --verbose && npm rebuild --verbose'
www:
	mkdir www

clean:
	rm -Rf www plugins platforms

purge: clean
	rm -Rf node_modules
	docker-compose down -v

.PHONY: all build start stop clean purge
