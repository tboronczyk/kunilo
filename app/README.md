# Kunilo App

This is the *Kunilo App* source code.

## Development
Be sure [Docker](https://docker.com/) and
[Make](https://www.gnu.org/software/make/) are installed before proceeding.
Docker is used to provision a Node.js container, and Make is used to coordinate
build actions.

### Makefile Targets

The `Makefile` offers the following targets:

  * `start` - run `cordova serve` in the Node.js container
  * `stop` - stop Cordova's web server
  * `build` - run `cordova build browser` to build the project (default)
  * `clean` - delete build artifacts
  * `purge` - delete build artifacts and tear-down container

