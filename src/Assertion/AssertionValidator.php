<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Assertion;

use webignition\BasilDataValidator\ResultType;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ResultInterface;
use webignition\BasilValidationResult\ValidResult;

class AssertionValidator
{
    public const REASON_INVALID_IDENTIFIER = 'assertion-invalid-identifier';
    public const REASON_INVALID_VALUE = 'assertion-invalid-value';
    public const REASON_INVALID_OPERATOR = 'assertion-invalid-operator';
    public const CONTEXT_OPERATOR = 'operator';

    private const VALID_OPERATIONS = ['is', 'is-not', 'exists', 'not-exists', 'includes', 'excludes', 'matches'];

    private AssertionContentValidator $assertionContentValidator;

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

        if (!in_array($assertion->getOperator(), self::VALID_OPERATIONS)) {
            return (new InvalidResult(
                $assertion,
                ResultType::ASSERTION,
                self::REASON_INVALID_OPERATOR
            ))->withContext([
                self::CONTEXT_OPERATOR => $assertion->getOperator(),
            ]);
        }

        if ($assertion->isComparison()) {
            $valueValidationResult = $this->assertionContentValidator->validate((string) $assertion->getValue());
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
