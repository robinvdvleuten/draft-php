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

    /** @var bool */
    private $validateEntityKeyExists = true; // NOT auto fixable - @TODO: not used yet - always true.

    /** @var array */
    private $blockTypesWithDepth = Defaults::LIST_BLOCK_TYPES; // Auto fixable: set depth to 0

    /** @var bool */
    private $incrementalDepthSteps = true; // Auto fixable: set depth to biggest possible depth

    /**
     * ValidatorConfig constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        if (isset($config['content_block_max_depth'])) {
            $this->contentBlockMaxDepth = $config['content_block_max_depth'];
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

        if (isset($config['validate_entity_key_exists'])) {
            $this->validateEntityKeyExists = $config['validate_entity_key_exists'];
        }

        if (isset($config['block_types_With_depth'])) {
            $this->blockTypesWithDepth = $config['block_types_With_depth'];
        }

        if (isset($config['incremental_depth_steps'])) {
            $this->incrementalDepthSteps = $config['incremental_depth_steps'];
        }
    }

    /**
     * @return int
     */
    public function getContentBlockMaxDepth(): int
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
    public function getContentBlockTypes(): array
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
    public function getInlineStyles(): array
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
    public function getEntityTypes(): array
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
     * @return bool
     */
    public function isValidateEntityKeyExists(): bool
    {
        return $this->validateEntityKeyExists;
    }

    /**
     * @param bool $validateEntityKeyExists
     */
    public function setValidateEntityKeyExists(bool $validateEntityKeyExists)
    {
        $this->validateEntityKeyExists = $validateEntityKeyExists;
    }

    /**
     * @return array
     */
    public function getBlockTypesWithDepth(): array
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
    public function isIncrementalDepthSteps(): bool
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
}
