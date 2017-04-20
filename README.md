# Thinbus SRP PHP

Copyright (c) Simon Massey, 2015-2017

Thinbus SRP PHP is an implementation of the SRP-6a Secure Remote Password  protocol. It is compatible with [Thinbus](https://bitbucket.org/simon_massey/thinbus-srp-js) a JavaScript SRP implementation. This allows you to generate a verifier for a temporary password in PHP and have users login to a PHP server using a browser.  

This repository also includes a slightly contrived demo of a browser running the [Thinbus Javascript library](https://bitbucket.org/simon_massey/thinbus-srp-js) authenticating to a PHP server. 

**Note** Please read the [Thinbus documentation page](https://bitbucket.org/simon_massey/thinbus-srp-js) before attempting to use this demo code. 

## Trying The Demo

This demo code may or may not be running on a redhat [demo server](http://thinbusphp-n00p.rhcloud.com/). 
If not then PHP installs come with a [built in webserver](http://php.net/manual/en/features.commandline.webserver.php) for local testing purposes:  

```sh
# Run this in the same folder as the top level demo files
php -S localhost:8000
```

This lets you try this demo with your PHP version and use browser developer tools to inspect the AJAX traffic. Note the built in webserver is very slow compared to a real PHP server install. 

## Using In Your Application

This work is based on [Ruslan Zazvacky's SRP PHP demo](https://github.com/RuslanZavacky/srp-6a-demo) and registers users into a SQLite database. 
It is very artificial as it only uses AJAX to confirm that authentication is successful. With a real application post authentication the browser should load a main application 
page in a traditional (not AJAX) way to force the cleanup of any traces of the password as recommended on the [Thinbus page](https://bitbucket.org/simon_massey/thinbus-srp-js). 

The core PHP library files are in the `thinbus` folder:

* `thinbus/thinbus-srp-config.php` SRP configuration global variables. Must be included before the thinbus library code. Must match the values configured in the JavaScript. 
* `thinbus/thinbus-srp.php` PHP port of the Thinbus Java SRP server code based on code by [Ruslan Zavacky](https://github.com/RuslanZavacky/srp-6a-demo).
* `thinbus/thinbus-srp-client.php` PHP SRP client code contributed by Keith Wagner.
* `thinbus/thinbus-srp-common.php` common functions used by the client and server. 
* `thinbus/BigInteger.php` pear.php.net [BigInteger math package](http://pear.php.net/package/BigInteger).
* `thinbus/srand.php` strong random numbers from [George Argyros](https://github.com/GeorgeArgyros/Secure-random-bytes-in-PHP) avoiding known buggy versions of random libraries.
* `thinbus/thinbus-srp-client.php` PHP client code contributed by Keith Wagner.     

The core Thinbus JavaScript library files are in the `resources/thinbus` folder: 

* `thinbus/rfc5054-safe-prime-config.js` A sample configuration. See the main thinbus documentation for how to create your own safe prime. 
* `thinbus/thinbus-srp6a-sha256-versioned.js` The thinbus JS library which is tested in the [main project](https://bitbucket.org/simon_massey/thinbus-srp-js). See the header in that file which states the version. 

The file `thinbus-srp-config.php` contains the SRP constants which looks something like: 

```
$SRP6CryptoParams = [
    "N_base10" => "19502997308..."
    "g_base10" => "2",
    "k_base16" => "1a3d1769e1d..."
    "H" => "sha256"
];
```

The numeric constants must match the values configured in the JavaScript; see the [Thinbus documentation](https://bitbucket.org/simon_massey/thinbus-srp-js). 
Consider creating your own large safe prime values using openssl using the Thinbus instructions. 

The files named above are Thinbus library code and are supported. The rest of the demo application is purely for demonstration purposes only and not 
intended to be deployed into production. The idea is that you have your own user management and user authorisation logic and you simply want to 
swap out plain text password authentication with SRP authentication. The Thinbus core library provides the cryptography and you will supply your own 
HTML, AJAX and database access logic. SRP is independent of those such things and they will be specific to your application. All you need to understand 
is that:

* Every users has a password verifier and a unique salt that you store in your database. This implementation uses the [RFC2945](https://www.ietf.org/rfc/rfc2945.txt) approach of hashing the username into the password verifier. This means that if your application lets a user change their username then they will be locked out unless you generate and store a fresh password verifier.  
* At every login attempt the browser first makes an AJAX call to get a one-time random challenge and the user salt from the server. The browser then uses that to compute a one-time proof-of-password and then immediately posts the proof-of-password to the server. The server checks the proof-of-password using both the client verifier and the one-time challenge. This means the server has to hold the thinbus object that generated the challenge only long enough to verify the corresponding proof-of-password. 

The following diagram shows what you need to know: 

![Thinbus SRP Login Diagram](http://simonmassey.bitbucket.io/thinbus/login.png "Thinbus SRP Login Diagram")

The demo saves the use salt and verifier in an [SQLite](http://php.net/manual/en/book.sqlite.php) flat file database at `/tmp/srp_db.txt`. The location is configured in `require.php`. 
The demo application comprises of the following top level php demo files that you probably *don't* want to use in your own application: 

* `require.php` a fragment to pull in the SRP constants, Thinbus library, RedBean library. It also initialises the SQLite database. 
* `rb.php` [RedBeanPHP](http://redbeanphp.com) "an easy-to-use, on-the-fly ORM for PHP" used to abstract the database solely for the convenience of this demo. You are not expected to use this library code in your own application.   
* `register.php` accepts a POST with the user email, salt and verifier and saves them into the SQLite database. It is expected you have your own logic for registering users and you are going to modify that to save a salt and verifier for each user rather than use this code.
* `challenge.php` accepts a POST with the user email, looks up the salt and verifier in the SQLite database, and uses Thinbus core library code to generate a one-time server challenge. It saves the Thinbus object in the SQLite database in an 'authentication' table so that it can look up everything needed to verify the client password proof based on the one-time challenge. It is expected that you modify this code to use your own database access library code.  
* `login.php` verifies the user password proof. Note that the server needs to remember the one-time challenge that it gave the client to check the one-time password proof. It therefore looks up the object that created the one-time challenge in the SQLite database. It is expected that you modify this code to use your own database access library code. This logic uses the core Thinbus library code to check the password proof which will throw a PHP exception if authentication fails. 

It is expected that you create your own code for loading and saving data to a real database. It is expected that you use your own code for handling authorisation of 
which pages users can or cannot access. Modifying the demo files to support your application may be harder than just modifying your current application to simply use the 
core Thinbus library at `thinbus\*.php`. 

Please read the recommendations in the [main thinbus documentation](https://bitbucket.org/simon_massey/thinbus-srp-js) and take additional steps such as using HTTPS and encrypting the password verifier in the database which are not shown in this demo. 

*Note:* It is recommended that you install the PHP [Open SSL extention](http://php.net/manual/en/book.openssl.php) which the random number generator in `srand.php` prefers to use. If it cannot find that extension then it's second choice is the PHP [Mcrypt Extension](http://php.net/manual/en/book.mcrypt.php). If it cannot find that it else if it is running on Windows it uses its own random number generating approach. 

## Troubleshooting

If you are having problems first check that the demo code runs locally on your worksation using the exact same version of PHP as you run on your server: 

```sh
# download the php phar if you don't have it installed globally and check it can print out its version
wget https://phar.phpunit.de/phpunit.phar
php phpunit.phar --version
# run the Thinbus unit tests which tests the cryptography
php phpunit.phar ThibusTest.php
```

If all test pass should output a final line such as `OK (5 tests, 204 assertions)`. If not raise an issue with the exact PHP version and the output of `phpinfo();`

Next run the local [built in webserver](http://php.net/manual/en/features.commandline.webserver.php) on your workstation and try to register then sign-in: 

```sh
# Run this in the same folder as the top level demo files 
php -S localhost:8000
```

If that doesn't work try a couple of different browsers (chrome, firefox, edge/safari) to check if it is a browser JavaScript compatibility problem. 
Then raise an issue naming all the browser versions you tested with and the results. 
Bonus marks for using the browser developer tools to capture the network traffic (particularly the AJAX calls) to see if the server put any error messages 
into the traffic which may break the demo code. Further bonus marks for including any browser JS console output and any PHP scripting engine logs which may 
indicate what the problem might be.  

If you got this far and couldn't find any problems with the demo code running locally then next try deploying the demo code to your main webserver. 
Your webserver might not have permission to write to `/tmp/srp_db.txt` see the documentation above about where that is hardcoded in the demo to change it. 
If the demo works locally but doesnt work on your main server then I suggest you use the browser developer tools to compare the network traffic 
(particularly the AJAX calls) that happen when running locally verses running on your main server to see if the server traffic indicates a server configuration problem. 

If you got this far and could not find any problems running the demo code locally nor on your server then I assume you are having problems using the thinbus library code in your own application. Deploy the demo app onto the same server as your own app and use browser developer tools to inspect the network traffic (particularly the AJAX calls)
to compare what the traffic looks like between the working demo app and your own app. If the browser traffic seems okay double check that all the necessary config and database data is being both saved and loaded correctly.

## License

```
   Copyright 2015 Simon Massey

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