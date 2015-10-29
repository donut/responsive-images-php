<?php

namespace ResponsiveImages;


interface SrcsetGeneratorInterface
{
  /**
   * Generates a list of values for the `srcset` HTML attribute for the
   * passed URI.
   *
   * @param string $uri
   *   The URI to generate the srcset for.
   * @param Size   $size
   *   The Size instance to base the srcset on.
   *
   * @return Src[]
   *   List of values for the `srcset` attribute.
   */
  public function listFor($uri, Size $size);
}
