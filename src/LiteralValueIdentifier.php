<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator;

class LiteralValueIdentifier
{
    private const QUOTE = '"';
    private const ESCAPE = '\\';

    public function is(string $value): bool
    {
        $value = trim($value);
        if ('' === $value) {
            return false;
        }

        if (self::QUOTE !== $value[0]) {
            return false;
        }

        $characters = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);
        array_shift($characters);
        $characterCount = count($characters);

        $previousCharacter = '';

        foreach ($characters as $index => $character) {
            if (self::QUOTE === $character) {
                if (self::ESCAPE !== $previousCharacter) {
                    if ($index === $characterCount - 1) {
                        return true;
                    }
                }
            }

            $previousCharacter = $character;
        }

        return false;
    }
}
