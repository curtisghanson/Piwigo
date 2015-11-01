<?php
namespace Piwigo\Model;

/**
 * Represents a menu block registered in a BlockManager object.
 */
class RegisteredBlock
{
    protected $id;
    protected $name;
    protected $owner;

  /**
   * @param string $id
   * @param string $name
   * @param string $owner
   */
    public function __construct($id, $name, $owner)
    {
        $this->id    = $id;
        $this->name  = $name;
        $this->owner = $owner;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
