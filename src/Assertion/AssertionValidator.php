<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Assertion;

use webignition\BasilDataValidator\ResultType;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\ComparisonAssertionInterface;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ResultInterface;
use webignition\BasilValidationResult\ValidResult;

class AssertionValidator
{
    public const REASON_INVALID_IDENTIFIER = 'assertion-invalid-identifier';
    public const REASON_INVALID_VALUE = 'assertion-invalid-value';
    public const REASON_INVALID_COMPARISON = 'assertion-invalid-comparison';

    private const VALID_COMPARISONS = ['is', 'is-not', 'exists', 'not-exists', 'includes', 'excludes', 'matches'];

    private $assertionContentValidator;

    public function __construct(AssertionContentValidator $assertionContentValidator)
    {
        $this->assertionContentValidator = $assertionContentValidator;
    }

    public static function create(): AssertionValidator
    {
        return new AssertionValidator(
            AssertionContentValidator::create()
        );
    }

    public function validate(AssertionInterface $assertion): ResultInterface
    {
        $identifierValidationResult = $this->assertionContentValidator->validate($assertion->getIdentifier());
        if ($identifierValidationResult instanceof InvalidResultInterface) {
            return new InvalidResult(
                $assertion,
                ResultType::ASSERTION,
                self::REASON_INVALID_IDENTIFIER,
                $identifierValidationResult
            );
        }

        if (!in_array($assertion->getComparison(), self::VALID_COMPARISONS)) {
            return new InvalidResult(
                $assertion,
                ResultType::ASSERTION,
                self::REASON_INVALID_COMPARISON
            );
        }

        if ($assertion instanceof ComparisonAssertionInterface) {
            $valueValidationResult = $this->assertionContentValidator->validate($assertion->getValue());
            if ($valueValidationResult instanceof InvalidResultInterface) {
                return new InvalidResult(
                    $assertion,
                    ResultType::ASSERTION,
                    self::REASON_INVALID_VALUE,
                    $valueValidationResult
                );
            }
        }

        return new ValidResult($assertion);
    }
}
