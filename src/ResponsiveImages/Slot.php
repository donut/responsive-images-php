<?php

namespace ResponsiveImages;

use Functional as F;


/**
 * Class Slot
 */
class Slot
{
  /**
   * @var Size[]
   */
  private $sizes = [];

  /**
   * @var Source[]
   */
  private $sources = [];

  function __construct(array $sizes)
  {
    $this->sizes = $sizes;
    $this->groupSizesIntoSources();
  }

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

  public function renderWith($uri, SrcsetGeneratorInterface $srcset_gen)
  {
    if (count($this->sources) === 1)
      return $this->sources[0]->renderWith($uri, $srcset_gen);

    $html = F\reduce_left($this->sources,
      function(Source $source, $i, $c, $acc) use ($uri, $srcset_gen){
        return "$acc\n  ".$source->renderWith($uri, $srcset_gen);
      }, '');

    return "<picture>$html\n</picture>";
  }
}
