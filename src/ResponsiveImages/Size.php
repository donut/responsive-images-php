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
   * How close to the aspect ratio a set of dimensions must be to be used for
   * this size. This positive float will be subtracted and added to
   * $aspect_ratio to find the minimum and maximum aspect ratio.
   *
   * @var float
   */
  private $aspect_ratio_tolerance;

  /**
   * Size constructor.
   *
   * @param integer[]   $range
   * @param float       $aspect_ratio
   * @param string|null $query
   * @param float|null  $vw
   * @param float|null  $ar_tolerance
   */
  function __construct(array $range, $aspect_ratio, $query=null, $vw=null
                      ,$ar_tolerance=0.0)
  {
    $this->min_width = $range[0];
    $this->max_width = isset($range[1]) ? $range[1] : $this->min_width;

    $this->media_query    = $query;
    $this->viewport_width = $vw;

    $this->aspect_ratio = $aspect_ratio;
    $this->aspect_ratio_tolerance = $ar_tolerance;
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
