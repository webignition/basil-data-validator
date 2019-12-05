<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Step;

use webignition\BasilDataValidator\Action\ActionValidator;
use webignition\BasilDataValidator\ResultType;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ResultInterface;
use webignition\BasilValidationResult\ValidResult;

class StepValidator
{
    public const REASON_INVALID_ACTION = 'step-invalid-action';

    private $actionValidator;

    public function __construct(ActionValidator $actionValidator)
    {
        $this->actionValidator = $actionValidator;
    }

    public static function create(): StepValidator
    {
        return new StepValidator(
            ActionValidator::create()
        );
    }

    public function validate(StepInterface $step): ResultInterface
    {
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

        return new ValidResult($step);
    }
}
