<?php
namespace SharkyDog\Log;

final class Handle {
  private $_id;
  private $_cb;

  public function __construct(callable $cb) {
    $this->_id = \spl_object_id($this);
    $this->_cb = $cb;
  }

  public function __destruct() {
    ($this->_cb)($this->_id);
  }

  public function id() {
    return $this->_id;
  }
}
