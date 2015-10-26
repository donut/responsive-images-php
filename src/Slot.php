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
    $this->sizes = F\map($sizes, function($size){
      return new Size($size['min_width'], $size['aspect_ratio']
        ,$size);
    });

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
    $sources[] = new Source($source, true);
  }

  public function renderFor($uri)
  {
    if (count($this->sources) === 1)
      return $this->sources[0]->renderFor($uri);

    $html = F\reduce_left($this->sources,
      function(Source $source, $i, $c, $acc) use ($uri){
        return "$acc\n  ".$source->renderFor($uri);
      }, '');

    return "<picture>$html\n</picture>";
  }
}
