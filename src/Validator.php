<?php
namespace Draft;

use Draft\Exception\InvalidContentStateException;
use Draft\Model\Entity\DraftEntity;
use Draft\Model\Immutable\ContentState;

/**
 * See ValidatorConfig for configurable validations
 *
 * Checks:
 * - Validate the depth
 *   - Is possible on target block type
 *   - Depth raises in incremental steps
 *   - Maximal depth
 * - Allows only specific
 *   - content block types
 *   - entity types
 *   - inline styles
 * - Check for limits when set
 *   - Character count
 *   - Word count
 *   - Line count
 * - ContentBlock text must not contains newline character
 * - CharacterMetadata entity must reference to an existing entity in the entity map
 * - Check for valid entity mutability
 *
 * IDEAS:
 * - clear not used entities from entity map (no reference in character meta data)
 *
 * Class Validator
 * @package Draft
 */
class Validator
{
    /**
     * @param ContentState $contentState
     * @param ValidatorConfig|null $validatorConfig
     * @param null $tryAutoFix
     *
     * @return ContentState
     * @throws InvalidContentStateException
     */
    public function validate(ContentState $contentState, ValidatorConfig $validatorConfig = null, $tryAutoFix = null)
    {
        if ($validatorConfig === null) {
            $validatorConfig = new ValidatorConfig();
        }

        if ($tryAutoFix === null) {
            $tryAutoFix = true;
        }

        $maxCharacterCount = $validatorConfig->getMaxCharacterCount();
        $maxWordCount = $validatorConfig->getMaxWordCount();
        $maxLineCount = $validatorConfig->getMaxLineCount();

        if ($maxCharacterCount !== null) {
            if (strlen($contentState->getPlainText()) > $maxCharacterCount) {
                throw new InvalidContentStateException('The content contains more character than allowed.');
            }
        }

        if ($maxWordCount !== null) {
            if (str_word_count($contentState->getPlainText()) > $maxWordCount) {
                throw new InvalidContentStateException('The content contains more lines than allowed.');
            }
        }

        if ($maxLineCount !== null) {
            if (count($contentState->getBlockMap()) > $maxLineCount) {
                throw new InvalidContentStateException('The content contains more lines than allowed.');
            }
        }

        $lastDepth = 0;

        foreach ($contentState->getEntityMap() as $key => $entity) {
            $type = $entity->getType();
            $mutability = $entity->getMutability();

            $validMutability = [
                DraftEntity::MUTABILITY_IMMUTABLE,
                DraftEntity::MUTABILITY_MUTABLE,
                DraftEntity::MUTABILITY_SEGMENTED,
            ];

            if (!in_array($mutability, $validMutability)) {
                throw new InvalidContentStateException('Entity contains invalid mutability');
            }

            if (!in_array($type, $validatorConfig->getEntityTypes())) {
                if ($tryAutoFix) {
                    $contentState->__removeEntity($key);
                } else {
                    throw new InvalidContentStateException('Entity contains not allowed type '. $type);
                }
            }
        }

        foreach ($contentState->getBlockMap() as $contentBlock) {
            $type = $contentBlock->getType();
            $depth = $contentBlock->getDepth();
            $text = $contentBlock->getText();
            $characterList = $contentBlock->getCharacterList();

            if (!in_array($type, $validatorConfig->getContentBlockTypes())) {
                if ($tryAutoFix) {
                    $contentBlock->setType(Defaults::DEFAULT_BLOCK_TYPE);
                } else {
                    throw new InvalidContentStateException('Content block of type ' . $type . ' is invalid.');
                }
            }

            if (strstr($text, PHP_EOL) !== false) {
                throw new InvalidContentStateException('Content block text in content state cannot contain new lines.');
            }

            if ($depth > 0) {
                if (!in_array($type, $validatorConfig->getBlockTypesWithDepth())) {
                    if ($tryAutoFix) {
                        $contentBlock->setDepth(0);
                    } else {
                        throw new InvalidContentStateException('Content block of type ' . $type . ' cannot have a depth.');
                    }
                }
                if ($depth > $validatorConfig->getContentBlockMaxDepth()) {
                    if ($tryAutoFix) {
                        $contentBlock->setDepth($validatorConfig->getContentBlockMaxDepth());
                    } else {
                        throw new InvalidContentStateException('Content block maximal depth exceeded.');
                    }
                }
                if ($validatorConfig->isIncrementalDepthSteps()) {
                    if ($depth > $lastDepth + 1) {
                        if ($tryAutoFix) {
                            $contentBlock->setDepth($lastDepth + 1);
                            $depth = $contentBlock->getDepth();
                        } else {
                            throw new InvalidContentStateException('Content block depth must raise in incremental steps.');
                        }
                    }
                }
            } else if ($depth < 0) {
                if ($tryAutoFix) {
                    $contentBlock->setDepth(0);
                } else {
                    throw new InvalidContentStateException('Content block depth must equal or greater than 0.');
                }
            }

            foreach ($characterList as $characterMetadata) {
                $characterEntity = $characterMetadata->getEntity();
                $characterStyle = $characterMetadata->getStyle();

                if ($contentState->getEntity($characterEntity) === null) {
                    if ($tryAutoFix) {
                        $characterMetadata->setEntity(null);
                    } else {
                        throw new InvalidContentStateException('Character metadata contains not existing entity.');
                    }
                }

                $stylesToRemove = [];

                foreach ($characterStyle as $style) {
                    if (!in_array($style, $validatorConfig->getInlineStyles())) {
                        if ($tryAutoFix) {
                            $stylesToRemove[] = $style;
                        } else {
                            throw new InvalidContentStateException('Character metadata contains not allowed style.');
                        }
                    }
                }

                if (count($stylesToRemove) > 0) {
                    $characterMetadata->setStyle(array_diff($characterStyle, $stylesToRemove));
                }
            }

            $lastDepth = $depth;
        }

        return $contentState;
    }
}
