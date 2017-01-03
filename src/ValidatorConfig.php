<?php
namespace Draft;

/**
 * Class ValidatorConfig
 * @package Draft
 */
class ValidatorConfig
{
    /** @var int */
    private $contentBlockMaxDepth = 4; // Auto fixable: Set depth to this value

    /** @var array */
    private $contentBlockTypes = Defaults::BLOCK_TYPES; // Auto fixable: set type to unstyled

    /** @var array */
    private $inlineStyles = Defaults::INLINE_STYLES; // Auto fixable: remove not allowed style from character

    /** @var array */
    private $entityTypes = []; // Auto fixable: remove not allowed entity from character and from entity map

    /** @var array */
    private $blockTypesWithDepth = Defaults::LIST_BLOCK_TYPES; // Auto fixable: set depth to 0

    /** @var bool */
    private $incrementalDepthSteps = true; // Auto fixable: set depth to biggest possible depth

    /** @var int|null */
    private $maxCharacterCount = null;

    /** @var int|null */
    private $maxWordCount = null;

    /** @var int|null */
    private $maxLineCount = null;

    /**
     * ValidatorConfig constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = null)
    {
        if (isset($config['content_block_max_depth'])) {
            $this->contentBlockMaxDepth = intval($config['content_block_max_depth']);
        }

        if (isset($config['content_block_types'])) {
            $this->contentBlockTypes = $config['content_block_types'];
        }

        if (isset($config['inline_styles'])) {
            $this->inlineStyles = $config['inline_styles'];
        }

        if (isset($config['entity_types'])) {
            $this->entityTypes = $config['entity_types'];
        }

        if (isset($config['block_types_with_depth'])) {
            $this->blockTypesWithDepth = $config['block_types_with_depth'];
        }

        if (isset($config['incremental_depth_steps'])) {
            $this->incrementalDepthSteps = boolval($config['incremental_depth_steps']);
        }

        if (isset($config['max_character_count'])) {
            $this->maxCharacterCount = intval($config['max_character_count']);
        }

        if (isset($config['max_word_count'])) {
            $this->maxWordCount = intval($config['max_word_count']);
        }

        if (isset($config['max_line_count'])) {
            $this->maxLineCount = intval($config['max_line_count']);
        }
    }

    /**
     * @return int
     */
    public function getContentBlockMaxDepth()
    {
        return $this->contentBlockMaxDepth;
    }

    /**
     * @param int $contentBlockMaxDepth
     */
    public function setContentBlockMaxDepth(int $contentBlockMaxDepth)
    {
        $this->contentBlockMaxDepth = $contentBlockMaxDepth;
    }

    /**
     * @return array
     */
    public function getContentBlockTypes()
    {
        return $this->contentBlockTypes;
    }

    /**
     * @param array $contentBlockTypes
     */
    public function setContentBlockTypes(array $contentBlockTypes)
    {
        $this->contentBlockTypes = $contentBlockTypes;
    }

    /**
     * @return array
     */
    public function getInlineStyles()
    {
        return $this->inlineStyles;
    }

    /**
     * @param array $inlineStyles
     */
    public function setInlineStyles(array $inlineStyles)
    {
        $this->inlineStyles = $inlineStyles;
    }

    /**
     * @return array
     */
    public function getEntityTypes()
    {
        return $this->entityTypes;
    }

    /**
     * @param array $entityTypes
     */
    public function setEntityTypes(array $entityTypes)
    {
        $this->entityTypes = $entityTypes;
    }

    /**
     * @return array
     */
    public function getBlockTypesWithDepth()
    {
        return $this->blockTypesWithDepth;
    }

    /**
     * @param array $blockTypesWithDepth
     */
    public function setBlockTypesWithDepth(array $blockTypesWithDepth)
    {
        $this->blockTypesWithDepth = $blockTypesWithDepth;
    }

    /**
     * @return bool
     */
    public function isIncrementalDepthSteps()
    {
        return $this->incrementalDepthSteps;
    }

    /**
     * @param bool $incrementalDepthSteps
     */
    public function setIncrementalDepthSteps(bool $incrementalDepthSteps)
    {
        $this->incrementalDepthSteps = $incrementalDepthSteps;
    }

    /**
     * @return int|null
     */
    public function getMaxCharacterCount()
    {
        return $this->maxCharacterCount;
    }

    /**
     * @return int|null
     */
    public function getMaxWordCount()
    {
        return $this->maxWordCount;
    }

    /**
     * @return int|null
     */
    public function getMaxLineCount()
    {
        return $this->maxLineCount;
    }
}
