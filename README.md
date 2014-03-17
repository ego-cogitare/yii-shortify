# Yii shortify

This component will shorten a string to a shorter string. Can also expand the shorter string back to the full string.

### Requirements

 - php >= 5.2

### Usage

Install using composer ( https://packagist.org/packages/opus-online/yii-shortify )

### Setting up for Yii v1:

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