<?php

namespace ResponsiveImages;


use Functional as F;


/**
 * Represents a group of slots that can be pulled up by index.
 *
 * @package ResponsiveImages
 */
class SlotGroup
{
  /**
   * A list of slots paired with functions that take a 1-based index and return
   * whether or not they apply to that index. Each entry in this array is a
   * two element array, with the first being the function and the second
   * being the Slot instance.
   *
   * @var array[]
   */
  private $slots;

  /**
   * SlotGroup constructor.
   *
   * @param array $slots
   *   A 2 element array. The first being a function that takes a 1-based
   *   index and returns a boolean indicating whether or not the slot can be
   *   used for that index. The second element is the Slot instance. The first
   *   element can also be the string 'all' which means it works for all
   *   indexes.
   */
  function __construct(array $slots)
  {
    $this->slots = $slots;
  }

  /**
   * Finds the first slot that works for the passed index.
   *
   * @param integer $nth
   *   The 1-based index of the item in the group.
   *
   * @return Slot
   *   The Slot for the passed index.
   */
  public function slotForNth($nth)
  {
    $slot = F\first($this->slots, function($slot) use ($nth){
      return is_callable($slot[0]) ? $slot[0]($nth) : true;
    });

    return $slot[1];
  }
}
