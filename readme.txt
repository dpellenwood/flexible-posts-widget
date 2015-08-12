=== Flexible Posts Widget ===
Contributors: dpe415
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DJKSKHJWYAWDU
Tags: widget, widgets, posts, categories, tags, recent posts, thumbnails, custom post types, custom taxonomies, feature image
Requires at least: 3.2
Tested up to: 4.3
Stable tag: 3.5.0
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

An advanced posts display widget with many options. Display posts in your sidebars any way you'd like!

== Description ==
The default Recent Posts widget is exceptionally basic. I always find myself in need of a way to easily display a selection of posts from any combination post type or taxonomy. Hence, Flexible Posts Widget.

Flexible Posts Widget (FPW) is more than just a simple alternative to the default Recent Posts widget.  With many per-instance options it is highly customizable and allows advanced users to display the resulting posts virtually any way imaginable.

= Features & options =
* Customizable widget title
* Get posts by post type(s) and/or taxonomy & term(s) or directly by a list of post IDs.
* Control the number of posts displayed and the number of posts to offset.
* Option to display the post feature image.
* Select the post feature image size to display from existing image sizes: thumbnail, medium, large, post-thumbnail or any size defined by the current theme.
* Order posts by: date, modified date, ID, title, menu order, random, Post ID Order; and sort posts: ascending or descending.
* Each widget's output can be customized by user-defined templates added to the current theme folder.
* Multi Language support. Compatible with [WPML](http://wpml.org/) and [PolyLang](https://wordpress.org/plugins/polylang/) for sure. Not tested with other multi-language plugins, but it should work.

= Supported Languages =
* English
* Finnish
* Italian
* Polish
* Russian
* Spanish

== Installation ==
1. Upload the `flexible-posts-widget` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to 'Appearance' > 'Widgets' and place the widget into a sidebar to configure it.

= To use a custom HTML output template =
1. Create a folder called `flexible-posts-widget` in the root folder of the currently active theme.
1. Copy `widget.php` from within the plugin's `views` folder into your theme's new `flexible-posts-widget` folder.
1. Rename your theme's `widget.php` template file to a name of your choice. Example: `my-template.php`.
1. Go to 'Appearance' > 'Widgets' in WordPress to configure an instance of the widget.
1. In the 'Template Filename' field choose the name of the template file you added to your theme. Example: `My Template`

== Frequently Asked Questions ==

= How does the "Comma-separated list of post IDs" work? =
The third option (tab) available for getting posts is directly with a list of post IDs.  If there is a value in this field, any settings in the "Post Type" or "Taxonomy & Term" tabs will be ignored and all public post types will be queried for the specific post IDs provided.  All the other widget options (Display, Thumbnails and Template settings) will still be applied. 

= How do I find a post's ID? =
Per a [WordPress support thread](http://wordpress.org/support/topic/where-can-find-the-post-id):

Go to Posts in your WordPress admin, and click the post you need the ID of. Then, if you look in the address bar of your browser, you'll see something like this:

`http://example.com/wp-admin/post.php?post=1280&action=edit`

The number, in this case 1280, is the post ID.

= How can I display custom fields (custom meta values) with FPW? =
You'll want to create a custom HTML template by following [the instructions](http://wordpress.org/extend/plugins/flexible-posts-widget/installation/ "View instructions for creating custom FPW templates") and then you can use the standard WordPress [Custom Field](http://codex.wordpress.org/Custom_Fields "View custom field functions on the WordPress Codex") functions the same way you would if you were editing your theme's other template files.

A simple code example for a custom field named "test_field" _might_ look like the following:

` $test_field_value = get_post_meta( get_the_ID(), 'test_field', true );
echo $test_field_value; `

= How can I style the images, titles or other widget output a certain way? =
FPW intentionally does NOT add any styling of it's own.  To adjust the font size, color, alignment, image size, etc. of any output from this widget, you'll need to edit your theme's styles.

= Does this plugin/widget insert any styles or scripts into my site? =
FPW does not add styles or scripts to your public theme.  The plugin is intentionally designed to work within your existing theme.  FPW does add one stylesheet and one JavaScript to the Widgets page in wp-admin to help with the administration of any FPWs in use.

= Want to add support for another language? =
I'd love to support more languages.  The plugin's POT file is available with the download.  Feel free to post PO & MO files for your language to a [new forum thread](http://wordpress.org/tags/flexible-posts-widget/) and I'll get them added to the plugin.

= Questions, Support & Bug Reports =
To get answers to your questions, request help or submit a bug report, please start a [new forum thread](http://wordpress.org/tags/flexible-posts-widget/).

== Screenshots ==
1. Configuring a FPW in wp-admin with the Post Type tab displayed.
1. Configuring a FPW in wp-admin with the Taxonomy & Term tab displayed.
1. Configuring a FPW in wp-admin with the ID tab displayed.
1. An example FPW displayed using WordPress's TwentyTwelve theme and the default Feature Image (post-thumbnail) size.  This demonstrates how the plugin looks out-of-the-box with no user-customized styling or output in a default theme.
1. In the Wild: FPW displaying a selection of featured beers (Post Type: Brew) over at http://canalparkbrewery.com.  This example uses slightly customized output and some theme-specific styles. 
1. In the wild: FPW displaying a selection media attachments, with custom thumbnails.  This example uses highly customized HTML output and very theme-specific styles.
1. In the wild: FPW displaying several posts over at http://chnl7700.mnsu.edu.  Also highly customized output and theme styles.

== Other Notes ==
= Plugin Hooks =
Flexible posts widget currently has two public hooks:

* Filter: [`dpe_fpw_args`](https://plugins.trac.wordpress.org/browser/flexible-posts-widget/trunk/includes/class-fpw-widget.php#L191) allows filtering the query vars before submitting the widget posts query.
* Filter: [`dpe_fpw_template_{$template_name}`](https://plugins.trac.wordpress.org/browser/flexible-posts-widget/trunk/includes/class-fpw-widget.php#L354) filters the template file path used to display the widget output.

= Future updates & feature requests list =
* Use search box instead of ID text field for post id's
* Shortcode functionality.
* Get posts by Author.
* Filter out the post currently being viewed.
* Get posts from the same archive (term/post type/etc).
* Limit results by a time period.

== Upgrade Notice ==
= 3.5.0 =
Added multi-language support (WPML & PolyLang) and nested terms in the taxonomy & term select box. The plugin now updates widget settings in the background on update (if necessary).

== Changelog ==
= 3.5.0 =
* Major codebase rewrite to prepare for additional widgets, options.
* Automatically updates any widget settings when the plugin is updated to a new version
* Multi Language support (WPML & PolyLang)
* Term selection box now uses built-in WordPress function [`wp_terms_checklist()`](http://codex.wordpress.org/Function_Reference/wp_terms_checklist). This provides support for visually displaying nested terms and brings the plugin into compliance with WordPress code.

= 3.4.1 =
* Version bump for WordPress 4.1 support.

= 3.4 =
* Added Finnish language support. (Props: @eccola)
* Made the Template Filename field a select box based on the templates available in the current theme, the parent theme (if the current theme is a child theme) and the plugin's views folder. (Props @w3b-beweb)
* Added a new default template `Default.php` that works better in most sidebar situations.  The current default template `Widget.php` will be used by any existing widgets unless manually changed.
* Added support to order posts by Modified Date.
* Migrated admin CSS to SASS.
* Much code clean up and refactoring.
* Fixed an issue with language files not loading properly (Props @sajtdavid).

= 3.3.1 =
* Added plugin icon.
* Version bump for WordPress 4.0 support.

= 3.3 =
* Refactored the PHP Class to encapsulate the plugin.
* Added the ability to sort posts by "Post ID Order".  Useful when getting posts using the ID tab `post__in`. (Props: @cinus89)
* Added Russian translation. (Props: @mizhgun)
* Tested To bump for WordPress 3.9 support.

= 3.2.2 =
* Version bump for WordPress 3.8 support

= 3.2.1 =
* Added Italian language support. (Props: @adriano-esposito)

= 3.2 =
* Added option to ignore sticky posts.
* Added support to get post by post ID directly.
* Added Polish language support. (Props: @Invens)
* Added a few filters: `dpe_fpw_args` to filter the query vars before submitting the query and `dpe_fpw_template_{$template_name}` to filter the selected template.

= 3.1.2 =
* Fixed several pesky PHP notices. (Props: @eeb1)

= 3.1.1 =
* Fixed incorrect use of rtrim in getTemplateHierarchy when getting custom template files. (Props: @mortenf) 

= 3.1 =
* Internationalized and added Spanish language support. (Props: @elarequi)
* Added support for Media post types with "image/" mime types to be displayed directly in the default template.

= 3.0.2 =
* Bug fix: Added a check to make sure both taxonomy & term are set for tax queries.

= 3.0.1 =
* Bug fix: Not able to get all registered post types & taxonomies until after widget init.  Had to reorder some code.
* Bug fix: when getting post types for display in widget admin. (Props: @angelfish276)

= 3.0 =
* Allow widgets to query by post type and/or taxonomy & term instead of just one or the other. (Props: @vernal)
* Allow widgets to query by multiple post types and multiple terms within the same taxonomy.  (Props: @vernal)
* Changed the list of available post types and taxonomies from every possible option to just those that are public.
* General UI enhancements for the widget admin.
* Some minor code cleanup and security improvements.

= 2.1.1 =
* Fixed a source order bug in the widget.php template file. (Props: @carstenbach).

= 2.1 =
* Added offset parameter to display options.

= 2.0 =
* *Upgrade notice:* When upgrading from v1.x.x to v2.x, remember to double-check the settings for any existing widgets.
* Dynamically populate available terms based on selected taxonomy.
* Make the "Get Posts By" section selectable and only show the chosen method: Taxonomy & Term or Post Type.
* Miscellaneous admin improvements.

= 1.0.5 =
* Bug fix: Removed post_status 'private' from wp_queries. We don't want to show private posts in our loops.

= 1.0.4 =
* Fixed an issue where post thumbnails aren't displaying.

= 1.0.3 =
* Fixed PHP notices that showed in the admin when WP_DEBUG is enabled
* Added some stub code for future admin JavaScripts (not active yet).
* Readme.txt updates

= 1.0.2 =
* Readme.txt updates

= 1.0 =
* First public release