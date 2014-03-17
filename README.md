# Yii Loggly

This module is a log writer for [Yii](http://www.yiiframework.com/) that will send all log messages to a [Loggly](http://loggly.com/) input.

### Requirements

 - php >= 5.2
 - php5-curl extension
 - Yii 1.1.13 (should work on prior versions but not tested)

### Usage

Install using composer ( https://packagist.org/packages/opus-online/yii-shortify )

At the head of protected/config/main.php add:

Yii::setPathOfAlias('OpusOnline.Shortify', dirname(__FILE__) . '/../vendors/opus-online/yii-shortify');

Example usage:

```php
    $redis = new Predis\Client('tcp://localhost:6379');
    $redis->select(15);

    $shortify = new \OpusOnline\Shortify\Shortify($redis);

    $shorten = $shortify->shorten('route3');
    var_dump($shorten);
    $expand = $shortify->expand($shorten);
    var_dump($expand);
```