# Mini Gallery

- Stable tag: 2.0
- Requires at least: 6.0
- Tested up to: 6.7.1
- Requires PHP: 8.0
- License: GPLv2 or later
- License URI: [GPLv2 License](https://www.gnu.org/licenses/gpl-2.0.html)
- Tags: gallery, lite
- Contributors: @omarashzeinhom

A simple gallery that acts as a carousel.

## Description

This WordPress plugin provides a lightweight gallery with a simple carousel. It allows your marketing team to easily upload images and display them in a simple gallery format. The plugin includes a shortcode to implement a carousel on your site.

### Features

- Allows the user marketing team to upload images.
- Simple gallery carousel.
- Lightweight carousel with shortcode.

## Screenshots

1. Single Gallery
2. Multi Carousel
3. Grid Carousel
4. All Galleries

## Installation

1. Upload the folder `mini-gallery` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Add the gallery using the provided shortcode.

## Frequently Asked Questions

### What is the carousel feature?

The carousel feature allows the gallery images to be displayed in a rotating slider format.

### Can I customize the gallery?

Yes, you can customize the gallery by adjusting the settings provided in the plugin.

### How do I add images to the gallery?

You can upload images through the plugin's interface, and they will be displayed in the gallery using a simple carousel layout.

## Changelog

### 1.0

- Initial release.

### 1.1

- Added `<table>` to Gallery Item.

#### Unit testing

- Phpunit testing without wp standards.

``` bash
./vendor/bin/phpunit --version
```

- Php Unit testing with wp standards.

```bash
 vendor/bin/phpcs -ps mini-gallery.php --standard=WordPress
```
##### Open Source Assets 

[Gallery Icon](https://icon-sets.iconify.design/?query=gallery)

