# file-sharing

Simple service for sharing files

Implemented via:
* Slim Framework https://www.slimframework.com/
* Twig https://twig.symfony.com/
* PostgreSQL https://www.postgresql.org/
* Redis https://redis.io/
* Workerman https://www.workerman.net/
* Bootstrap https://getbootstrap.com/
* Plyr https://plyr.io/
* Webpack https://webpack.js.org/

## Features
* File uploading and sharing
* File downloading via PHP or Apache `mod_xsendfile`
* Automatic image thumbnail generation
* GIF preview generation from videos
* Comments under files
* Background processing of media files

## Requirements
* PHP 8.4 or above
* PostgreSQL
* Redis
* PHP Imagick extension
* FFmpeg
* Composer
* Node.js and npm
* Apache `mod_xsendfile` (optional, for X-Sendfile downloads)

## Installation
1. `git clone https://github.com/greentea242/file-sharing.git`
1. `cd file-sharing`
1. `composer install`
1. Configure the database connection, Redis settings, and file download method in `app/settings.php`
1. Set up the web server to serve the `public` directory
1. `npm install`
1. `npm run build`
1. Start the Workerman workers for background media processing