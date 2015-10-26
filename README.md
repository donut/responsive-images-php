# ResponsiveImages
A PHP library for defining and rendering responsive images in HTML5 using 
`<picture>`, `srcset`, and `sizes`. 


## Todos

* [ ] Write overview of how this should work.
* [ ] Add in Functional (as subtree or submodule?)
* [ ] Add in what's already been built.
* [ ] Finish building existing classes.
* [ ] Create `SlotGroup` for `nth-child`-esque `Slot` definitions.
* [ ] Create `srcset` generator interface
* [ ] Create Drupal `srcset` generator
* [ ] Comment all the things.


## Use Overview

The idea is that one can define a `Slot` that represents an image on a page, 
including all the different sizes it could take base do media queries. With 
this definition, URI, and a given `SrcsetGeneratorInterface` instance a 
`<picture>` tag is rendered with the appropriate `<source>`s and `<img>` so 
that it displays the best image possible for the given viewport conditions.
