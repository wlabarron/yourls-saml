# YOURLS SAML Authentication
Log in to [YOURLS (Your Own URL Shortener)](https://yourls.org) using your SAML IdP.

Users authenticated by SAML have access to the whole admin interface, excluding the plugins manager, so you can use access rules at your IdP to control who can make short URLs.

Admins have access to everything including the plugins manager. To make a user an admin, add their details to the `$yourls_user_passwords` array in your normal YOURLS config. Set their username to be the same as their SAML Name ID, and pick something long and random for the password since they're not going to be using that anyway.

[Single logout is not implemented](https://github.com/wlabarron/yourls-saml/issues/1).

## Is this safe?
**Probably.**

It uses the popular [onelogin/php-saml](https://github.com/onelogin/php-saml) library and follows most of their example code.

However, I don't make any claims that this is a perfect implementation and 100% secure. Trust it as much as you'd trust any other random code on the internet, and maybe check through it to ensure you're happy.

In short, **use at your own risk** and know that there's no warranty or guarantees.

## Installation
Here's an overview. You'll have to modify to fit your environment.

1. [Download the latest release](https://github.com/wlabarron/yourls-saml/releases), decompress it, and copy the entire `yourls-saml` directory to your YOURLS plugin folder.
2. Run `composer install` in the `yourls-saml` directory.
3. Copy `settings_example.php` to `settings.php`. The file has comments inside which explains what each variable does. [OneLogin's documentation](https://developers.onelogin.com/saml/php) is also helpful.
    1. Set `$wlabarron_saml_yourls_base_url` to your YOURLS base URL (e.g. `https://sho.rt/`). Include the trailing slash.
    2. Check through the rest of the config and enter your IdP's details. Particular things to check include `$wlabarron_saml_settings['sp']['NameIDFormat']` and everything under `$wlabarron_saml_settings['idp']`. The SAML library is generally good about telling you what's not right when you try and use it, so if you have problems when you enable the plugin, check the PHP error log.
4. Create admin users by adding them to their `$yourls_user_passwords` array in your normal YOURLS config. Set their username to be the same as their SAML Name ID, and pick something long and random for the password since they're not going to be using that anyway.
5. Serve the contents of `yourls-saml/public` on `https://sho.rt/admin/auth`, where `https://sho.rt/` is your YOURLS domain. In nginx, this might look like:
   ``` nginx
    location /admin/auth {
        alias /var/www/yourls/user/plugins/yourls-saml/public;

        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $request_filename;
            fastcgi_param DOCUMENT_ROOT /var/www/yourls/user/plugins/yourls-saml/public;
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        }
    }
   ```
6. Enable the plugin in the YOURLS admin interface.