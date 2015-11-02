<?php

namespace ResponsiveImages;


use Functional as F;


/**
 * Represents a <source> to be used in a <picture>.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/source
 *
 * @package ResponsiveImages
 */
class Source
{
  /**
   * A list of image sizes represented in this <source>.
   *
   * @var Size[]
   */
  private $sizes;

  /**
   * Whether or not to render as an <img> instead of <source>. This is important
   * since the last element in a <picture> should be an <img>, but otherwise is
   * nearly the same as a <source>.
   *
   * @var bool
   */
  private $as_img;

  /**
   * Source constructor.
   *
   * @param Size[] $sizes
   *   List of sizes in the order their conditions should be rendered in the
   *   `sizes` attribute. Browsers evaluate the conditions from left to right,
   *   stopping at the first condition met.
   * @param bool   $as_img
   *   Whether or not this source should be rendered as an <img> instead of
   *   <source>.
   */
  function __construct(array $sizes, $as_img=false)
  {
    $this->sizes  = $sizes;
    $this->as_img = $as_img;
  }

  /**
   * Generates the HTML for the represented <source> or <img> element.
   *
   * @param SrcsetGeneratorInterface $srcset_gen
   *   The generator instance that will be used to generate the srcset
   *   attributes.
   *
   * @param mixed                    $image
   *   The image representation that will be passed to $srcset_gen
   *
   * @return string
   *   The HTML for either a <source> or <img> element depending on the value
   *
   * @see SrcsetGeneratorInterface
   */
  public function renderWith(SrcsetGeneratorInterface $srcset_gen, $image)
  {
    $last = F\last($this->sizes);

    $srcset = F\map($this->sizes, function(Size $size) use ($image, $srcset_gen){
      return $srcset_gen->listFor($image, $size);
    });
    $srcset = F\unique(F\flatten($srcset), function(Src $src){
      return $src->getUrl();
    });
    # Not really needed, but makes debugging nicer.
    usort($srcset, function(Src $l, Src $r){
      $l = $l->getWidth() ?: (int)$l->getMultiplier();
      $r = $r->getWidth() ?: (int)$r->getMultiplier();
      return $l - $r;
    });
    $srcset = implode(', ', $srcset);

    $sizes  = F\map($this->sizes, function(Size $size) use ($last){
      return $size === $last ? $size->renderWidthOnly() : (string)$size;
    });
    $sizes  = implode(', ', $sizes);

    $media = !$this->as_img ? ' media="'.$last->getMediaQuery().'"' : '';

    $attributes = "srcset=\"$srcset\" sizes=\"$sizes\"$media";
    return $this->as_img ? "<img $attributes>" : "<source $attributes>";
  }
}
