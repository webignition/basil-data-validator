<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Action;

use webignition\BasilDataValidator\ResultType;
use webignition\BasilDataValidator\ValueValidator;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ResultInterface;
use webignition\BasilValidationResult\ValidResult;

class ActionValidator
{
    public const REASON_INVALID_TYPE = 'action-invalid-type';
    public const REASON_INVALID_IDENTIFIER = 'action-invalid-identifier';
    public const REASON_INVALID_VALUE = 'action-invalid-value';
    private const VALID_TYPES = ['click', 'set', 'submit', 'wait', 'wait-for', 'back', 'forward', 'reload'];

    private IdentifierTypeAnalyser $identifierTypeAnalyser;
    private ValueValidator $valueValidator;

    public function __construct(IdentifierTypeAnalyser $identifierTypeAnalyser, ValueValidator $valueValidator)
    {
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->valueValidator = $valueValidator;
    }

    public static function create(): ActionValidator
    {
        return new ActionValidator(
            IdentifierTypeAnalyser::create(),
            ValueValidator::create()
        );
    }

    public function validate(ActionInterface $action): ResultInterface
    {
        if ($action->isInteraction() || $action->isInput()) {
            $identifier = (string) $action->getIdentifier();

            if (
                !$this->identifierTypeAnalyser->isElementIdentifier($identifier)
                && !$this->identifierTypeAnalyser->isDescendantDomIdentifier($identifier)
            ) {
                return new InvalidResult(
                    $action,
                    ResultType::ACTION,
                    self::REASON_INVALID_IDENTIFIER
                );
            }
        }

        if ($action->isInput()) {
            $valueValidationResult = $this->valueValidator->validate((string) $action->getValue());

            if ($valueValidationResult instanceof InvalidResultInterface) {
                return new InvalidResult(
                    $action,
                    ResultType::ACTION,
                    self::REASON_INVALID_VALUE,
                    $valueValidationResult
                );
            }
        }

        if ($action->isWait()) {
            $value = (string) $action->getValue();
            $value = ctype_digit($value) ? '"' . $value . '"' : $value;

            $valueValidationResult = $this->valueValidator->validate($value);

            if ($valueValidationResult instanceof InvalidResultInterface) {
                return new InvalidResult(
                    $action,
                    ResultType::ACTION,
                    self::REASON_INVALID_VALUE,
                    $valueValidationResult
                );
            }
        }

        if (!in_array($action->getType(), self::VALID_TYPES)) {
            return new InvalidResult(
                $action,
                ResultType::ACTION,
                self::REASON_INVALID_TYPE
            );
        }

        return new ValidResult($action);
    }
}
