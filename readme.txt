=== HTML to Shortcode Generator ===
Donate link: http://codecanyon.net/item/plugin-composer-for-wordpress/15913717?ref=webmediatree
Contributors: webmediatree
Tags: 	html to shortcode generator, custom plugin builder, custom plugin creator, custom plugin maker, Custom Wordpress plugin builder, Custom Wordpress plugin creator, Custom Wordpress plugin designer, Custom Wordpress plugin maker, html plugin, html to plugin, html to wordpress plugin, html to WP plugin, make plugin, plugin from html, WP plugin builder
Requires at least: 3.0.1
Tested up to: 4.5.2
Stable tag: 4.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Generate a WordPress Shortcode with HTML/CSS knowledge only! Install this plugin in your WordPress website and use a simple form to generate a shortcode.

== Description ==
Generate a WordPress Shortcode with basic HTML/CSS knowledge only - No PHP knowledge required!

Install this plugin in your Wordpress website and use a simple form to generate shortcode that a non-technical person can easily use. The only things you need are knowledge of basic Wordress & HTML / CSS and absolutely NOTHING ELSE!

For example, you like a ‘photo gallery’ and this gallery is only available in HTML/CSS but you want to add this to your Wordpress site and you want the admin of your site to change contents (add/delete photos, titles, etc.) of this gallery through wp-admin. Using this plugin, you can easily do this – generate a shortcode without using PHP/mySQL and then this shortcode you have generated can be used by the wp-admin to add/delete/update items in the gallery. 


This plugin also has a <a href='http://codecanyon.net/item/plugin-composer-for-wordpress/15913717?ref=webmediatree'>Pro Version</a> with following Advance Features

* Create Unlimited Modules.
* PHP Code Support
* Extra advance fields.
* Ability to include external resource

== Installation ==

Unzip Plugin file.
Navigate to the unzip folder.
Upload the the plugin folder to your `wp-content/plugins` folder. If you're using FTP, use 'binary' mode. Your directory structure on FTP should look as following: wp-content/plugins/html-to-shortcode-generator/[...files..here...]
Activate the plugin in your plugins administration panel.

== Frequently Asked Questions ==

= What skills should I have to create shortcode from html using this plugin? =

You only need to have a basic knowledge of HTML/CSS to create a plugin!

= What type of shortcode I can generate using this plugin? =

* Carousels (image slider)
* Galleries
* Listings (product listings, property listings, service listings, Team listing, Client listing etc.)
* FAQs
* Accordion
* News Slider
* Progress Bars
* Tables


== Practical Example ==

In this example we will show you how you will convert existing HTML code into wordpress shortcode which you can place anywhere in the wordpress to display your HTML.

