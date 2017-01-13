<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) The Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Draft\Model\Immutable;

/**
 * @CounterpartURL https://facebook.github.io/draft-js/docs/api-reference-character-metadata.html
 *
 * Not implemented functions:
 * - static create(config?: CharacterMetadataConfig): CharacterMetadata
 *   Because CharacterMetadata are not immutable here there is no need for this factory method
 */
class CharacterMetadata
{
    /**
     * @var array
     */
    private $style;

    /**
     * @var string|null
     */
    private $entity;

    /**
     * Constructor.
     *
     * @param array       $style
     * @param string|null $entity
     */
    public function __construct(array $style = [], $entity = null)
    {
        $this->style = $style;
        $this->entity = $entity;
    }

    /**
     * @Counterpart CharacterMetadata::getStyle(): DraftInlineStyle
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/CharacterMetadata.js#L43
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-character-metadata.html#getstyle
     * @OfficialDocumentation
     * Returns the DraftInlineStyle for this character, an OrderedSet of strings that represents the inline style to
     * apply for the character at render time.
     *
     * @return array
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * @Counterpart None
     *
     * @Notes
     * In draft.js you have applyStyle and removeStyle instance methods.
     * When you want override a style however you create a new immutable CharacterMetadata with the static create method
     *
     * @param array $style
     */
    public function setStyle(array $style)
    {
        // Just ensures it's not a a assoc array
        // Especially that the values are unique (for haveEqualStyle)
        // Same behaviour than immutable OrderedList in draft.js
        $this->style = array_values(array_unique($style));
    }

    /**
     * @Counterpart CharacterMetadata::getEntity(): ?string
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/CharacterMetadata.js#L47
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-character-metadata.html#getentity
     * @OfficialDocumentation
     * Returns the entity key (if any) for this character, as mapped to the global set of entities tracked by the Entity module.
     * By tracking a string key here, we can keep the corresponding metadata separate from the character representation.
     * If null, no entity is applied for this character.
     *
     * @return null|string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @Counterpart applyEntity(record: CharacterMetadata, entityKey: ?string): CharacterMetadata
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/CharacterMetadata.js#L71
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-character-metadata.html#applyentity
     * @OfficialDocumentation
     * Apply an entity key -- or provide null to remove an entity key -- on this CharacterMetadata.
     *
     * @Notes
     * For convention this method is renamed to setEntity instead of applyEntity
     * This method modifies directly the Content State.
     * In draft.js however this is a static method which returns a new immutable CharacterMetadata
     *
     * @param null|string $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @Counterpart CharacterMetadata::hasStyle(style: string): boolean
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/CharacterMetadata.js#L51
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-character-metadata.html#hasstyle
     * @OfficialDocumentation
     * Returns whether this character has the specified style.
     *
     * @param string $style
     *
     * @return string bool
     */
    public function hasStyle($style)
    {
        return in_array($style, $this->style);
    }

    /**
     * @Counterpart static applyStyle(record: CharacterMetadata, style: string): CharacterMetadata
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/CharacterMetadata.js#L55
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-character-metadata.html#applystyle
     * @OfficialDocumentation
     * Apply an inline style to this CharacterMetadata.
     *
     * @Notes
     * This method modifies directly the Content State.
     * In draft.js however this is a static method which returns a new immutable CharacterMetadata
     *
     * @param string $style
     *
     * @return void
     */
    public function applyStyle($style)
    {
        if (is_string($style)) {
            $style = [$style];
        }
        $this->style = array_unique($this->style, $style);
    }

    /**
     * @Counterpart static removeStyle(record: CharacterMetadata, style: string): CharacterMetadata
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/CharacterMetadata.js#L63
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-character-metadata.html#removestyle
     * @OfficialDocumentation
     * Remove an inline style from this CharacterMetadata.
     *
     * @param string $style
     *
     * @return void
     */
    public function removeStyle($style)
    {
        if (is_string($style)) {
            $style = [$style];
        }
        $this->style = array_diff($this->style, $style);
    }

    /**
     * In draft.js the style property is a OrderedList and can compared directly.
     * In PHP the style in a plain array (different orders results in a not-equal!)
     *
     * @Counterpart None (not directly)
     *
     * @Notes
     * Replaces this private function indirectly:
     * haveEqualStyle(charA: CharacterMetadata, charB: CharacterMetadata): boolean
     * (https://github.com/facebook/draft-js/blob/master/src/model/immutable/ContentBlock.js#L120)
     *
     * @param CharacterMetadata $otherCharacterMetadata
     *
     * @return bool
     */
    public function haveEqualStyle(CharacterMetadata $otherCharacterMetadata)
    {
        return array_count_values($this->getStyle()) == array_count_values($otherCharacterMetadata->getStyle());
    }

    /**
     * @Counterpart None (not directly)
     *
     * @Notes
     * Just for convention because haveEqualStyle exists here too.
     * However have the same function than the following private function:
     * function haveEqualEntity(charA: CharacterMetadata, charB: CharacterMetadata)
     * (https://github.com/facebook/draft-js/blob/master/src/model/immutable/ContentBlock.js#L127)
     *
     * @param CharacterMetadata $otherCharacterMetadata
     *
     * @return bool
     */
    public function haveEqualEntity(CharacterMetadata $otherCharacterMetadata)
    {
        return $this->getEntity() === $otherCharacterMetadata->getEntity();
    }
}
