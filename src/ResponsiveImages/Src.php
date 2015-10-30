<?php

namespace ResponsiveImages;


/**
 * Defines an image source to be used in the `srcset` attribute.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img#attr-srcset
 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/source#attr-srcset
 *
 * @package ResponsiveImages
 */
class Src
{
  /**
   * The URL pointing to the image.
   *
   * @var string
   */
  private $url;
  public function getUrl(){ return $this->url; }

  /**
   * The width of the image in pixels.
   *
   * @var integer
   */
  private $width;
  public function getWidth(){ return $this->width; }

  /**
   * Pixel density descriptor. How many times the pixel density of this image
   * is of the 1:1 density version.
   *
   * @var float
   */
  private $multiplier;
  public function getMultiplier(){ return $this->multiplier; }

  /**
   * Src constructor.
   *
   * @param string     $url
   *   The URL of the image.
   * @param int|null   $width
   *   The width of the image in pixels.
   * @param float|null $multiplier
   *   The pixel density descriptor as defined for the `srcset` attribute.
   *   Used only if $width is null.
   */
  function __construct($url, $width=null, $multiplier=null)
  {
    $this->url        = $url;
    $this->width      = $width;
    $this->multiplier = $multiplier;
  }

  /**
   * Renders the srcset source definition.
   *
   * @return string
   *   The srcset source value.
   */
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
