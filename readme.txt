=== Plugin Name ===
Contributors: innovese technologies 
Donate link: http://www.innovese.com
Tags: comments, registration, yoCAPTCHA, antispam, mailhide, captcha, wpmu, CAPTCHA Advertising, advertising
Requires at least: 2.7
Tested up to: 2.9.1
Stable tag: 3.1.4

Integrates yoCAPTCHA anti-spam methods with WordPress including comment, registration, and email spam protection. WPMU Compatible.

== Description ==

= What is yoCAPTCHA? =

In early 2011 we had set out with an aim to put CAPTCHAs to better use and launched [yoCAPTCHA](http://yocaptcha.com/ "yoCAPTCHA"). yoCAPTCHA, is a revolutionary platform for online advertising, which leverages the user-engaging potential CAPTCHAs have. Instead of the regular warped-text based CAPTCHAs it presents the user with a crisp readable brand advertisement. Below the advertisement is a simple fill in the blank based on ad-image, which the user needs to fill in order to pass the CAPTCHA. This leaves the ad-message etched in the user’s mind, making it the most powerful form of advertising. yoCAPTCHA’s robust back-end takes care of the security standards a CAPTCHA should possess. This ended up revolutionizing the digital brand-engagement scenario.

It is accessible by everyone. If the user has trouble reading the CAPTCHA challenge, he or she has the option of requesting a new one. If this does not help, there is also an audio challenge which users may use.

This plugin is [WordPress MU](http://mu.wordpress.org/) compatible.

For more information please view the [plugin page](http://yocaptcha.com/how-to-install-wordpress/ "WP-yoCAPTCHA - innovese").

== Installation ==

To install in regular WordPress and [WordPress MultiSite](http://codex.wordpress.org/Create_A_Network):

1. Upload the `wp-yoCAPTCHA` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. Get the yoCAPTCHA keys [here](http://yoCAPTCHA.net/api/getkey?domain=www.blaenkdenum.com&app=wordpress "yoCAPTCHA API keys") and/or the MailHide keys [here](http://mailhide.yoCAPTCHA.net/apikey "MailHide keys")

To install in WordPress MU (Forced Activation/Site-Wide):

1. Upload the `wp-yoCAPTCHA` folder to the `/wp-content/mu-plugins` directory
1. **Move** the `wp-yoCAPTCHA.php` file out of the `wp-yoCAPTCHA` folder so that it is in `/wp-content/mu-plugins`
1. Now you should have `/wp-content/mu-plugins/wp-yoCAPTCHA.php` and `/wp-content/mu-plugins/wp-yoCAPTCHA/`
1. Go to the administrator menu and then go to **Site Admin > yoCAPTCHA**
1. Get the yoCAPTCHA keys [here](http://yoCAPTCHA.net/api/getkey?domain=www.blaenkdenum.com&app=wordpress "yoCAPTCHA API keys") and/or the MailHide keys [here](http://mailhide.yoCAPTCHA.net/apikey "MailHide keys")

== Requirements ==

* You need the yoCAPTCHA keys [here](http://yocaptcha.com "yoCAPTCHA API keys")
* If you plan on using MailHide, you will need to have the [mcrypt](http://php.net/mcrypt "mcrypt") PHP module loaded (*Most servers do*)
* If you turn on XHTML 1.0 Compliance you and your users will need to have Javascript enabled to see and complete the yoCAPTCHA form
* Your theme must have a `do_action('comment_form', $post->ID);` call right before the end of your form (*Right before the closing form tag*). Most themes do.

== ChangeLog ==

== Frequently Asked Questions ==

= HELP, I'm still getting spam! =
There are four common issues that make yoCAPTCHA appear to be broken:

1. **Moderation Emails**: yoCAPTCHA marks comments as spam, so even though the comments don't actually get posted, you will be notified of what is supposedly new spam. It is recommended to turn off moderation emails with yoCAPTCHA.
1. **Akismet Spam Queue**: Again, because yoCAPTCHA marks comments with a wrongly entered CAPTCHA as spam, they are added to the spam queue. These comments however weren't posted to the blog so yoCAPTCHA is still doing it's job. It is recommended to either ignore the Spam Queue and clear it regularly or disable Akismet completely. yoCAPTCHA takes care of all of the spam created by bots, which is the usual type of spam. The only other type of spam that would get through is human spam, where humans are hired to manually solve CAPTCHAs. If you still get spam while only having yoCAPTCHA enabled, you could be a victim of the latter practice. If this is the case, then turning on Akismet will most likely solve your problem. Again, just because it shows up in the Spam Queue does NOT mean that spam is being posted to your blog, it's more of a 'comments that have been caught as spam by yoCAPTCHA' queue.
1. **Trackbacks and Pingbacks**: yoCAPTCHA can't do anything about pingbacks and trackbacks. You can disable pingbacks and trackbacks in Options > Discussion > Allow notifications from other Weblogs (Pingbacks and trackbacks).
1. **Human Spammers**: Believe it or not, there are people who are paid (or maybe slave labor?) to solve CAPTCHAs all over the internet and spam. This is the last and rarest reason for which it might appear that yoCAPTCHA is not working, but it does happen. On this plugin's [page](http://www.blaenkdenum.com/wp-yoCAPTCHA/ "WP-yoCAPTCHA - Blaenk Denum"), these people sometimes attempt to post spam to try and make it seem as if yoCAPTCHA is not working. A combination of yoCAPTCHA and Akismet might help to solve this problem, and if spam still gets through for this reason, it would be very minimal and easy to manually take care of.

= Why am I getting Warning: pack() [function.pack]: Type H: illegal hex digit?
You have the keys in the wrong place. Remember, the yoCAPTCHA keys are different from the MailHide keys. And the Public keys are different from the Private keys as well. You can't mix them around. Go through your keys and make sure you have them each in the correct box.

= Aren't you increasing the time users spend solving CAPTCHAs by requiring them to type two words instead of one? =
Actually, no. Most CAPTCHAs on the Web ask users to enter strings of random characters, which are slower to type than English words. yoCAPTCHA requires no more time to solve than most other CAPTCHAs.

= Are yoCAPTCHAs less secure than other CAPTCHAs that use random characters instead of words? =
Because we ask users to enter two words instead of one, we can increase the security of yoCAPTCHA against programs that attempt to guess the words using a dictionary. Whenever an IP address fails one yoCAPTCHA, we can show them more distorted words, and give them challenges for which we know both words. The probability of randomly guessing both words correctly would be less than one in ten million.

= Are CAPTCHAs secure? I heard spammers are using porn sites to solve them: the CAPTCHAs are sent to a porn site, and the porn site users are asked to solve the CAPTCHA before being able to see a pornographic image. =

CAPTCHAs offer great protection against abuse from automated programs. While it might be the case that some spammers have started using porn sites to attack CAPTCHAs (although there is no recorded evidence of this), the amount of damage this can inflict is tiny (so tiny that we haven't even seen this happen!). Whereas it is trivial to write a bot that abuses an unprotected site millions of times a day, redirecting CAPTCHAs to be solved by humans viewing pornography would only allow spammers to abuse systems a few thousand times per day. The economics of this attack just don't add up: every time a porn site shows a CAPTCHA before a porn image, they risk losing a customer to another site that doesn't do this.

== Screenshots ==

1. The yoCAPTCHA Settings
2. The MailHide Settings
