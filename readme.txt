=== Widget Locationizer ===
Contributors: Nick Ohrn, Kevin Eklund
Donate link: http://tomuse.com/donation
Tags: widgets, widget, widget locationizer, smart widgets, sidebar, location, display, ads
Requires at least: 2.6.2
Tested up to: 2.7
Stable tag: 1.2.2

== Description ==

Widget Locationizer permits you to define where you want your widgets to appear.  You may specify 
the tags, categories, and page/post IDs for which a widget will be displayed.  It also provides an option 
to exclude the widget from being displayed on selected tags, categories, and post/page IDs.  Furthermore, 
you can assign a nofollow or dofollow status for a widget's contents too.

The following is how the plugin decides if a widget should show or not:

*	Is it a tag page and the tag is in the list of tags for which to show the widget?  If so, show the widget.
*	Is it a category page and the tag is in the list of categories for which to show the widget?  If so, show the widget.
*	Is the page a single page (a single post/page, basically)?  If so, is the post/page ID contained in the list of post/page IDs that were entered (if any)?  If so, show the widget.
*	Is the page a single page, no post/page IDs have been entered for the widget, and the Show on Other Pages option has been selected?  If so, show the widget.
*	Is the page none of a tag page, category page, or single page and the Show on Other pages option has been selected?  If so, show the widget.
*	Is the page a single page and is tagged or categorized with a tag or category from the list of tags or categories entered for the widget?  If so, show the widget.
*	Otherwise, don't show the widget

**More Info**

 *	[Widget Locationizer plugin](http://tomuse.com/wordperss/widget-locationizer/ "ToMuse.com Page for Widget Locationizer")
 *	Check out the other plugins from [ToMuse.com](http://tomuse.com/ "ToMuse.com") and [Nick Ohrn of Plugin-Developer.com](http://wordpress.org/extend/plugins/profile/nickohrn "Plugin-Developer.com")

== Installation ==

1. Upload the `widget-locationizer` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to `Settings > Widget Location and Following` and checkmark the settings you want to use
4. Modify your widgets to make sure they appear where you want them to


== FAQs ==

1. How do I get a widget to display on my homepage? - Select 'Yes' for "Should this widget appear on all non-tag and non-category pages?"
