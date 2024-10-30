=== Bulk Change Attachment Parent ===
Contributors: Viper007Bond
Donate link: http://www.viper007bond.com/donate/
Tags: attachments, uploads, attachment, upload, parent
Requires at least: 2.9
Tested up to: 3.0
Stable tag: trunk

Easily change the parents of multiple uploads.

== Description ==

Did you upload an image or other item and now want to change it's parent post/page to another one? This plugin will allow you to do that. It adds a new option to the "Bulk Actions" dropdown on the media management page.

The parent matters because the `[gallery]` feature simply lists out all uploads that have been attached to that post or page, so this will effectively allow you to move images between galleries.

[Joel Sholdice](http://lacquerhead.ca/) deserves some credit for this plugin and I used his single-item changing plugin for inspiration.

== Installation ==

###Upgrading From A Previous Version###

To upgrade from a previous version of this plugin, delete the entire folder and files from the previous version of the plugin and then follow the installation instructions below.

###Uploading The Plugin###

Extract all files from the ZIP file, **making sure to keep the file/folder structure intact**, and then upload it to `/wp-content/plugins/`.

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

###Plugin Activation###

Go to the admin area of your WordPress install and click on the "Plugins" menu. Click on "Activate" below this plugin's name.

###Plugin Usage###

Go to Media -> Library and check the boxes of the attachments you want to change. Then select "Change Parent" from the "Bulk Actions" dropdown.

You can also edit an individual media item and enter a new parent ID.

== Screenshots ==

1. Check the images you want and select "Change Parent" from the "Bulk Actions" dropdown.
2. Enter the ID of the new parent.
3. You can also change the parent of a single item.

== ChangeLog ==

= Version 1.0.0 =

* Initial release!