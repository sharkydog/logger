# logger
Simple static logger.

The main purpose of this package is to provide simple logging/debugging from any scope.
The logger can be easily filtered, adjusted or switched off.

Logging backends and message formatting are TBD and for now everything is printed on stdout.
That might change, but the public api will not without a major version bump.

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

Destruct logs when objects are being destroyed, in three ways.
- If `$object` is a string, it is assumed to be a class name and is just logged followed by the text in `$msg` argument.
- If `$object` is an object and PHP version is 8+, the object is added to a [WeakMap](https://www.php.net/manual/en/class.weakmap.php)
  and data is a Handle object which when destroyed, calls `destruct()` with the class name of the object being destructed.
- If `$object` is an object and PHP version is 7.4, the Handle is set as a dynamic property to the object,
  named `SharkyDog\Log\Handle(handle_id)`, where `handle_id` is the id of the Handle object as returned by `spl_object_id()`.
  Caution should be taken when the class of the tracked object implements `__set()`.

Memory level logs `memory_get_usage()` and `memory_get_peak_usage()`.
In the message:
```
[15:07:59.134][Memory]  495.39K (+ 495.39K), P:  558.25K, R:    2.00M (    0.00B), P:    2.00M (tags: tag1)
```
`P:` is the peak memory, `R:` is real memory (`memory_get_usage(true)`), `(+ 495.39K)` is the change from previous memory log.
If `$real` is `true`, only real usage is logged.

#### Adjust log level
Logging level is set with:
```php
public static function level(int $level);
```
Level are available through constants
- `Logger::SILENT`,`Logger::ERROR`,`Logger::WARNING`,`Logger::INFO`,
  `Logger::DEBUG`,`Logger::DESTRUCT`,`Logger::MEMORY`,`Logger::ALL`

---
To be continued...
