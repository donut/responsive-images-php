<?php

namespace ResponsiveImages;


use Functional as F;


/**
 * Represents a condition of a `sizes` attribute along with the necessary image
 * dimensions to be matched with the condition.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img#attr-sizes
 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/source#attr-sizes
 *
 * @package ResponsiveImages
 */
class Size
{
  /**
   * The media query condition including parentheses.
   *
   * @var string|null
   */
  private $media_query;
  public function getMediaQuery(){ return $this->media_query; }

  /**
   * The viewport width for this size that will be used in the `sizes`
   * attribute value. Expressed in `vw` units.
   *
   * @see https://developer.mozilla.org/en-US/docs/Web/CSS/length#Viewport-percentage_lengths
   *
   * @var float|null
   */
  private $viewport_width;

  /**
   * The minimum width the image will take when the condition of this size is
   * met. Expressed in pixels at a 1:1 DPI.
   *
   * @var integer
   */
  private $min_width;
  public function getMinWidth(){ return $this->min_width; }

  /**
   * The maximum width the image will take when the condition of this size is
   * met. Expressed in pixels at a 1:1 DPI.
   *
   * @var integer
   */
  private $max_width;
  public function getMaxWidth(){ return $this->max_width; }

  /**
   * The aspect ratio of the image.
   *
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
   *   The range of widths the image can take specified either as
   *   `[$min_width]` or `[$min_width, $max_width]`. If only $min_width
   *   is specified, it will be used for the $max_width value as well.
   * @param float       $aspect_ratio
   *   The aspect ratio. Looks best if you pass it as a fraction, such as 16/9.
   * @param string|null $query
   *   The media query to be used in the `sizes` attribute value. If null, no
   *   condition is set.
   * @param float|null  $vw
   *   The viewport width to be used in the `sizes` attribute value, specified
   *   in `vw` units. If null, $min_width will be used with with `px` units.
   * @param float       $ar_tolerance
   *   The aspect ratio tolerance. Used to define the minimum and maximum
   *   acceptable aspect ratios.
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


  /**
   * Determines whether or not the passed aspect ratio is close enough to
   * $this->aspect_ratio based on $this->aspect_ratio_tolerance.
   *
   * @param float $aspect_ratio
   *   The aspect ratio to test.
   *
   * @return bool
   *   true if it matches, false otherwise.
   */
  public function matchesAspectRatio($aspect_ratio)
  {
    $min = $this->aspect_ratio - $this->aspect_ratio_tolerance;
    $max = $this->aspect_ratio + $this->aspect_ratio_tolerance;

    return ($min <= $aspect_ratio and $aspect_ratio <= $max);
  }

  /**
   * Renders only the width portion of the `sizes` attribute value.
   *
   * @return string
   *   The width the image is expected to be at in either `vw` or `px` units.
   */
  public function renderWidthOnly()
  {
    return (isset($this->viewport_width))
         ? "{$this->viewport_width}vw" : "{$this->min_width}px";
  }

  /**
   * Renders a full `sizes` attribute value, with condition and expected width.
   *
   * @return string
   *   A value to be used in the comma separated conditions of a `sizes`
   *   attribute.
   */
  public function __toString()
  {
    $size = '';
    if (isset($this->media_query))
      $size .= "$this->media_query ";

    return $size . $this->renderWidthOnly();
  }
}
