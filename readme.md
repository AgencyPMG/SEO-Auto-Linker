SEO Auto Linker
===============

Requires at least: 3.2

Tested up to: 3.4

SEO Auto Linker allows you to automagically add links into your content. Great for internal linking!

Description
---------------------

SEO Auto Linker is an update to the much loved [SEO Smart Links](http://wordpress.org/extend/plugins/seo-automatic-links/ "SEO Smart Links") plugin.

The plugin automatically links words and phrases in your post, page or custom post type content.

The difference is that you no longer have to try and guess what links will appear.  Specify keywords in a comma separated list, type in the URL to which those keywords will link, specify how many links to the specified URL per post, and then specify the post type. SEO Auto Linker does the rest.

Bugs?  Problems?  [Get in touch](http://pmg.co/contact).

Installation
----------------

1. Download the [zip file](https://github.com/chrisguitarguy/SEO-Auto-Linker/zipball/0.7)
2. Unzip and upload the `seo-auto-linker` folder to your `wp-content/plugins` directory OR Install the plugin directly from the zip folder via the upload feature in the new plugin area
3. In the WordPress admin area, click "Plugins" on the menu and activate SEO Auto Linker
4. Set up your keywords and sit back!

Frequently Asked Questions
-----------------------------

**When I specify keywords, will they all get linked?**

Sort of.  If you keyword list is `lorem, ipsum`, the word `lorem` OR the word `ipsum` will be linked to the specified URL.  If the content contains both `lorem` and `ipsum`, they will only both be linked if you set the number of links per post to more than one for that keyword list.

**Will this slow my site down?**

If you add hundreds of keywords, the answer is probably yes.

**This is breaking my HTML! What gives?**

In order to keep things simple, SEO Auto Linker searches for some common elements in your HTML (headings, images, inputs, etc) and removes them before adding links, adding them back later. It can't predict every bit of HTML, unfortunately, so sometimes text in attributes or other text gets linked where it shouldn't.

**Does this automatically link custom fields too?**

Nope. Because custom fields (aka post meta) can be used for so many different things, it doesn't make sense to automatically link that content. If that's something you want to happen, it can be done.


Changelog
----------------

**0.7.1**

* Removed auto save script on the link editing screen (see #6)
* Fixed versioning issue with the migration plugin (see #4)
* Fixed some sloppy saving of custom fields (see #6)

**0.7**

* Revamped admin area
* Support for multibyte strings? (maybe)
* Completely refactored code

**0.6.4**

* Better use of `preg_quote`

**0.6.3**

* Use `preg_quote`

**0.6.2**

* Quick fixes for image replacements

**0.6.1**

* Quick fixes to header replacements

**0.6**

* Switched regex to unicode
* Added feature to blacklist URL's by keyword level, or site wide level
* Content in shortcodes is immune to replaces (eg. image captions, etc)


**0.5**

* Headers with attributes now get caught by the regular expression to prevent linking within them
* Posts can no longer link to themselves

**0.4**

* Removed caching due to issues with content not showing up

**0.3**

* Fixed a bug that allowed substrings within words to be linked.

**0.2**

* Fixed the replacement so it doesn't break images or inputs
* Fixed the post type selection for each keyword set

**0.1**

* The very first version.
* Support for automatic linking added

