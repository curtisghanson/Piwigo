<?php
namespace Piwigo\Model;

use Piwigo\Model\RegisteredBlock;
/**
 * Represents a menu block ready for display in the BlockManager object.
 */
class DisplayBlock
{
    protected $registeredBlock;
    protected $position;
    protected $title;

    public $data;
    public $template;
    public $rawContent;

  /**
   * @param RegisteredBlock $block
   */
  public function __construct(RegisteredBlock $block)
  {
    $this->registeredBlock = $block;
  }

    /**
     * @return RegisteredBlock
     */
    public function getBlock()
    {
        return $this->registeredBlock;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if (isset($this->title))
        {
            return $this->title;
        }

        return $this->registeredBlock->getName();
    }

    /**
     * @param string
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
}
