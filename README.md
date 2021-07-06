# Opencart to WooCommerce Migration



**Tested Opencart Version:** 1.5.x<br/>
**Tested WooCommerce Version:** 3.x.x

### Features

  - Main Categories
  - Sub Categories
  - Products
  - Product Photos
  - Product Tags
  
### Installation
##### Step 1
```sh
$ cd opencart-to-woocommerce-migration\wc-api-php
$ composer require automattic/woocommerce
```
##### Step 2
Wordpress Admin Panel -> WooCommerce -> Settings -> Advanced -> Rest API -> Create Key<br/>
**Description:** Opencart Migration<br/>
**User:** Admin User<br/>
**Permission:** Read/Write<br/>
Create key button click.

##### Step 3
Open the config.php file in the **opencart-to-woocommerce-migration** folder.
Edit the file.
```php
$wpUrl = 'https://www.wordpressurl.com';
$token = 'xxx';
$tokenSecret = 'xxx';
```

##### Step 5
Go to opencart folder **../image/data**<br/>
Replace **htaccess** file name with **.htaccessx**

##### Step 6
Files to be run respectively:
- main-category-install.php
- sub-category-install.php
- run.php **(press the buton on the page)**
