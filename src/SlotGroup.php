<?php

namespace ResponsiveImages;

use Functional as F;


class SlotGroup
{
  private $slots;

  function __construct(array $slots)
  {
    $this->slots = $slots;
  }

  public function slotForNth($nth)
  {
    return F\first($this->slots, function($slot) use ($nth){
      return is_callable($slot[0]) ? $slot[0]($nth) : true;
    })[1];
  }
}
