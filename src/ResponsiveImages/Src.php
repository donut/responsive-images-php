<?php

namespace ResponsiveImages;

class Src
{
  /**
   * @var string
   */
  private $url;
  public function getUrl(){ return $this->url; }

  /**
   * @var integer
   */
  private $width;
  public function getWidth(){ return $this->width; }

  /**
   * @var float
   */
  private $multiplier;
  public function getMultiplier(){ return $this->multiplier; }

  function __construct($url, $width=null, $multiplier=null)
  {
    $this->url        = $url;
    $this->width      = $width;
    $this->multiplier = $multiplier;
  }

  function __toString()
  {
    $src = $this->url;
    if (isset($this->width))
      $src = "$src {$this->width}w";
    else if (isset($this->multiplier))
      $src = "$src {$this->multiplier}x";

    return $src;
  }
}
