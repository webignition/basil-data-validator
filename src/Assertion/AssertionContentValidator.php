<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Assertion;

use webignition\BasilDataValidator\ValueValidator;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilValidationResult\ResultInterface;
use webignition\BasilValidationResult\ValidResult;

class AssertionContentValidator
{
    private $identifierTypeAnalyser;
    private $valueValidator;

    public function __construct(IdentifierTypeAnalyser $identifierTypeAnalyser, ValueValidator $valueValidator)
    {
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->valueValidator = $valueValidator;
    }

    public static function create(): AssertionContentValidator
    {
        return new AssertionContentValidator(
            IdentifierTypeAnalyser::create(),
            ValueValidator::create()
        );
    }

    public function validate(string $content): ResultInterface
    {
        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($content)) {
            return new ValidResult($content);
        }

        return $this->valueValidator->validate($content);
    }
}
