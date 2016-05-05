=== Import users from CSV with meta ===
Contributors: hornero, carazo
Donate link: http://paypal.me/codection
Tags: csv, import, importer, meta data, meta, user, users, user meta,  editor, profile, custom, fields, delimiter, update, insert
Requires at least: 3.4
Tested up to: 4.4.2
Stable tag: 1.8.7.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin to import users using CSV files to WP database automatically including custom user meta

== Description ==

Clean and easy-to-use Import users plugin. It includes custom user meta to be included automatically from a CSV file and delimitation auto-detector. It also is able to send a mail to each user imported and all the meta data imported is ready to edit into user profile.

*	Import CSV file with users directly to your WordPress
*	Import thousends of users in only some seconds
*	You can also import meta-data like data from WooCommerce customers using the correct meta_keys
*	Send a mail to every new user
*	Use your own 
*	You can also update data of each user
*	Assing a role
*	Create a cron task to import users periodically
*	Edit the metadata (you will be able to edit the metadata imported using metakeys directly in the profile of each user)
*	Read our documentation
*	Ask anything in support forum, we try to give the best support

In Codection we have more plugins, please take a look to them.

*	[Clean Login a plugin to create your own register, log in, lost password and update profile forms](https://wordpress.org/plugins/clean-login/) (free)
*	[RedSys Gateway for WooCommerce Pro a plugin to connect your WooCommerce to RedSys](http://codection.com/producto/redsys-gateway-for-woocommerce) (premium)
*	[Ceca Gateway for WooCommerce Pro a plugin to connect your WooCommerce to Ceca](http://codection.com/producto/ceca-gateway-for-woocommerce-pro/) (premium)
*	[BBVA Bancomer for WooCommerce Pro a plugin to connect your WooCommerce to BBVA Bancomer](http://codection.com/producto/bbva-bancomer-mexico-gateway-for-woocommerce-pro/) (premium)

## **Basics**

*   Import users from a CSV easily
*   And also extra profile information with the user meta data (included in the CSV with your custom fields)
*   Just upload the CSV file (one included as example)
*   All your users will be created/updated with the updated information, and of course including the user meta
*   Autodetect delimiter compatible with `comma , `, `semicolon ; ` and `bar | `

## **Usage**

Once the plugin is installed you can use it. Go to Tools menu and there, there will be a section called _Insert users from CSV_. Just choose your CSV file and go!

### **CSV generation**

You can generate CSV file with all users inside it, using a standar spreadsheet software like: Microsoft Excel, LibreOffice Calc, OpenOffice Calc or Gnumeric.

You have to create the file filled with information (or take it from another database) and you will only have to choose CSV file when you "Save as..." the file. As example, a CSV file is included with the plugin.

### **Some considerations**

Plugin will automatically detect:

* Charset and set it to **UTF-8** to prevent problems with non-ASCII characters.
* It also will **auto detect line-ending** to prevent problems with different OS.
* Finally, it will **detect the delimiter** being used in CSV file ("," or ";" or "|")


== Screenshots ==

1. Plugin link from dashboard
2. Plugin page
3. CSV file structure
4. Users imported
5. Extra profile information (user meta)


== Changelog ==

= 1.8.7.2 =
*	Bug in delete_user_meta solved thanks for telling us lizzy2surge

= 1.8.7.1 =
*	Bug in HTML mails solved

= 1.8.7 =
*	You can choose between plugin mail settings or WordPress mail settings, thanks to Awaken Solutions web design (http://www.awakensolutions.com/)

= 1.8.6 =
*	Bug detected in mailer settings, thanks to Carlos (satrebil@gmail.com)

= 1.8.5 =
*	Include code changed, after BuddyPress adaptations we break the SMTP settings when activating

= 1.8.4 =
*	Labels for mail sending were creating some misunderstandings, we have changed it

= 1.8.3 =
*	Deleted var_dump message to debug left accidentally

= 1.8.2 =
*	BuddyPress fix in some installation to avoid a fatal error

= 1.8.1 =
*	Now you have to select at least a role, we want to prevent the problem of "No roles selected"
*	You can import now BuddyPress fields with this plugin thanks to André Ihlar

= 1.8 =
*	Email template has an own custom tab thanks to Amanda Ruggles
*	Email can be sent when you are doing a cron import thanks to Amanda Ruggles

= 1.7.9 =
*	Now you can choose if you want to send the email to all users or only to creted users (not to the updated one) thanks to Remy Medranda
*	Compatibility with New User Approve (https://es.wordpress.org/plugins/new-user-approve/) included thanks to Remy Medranda

= 1.7.8 =
*	Metadata can be sent in the mail thanks to Remy Medranda

= 1.7.7 =
*	Bad link fixed and new links added to the plugin

= 1.7.6 =
*	Capability changed from manage_options to create_users, this is a better capatibily to this plugin

= 1.7.5 =
*	Bug solved when opening tabs, it were opened in incorrect target
*	Documentation for WooCommerce integration included

= 1.7.4 =
*	Bug solved when saving path to file in Cron Import (thanks to Robert Zantow for reporting)
*	New tabs included: Shop and Need help
*	Banner background from WordPress.org updated

= 1.7.3 =
*	Users which are not administrator now can edit his extra fields thanks to downka (https://wordpress.org/support/topic/unable-to-edit-imported-custom-profile-fields?replies=1#post-7595520)

= 1.7.2 =
*	Plugin is now compatible with WordPress Access Areas plugin (https://wordpress.org/plugins/wp-access-areas/) thanks to Herbert (http://remark.no/)
*	Added some notes to clarify the proper working of the plugin.

= 1.7.1 =
*	Bug solved. Thanks for reporting this bug: https://wordpress.org/support/topic/version-17-just-doesnt-work?replies=3#post-7538427

= 1.7 =
*	New GUI based on tabs easier to use
*	Thanks to Michael Lancey ( Mckenzie Chase Management, Inc. ) we can now provide all this new features:	
*	File can now be refered using a path and not only uploading.
*	You can now create a scheduled event to import users regularly.

= 1.6.4 =
*	Bugs detected and solved thanks to a message from Periu Lane and others users, the problem was a var bad named.

= 1.6.3 =
*	Default action for empty values now is: leave old value, in this way we prevent unintentional deletions of meta data.
*	Included donate link in plugin.

= 1.6.2 =
*	Thanks to Carmine Morra (carminemorra.com) for reporting problems with <p> and <br/> tags in body of emails.

= 1.6.1 =
*	Thanks to Matthijs Mons: now this plugin is able to work with Allow Multiple Accounts (https://wordpress.org/plugins/allow-multiple-accounts/) and allow the possibility of register/update users with same email instead as using thme in this case as a secondary reference to the user as the username.

= 1.6 =
*	Now options that are only useful if some other plugin is activated, they will only show when those plugins were activated
* 	Thanks to Carmine Morra (carminemorra.com) for supporting the next two big features:
*	New role manager: instead of using a select list, you can choose roles now using checkboxes and you can choose more than one role per user
* 	SMTP server: you can send now from your WordPress directly or using a external SMTP server (almost all SMTP config and SMTP sending logic are based in the original one from WP Mail SMTP - https://wordpress.org/plugins/wp-mail-smtp/). When the plugin finish sending mail, reset the phpmailer to his previous state, so it won't break another SMTP mail plugin.
* 	And this little one, you can use **email** in mail body to send to users their email (as it existed before: **loginurl**, **username**, **password**)

= 1.5.2 =
* 	Thanks to idealien, if we use username to update users, the email can be updated as the rest of the data and metadata of the user and we silence the email changing message generated by core.

= 1.5.1 =
* 	Thanks to Mitch ( mitch AT themilkmob DOT org ) for reporting the bug, now headers do not appears twice.

= 1.5 =
* 	Thanks to Adam Hunkapiller ( of dreambridgepartners.com ) have supported all this new functionalities.
*	You can choose the mail from and the from name of the mail sent.
*	Mail from, from name, mail subject and mail body are now saved in the system and reused anytime you used the plugin in order to make the mail sent easier.
*	You can include all this fields in the mail: "user_nicename", "user_url", "display_name", "nickname", "first_name", "last_name", "description", "jabber", "aim", "yim", "user_registered" if you used it in the CSV and you indicate it the mail body in this way **FIELD_NAME**, for example: **first_name**

= 1.4.2 =
* 	Due to some support threads, we have add a different background-color and color in rows that are problematic: the email was found in the system but the username is not the same

= 1.4.1 =
* 	Thanks to Peri Lane for supporting the new functionality which make possible to activate users at the same time they are being importing. Activate users as WP Members plugin (https://wordpress.org/plugins/wp-members/) consider a user is activated

= 1.4 =
* 	Thanks to Kristopher Hutchison we have add an option to choose what you want to do with empty cells: 1) delete the meta-data or 2) ignore it and do not update, previous to this version, the plugin update the value to empty string

= 1.3.9.4 =
* 	Previous version does not appear as updated in repository, with this version we try to fix it

= 1.3.9.3 =
* 	In WordPress Network, admins can now use the plugin and not only superadmins. Thanks to @jephperro

= 1.3.9.2 =
* 	Solved some typos. Thanks to Jonathan Lampe

= 1.3.9.1 =
* 	JS bug fixed, thanks to Jess C

= 1.3.9 =
* 	List of old CSV files created in order to prevent security problems.
* 	Created a button to delete this files directly in the plugin, you can delete one by one or you can do a bulk delete.

= 1.3.8 =
* 	Fixed a problem with iterator in columns count. Thanks to alysko for their message: https://wordpress.org/support/topic/3rd-colums-ignored?replies=1

= 1.3.7 =
* 	After upload, CSV file is deleted in order to prevent security issues.

= 1.3.6 =
* 	Thanks to idealien for telling us that we should check also if user exist using email (in addition to user login). Now we do this double check to prevent problems with users that exists but was registered using another user login. In the table we show this difference, the login is not changed, but all the rest of data is updated.

= 1.3.5 =
* 	Bug in image fixed
*	Title changed

= 1.3.4 =
* 	Warning with sends_mail parameter fixed
*	Button to donate included

= 1.3.3 =
* 	Screenshot updated, now it has the correct format. Thank to gmsb for telling us the problem with screenshout outdated

= 1.3.2 =
* 	Thanks to @jRausell for solving a bug with a count and an array

= 1.3.1 =
* 	WooCommerce fields integration into profile
*	Duplicate fields detection into profile
*	Thanks to @derwentx to give us the code to make possible to include this new features

= 1.3 =
*	This is the biggest update in the history of this plugin: mails and passwords generation have been added.
*	Thanks to @jRausell to give us code to start with mail sending functionality. We have improved it and now it is available for everyone.
*	Mails are customizable and you can choose 
*	Passwords are also generated, please read carefully the documentation in order to avoid passwords lost in user updates.

= 1.2.3 =
*	Extra format check done at the start of each row.

= 1.2.2 =
*	Thanks to twmoore3rd we have created a system to detect email collisions, username collision are not detected because plugin update metadata in this case

= 1.2.1 =
*	Thanks to Graham May we have fixed a problem when meta keys have a blank space and also we have improved plugin security using filter_input() and filter_input_array() functions instead of $_POSTs

= 1.2 =
*	From this version, plugin can both insert new users and update new ones. Thanks to Nick Gallop from Weston Graphics.

= 1.1.8 =
*	Donation button added.

= 1.1.7 =
*	Fixed problems with \n, \r and \n\r inside CSV fields. Thanks to Ted Stresen-Reuter for his help. We have changed our way to parse CSV files, now we use SplFileObject and we can solve this problem.

=======
= 1.2 =
*	From this version, plugin can both insert new users and update new ones. Thanks to Nick Gallop from Weston Graphics.

= 1.1.8 =
*	Donation button added.

= 1.1.7 =
*	Fixed problems with \n, \r and \n\r inside CSV fields. Thanks to Ted Stresen-Reuter for his help. We have changed our way to parse CSV files, now we use SplFileObject and we can solve this problem.

>>>>>>> .r1121403
= 1.1.6 =
*	You can import now user_registered but always in the correct format Y-m-d H:i:s

= 1.1.5 =
*	Now plugins is only shown to admins. Thanks to flegmatiq and his message https://wordpress.org/support/topic/the-plugin-name-apears-in-dashboard-menu-of-non-aministrators?replies=1#post-6126743

= 1.1.4 =
*	Problem solved appeared in 1.1.3: sometimes array was not correctly managed.

= 1.1.3 =
*	As fgetscsv() have problems with non UTF8 characters we changed it and now we had problems with commas inside fields, so we have rewritten it using str_getcsv() and declaring the function in case your current PHP version doesn't support it.

= 1.1.2 =
*	fgetscsv() have problems with non UTF8 characters, so we have changed it for fgetcsv() thanks to a hebrew user who had problems.

= 1.1.1 =
*	Some bugs found and solved managing custom columns after 1.1.0 upgrade.
*	If you have problems/bugs about custom headers, you should deactivate the plugin and then activate it and upload a CSV file with the correct headers again in order to solve some problems.

= 1.1.0 =
*	WordPress user profile default info is now saved correctly, the new fields are: "user_nicename", "user_url", "display_name", "nickname", "first_name", "last_name", "description", "jabber", "aim" and "yim"
* 	New CSV example created.
*	Documentation adapted to new functionality.

= 1.0.9 =
*   Bug with some UTF-8 strings, fixed.

= 1.0.8 =
*   The list of roles is generated reading all the roles avaible in the system, instead of being the default always.

= 1.0.7 =
*   Issue: admin/super_admin change role when file is too large. Two checks done to avoid it.

= 1.0.6 =
*   Issue: Problems detecting extension solved (array('csv' => 'text/csv') added)

= 1.0.5 =
*   Issue: Existing users role change, fixed

= 1.0.0 =
*   First release

== Upgrade Notice ==

= 1.0 =
*   First installation

== Frequently Asked Questions ==

*   Not yet

== Installation ==

### **Installation**

*   Install **Import users from CSV with meta** automatically through the WordPress Dashboard or by uploading the ZIP file in the _plugins_ directory.
*   Then, after the package is uploaded and extracted, click&nbsp;_Activate Plugin_.

Now going through the points above, you should now see a new&nbsp;_Import users from CSV_&nbsp;menu item under Tool menu in the sidebar of the admin panel, see figure below of how it looks like.

[Plugin link from dashboard](http://ps.w.org/import-users-from-csv-with-meta/assets/screenshot-1.png)

If you get any error after following through the steps above please contact us through item support comments so we can get back to you with possible helps in installing the plugin and more.
