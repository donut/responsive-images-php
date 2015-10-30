<?php

namespace ResponsiveImages;


/**
 * A way to generate values for the HTML `srcset` attribute.
 *
 * @package ResponsiveImages
 */
interface SrcsetGeneratorInterface
{
  /**
   * Generates a list of values for the `srcset` HTML attribute for the
   * passed image.
   *
   * @param mixed $image
   *   A representation of the image to use to generate the srcset values.
   * @param Size   $size
   *   The Size instance to base the srcset on.
   *
   * @return Src[]
   *   List of values for the `srcset` attribute.
   */
  public function listFor($image, Size $size);
}
