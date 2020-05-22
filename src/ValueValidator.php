<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator;

use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\BrowserProperty\BrowserProperty;
use webignition\BasilModels\PageProperty\PageProperty;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\ResultInterface;
use webignition\BasilValidationResult\ValidResult;

class ValueValidator
{
    public const REASON_PROPERTY_INVALID = 'value-property-invalid';
    public const REASON_INVALID = 'value-invalid';
    private const DATA_PARAMETER_REGEX = '/^\$data\.[^\.]+$/';
    private const ENVIRONMENT_PARAMETER_REGEX = '/^\$env\.[^\.]+$/';

    private IdentifierTypeAnalyser $identifierTypeAnalyser;
    private LiteralValueIdentifier $literalValueIdentifier;

    public function __construct(
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        LiteralValueIdentifier $literalValueIdentifier
    ) {
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->literalValueIdentifier = $literalValueIdentifier;
    }

    public static function create(): ValueValidator
    {
        return new ValueValidator(
            IdentifierTypeAnalyser::create(),
            new LiteralValueIdentifier()
        );
    }

    public function validate(string $value): ResultInterface
    {
        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
            return new ValidResult($value);
        }

        if (preg_match(self::DATA_PARAMETER_REGEX, $value) > 0) {
            return new ValidResult($value);
        }

        if (preg_match(self::ENVIRONMENT_PARAMETER_REGEX, $value) > 0) {
            return new ValidResult($value);
        }

        if ($this->literalValueIdentifier->is($value)) {
            return new ValidResult($value);
        }

        if ($this->isPrefixedProperty('$browser.', $value)) {
            return BrowserProperty::is($value)
                ? new ValidResult($value)
                : new InvalidResult($value, ResultType::VALUE, self::REASON_PROPERTY_INVALID);
        }

        if ($this->isPrefixedProperty('$page.', $value)) {
            return PageProperty::is($value)
                ? new ValidResult($value)
                : new InvalidResult($value, ResultType::VALUE, self::REASON_PROPERTY_INVALID);
        }

        return new InvalidResult($value, ResultType::VALUE, self::REASON_INVALID);
    }

    private function isPrefixedProperty(string $prefix, string $value): bool
    {
        $prefixLength = strlen($prefix);

        return substr($value, 0, $prefixLength) === $prefix;
    }
}
