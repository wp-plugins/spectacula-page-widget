=== Plugin Name ===
Contributors: spectacula
Donate link: http://spectacu.la/signup/signup.php
Tags: widget, page, sidebar, plugin
Requires at least: 2.8.0
Tested up to: 2.8.5
Stable tag: 1.0.0

Widget that lets you output the content of a page in a any place that'll accept a widget and allows you to hide said page from wp_list_pages.

== Description ==

It’s often nice to be able to create a profile page or similar, and to display it in a sidebar.
You get to use the visual editor, and you don’t necessarily need admin access. Similarly, you avoid the issue of having to work with the much more difficult text widget.

Downsides include that the theme may not have CSS styles properly set, so for example a captioned image may not display correctly.
You should test this widget with your theme, across several browsers, before using it on a critical live site.

The widget will not show up on the page that is chosen to show in the widget. So if you click through to the page the widget will disappear from the sidebar.

If you want to translate the widget interface the files need for that are held in a sub-folder called lang.
Just copy the spec-page-widget-en-US.po file to match your language (spec-page-widget-xx-XX.po) then load it up in poedit and change what you need to change.
If you create a translation and you'd like your language file included with the plugin contact us at [Spectacu.la](http://spectau.la/) and we'll see about adding it.

== Installation ==

## The install ##
1. Upload `spec-page-widget.php` and lang/*.* to `/wp-content/plugins/spec-page-widget/` or `/wp-content/mu-plugins/` directory. If the directory doesn't exist then create it.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You should now see the widget show up under 'widgets' menu. Drop that widget into a sidebar.
4. With the widget in the sidebar you should see the config for this widget.

## The config ##
1.  First select the page you'd like to show.
2.  If you want a title to show tick the show title option. This makes two more options available, 'link title' and 'alternative title'. If you want to link to source or use another title enter them in this new area.
3.  Next we have an option to use an excerpt rather than the full content.
    By default Wordpress won't let you create an excerpt for pages so one will be generated unless you've used another plugin to create an excerpt in which case that'll be used.
4.  Hide the page from the normal Wordpress list of pages.
    This lets you to remove links to that page from other parts of Wordpress that use the wp\_list\_pages() call.
    If you have two of these widgets both calling the same page then hiding the page in one will hide it in all.
5.  Finally hit save.

== Changelog ==

= 1.0.0 =
*   Initial public release
*   Added a more user friendly interface on the widget
*   Added the option to hide the widget from wp_list_pages


== Frequently Asked Questions ==
= Why not just use a text widget =
Because using pages gives you the visual editor, revisions, short codes, easier management and numerous other benefits.

== Screenshots ==

== Upgrade Notice ==

= 1.0.0 =
If you have an older version of this I'd recommend you upgrade.
