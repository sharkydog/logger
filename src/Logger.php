<?php
namespace SharkyDog\Log;

class Logger {
  const SILENT = 0;
  const ERROR = 1;
  const WARNING = 2;
  const INFO = 3;
  const DEBUG = 4;
  const DESTRUCT = 5;
  const MEMORY = 6;
  const ALL = 99;

  private static $_level = 2;
  private static $_filters = [];
  private static $_filter_idx = 0;
  private static $_destruct_handles;

  public static function level(int $level) {
    self::$_level = $level;
  }

  public static function filter(int $level, callable $filter) {
    if(!isset(self::$_filters[$level])) self::$_filters[$level] = [];
    self::$_filters[$level][++self::$_filter_idx] = $filter;
  }

  public static function error(string $msg, string ...$tags) {
    if(self::$_level < self::ERROR) return;
    self::_log(self::ERROR, 'error', $msg, $tags);
  }

  public static function warning(string $msg, string ...$tags) {
    if(self::$_level < self::WARNING) return;
    self::_log(self::WARNING, 'warning', $msg, $tags);
  }

  public static function info(string $msg, string ...$tags) {
    if(self::$_level < self::INFO) return;
    self::_log(self::INFO, 'info', $msg, $tags);
  }

  public static function debug(string $msg, string ...$tags) {
    if(self::$_level < self::DEBUG) return;
    self::_log(self::DEBUG, 'debug', $msg, $tags);
  }

  public static function destruct($object, string $msg='', string ...$tags) {
    if(self::$_level < self::DESTRUCT) return;

    if(is_string($object)) {
      self::_log(self::DESTRUCT, 'destruct', $object.($msg ? ": ".$msg : ""), $tags);
      return;
    }
    if(!is_object($object)) {
      return;
    }

    $class = get_class($object);
    $handle = new Handle(function() use($class,$msg,$tags) {
      self::destruct($class, $msg, ...$tags);
    });

    if(PHP_MAJOR_VERSION >= 8) {
      if(!self::$_destruct_handles) {
        self::$_destruct_handles = new \WeakMap;
      }
      self::$_destruct_handles[$object] = $handle;
    } else {
      $prop = get_class($handle).'('.$handle->id().')';
      $object->$prop = $handle;
    }
  }

  public static function memory(bool $real=false, string ...$tags) {
    if(self::$_level < self::MEMORY) return;
    $msg = (!$real ? self::_memory(false).', ' : '').self::_memory(true);
    self::_log(self::MEMORY, 'memory', $msg, $tags);
  }

  public static function bytesHR(int $bytes, bool $short=false, bool $pad=false): string {
    static $units = ['B','KiB','MiB','GiB','TiB','PiB'];
    static $log1024;
    if(!$log1024) $log1024 = log(1024);

    $bytes = ceil($bytes);

    if($bytes <= 0) {
      $pow = 0;
      $num = 0;
    } else {
      $pow = floor(log($bytes)/$log1024);
      $num = round($bytes/pow(1024,$pow),2);
    }

    $str = number_format($num,2,'.','');
    if($pad) $str = str_pad($str, 7, ' ', STR_PAD_LEFT);
    $str = $str.($short ? $units[$pow][0] : ' '.$units[$pow]);
    if($pad) $str = str_pad($str, $short ? 8 : 11, ' ', STR_PAD_RIGHT);

    return $str;
  }

  private static function _memory($real) {
    static $prev = 0;
    static $prev_real = 0;

    $curr = memory_get_usage($real);
    $peak = memory_get_peak_usage($real);

    $diff = $curr - ($real ? $prev_real : $prev);

    $msg  = ($real?'R: ':'').self::bytesHR($curr,true,true);
    $msg .= ' (';
    $msg .= (!$diff?' ':($diff<0?'-':'+'));
    $msg .= self::bytesHR(abs($diff),true,true);
    $msg .= ')';

    $msg .= ', P: '.self::bytesHR($peak,true,true);

    if($real) $prev_real = $curr;
    else $prev = $curr;

    return $msg;
  }

  private static function _log($level,$lvltxt,$msg,$tags) {
    $filters = [];

    if(isset(self::$_filters[$level])) {
      $filters += self::$_filters[$level];
    }
    if(isset(self::$_filters[self::ALL])) {
      $filters += self::$_filters[self::ALL];
    }

    if(!empty($filters)) {
      ksort($filters);
      foreach($filters as $filter) {
        if($filter($msg,$tags,$level) === false) {
          return;
        }
      }
    }

    $dt = (new \DateTime())->format('H:i:s.v');
    $hdr = "[".$dt."][".ucfirst($lvltxt)."] ";
    $msg = $hdr.$msg;

    if(!empty($tags)) {
      //$msg .= "\n".$hdr;
      $msg .= " (tags: ".implode(",",$tags).")";
    }

    print $msg."\n";
  }
}
