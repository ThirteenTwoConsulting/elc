# ELC Assessment

## Docker

To run this, it is advisable to use Docker, though it is inot mandatory as long as you have PHP and Composer installed on the machine.

This project assumes and is built with the following assumptions:

* PHP 8.1 (& supporting Composer) is accessible
* Linux-style pathing (`/`) is supported

With Docker available, you just need to run this command to build the necessary image: `docker-compose up -d`

After this, enter into a shell for the service and run the `src/app.php` script:
* `docker-compose exec app /bin/sh`
* `php src/app.php food:facility:info`

## Non-Docker

If you aren't using Docker but have the necessary tooling installed, the process is sort of similar.  From the terminal:

* `composer install`
* `php src/app.php food:facility:info`

You can pass `--help` to the `php` command for info on how to make the data more sensible, but a run down of the various options available:

|Switch|Description|
|--|--|
|`--live`|Fetches data from the SF food truck source directly, otherwise uses a cached/downloaded version|
|`--cap`|Limit/cap how many rows are printed out (default is `500` which should print most if not all)|
|`--type`|Filter only those who have this phrase in their type|
|`--status`|Filter only those who have this phrase in their status|
|`--permit`|Know the permit you're looking for?  Filter only those who have this phrase in their permit!|
|`--address`|Look for facilities on a certain street, number, etc...|

Unless specified there is no default value and so if it is not passed in, it gets ignored.

If `--live` is not passed in, a message will also display warning the content may be stale/outdated, since it is cached.

## Ways To Improve

Below is a non-complete list of things I can think of to build on or improve this:

* Make the data more meaningful (I don't do food trucks nor have them near me so not sure what is useful to know exactly)
* Use a database for more impressive querying/filtering (this would also make it easier for me to do geospatial logic like "within 5 miles of <zip>")
* Add more available commands
* Build out a frontend
* Use a more solid framework instead of pieces of my 2 favorite ones
* Mount `./resources` in app container so if `facility_data.json` gets changed on the host a new image doesn't have to be built
* Use a property-agnostic filtering system (i.e.: `--filter name="bob hope"`) so as properties change new releases aren't mandatory