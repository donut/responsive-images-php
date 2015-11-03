# ResponsiveImages
A PHP library for defining and rendering responsive images in HTML5 using 
`<picture>`, `srcset`, and `sizes`. 


## The Idea

The idea is that one can define a `Slot` that represents a place on a page 
where an image can live, including the different sizes and shapes (`Size`) it
can take in response to viewport conditions. Using this definition,
`<picture>`, `<source>`, and `<img>` tags can be rendered with the appropriate
`media`, `srcset`, and `sizes` attributes.


## Usage

```php
<?php

use ResponsiveImages as RImg;

# Define a list of sizes the image can take based on viewport
# conditions (media queries) in the order the browser should
# evaluate them.
#
# The `Size` constructor takes the following parameters:
#
# @param integer[]   $range
#   The range of widths the image can take specified either as
#   `[$min_width]` or `[$min_width, $max_width]`. If only $min_width
#   is specified, it will be used for the $max_width value as well.
# @param float       $aspect_ratio
#   The aspect ratio. Looks best if you pass it as a fraction, such as 16/9.
# @param string|null $query
#   The media query to be used in the `sizes` attribute value. If null, no
#   condition is set.
# @param float|null  $vw
#   The viewport width to be used in the `sizes` attribute value, specified
#   in `vw` units. If null, $min_width will be used with with `px` units.
# @param float       $ar_tolerance
#   The aspect ratio tolerance. Used to define the minimum and maximum
#   acceptable aspect ratios.

$sizes = [
  new RImg\Size([300], 3/2, '(min-width: 60em)'),
  # -> Image will be 300px wide when the viewport is at least 60em wide
  #    with an aspect ratio of 3:2.
  new RImg\Size([177], 4/3, '(min-width: 48em)'),
  # -> Image will be 300px wide when the viewport is at least 48em wide
  #    with an aspect ratio of 4:3.
  new RImg\Size([226, 370], 16/9, '(min-width: 30em)', 48),
  # -> Image will be between 226px and 370px wide when the viewport is at least
  #    30em wide, taking up about 48vw (48%) of the viewport's width with an
  #    aspect ratio of 16:9.
  new RImg\Size([320, 749], 16/9, null, 100),
  # -> Image will be between 320px and 749px wide when no other media queries
  #    have matched, taking up 100vw (100%) of the viewport's width with an
  #    aspect ratio of 16:9.
];

# Second, use the list of sizes to define the slot.
#
# A slot simply takes a list of Size instances in the order in which the
# browser should evaluate them.

$slot = new RImg\Slot($sizes);

# Third, create an instance of a class that implements SrcsetGeneratorInterface.
#
# Since there are many different ways to generate image sizes based an original
# image, there is no good generalization that would be applicable to every
# CMS/framework/project out there.
#
# The DrupalImageStyle class implements SrcsetGeneratorInterface and is
# included under [ResponsiveImages\srcset_generators]. It generates srcset 
# values by looking at all the image styles defined in a Drupal site and finds 
# those that match the Size instances passed to its implementation of 
# SrcsetGeneratorInterface::listFor().

$srcset_gen = new RImg\srcset_generators\DrupalImageStyle();

# Fourth and finally, render the image to HTML.
#
# Slot::renderWith() takes a SrcsetGeneratorInterface instance along with
# a representation of an image that the SrcsetGeneratorInterface instance will
# understand. In the case of DrupalImageStyle, this is a Drupal file URI to the 
# image. However this can be whatever as long as the chosen 
# SrcsetGeneratorInterface implementation understands it.

$image = 'public://my/image/representation/as/a/uri/or/anything/really.png';
$html  = $slot->renderWith($srcset_gen, $image, 'goomba');

# The contents of $html will look something like this depending on the output
# of the srcset generator.
?>

<picture>
  <source srcset="/styles/3_2_300w/my-image.jpg 300w,
                  /styles/3_2_600w/my-image.jpg 600w,
                  /styles/profile_large/my-image.jpg 924w"
          sizes="300px" media="(min-width: 60.9375em)">
  <source srcset="/styles/4_3_180w/my-image.jpg 180w,
                  /styles/4_3_360w/my-image.jpg 360w"
          sizes="177px" media="(min-width: 48em)">
  <img srcset="/styles/16_9_240w/my-image.jpg 240w,
               /styles/featured_item_medium/my-image.jpg 368w,
               /styles/16_9_480w/my-image.jpg 480w,
               /styles/16_9_576w/my-image.jpg 576w,
               /styles/16_9_768w/my-image.jpg 768w,
               /styles/16_9_960w/my-image.jpg 960w"
       sizes="(min-width: 30em) 48vw, 100vw" alt="goomba">
</picture>

<?php

# Note that the last two Size instances were combined into the single <img>
# tag as they shared the same aspect ratio. If all Size instances shared the
# same aspect ratio, Slot::renderWith() would have returned a single <img> with
# the appropriate values in `srcset` and `sizes`. It would not be wrapped in a
# <picture>. Most modern browsers have support for the `srcset` and `sizes` 
# attributes, but support for <picture> is weaker. Return a lone <img> when
# possible reduces how often a polyfill such as picturefill.js would need to
# run.
```

