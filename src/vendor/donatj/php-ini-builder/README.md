# PHP INI Builder

[![Latest Stable Version](https://poser.pugx.org/donatj/php-ini-builder/v/stable.png)](https://packagist.org/packages/donatj/php-ini-builder)
[![License](https://poser.pugx.org/donatj/php-ini-builder/license.png)](https://packagist.org/packages/donatj/php-ini-builder)
[![Build Status](https://travis-ci.org/donatj/PhpIniBuilder.svg?branch=master)](https://travis-ci.org/donatj/PhpIniBuilder)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/donatj/PhpIniBuilder/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/donatj/PhpIniBuilder/?branch=master)

Create PHP `parse_ini_string` / `parse_ini_file` compatible INI strings from associative arrays.

## Requirements

- PHP 5.3+

## Installing

PHP INI Builder is available through Packagist via Composer

```json
{
    "require": {
        "donatj/php-ini-builder": "~0.1.3"
    }
}
```

## Example

Here is a simple example script:

```php
<?php

require(__DIR__ . '/../vendor/autoload.php');

$data = array(
	'Title' => array(
		'str' => 'awesome',
		'int' => 7,
		'flt' => 10.2,
	),
	'Title 2' => array(
		'bool' => true,
		'arr' => array(
			'a', 'b', 'c', 6 => 'd', 'e', 'key' => 'f'
		)
	)
);

$x = new \donatj\Ini\Builder();
echo $x->generate($data);
```

Which outputs:

```php
[Title]
str = 'awesome'
int = 7
flt = 10.2

[Title 2]
bool = true
arr[] = 'a'
arr[] = 'b'
arr[] = 'c'
arr[6] = 'd'
arr[] = 'e'
arr[key] = 'f'

```

## Documentation

### Class: Builder \[ `\donatj\Ini` \]

Utility for Converting An Array to a INI string

#### Method: `Builder->__construct([ $enableBool = true [, $enableNumeric = true [, $enableAlphaNumeric = true [, $skipNullValues = false]]]])`

##### Parameters:

- ***bool*** `$enableBool`
- ***bool*** `$enableNumeric`
- ***bool*** `$enableAlphaNumeric`
- ***bool*** `$skipNullValues`



---

#### Method: `Builder->generate($data)`

INI String Result  
  


##### Parameters:

- ***array*** `$data`


##### Returns:

- ***string***


---

#### Method: `Builder->__invoke($data)`

##### Parameters:

- ***array*** `$data`


##### Returns:

- ***string***


---

#### Method: `Builder->escape($value)`

Escapes Values According to Currently Set Rules  
  


##### Parameters:

- ***mixed*** `$value`


##### Returns:

- ***string***


---

#### Method: `Builder->enableBoolDetection($enableBool)`

Enable / Disable Automatic Boolean Detection  
PHP's built in `parse_ini_*` methods parse `1`, `'1'` and `true` and likewise `''`, and `false` to the same values  
when the scanner mode is set to `INI_SCANNER_NORMAL`, enabling this option causes these values to be output  
as `true` / `false`  


##### Parameters:

- ***bool*** `$enableBool`



---

#### Method: `Builder->enableNumericDetection($enableNumeric)`

Enable / Disable Automatic Numeric Detection  
PHP's built in `parse_ini_*` methods parse all values to string. Enabling this option enables numeric detection  
so they will be output once again as floats/ints  


##### Parameters:

- ***boolean*** `$enableNumeric`



---

#### Method: `Builder->enableAlphaNumericDetection($enableAlphaNumeric)`

Enable / Disable Automatic AlphaNumeric Detection  
PHP's built in `parse_ini_*` methods does not require quotation marks around simple strings without spaces. Enabling  
this option removes the quotation marks on said simple strings.  


##### Parameters:

- ***boolean*** `$enableAlphaNumeric`



---

#### Method: `Builder->enableSkipNullValues($skipNullValues)`

Enable / Disable Skipping Null Values  
When enabled, null values will be skipped.  


##### Parameters:

- ***boolean*** `$skipNullValues`

