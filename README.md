# Thinbus SRP PHP Demo

Copyright (c) Simon Massey, 2015

Demo of Secure Remote Password (SRP-6a) protocol implementation of a browser authenticating to a PHP server using the [Thinbus](https://bitbucket.org/simon_massey/thinbus-srp-js) Javascript library. 
**Note** Please read the [Thinbus documentation page](https://bitbucket.org/simon_massey/thinbus-srp-js) before attempting to use this demo code. The demo code may or may not be running 
on the [demo server](http://thinbusphp-n00p.rhcloud.com/).

This work is based on [Ruslan Zazvacky's SRP PHP demo](https://github.com/RuslanZavacky/srp-6a-demo) and registers users into a SQLite database. 
It is very artificial as it only uses AJAX to confirm that authentication is successful. With a real application upon successful authentication the login page should load the main application page. 
That would unload the login page HTML containing the password and delete the Thinbus SRP JavaScript object that holds traces of the password as recommended on the [Thinbus page](https://bitbucket.org/simon_massey/thinbus-srp-js). 

The core PHP library files are in the `thinbus` folder:

* `thinbus/thinbus-srp-config.php` SRP configuration global variables. Must be included before the thinbus library code. Must match the values configured in the JavaScript. 
* `thinbus/thinbus-srp.php` PHP port of the Thinbus SRP6JavaClientSession based on code by [Ruslan Zavacky](https://github.com/RuslanZavacky/srp-6a-demo).
* `thinbus/BigInteger.php` pear.php.net [BigInteger math package](http://pear.php.net/package/BigInteger).
* `thinbus/srand.php` strong random numbers from [George Argyros](https://github.com/GeorgeArgyros/Secure-random-bytes-in-PHP) avoiding known buggy versions of random libraries. 

The core Thinbus JavaScript library files are in the `resources/thinbus` folder: 

* `thinbus/rfc5054-safe-prime-config.js` A sample configuration. See the main thinbus documentation for how to create your own safe prime. 
* `thinbus/thinbus-srp6a-sha256-versioned.js` The thinbus JS library which is tested in the Java project. See the header in that file which states the version. 

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

* Every users has a password verifier and a unique salt that you store in your database. 
* Evern login attempt first gets a unique challange and the user salt from the server then posts a one-time proof-of-passowrd. This means the server has to hold the thinbus object that generated the challenge long enough to verify the user one-time proof-of-password. 

The following diagram shows shows what you need to know: 

![Thinbus SRP Login Diagram](http://simon_massey.bitbucket.org/thinbus/login.png "Thinbus SRP Login Diagram")

The demo saves the use salt and verifier in an [SQLite](http://php.net/manual/en/book.sqlite.php) flat file database at `/tmp/srp_db.txt`. The location is configured in `require.php`. 
The demo application comprises of the following top level php demo files: 

* `require.php` a fragment to pull in the SRP constants, Thinbus library, RedBean library. It also initialises the SQLite database. 
* `rb.php` [RedBeanPHP](http://redbeanphp.com) "an easy-to-use, on-the-fly ORM for PHP" used to abstract the database solely for the convenience of the demo.   
* `register.php` accepts a POST with the user email, salt and verifier and saves them into the SQLite database. It is expected you have your own logic for registering users and you are going to modify that to save a salt and verifier for each user rather than use this code.
* `challenge.php` accepts a POST wih the user email, looks up the salt and verifier in the SQLite database, and uses Thinbus core library code to generate a one-time server challenge. It saves the Thinbus object in the SQLite database in an 'authentication' table so that it can look up everything needed to verify the client password proof based on the one-time challenge. You should consider rewriting this to use your own database access library code.  
* `login.php` verifies the user password proof. Note that the server needs to remember the one-time challenge that it gave the client to check the one-time password proof. It therefore looks up the object that was created to issue the one-time challenge in the SQLite database. You should consider rewriting this to use your own database access library code. If the password proof not correct the thinbus core library code will throw a PHP exception and the user authentication has failed. 

Once again it is expected that you have your own code for loading and saving user data to a real database and your own code, or framework code, for handling authorisation of 
which pages the authenticated users can or cannot access. Modifying the demo files to support your application may be harder than just modifying your current application to simply use the 
core Thinbus library at `thinbus\*.php` so that you can use your own favourite AJAX libraries and PHP database access library code.   

Please read the recommendations in the main thinbus documentation and take additional steps such as using HTTPS and encrypting the password verifier in the database which are not covered in this demo. 

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