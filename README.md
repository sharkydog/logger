# logger
Simple static logger.

The main purpose of this package is to provide simple logging/debugging from any scope.
The logger can be easily filtered, adjusted or switched off.

Logging backends and message formatting are TBD and for now everything is printed on stdout.
That might change, but the public api will not whithout a major version bump.

Currently messages look like this:
```
[15:07:59.134][Warning] warn (tags: tag1,tag2)
[15:07:59.134][Destruct] SharkyDog\Log\Handle: bye bye handle (tags: tag)
[15:07:59.134][Memory]  495.39K (+ 495.39K), P:  558.25K, R:    2.00M (    0.00B), P:    2.00M (tags: tag1)
```

### Usage
Messages are sent to the logger through several static methods that represent different logging levels.
These are (low to high): `error`, `warning`, `info`, `debug`, `destruct`, `memory`.

First four have the same signature:
```php
public static function error(string $msg, string ...$tags);
```
Prefix (time,level) and suffix (tags) are added to the message after it is filtered and before being printed.
Tags are arbitrary strings that can be used to filter out (or in) messages.

Destruct and memory levels are special, they are higher than debug (think verbose) and have different signatures:
```php
public static function destruct($object, string $msg='', string ...$tags);
public static function memory(bool $real=false, string ...$tags);
```

---
To be continued...
