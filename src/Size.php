<?php

namespace ResponsiveImages;

use Functional as F;


class Size
{
  /**
   * @var string|null
   */
  private $media_query;
  public function getMediaQuery(){ return $this->media_query; }
  /**
   * @var integer|null
   */
  private $viewport_width;

  /**
   * @var integer
   */
  private $min_width;
  public function getMinWidth(){ return $this->min_width; }
  /**
   * @var integer
   */
  private $max_width;
  public function getMaxWidth(){ return $this->max_width; }

  /**
   * @var float
   */
  private $aspect_ratio;
  public function getAspectRatio(){ return $this->aspect_ratio; }

  /**
   * @var float
   * How close to the aspect ratio a set of dimensions must be to be used for
   * this size. This positive float will be subtracted and added to
   * $aspect_ratio to find the minimum and maximum aspect ratio.
   */
  private $aspect_ratio_tolerance;

  function __construct($min_width, $aspect_ratio, array $options)
  {
    $this->min_width = $min_width;
    $this->max_width = isset($options['max_width'])
                     ? $options['max_width'] : $this->min_width;

    $this->aspect_ratio = $aspect_ratio;
    $this->aspect_ratio_tolerance = isset($options['aspect_ratio_tolerance'])
                                  ? $options['aspect_ratio_tolerance'] : 0;

    $this->media_query    = isset($options['media_query'])
                          ? $options['media_query'] : null;
    $this->viewport_width = isset($options['viewport_width'])
                          ? $options['viewport_width'] : null;
  }


  public function matchesAspectRatio($aspect_ratio)
  {
    $min = $this->aspect_ratio - $this->aspect_ratio_tolerance;
    $max = $this->aspect_ratio + $this->aspect_ratio_tolerance;

    return ($min <= $aspect_ratio and $aspect_ratio <= $max);
  }

  public function renderWidthOnly()
  {
    return (isset($this->viewport_width))
         ? "{$this->viewport_width}vw" : "{$this->min_width}px";
  }

  public function __toString()
  {
    $size = '';
    if (isset($this->media_query))
      $size .= "$this->media_query ";

    return $size . $this->renderWidthOnly();
  }
}
