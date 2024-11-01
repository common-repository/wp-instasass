=== wp_instasass ===
Contributors: siafixcms
Donate link: 
Tags: 
Requires at least: 4.8
Tested up to: 4.9.1
Stable tag: 1.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows to use sass/scss as if it were css. It takes care of the compiling of the code and delivers the css from a Cloudflare cdn.

== Description ==

This plugin allows to use sass/scss as if it were css. It takes care of the compiling of the code and delivers the css from a Cloudflare cdn. Therefore it is very fast. The compiling is done with the C++ library for sass.

== Installation ==

1. Find the `wp_instasass` plugin in the plugin catalogue or upload `wp-instasass` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to our site to get an API key and save it in the Instasass settings

== Frequently asked questions ==

= What SASS/SCSS Compiler Are You Using? =
Glad you asked. We are using the fastest possible compiler - sassc based on the C++ library. This is the quickest compiler out there and yes we have done some testing - seems to do well even under 100 concurrent connections.

= Where do I put my scss code? =
Please refer to the Instasass settings page for a full path of the scss files to be set up. You will also find a dummy file called 0main.scss. This is there to illustrate that the files will be enqueued using the WordPress function in the order they are listed in the directory.

= Is it being cached? =
Yes and no. Our CDN caches everything we put out, but your caching solution depending what kind it is might not cache it because the file name changes from every change. However, this is good, because the caching is done on the CDN level so it shouldn't bother you. However if you are using the local compiled css files instead of the CDN then your caching solution most likely is caching it so take care to clear the cache when you use this or initially disable the caching solution so you can develop freely.

= I have changed the theme. How do I get the folders created? =
You can just go ahead and create the folders freely your self. But you can also just go to the InstaSASS settings page and that will check if the current theme has the folders and if not create them for you. Just make sure you have the appropriate permissions set up on your system.

= I have chosen a different compilation mode. Why is the result the same? =
The CDN is making the response css optimal for transfer. If you wish to go around the CDN then just use the checkbox saying "Go around CDN entirely (do not use the CDN URLs)". This will allow you to see and use the compilation mode you chose.

= Error message: In your PHP.INI - allow_url_fopen is disabled. The plug-in API depends on this - please enable it! =
This means your php.ini does not allow overriding the setup from php and the plugin can't access the API it depends on. Please enable the allow_url_fopen setting and it will work fine.

= Can I use the same API key for all my projects? =
In short Yes! Because this is a service we sell to developers and not to their clients, we understand that many different projects/domains will use the same API key, but to avoid leaving your API key in there you should use the "Save css compiled resulting copies locally" option to get the system to use it instead of the CDN. If you do feel like your client would benefit from having a CDN then please ask them to get their own API key. Because they are not developers the key is free as they don't need to raise their compilation request count. And it will allow for 25 changes per month just for supporting it.

= Where can I find support? =
Post your problem (description, examples, links) in the support forum for this plugin and we will try to answer as soon as possible. If possible include any additional information of versions and browsers and other technical info that might be useful.

= Do I have to pay for this? =
We do offer a limited free version that allows for very little requests to the compiler per month. But we recommend you invest a little to have this tool without any limits. We are not very hungry just 15 EUR a month will go a long way. We hope to improve this over time and make this our business. We want to make WordPress better with our plug-ins and make your life easier.

= How can I pay for the service? =
We are currently in the process of creating a payment system where you will be able to add your credit card and it will be automatically charged once a month. But for now you can just get in touch with us via e-mail info@fixcms.lv and ask for a paid version of this and we will send you the necessary information on how to do this at the moment.

= What happens when I stop paying for the service? =
Any changes you have done to your scss will not work and it will in fact error telling you that you have not paid. However, everything will resume once you have paid.

== Screenshots ==
1. The settings are pretty straight forward
2. The result is an enqued script

== Changelog ==

= 1.0.5 =
* Allow overriding all styles by loading the scss files last

= 1.0.4 =
* Allow bypassing CDN completely
* Allow choice if you want to enque the scripts
* Allow to choose the compiling mode

= 1.0.3 =
* More informative error message in case you have not enabled the required php settings
* Attempt to enable the PHP settings for the plugin to work

= 1.0.2 =
* Save compiled files locally and use them instead of the CDN
* Save compiled files locally, but don't use them in the page
* Allow the user to include the resulting css inline instead of using a link
* Show the amount of scss files and advise the user to keep the number low


== Upgrade notice ==

= 1.0.5 =
The enqued files are called last now - so you can override all you need

= 1.0.4 =
This upgrade will allow you to avoid CDN all together and have a bit more control over the plugins workings.

= 1.0.3 =
A bit more informative errors regarding dependencies.

= 1.0.2 =
Way more flexibility in storing and using the resulting compiled css code.

