<?php

namespace ResponsiveImages\srcset_generators;


use Functional as F;
use ResponsiveImages as RImg;


class DrupalImageStyle implements RImg\SrcsetGeneratorInterface
{
  private $target_effects =
    ['image_scale_and_crop', 'image_resize', 'image_crop', 'image_scale'];


  private function getStyles()
  {
    static $styles;

    if (isset($styles))
      return $styles;

    $styles = F\filter(image_styles(), function($style){
      foreach ($style['effects'] as $effect) {
        if (!in_array($effect['name'], $this->target_effects))
          # Unknown effects could have unintended side-effects.
          return false;
      }
      return true;
    });

    $styles = F\map($styles, function($style){
      $dimensions = $this->finalDimensionsOfStyle($style);
      return (object)[
         'name'   => $style['name']
        ,'width'  => $dimensions->width
        ,'height' => $dimensions->height
        ,'firm'   => $dimensions->firm
      ];
    });

    uasort($styles, function($a, $b){ return $a->width - $b->width; });

    return $styles;
  }


  private function stylesMatchingSize(RImg\Size $size)
  {
    # @todo Support Size instances without aspect ratio restrictions.
    # @todo Support Size instances with only width or only height restrictions.
    $styles = F\filter($this->getStyles(), function($style) use ($size){
      return ($style->firm
              and $size->matchesAspectRatio($style->width/$style->height));
    });

    $min_width = $size->getMinWidth();
    $max_width = $size->getMaxWidth();
    $styles = F\group($styles, function($style) use ($min_width, $max_width){
      if ($style->width < $min_width)
        return 'less';
      if ($style->width > ($max_width * 2)) # Multiplied for high DPI displays.
        return 'greater';
      return 'within';
    });

    if (!empty($styles['greater']))
      # Make sure the end of the range is covered.
      $styles['within'][] = F\head($styles['greater']);

    if (empty($styles['within'])) {
      if (!empty($styles['less']))
        # Better to have something too small than something too non-existent.
        $styles = [F\last($styles['less'])];
      else
        $styles = [];
    }
    else
      $styles = $styles['within'];

    return F\unique($styles, function($s){ return $s->width; });
  }


  /**
   * Calculates what the final dimensions of an image will be after the
   * passed style is applied to it.
   *
   * @param array $style
   *   A Drupal style definition array.
   *
   * @return \stdClass
   *   An object with the integer|null properties `width` and `height`, and
   *   the boolean property `firm`. `firm` represents whether or not the
   *   dimensions are guaranteed. If not guaranteed, `width` and `height`
   *   are only maximums.
   *
   */
  private function finalDimensionsOfStyle(array $style)
  {
    $dimensions = F\reduce_left($style['effects'],
    function ($effect, $i, $c, $dim){
      $width  = (int)$effect['data']['width']  ?: null;
      $height = (int)$effect['data']['height'] ?: null;

      if ($effect['name'] !== 'image_scale')
        # Every targeted effect except for `image_scale` sets a firm width and
        # height. This means that the width and height can be guaranteed.
        return (object)['width' => $width, 'height' => $height, 'firm' => true];

      foreach (['width', 'height'] as $side)
        if ($dim->$side === 0)
          $dim->$side = (int)$$side;

      $upscale = $effect['data']['upscale'];
      if (!$upscale
          and (!$width or $width > $dim->width)
          and (!$height or $height > $dim->height))
        return $dim;

      $dim = (array)$dim;
      image_dimensions_scale($dim, $width, $height, $upscale);

      return (object)$dim;
    }, (object)['width' => 0, 'height' => 0, 'firm' => false]);

    $dimensions->width  = $dimensions->width ?: null;
    $dimensions->height = $dimensions->height ?: null;

    return $dimensions;
  }


  /**
   * @see \ResponsiveImages\SrcsetGeneratorInterface
   *
   * @param string    $uri
   * @param RImg\Size $size
   *
   * @return RImg\Src[]
   */
  public function listFor($uri, RImg\Size $size)
  {
    $styles = $this->stylesMatchingSize($size);

    return F\map($styles, function($style) use ($uri){
      $url = image_style_url($style->name, $uri);
      return  new RImg\Src($url, $style->width);
    });
  }
}