### Using `SlotGroup`

A `SlotGroup` instance defines a list of `Slot` instances with rules for 
which index in the group each slot is delivered for. For example, on 
[RightThisMinute](http://www.rigtthisminute.com) we have several groups of 
images that, while all representing the same type of object, often are 
displayed at differing sizes, not every slot in a group of images is displayed 
the same way.

`SlotGroup` solves this by attaching a function to each of its `Slot` 
instances. These functions take a 1-based index and return `true` if the slot
 is appropriate for that index, or `false` otherwise. The order that the 
 `Slot`/function pairs are defined in the array passed to the `SlotGroup` 
 constructor determines the order in which they will be evaluated. 
 
 Here is how the `SlotGroup` instance for [rightthisminute.com/newest]
 (http://www.rightthisminute.com/newest) is defined:
 
```php
<?php

use ResponsiveImages as RImg;

$all_tiny    = new RImg\Size([320, 479], 16/9, null, 100);
$n9_small    = new RImg\Size([462, 749], 16/9, '(min-width: 30em)', 98);
$other_small = new RImg\Size([226, 370], 16/9, '(min-width: 30em)', 48);
$all_medium  = new RImg\Size([236, 305], 16/9, '(min-width: 48em)', 31);
$all_large   = new RImg\Size([307], 16/9, '(min-width: 60.9375em)');

$newest_group = new RImg\SlotGroup([
  [ function($n){ return ($n-1) % 9 === 0; }, 
    new RImg\Slot([$all_large, $all_medium, $n9_small, $all_tiny]) ],

  [ function($n){ return ($n-1) % 9 !== 0; },
    new RImg\Slot([$all_large, $all_medium, $other_small, $all_tiny]) ],
]);
```

To get the appropriate `Slot` instance for a position on the page, we simple 
pass in the 1-based index to `SlotGroup::slotForNth()`:

```php
$newest_group->slotForNth(19);
```

`19` would be passed to each `Slot`/function pair, stopping at the first 
whose function returns `true` for `19`. In this case, it would return the 
first slot in the list.


## Outstanding Issues

* `Size` should be able to define a width restriction without also needing to
 define an aspect ratio.
* `Size` should be able to be defined in terms of height instead of width.

## Contributing

Please do! There is probably a lot that's been done wrong, or at least could 
be done better. For the time being I'm not putting up any rules or 
restrictions on contributions. The only thing I request is that any issues or
 pull requests come with the reasoning behind the issue/change/addition.
 
 
## Questions/Comments

Open up an issue! Also, everything has been documented, hopefully well, 
taking a look at the code may answer your questions.
