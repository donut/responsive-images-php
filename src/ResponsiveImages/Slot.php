<?php

namespace ResponsiveImages;


use Functional as F;


/**
 * Defines an image position and how it responds to different viewport
 * conditions.
 *
 * @package ResponsiveImages
 */
class Slot
{
  /**
   * List of possible sizes the image could take, and the conditions required
   * by those sizes.
   *
   * @var Size[]
   */
  private $sizes = [];

  /**
   * A generated list of <source> tags for the <picture> element, including
   * the final <img> tag.
   *
   * @var Source[]
   */
  private $sources = [];

  /**
   * Slot constructor.
   *
   * @param Size[] $sizes
   *   List of sizes images in this slot can take in the order that they should
   *   be evaluated. The <source> tags of a <picture> element are evaluated in
   *   the order they are defined in the HTML code, stopping at the first one
   *   whose `media` attribute matches the current viewport conditions. The
   *   same is true for the comma separated values in a `sizes` attributed.
   *   This list must reflect that desired order.
   */
  function __construct(array $sizes)
  {
    $this->sizes = $sizes;
    $this->groupSizesIntoSources();
  }

  /**
   * Groups the sizes into Source instances representing <source> tags as
   * appropriate.
   *
   * The images defined in a `srcset` attribute should all have the same
   * aspect ratio and generally be the same image, just at different sizes. If
   * an image needs to change more than scale between different viewport
   * conditions then each variation should be housed in its own <source> tag.
   */
  private function groupSizesIntoSources()
  {
    $prev_aspect_ratio = null;
    $source = [];
    foreach ($this->sizes as $size) {
      $aspect_ratio = $size->getAspectRatio();
      if (isset($prev_aspect_ratio) and $aspect_ratio !== $prev_aspect_ratio) {
        $this->sources[] = new Source($source);
        $source = [];
      }
      $prev_aspect_ratio = $aspect_ratio;
      $source[] = $size;
    }
    $this->sources[] = new Source($source, true);
  }

  /**
   * Generates the HTML to represent the passed image in this slot definition.
   *
   * @param SrcsetGeneratorInterface $srcset_gen
   *   The generator instance that will be used to generate the srcset
   *   attributes.
   * @param mixed                    $image
   *   The image representation that will be passed to $srcset_gen
   * @param string                   $alt
   *   The value of the <img> `alt` attribute.
   *
   * @return string
   *   The HTML for the image. An <img> if the slot can be represented by a
   *
   * @see SrcsetGeneratorInterface
   * @see Source::renderWith()
   */
  public function renderWith(SrcsetGeneratorInterface $srcset_gen, $image
                            ,$alt='')
  {
    # A naked <img> is used if possible since browser support for <picture>
    # is still not great. However, support for <img> with `srcset` and `sizes`
    # attributes is much better. This reduces how often a polyfill is required
    # to run.
    # @see http://caniuse.com/#search=picture
    if (count($this->sources) === 1)
      return $this->sources[0]->renderWith($srcset_gen, $image, $alt);

    $html = F\reduce_left($this->sources,
      function(Source $source, $i, $c, $acc) use ($image, $srcset_gen, $alt){
        return "$acc\n  ".$source->renderWith($srcset_gen, $image, $alt);
      }, '');

    return "<picture>$html\n</picture>";
  }
}
