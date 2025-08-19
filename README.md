# Thinbus SRP PHP Demo

Copyright (c) Simon Massey, 2015-2017

This is a demo of the [Thinbus SRP PHP](https://packagist.org/packages/simon_massey/thinbus-php-srp) implementation of the SRP-6a Secure Remote Password  protocol. It demonstrates generating a password verifier at the browser which is saved at the server to use to authenticate the user. 

**Note** Please read the [Thinbus documentation page](https://bitbucket.org/simon_massey/thinbus-srp-js) before attempting to use this demo code which is the project where the JavaScript came from which outlines some best practices. 

## Security Improvement

**Important Security Fix**: This demo has been updated to address a security vulnerability (Issue #1) where `serialize()` and `unserialize()` functions were used to store SRP authentication state. These functions can lead to code execution vulnerabilities when used with potentially untrusted data.

**Changes Made**:
- Replaced database storage with `serialize()/unserialize()` with secure session-based storage
- SRP objects are now stored in `$_SESSION` instead of being serialized to the database
- Added session timeout protection (5 minutes)
- Automatic session cleanup after successful authentication

This approach eliminates the security risk while maintaining the same functionality. Session storage is more appropriate for temporary authentication state and follows security best practices. 

## Trying The Demo

This demo lets you test the library with your PHP version and use browser developer tools to inspect the AJAX traffic. Be sure to test locally on your workstation with the exact same version of php on your server before trying to deploy on your server!

```sh
# use the PHP composer to pull down the thinbus libraries: see package.json for how the version of thinbus PHP is specified
# see https://getcomposer.org/doc/01-basic-usage.md
composer update
# run all the unit tests to be sure that you have installed a version of the library that works with your PHP intall
./vendor/phpunit/phpunit/phpunit ./vendor/simon_massey/thinbus-php-srp/test/ThinbusTest.php
# Run this in the same folder as the top level demo files
php -S localhost:8000
# now open the demo in your browser which is running at http://localhost:8000
```

Note the [built in webserver](http://php.net/manual/en/features.commandline.webserver.php) is very slow compared to the way that a webserver runs PHP. See the recommenations below. 

## The Demo Application

This work is based on [Ruslan Zazvacky's SRP PHP demo](https://github.com/RuslanZavacky/srp-6a-demo) and registers users into a SQLite database. You shouldn't be using a SQLite database you should use the main database of your main application (and possibly a distributed cache to store the temporary state created during authentication if you don't want to put that into your main database).
This demo is very artificial as it only uses AJAX to confirm that authentication is successful. With a real application after authentication the browser should load a main application page in a traditional (not AJAX) way to force the cleanup of any traces of the password as recommended on the [Thinbus page](https://bitbucket.org/simon_massey/thinbus-srp-js). The demo also doesn't cover authorisation (which uses can see what, which pages are public, which pages are not). 

The demo saves the use salt and verifier in an [SQLite](http://php.net/manual/en/book.sqlite.php) flat file database at `/tmp/srp_db.txt`. The location is configured in `require.php` which also shows where composer installs the Thinbus PHP library code. The demo application comprises of the following top level php demo files that you probably *don't* want to use in your own application: 

* `require.php` a fragment to pull in the SRP constants, Thinbus library, RedBean library. It also initialises the SQLite database. 
* `rb.php` [RedBeanPHP](http://redbeanphp.com) "an easy-to-use, on-the-fly ORM for PHP" used to abstract the database solely for the convenience of this demo. You are not expected to use this library code in your own application.   
* `register.php` accepts a POST with the user email, salt and verifier and saves them into the SQLite database. It is expected you have your own logic for saving to an industrial database rather than use this code.
* `challenge.php` accepts a POST with the user email, looks up the salt and verifier in the SQLite database, and uses Thinbus core library code to generate a one-time server challenge. It saves the Thinbus object in the SQLite database in an 'authentication' table so that it can look up everything needed to verify the client password proof made by the browser using the one-time challenge. It is expected that you don't use my SQLLite demo code but supply your own code to save things in your main database. 
* `login.php` verifies the user password proof. Note that the server needs to remember the one-time challenge that it gave the client to check the one-time password proof. It therefore looks up the object that created the one-time challenge in the SQLite database. It is expected that you don't use my SQLLite demo code but supply your own code to load things in your main database. The logic then uses the core Thinbus library code to check the password proof and will throw a PHP exception if authentication fails. 

It is expected that you create your own code for loading and saving data to a real database. Do not use my SQLLite or RedBean code. Only use the PHP files installed under the `vendor` folder when you run `composer update`. It is expected that you use your own code for handling authorisation of which pages users can or cannot access. Trying to modifying the demo files to support your application may be far harder than just modifying your current application to simply use the core Thinbus library at `thinbus\*.php`. 

Please read the recommendations in the [main thinbus documentation](https://bitbucket.org/simon_massey/thinbus-srp-js) and take additional steps such as using HTTPS and encrypting the password verifier in the database which are not shown in this demo. 

### Big Thanks

Cross-browser Testing Platform and Open Source <3 Provided by [Sauce Labs][homepage]

Using Sauce Labs the demo app code [thinbus-php-srp-demo](https://packagist.org/packages/simon_massey/thinbus-php-srp-demo) has been tested to work on:

 * Android 6.0 
 * Android 5.1
 * Android 5.0
 * Android 4.4
 * iOS 11.0
 * iOS 8.1
 * Microsoft Edge 15
 * Microsoft Edge 13
 * Microsoft Explorer 11 (note all previous versions were end of life Jan 2016)
 * Chrome 63
 * Chrome 26
 * Firefox 57
 * Firefox 4 (released March 22, 2011!)
 * Safari 11 
 * Safari 7 

## Troubleshooting

If you are having problems first check that the PHP unit code runs locally on your worksation using the exact same version of PHP as you run on your server: 

```sh
composer update
# run the Thinbus unit tests which tests the cryptography
./vendor/phpunit/phpunit/phpunit --verbose ./vendor/simon_massey/thinbus-php-srp/test/ThinbusTest.php
```

If all test pass should output a final line such as `OK (xx tests, yyy assertions)`. If not raise an issue with the exact PHP version and the output of `phpinfo();`

Next run the local [built in webserver](http://php.net/manual/en/features.commandline.webserver.php) on your workstation and try to register then authenticate (note that successfuli authentication only writes "Success" in a green banner on the login page): 

```sh
# Run this in the same folder as the top level demo files 
php -S localhost:8000
```

If that doesn't work try a couple of different browsers (chrome, firefox, edge/safari) to check if it is a browser JavaScript compatibility problem. Use your favourite browsers developer view to look at the network traffic to see if it corresponds to the diagrams above. Supply the details of the browser traffic in any issue that you raise. 
Then raise an issue naming all the browser versions you tested with and the results. 
Bonus marks for using the browser developer tools to capture the network traffic (particularly the AJAX calls) to see if the server put any error messages 
into the traffic which may break the demo code. Further bonus marks for including any browser JS console output and any PHP scripting engine logs which may 
indicate what the problem might be.  

If you got this far and couldn't find any problems with the demo code running locally then next try deploying the demo code to your main webserver. 
Your webserver might not have permission to write to `/tmp/srp_db.txt` see the documentation above about where that is hardcoded in the demo to change it. 
If the demo works locally but doesnt work on your main server then I suggest you use the browser developer tools to compare the network traffic 
(particularly the AJAX calls) that happen when running locally verses running on your main server to see if the server traffic indicates a server configuration problem. Typically the AJAX resonpse from the server pukes up a warning message that the browser cannot parse. Try to fix the PHP warning message before raising an issue as its normally something missing from a custom PHP install. 

If the PHP code is running very slow on your server then its likely that your server is missing some native libraries that PHP normally uses. When this is the case the PHP engine runs much slower than the webbrowser. This is not something that I can debug for you so please don't raise an issue. Instead ask a question on stackoverflow or another stackexchange site about your OS and PHP version asking whether there are "known issues" of code running slowly that can be fixed by changing settings or installing native libraries. (The typical one is to install openssl and the php library which uses it.)

## License

```
   Copyright 2015-2017 Simon Massey

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
```
   
End.
