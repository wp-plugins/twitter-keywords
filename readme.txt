=== Twitter Keywords ===
Contributors: Jose Llinares
Tags: twitter, keywords
Requires at least: 2.7
Tested up to: 2.7.1 and 2.8
Stable tag: 1.0

Add tweets about a certain word, in certain language and from a specific user on your sidebar

== Description ==

Do you want to show your users what is being said in Twitter about a certain keyword? Want to increase your keyword density for a word? This plugin does exactly that.

You can configure the keyword, Tweet's language and the number of tweets that appear on the sidebar.

== Installation ==

Just Download the plugin and activate it in administration->plugins->Twitter Keywords.

Configure its values in administration->settings->Twitter Keywords

Add the following in the sidebar.php of your template
//Twitter Keyword Plugin code\r\n
if (function_exists('callTwitterKeywords')) {callTwitterKeywords();}

Or drag and drop in the widget administration.

You can edit your style in order to fit better with your blog design
== Changelog ==
v1.0
- Initial Release