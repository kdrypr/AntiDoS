# About
AntiDos is a open source PHP class having simply working method to block HTTP flood, DoS _(Denial Of Service)_ attacks. It records user's requests and blocks the attacker IP adress using Apache htaccess file by you've specified config.

#Installation
1. Upload the files to your web site via FTP _(to a desired location)_: **"AntiDos.php", "AntiDos_config.php", "AntiDos_cron.php"**

2. Create database _(or you may use existing database upon request)_ with utf-8 or latin charset. Import **"db.sql"**.

3. Open **"AntiDos_config.php"** file on any text editor and enter the connection informations to inside of quotes as in example:
  ```php
	'DB_SERVER' => 'localhost',
	'DB_NAME' => 'antidos',
	'DB_USER' => 'root',
	'DB_PASSWORD' => 'P@sword',
	'DB_PREFIX' => 'antidos_'
  ```
  Then do not forget update via FTP.
4. Open your site's wrapper php files _(usually it's only **index.php**)_. Add empty two lines to beginning of the file. After copy these codes there and save it:
  ```php
  <?php
  
  require_once('AntiDoS.php');
  $DOSFlood = new \AntiDoS;
  $DOSFlood->Process();
  
  ?>
  ```
  If **"AntiDos.php"** and the file aren't in the same directory:
  * For run it from a subdirectory: _subdirectory/AntiDoS.php_
  * From a parent folder:  _../AntiDoS.php_
  * From a specified path: _/home/public_html/AntiDoS/AntiDoS.php_

#Configuration
You may change many things like; ban time, maximum request limit, htaccess paths, keeping time of records in **"AntiDoS_config.php"** file.
* **RESET_REQUEST**: While request counts are calculated, this time interval prevails (as second).
* **MAX_REQUEST**: Maximum possible request count from an IP adress in the specified time interval.
* **BAN_TIME**: When too many requests was received from an IP adress, it is blocked during this time (as second).
* **KEEP_TIME**: Deleting time of old IP adress records from database. If it leaved as empty, old records aren't never delete.
* **HTACCESS_PATH**: Htaccess paths to manage banned IP adresses. If you leave it as empty, the htaccess file is used in same path with the wrapper file.
Also you may specify more than one paths as in example:
```php
	'HTACCESS_PATH' => array(
		'/home/site1/public_html/.htaccess',
		'subdirectory/.htaccess'),
```

#Warnings

* **This system only can work for HTTP GET/POST flood, DoS attacks. May not work on other DoS, DDoS attacks.**

* MySQL memory engine is used to fast access and recording as database system. If you want, you may change the database engine without need modify in php files.

* For the present, only IPv4 adresses are supported.

#Changelog
>**1.0.0.0 (2015-10-14)**
>***
>The first version has been released.

#Supports - Feedbacks

You may contribute and develop the project via Github, from our e-mail and from our volunteer programming community. English and Turkish feedbacks are accepted.

* _E-mail:_ **titan[at]kodevreni.com**

* _Website:_ [http://www.kodevreni.com/](http://www.kodevreni.com/)


#License
This project is open source, so protected with MIT License. You may use, copy, merge, publish, modify, sell copies of the software, distribute and sublicense it with attribution to developers condition.

> The MIT License (MIT)

> Copyright (c) 2015 Kod Evreni

> Permission is hereby granted, free of charge, to any person obtaining a copy
> of this software and associated documentation files (the "Software"), to deal
> in the Software without restriction, including without limitation the rights
> to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
> copies of the Software, and to permit persons to whom the Software is
> furnished to do so, subject to the following conditions:

> The above copyright notice and this permission notice shall be included in all
> copies or substantial portions of the Software.

> THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
> IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
> FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
> AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
> LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
> OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
> SOFTWARE.