Existing HTML:
				<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">
				<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600" rel="stylesheet" type="text/css">
				<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
				<style>
					body{font-family: 'Open Sans', sans-serif;}
					#faqs h3 {cursor: pointer;font-size: 16px;font-weight: 400; margin-bottom: 0px; color: #333;}
					#faqs h3.active	{ color:#d74646; }
					#faqs div { height:0; overflow:hidden; position:relative; }
					#faqs div p	{ padding:0; margin-bottom:15px; font-size: 14px; padding-left: 12px; }
					.heading span { padding: 8px 20px 8px 10px; display: inline-block; background: #f9f9f9;}
					.heading .fa{margin-right: 9px; color: rgba(193,103,103,0.6);}
					.heading .fa:before {content: "\f0fe";}
					.active .fa:before {content: "\f146" !important;}

				</style>
				<script>
					$(document).ready(function () {
						$('#faqs h3').each(function () {
							var tis = $(this), state = false, answer = tis.next('div').hide().css('height', 'auto').slideUp();
							tis.click(function () {
								state = !state;
								answer.slideToggle(state);
								tis.toggleClass('active', state);
							});
						});
					});
				</script>
				
				
		<div id="faqs">
					<h3 class="heading"><span><i class="fa"></i>What is lorem ipsum?</span></h3>
					<div style="display: none; height: auto;">
						<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap in</p>
					</div>
					<!-- / Cell --> 

		<h3 class="heading"><span><i class="fa"></i>What is lorem ipsum?</span></h3>
					<div style="display: none; height: auto;">
						<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap in</p>
					</div>
					<!-- / Cell --> 

					
					<h3 class="heading"><span><i class="fa"></i>What is lorem ipsum?</span></h3>
					<div style="display: none; height: auto;">
						<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap in</p>
					</div>
					<!-- / Cell --> 

					
					<h3 class="heading"><span><i class="fa"></i>What is lorem ipsum?</span></h3>
					<div style="display: none; height: auto;">
						<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap in</p>
					</div>
					<!-- / Cell --> 

					
					<h3 class="heading"><span><i class="fa"></i>What is lorem ipsum?</span></h3>
					<div style="display: none; height: auto;">
						<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap in</p>
					</div>
					<!-- / Cell --> 

					</div>
	
	
	Breif detail of above HTML
	This is HTML for FAQs. In this HTML you can see that this snippet contain External CSS resource, fonts and script and also contain inline css and javascript code. If you further review the html code you will see that there is a div whose id is 'faqs' and this div contain list of Faqs, and you can also see the pattern in the list, which is each FAQ consist of a h3 and a div with paragraph tag, so here we can divide this html code in three parts. One is Repeating pattern part, one is TOP HTML before the pattern and one is Bottom HTML after the pattern. 
	
	Top HTML:
	
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">
				<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600" rel="stylesheet" type="text/css">
				<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
				<style>
					body{font-family: 'Open Sans', sans-serif;}
					#faqs h3 {cursor: pointer;font-size: 16px;font-weight: 400; margin-bottom: 0px; color: #333;}
					#faqs h3.active	{ color:#d74646; }
					#faqs div { height:0; overflow:hidden; position:relative; }
					#faqs div p	{ padding:0; margin-bottom:15px; font-size: 14px; padding-left: 12px; }
					.heading span { padding: 8px 20px 8px 10px; display: inline-block; background: #f9f9f9;}
					.heading .fa{margin-right: 9px; color: rgba(193,103,103,0.6);}
					.heading .fa:before {content: "\f0fe";}
					.active .fa:before {content: "\f146" !important;}

				</style>
				<script>
					$(document).ready(function () {
						$('#faqs h3').each(function () {
							var tis = $(this), state = false, answer = tis.next('div').hide().css('height', 'auto').slideUp();
							tis.click(function () {
								state = !state;
								answer.slideToggle(state);
								tis.toggleClass('active', state);
							});
						});
					});
				</script>
				
				
		<div id="faqs">
		
		
	Repeating Part:
	We will only take the repeating pattern. Below is the repeating part!
	
	
		<h3 class="heading"><span><i class="fa"></i>What is lorem ipsum?</span></h3>
					<div style="display: none; height: auto;">
						<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap in</p>
					</div>
					<!-- / Cell --> 
					
					
					
Bottom HTML:
	
	</div>
	
	
	
	Integrate into Plugin.

Now we know the before part, repeating part and after part! We will now integrate it into our plugin
Install the plugin and Click on HTML Shortcode Generator link on left navigation menu. Click on the first entry in the list. Now Place the Top HTML part in Top HTML Field, Bottom HTML part in Bottom HTML Field and Repeating HTMl part in Reapting HTML Field.
Repeating HTML will need some changes in the code as explained below
We will need to replace the dynamic section of repeating HTML into the fields, in our current example there are two dynamic section, one is question and second is Answer, question is text field whereas answer is textarea field. so we will add text and text area field. After placing the Repeating HTML in the Repeating HTML field, Delete the Question "What is lorem ipsum?" and click on Editable Text Field button Provide the label "Question" in the prompt, this label will be use when adding the items, which is described later below, After that Delete the answer part with in the paragraph, and click on Editable Description Provide the label of this field "Answer" and click Ok. Now the repeating HTML section should be like this:

<h3 class="heading"><span><i class="fa"></i>#--txt--Question--#</span></h3>
					<div style="display: none; height: auto;">
						<p>#--txtarea--Answer--#</p>
					</div>
					<!-- / Cell --> 
					
					
Now Click on update button and and after that you will see a checkbox beside the update button click on it and press the update button again. Now you will see a new navigation menu item on the left side named "WP Plugins".

Add Items:
Now we will add question and answers which we want to display in front end. To do this click WP Plugins Link on the left side and click Add New button. Now you will see a form with Title field and Two dynamic fields which you have added in the plugin composing part! Fill out these fields and click Publish. Repeat this step to add as many question and answers as you want.

Display in front end.

Now Again go to the plugin composing form and copy the shortcode displayed under the Implementation heading. and place this shortcode anywhere you want to display it.

Open the page on which you have place the shortcode and you will see a nice Faq list.


== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png
3. screenshot-3.png

== Changelog ==

= 1.0 =
* Initial release.


== Upgrade Notice ==

No-Upgrade Notice

