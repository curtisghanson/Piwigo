<?php
namespace Piwigo\Model;

use Piwigo\Model\DisplayBlock;
use Piwigo\Model\RegisteredBlock;

/**
 * Manages a set of RegisteredBlock and DisplayBlock.
 */
class BlockManager
{
    protected $id;
    protected $registeredBlocks = array();
    protected $displayBlocks    = array();

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Triggers a notice that allows plugins of menu blocks to register the blocks.
     */
    public function loadRegisteredBlocks()
    {
        trigger_notify('blockmanager_register_blocks', array($this));
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return RegisteredBlock[]
     */
    public function getRegisteredBlocks()
    {
        return $this->registeredBlocks;
    }

    /**
     * Add a block with the menu. Usually called in 'blockmanager_register_blocks' event.
     *
     * @param RegisteredBlock $block
     */
    public function registerBlock($block)
    {
        if (isset($this->registeredBlocks[$block->getId()]))
        {
            return false;
        }

        $this->registeredBlocks[$block->getId()] = $block;

        return true;
    }

    /**
     * Performs one time preparation of registered blocks for display.
     * Triggers 'blockmanager_prepare_display' event where plugins can
     * reposition or hide blocks
     */
    public function prepareDisplay()
    {
        global $conf;

        $confId = 'blk_'.$this->id;
        $mbConf = isset($conf[$confId]) ? $conf[$confId] : array();

        if (!is_array($mbConf))
        {
            $mbConf = @unserialize($mbConf);
        }

        $idx = 1;
        foreach ($this->registeredBlocks as $id => $block)
        {
            $pos = isset($mbConf[$id]) ? $mbConf[$id] : $idx*50;
            if ($pos>0)
            {
                $this->displayBlocks[$id] = new DisplayBlock($block);
                $this->displayBlocks[$id]->setPosition($pos);
            }
            $idx++;
        }

        $this->sortBlocks();
        trigger_notify('blockmanager_prepare_display', array($this));
        $this->sortBlocks();
    }

    /**
     * Returns true if the block is hidden.
     *
     * @param string $blockId
     * @return bool
     */
    public function isHidden($blockId)
    {
        return !isset($this->displayBlocks[$blockId]);
    }

    /**
     * Remove a block from the displayed blocks.
     *
     * @param string $blockId
     */
    public function hideBlock($blockId)
    {
        unset($this->displayBlocks[$blockId]);
    }

    /**
     * Returns a visible block.
     *
     * @param string $blockId
     * @return DisplayBlock|null
     */
    public function getBlock($blockId)
    {
        if (isset($this->displayBlocks[$blockId]))
        {
            return $this->displayBlocks[$blockId];
        }
        return null;
    }

    /**
     * Changes the position of a block.
     *
     * @param string $blockId
     * @param int $position
     */
    public function setBlockPosition($blockId, $position)
    {
        if (isset($this->displayBlocks[$blockId]))
        {
            $this->displayBlocks[$blockId]->setPosition($position);
        }
    }

    /**
     * Sorts the blocks.
     */
    protected function sortBlocks()
    {
        uasort($this->displayBlocks, array(get_class($this), 'cmpByPosition'));
    }

    /**
     * Callback for blocks sorting.
     */
    static protected function cmpByPosition($a, $b)
    {
        return $a->getPosition() - $b->getPosition();
    }

    /**
     * Parse the menu and assign the result in a template variable.
     *
     * @param string $var
     * @param string $file
     */
    public function apply($var, $file)
    {
        global $template;

        $template->set_filename('menubar', $file);
        trigger_notify('blockmanager_apply', array($this) );

        foreach ($this->displayBlocks as $id=>$block)
        {
            if (empty($block->rawContent) and empty($block->template))
            {
                $this->hideBlock($id);
            }
        }
        $this->sortBlocks();
        $template->assign('blocks', $this->displayBlocks);
        $template->assign_var_from_handle($var, 'menubar');
    }
}