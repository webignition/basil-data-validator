<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Step;

use webignition\BasilDataValidator\Action\ActionValidator;
use webignition\BasilDataValidator\Assertion\AssertionValidator;
use webignition\BasilDataValidator\ResultType;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ResultInterface;
use webignition\BasilValidationResult\ValidResult;

class StepValidator
{
    public const REASON_NO_ASSERTIONS = 'step-no-assertions';
    public const REASON_INVALID_ACTION = 'step-invalid-action';
    public const REASON_INVALID_ASSERTION = 'step-invalid-assertion';

    private $actionValidator;
    private $assertionValidator;

    public function __construct(ActionValidator $actionValidator, AssertionValidator $assertionValidator)
    {
        $this->actionValidator = $actionValidator;
        $this->assertionValidator = $assertionValidator;
    }

    public static function create(): StepValidator
    {
        return new StepValidator(
            ActionValidator::create(),
            AssertionValidator::create()
        );
    }

    public function validate(StepInterface $step): ResultInterface
    {
        $assertions = $step->getAssertions();
        if (0 === count($assertions)) {
            return new InvalidResult(
                $step,
                ResultType::STEP,
                self::REASON_NO_ASSERTIONS
            );
        }

        foreach ($step->getActions() as $action) {
            $actionValidationResult = $this->actionValidator->validate($action);

            if ($actionValidationResult instanceof InvalidResultInterface) {
                return new InvalidResult(
                    $step,
                    ResultType::STEP,
                    self::REASON_INVALID_ACTION,
                    $actionValidationResult
                );
            }
        }

        foreach ($assertions as $assertion) {
            $assertionValidationResult = $this->assertionValidator->validate($assertion);

            if ($assertionValidationResult instanceof InvalidResultInterface) {
                return new InvalidResult(
                    $step,
                    ResultType::STEP,
                    self::REASON_INVALID_ASSERTION,
                    $assertionValidationResult
                );
            }
        }

        return new ValidResult($step);
    }
}
