# ResponsiveImages
A PHP library for defining and rendering responsive images in HTML5 using 
`<picture>`, `srcset`, and `sizes`. 


## Todos

* [x] Write overview of how this should work.
* [x] Add in Functional (as subtree or submodule?)
* [x] Add in what's already been built.
* [x] Finish building existing classes.
* [ ] Create `SlotGroup` for `nth-child`-esque `Slot` definitions.
* [x] Create `srcset` generator interface
* [x] Create Drupal `srcset` generator
* [ ] Document and clean up all the things.


## Use Overview

The idea is that one can define a `Slot` that represents an image on a page, 
including all the different sizes it could take base do media queries. With 
this definition, URI, and a given `SrcsetGeneratorInterface` instance a 
`<picture>` tag is rendered with the appropriate `<source>`s and `<img>` so 
that it displays the best image possible for the given viewport conditions.

### `Slot` class

Defines an image on a page and how it is responsive. Mainly, it contains a 
list of `Size` instances in the order in which they will be evaluated.

### `SlotGroup` class

Defines a list of slots defined in terms of explicit index or `nth-child`. 
This facilitates image definitions for groups of images that may not have a 
uniform display.

### `Size` class

Represents an entry in the `sizes` HTML attribute for `<img>` and `<source>` 
tags. Contains the following information:

* `min_width` - The minimum width this size will take.
* `max_width` - The maximum width this size will take. Defaults to the value 
of `min_width` if not specified.
* `aspect_ratio` - Aspect ratio of the image expressed as a float. Used to 
determine height.
* `aspect_ratio_tolerance` - How far away from `aspect_ratio` a generated 
image can be and still be considered to fit. Defaults to `0`.
* `media_query` - The media query (including parentheses) to that needs to be
 met to use this size.
* `viewport_width` - The viewport width units expressed as an integer or 
 float to be used with the specified `media_query` or without. Defaults to 
 using `min_width` defined in `px` if not specified.

### `SrcsetGeneratorInterface`

An instance of this will take `Size` instance and return a array of strings 
representing values to go into a `srcset` attribute. Each string in the array
 should be made up of `<url> <width|multiplier>`.
