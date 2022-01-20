<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Primitive;

/**
 * WIP
 * Class StringLiteral.
 */
class ComplexStringLiteral
{
    /**
     * Returns a StringLiteral object given a PHP native string or StringLiteral as parameter(s).
     *
     * @param ...$value
     */
    public function __construct(...$strings)
    {
        $string = '';
        foreach ($strings as $piece) {
            $string .= $piece;
        }

        $this->value = $string;
    }

    /**
     * Tells whether the StringLiteral is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->length() === 0;
    }

    /**
     * Get string length.
     *
     * @return int The length of the string on success, and 0 if the <i>string</i> is empty
     */
    public function length()
    {
        return strlen($this->value);
    }

    /**
     * Find the position of the first occurrence of a substring in a string.
     *
     * @param string|StringLiteral $needle The string to search in
     * @param int                  $offset If specified, search will start this number of characters counted from
     *                                     the beginning of the string. The offset cannot be negative.
     *
     * @return bool|int
     */
    public function indexOf($needle, $offset = 0)
    {
        $needle = $this->getStringValue($needle);
        $value = $this->getValue();

        return strpos($value, $needle, $offset);
    }

    /**
     * Split a string by string.
     *
     * @param string|StringLiteral $delimiter the boundary string
     * @param null                 $limit     if limit is set and positive, the returned array will contain a maximum
     *                                        of limit elements with the last element containing the rest of string
     *
     * @return StringLiteral[]
     */
    public function split($delimiter, $limit = 100)
    {
        $delimiter = $this->getStringValue($delimiter);
        $value = $this->getValue();
        $strings = explode($delimiter, $value, $limit);
        $stringLiterals = [];
        foreach ($strings as $string) {
            $stringLiterals[] = new ComplexStringLiteral($string);
        }

        return $stringLiterals;
    }

    /**
     * Return the sub string from $start to a $length.
     *
     * @param int $start  If start is non-negative, the returned string will start at the start'th position in
     *                    string, counting from zero. For instance, in the string 'abcdef', the character at
     *                    position 0 is 'a', the character at position 2 is 'c', and so forth.
     * @param int $length If length is given and is positive, the string returned will contain at most length
     *                    characters beginning from start (depending on the length of string). [OPTIONAL]
     *
     * @return StringLiteral this is a different StringLiteral that is returned
     */
    public function slice($start, $length = 100)
    {
        $value = $this->getValue();

        return new ComplexStringLiteral(mb_substr($value, $start, $length));
    }

    /**
     * Replace all occurrences of the search string with the replacement string.
     *
     * @param mixed $search  The value being searched for, otherwise known as the needle. An array may be used to
     *                       designate multiple needles.
     * @param mixed $replace The replacement value that replaces found search values. An array may be used to
     *                       designate multiple replacements.
     * @param int   $count   [optional] If passed, this will hold the number of matched and replaced needles
     *
     * @return StringLiteral this function returns a new ComplexStringLiteral with the replaced values
     */
    public function replace($search, $replace, &$count = null)
    {
        return new ComplexStringLiteral(str_replace($search, $replace, $this->value, $count));
    }

    /**
     * Strip whitespace (or other characters) from the beginning and end of a string.
     *
     * @param string $charList [optional] Optionally, the stripped characters can also be specified using the
     *                         charList parameter
     *
     * @return StringLiteral string The trimmed string
     */
    public function trim($charList = " \t\n\r\0\x0B")
    {
        return new ComplexStringLiteral(trim($this->value, $charList));
    }

    /**
     * Returns the value of the string.
     *
     * @return string
     */
    public function string()
    {
        return $this->value;
    }

    /**
     * Returns the string value itself.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->string();
    }

    /**
     * Converts an object to a string.
     *
     * @param $objectInQuestion
     *
     * @return string
     */
    private function getStringValue($objectInQuestion)
    {
        return '' . $objectInQuestion;
    }

    protected function getValue()
    {
        return '' . $this->value;
    }
}
