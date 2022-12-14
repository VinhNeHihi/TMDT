=== Email Verification for WooCommerce Pro ===
Contributors: wpcodefactory
Tags: woocommerce, email, verification, email verification, woo commerce
Requires at least: 4.4
Tested up to: 5.5
Stable tag: 1.9.4
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Verify user emails in WooCommerce. Beautifully.

== Description ==

**Email Verification for WooCommerce** plugin lets you add email verification to WooCommerce.

= Main Features =

* Require **email verification** for new user registrations.
* Optionally enable email verification for **already registered users**.
* Skip email verification for selected **user roles**.
* Customize **frontend messages**.
* Optionally manually **verify**, **unverify**, **resend** email activation link **by admin**.
* Optionally **delete unverified users** from the database (manually or automatically once per week).
* Select if you want to send verification as a **separate email**, or **append** it to the standard WooCommerce "Customer new account" email.
* **Delay** standard WooCommerce **"Customer new account" email** until after successful verification (in a separate email).
* **Prevent** automatic user **login after registration**, including registration during checkout.
* And more...

= Premium Version =

[Email Verification for WooCommerce Pro](https://wpfactory.com/item/email-verification-for-woocommerce/) includes:

* **User email customization options**, including wrapping in standard WooCommerce email template.
* **Block "Thank you"** (i.e. "Order received") **page** access for non-verified users.
* **Block** standard WooCommerce customer **order emails** ("Order on-hold", "Processing order", "Completed order") for all non-verified users.
* **Block guests** from adding products to the cart.
* **Block checkout process** for non-verified users.
* Set activation link **expiration time**.
* Send **email to the admin** when new user verifies his email.
* Set emails **blacklist**.
* Automatically accept email verification from **social login** plugin.

= Feedback =

* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* [Visit plugin site](https://wpfactory.com/item/email-verification-for-woocommerce/).

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WooCommerce > Settings > Email Verification".

== Changelog ==

= 1.9.4 - 14/08/2020 =
* Plugin author updated.
* Tested up to: 5.5.

= 1.9.3 - 08/08/2020 =
* Dev - Code refactoring.

= 1.9.2 - 05/08/2020 =
* Dev - Advanced - "Mail function" option added.
* Dev - Code refactoring.

= 1.9.1 - 03/08/2020 =
* Dev - General - Redirect on success - "Redirect to custom URL" option added.

= 1.9.0 - 24/07/2020 =
* Dev - General - Prevent automatic user login after register - "Redirect" option moved from the "Advanced" section; "Custom redirect" option added.
* Dev - Emails - Email content - New placeholders added: `%user_id%`, `%user_first_name%`, `%user_last_name%`, `%user_login%`, `%user_nicename%`, `%user_email%`, `%user_display_name%`.
* Dev - Emails - WooCommerce template - Footer - Fallback `replace_placeholders()` function added (to ensure that e.g. `{site_title}` placeholder is replaced).
* Dev - Filters - `alg_wc_ev_redirect_on_registration` filter added; `alg_wc_ev_after_redirect_checkout` filter renamed to `alg_wc_ev_redirect_after_checkout`.
* Dev - Functions - `alg_wc_ev_is_user_verified_by_user_id()` and `alg_wc_ev_is_user_verified()` functions added.
* Dev - Code refactoring.
* WC tested up to: 4.3.

= 1.8.3 - 08/06/2020 =
* Dev - General Options - 'Logout unverified users on "My Account" page' option added.
* Dev - General Options - 'Redirect to "My account"' option renamed to "Redirect on success", and 'Redirect to "Shop" page' and "Redirect to home page" options added.
* Dev - Advanced Options - 'Force redirect on "Prevent automatic user login after register"' option added.
* WC tested up to: 4.2.

= 1.8.2 - 26/05/2020 =
* Fix - Security vulnerability fixed.

= 1.8.1 - 23/05/2020 =
* Fix - General Options - Send as a separate email - Correctly marking user as unverified now (when both "Send as a separate email" and "Enable email verification for already registered users" options are disabled).
* Dev - General Options - Logout unverified users on every page - "Redirect" option added (defaults to `yes`).
* Dev - General Options - Prevent automatic user login after checkout - Admin settings description updated.

= 1.8.0 - 22/05/2020 =
* Dev - General Options - "Send as a separate email" option added.
* Dev - General Options - "Logout unverified users on every page" options added.
* Dev - General Options - "Block checkout process for unverified users" options added.
* Dev - General Options - Blacklist emails - Now accepting multiple lines in settings.
* Dev - Admin Options - `manage_users_custom_column` hook priority increased.
* Dev - Admin Options - Settings descriptions updated.
* Dev - Saving "email sent" data in user meta ("activation", "WooCommerce customer new account", "admin" emails).
* Dev - Double checking if email was already sent ("WooCommerce customer new account", "admin" emails).
* Dev - `is_user_verified()` - Double checking guests.
* Dev - Code refactoring.
* Localization - `fr_FR` translation added.
* Tags updated.

= 1.7.0 - 08/05/2020 =
* Dev - General Options - "Expire activation link" options added.
* Dev - General Options - "Enable plugin" option removed.
* Dev - Admin Options - "Delete users automatically" option added.
* Dev - Admin Options - Delete users - `alg_wc_ev_delete_unverified_users_loop_args` filter added.
* Dev - Advanced Options - 'Notice for "Prevent automatic user login after checkout"' moved from "General" settings section.
* Dev - `[alg_wc_ev_translate]` shortcode added.
* WC tested up to: 4.1.

= 1.6.0 - 01/05/2020 =
* Dev - General Options - "Blacklist emails" options added.
* Dev - General Options - 'Accept verification from "WooCommerce - Social Login" plugin' option added.
* Dev - Admin Options - Users list column - Now checking "Skip email verification for user roles" and "Enable email verification for already registered users" option values when marking users as "verified".
* Dev - Admin Options - Users list column - Actions - "Resend" and "Unverify" admin actions added.
* Dev - Admin Options - Users list column - Actions - Notices added.
* Dev - Advanced Options - "Action for sending activation link email" option added.
* Dev - "Advanced" settings section added.
* Dev - Forcing to send activation email for non-verified users only now.
* Dev - `alg_wc_ev_verify_email` filter added.
* Dev - `alg_wc_ev_is_user_verified` filter added.
* Dev - `alg_wc_ev_new_user_action` filter added.
* Dev - Code refactoring.

= 1.5.1 - 27/04/2020 =
* Fix - Messages - Grammar errors fixed in the default messages (Resend: "... has been resend..." to "... has been resent..."; Failed/Error: "... can resend email..." to "... can resend the email...").
* Dev - General Options - Prevent automatic user login after register - Ensuring that `woocommerce_registration_auth_new_customer` is always `true`.
* Dev - General Options - Advanced - Action for "Prevent automatic user login after checkout" - 'On "'thank you' page"' option added (fixes the issue with CartFlows plugin).
* Dev - General Options - Restyled.

= 1.5.0 - 22/04/2020 =
* Fix - General Options - Prevent automatic user login after checkout - Logging out only unverified users now.
* Fix - General Options - Prevent automatic user login after checkout - Displaying "Activate" notice only for non-guests now (i.e. when guest checkout is allowed).
* Dev - General Options - Prevent automatic user login after checkout - 'Block "Thank you" page' option added.
* Dev - General Options - Prevent automatic user login after checkout - "Block customer order emails" option added.
* Dev - General Options - "Block guests from adding products to the cart" options added.
* Dev - General Options - Advanced - 'Action for "Prevent automatic user login after checkout"' option added.
* Dev - Admin Options - "Email" options added.
* Dev - `alg_wc_ev_core_loaded` action added.
* Dev - Settings - Restyled and descriptions updated.
* Dev - Code refactoring.

= 1.4.2 - 19/04/2020 =
* Fix - Emails - Email template - WooCommerce - Placeholders (e.g. `{site_title}`) are now replaced in footer text.

= 1.4.1 - 17/04/2020 =
* Dev - General Options - Advanced - 'Custom "logout" function' option added.
* Dev - Admin action link description updated.

= 1.4.0 - 17/04/2020 =
* Fix - General Options - Prevent automatic user login after checkout - Zero sum order bug fixed.
* Dev - General Options - Prevent automatic user login after checkout - "Add notice" option added.
* Dev - `%resend_verification_url%` - Using current URL (instead of "My account" URL) now.

= 1.3.1 - 08/04/2020 =
* Dev - General Options - "Prevent automatic user login after register" option added (defaults to `yes`).
* Dev - General Options - "Prevent automatic user login after checkout" option added (defaults to `yes`).
* Dev - Messages - Activate - `%resend_verification_url%` placeholder added.

= 1.3.0 - 03/04/2020 =
* Fix - "Reset settings" admin notice fixed.
* Dev - General Options - Skip email verification for user roles - "Guest" role removed.
* Dev - Admin Options - "Delete users" tool (deletes unverified users) added.
* Dev - Settings split into sections.
* Dev - `alg_wc_email_verification_after_save_settings` action added.
* Tested up to: 5.4.

= 1.2.0 - 13/03/2020 =
* Dev - General Options - 'Standard WooCommerce "Customer new account" email' (delay) option added.
* Dev - Code refactoring.
* Dev - Admin settings descriptions updated.
* Tested up to: 5.3.
* WC tested up to: 4.0.

= 1.1.1 - 10/11/2019 =
* Fix - Text domain changed to `emails-verification-for-woocommerce`.

= 1.1.0 - 10/11/2019 =
* Fix - Automatic logging in on "Create account" from checkout disabled.
* Dev - Admin Options - "Manual verification" option added.
* Dev - Admin Options - "Add column" option added.
* Dev - Shortcodes are now processed in "Email template > WooCommerce heading".
* Dev - Code refactoring.
* Plugin URI updated.
* WC tested up to: 3.8.
* Tested up to: 5.2.

= 1.0.0 - 27/05/2018 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =
This is the first release of the plugin.
