SEO Auto Linker
===============

Requires at least: 3.2

Tested up to: 3.3

SEO Auto Linker allows you to automagically add links into your content. Great for internal linking!

Description
---------------------

SEO Auto Linker is an update to the much loved [SEO Smart Links](http://wordpress.org/extend/plugins/seo-automatic-links/ "SEO Smart Links") plugin.

The plugin automatically links words and phrases in your post, page or custom post type content.

The difference is that you no longer have to try and guess what links will appear.  Specify keywords in a comma separated list, type in the URL to which those keywords will link, specify how many links to the specified URL per post, and then specify the post type. SEO Auto Linker does the rest.

Bugs?  Problems?  [Get in touch](http://pmg.co/contact).

Installation
----------------

1. Download the [zip file](https://github.com/chrisguitarguy/SEO-Auto-Linker/zipball/0.4)
2a. Unzip and upload the `seo-auto-linker` folder to your `wp-content/plugins` directory
2b. Install the plugin directly from the zip folder via the upload feature in the new plugin area
3. In the WordPress admin area, click "Plugins" on the menu and activate SEO Auto Linker
4. Set up your keywords and sit back!

Frequently Asked Questions
-----------------------------

**When I specify keywords, will they all get linked?**

Sort of.  If you keyword list is `lorem, ipsum`, the word `lorem` OR the word `ipsum` will be linked to the specified URL.  If the content contains both `lorem` and `ipsum, they will only both be linked if you set the number of links per post to more than one for that keyword list.

**Will this slow my site down?**

If you add hundreds of keywords, the answer is probably yes.


Changelog
----------------

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

