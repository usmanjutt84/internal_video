# Internal Video

It's useful when you want to add video from by the direct video path to the filesystem.

## What is does?

It provides a field, widget and format called "Internal video" that accepts the absolute URL to the video (e.g `https://www.example.com/video.mp4`) and it used the [Video.js](https://videojs.com/) plugin to show the video.

## Installation
Use composer to install the module, run `composer require usmanjutt84/internal_video`

## Install dependencies

The Internal video module comes shipped with a "**composer.libraries.json**" file containing information about all up-to-date libraries required by the module itself, and so we will be using this file to install all libraries by merging the "**composer.libraries.json**" with the "**composer.json**" file of our Drupal website.

### Install Composer Merge Plugin

The merging is accomplished by the aid of the [Composer Merge Plugin](https://github.com/wikimedia/composer-merge-plugin) plugin available on GitHub, so from the project directory, open a terminal and run:

```
composer require wikimedia/composer-merge-plugin
```

### Include libraries into root Composer.json file

Edit the "**composer.json**" file of your website and under the **"extra": {** section add:

* **note**: the `web` represents the folder where drupal lives like: ex. `docroot`.

```
"merge-plugin": {
    "include": [
        "web/modules/contrib/internal_video/composer.libraries.json"
    ]
}
```

From now on, every time the "composer.json" file is updated, it will also read the content of "composer.libraries.json" file located at web/modules/contrib/internal_video/ and update accordingly.

### Install libraries

Run the following command:

```
composer update drupal/internal_video --with-dependencies
```

## Configurations

Go to `/admin/config/media/internal-video` and set the configuration.