=== wp-senpy ===
Contributors: (agericke)
Donate link: http://example.com/
Tags: analysis, sentiments, emotions, comments, senpy
Requires at least: 4.6
Tested up to: 4.7
Stable tag: 0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin analyses the emotions and sentiments expressed by users on comments using Senpy.

== Description ==

This plugin analyses the emotions and sentiments expressed by user on comments. It uses [Senpy](http://senpy.readthedocs.io/en/latest/index.html) as a service for using several sentiment or emotion analysis algorithms. Senpy is an open source framework to build semantic and emotion analysis services that has been developed by the GSI of ETSIT-UPM.It returns data using semantic vocabularies such as Marl or Onyx.
The user will be able to select between the different emotions and sentiment algorithms available. This plugin uses the [Senpy API] (http://senpy.readthedocs.io/en/latest/apischema.html) for analysing the sentiments and emotions on comments.

Moreover, this analysis will be stored in the database. The user will select whether he wants to show this analysis information on comments or not.

For more information about Senpy and its available plugins, please visit [http://test.senpy.cluster.gsi.dit.upm.es/#about](http://test.senpy.cluster.gsi.dit.upm.es/#about)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-senpy` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Emotion & Sentiment Menu screen to configure the plugin and Senpy parameters.
4. Use the Emotion & Sentiment Menu > Comments Layout for selecting what to show in the comment's view

== Changelog ==

= 0.1 =
* Analyse comments using Senpy framework Service API.
* Basic configuration of comments visualization.
