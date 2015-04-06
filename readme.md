yii2-dhtmlx-gii-generators
=====================

This generator generates the grid on a given url with the help of DHTMLX Grid.

## Installation

Add to components if config the following
```php
    'dhtmlx-js'  => '/path/to/dhtmlx-js-file',
    'dhtmlx-css' => '/path/to/dhtmlx-css-file'
```
Make sure you have dhtmlx.css and dhtmlx.js in the head of your pages. Also make sure you have dhtmlx folder in your web folder with all pictures in codebase folder
so that /dhtmlx/codebase/imgs should be available.

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ php composer.phar require "dhtmlx/dhtmlx-gii": "dev-master"
```

or add

```
"dhtmlx/dhtmlx-gii": "dev-master"
```

to the ```require``` section of your `composer.json` file.

## Usage

```php
//if your gii modules configuration looks like below:
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';

//remove this two lines
```

```php
//Add this into common/config/web.php
    
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
            'class' => 'yii\gii\Module',
            'generators' => [
                'dhtmlx-one-table' => [
                    'class' => 'DHTMLX\Gii\SingleTable\Generator',
                ],
                'dhtmlx-many-tables' => [
                                    'class' => 'DHTMLX\Gii\ManyTables\Generator',
                                ],
            ],
        ];
```