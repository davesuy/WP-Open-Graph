<?php


class PostDecorator {


  public $post;


  public function __construct($post = null) {
    $this->post = is_null($post) ? $GLOBALS['post'] : $post;
  }


  public function __get($key) {
    if(!isset($this->post->$key)) {
      return null;
    }

    return $this->post->$key;
  }
}
