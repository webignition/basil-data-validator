<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Step;

use webignition\BasilDataValidator\Action\ActionValidator;
use webignition\BasilDataValidator\Assertion\AssertionValidator;
use webignition\BasilDataValidator\ResultType;
use webignition\BasilModels\Action\InputActionInterface;
use webignition\BasilModels\Assertion\ComparisonAssertionInterface;
use webignition\BasilModels\DataParameter\DataParameter;
use webignition\BasilModels\DataParameter\DataParameterInterface;
use webignition\BasilModels\DataSet\DataSetCollection;
use webignition\BasilModels\StatementInterface;
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
    public const REASON_DATA_SET_EMPTY = 'step-data-set-empty';
    public const REASON_DATA_SET_INCOMPLETE = 'step-data-set-incomplete';
    public const CONTEXT_STATEMENT = 'statement';
    public const CONTEXT_DATA_PARAMETER_NAME = 'data-parameter-name';
    public const CONTEXT_DATA_SET = 'data-set';

    private $actionValidator;
    private $assertionValidator;

    public function __construct(
        ActionValidator $actionValidator,
        AssertionValidator $assertionValidator
    ) {
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

        $stepDataParameterNames = $step->getDataParameterNames();
        $stepData = $step->getData();
        if (count($stepDataParameterNames) > 0 && (null === $stepData || 0 === count($stepData))) {
            return new InvalidResult(
                $step,
                ResultType::STEP,
                self::REASON_DATA_SET_EMPTY
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

            if ($action instanceof InputActionInterface) {
                $value = $action->getValue();

                if (DataParameter::is($value)) {
                    $dataValidationResult = $this->validateStatementData($step, $action, new DataParameter($value));

                    if ($dataValidationResult instanceof InvalidResultInterface) {
                        return $dataValidationResult;
                    }
                }
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

            $identifier = $assertion->getIdentifier();

            if (DataParameter::is($identifier)) {
                $dataValidationResult = $this->validateStatementData($step, $assertion, new DataParameter($identifier));

                if ($dataValidationResult instanceof InvalidResultInterface) {
                    return $dataValidationResult;
                }
            }

            if ($assertion instanceof ComparisonAssertionInterface) {
                $value = $assertion->getValue();

                if (DataParameter::is($value)) {
                    $dataValidationResult = $this->validateStatementData($step, $assertion, new DataParameter($value));

                    if ($dataValidationResult instanceof InvalidResultInterface) {
                        return $dataValidationResult;
                    }
                }
            }
        }

        $stepDataParameterNames = $step->getDataParameterNames();

        if (count($stepDataParameterNames) > 0) {
            $data = $step->getData();

            if (null === $data || 0 === count($data)) {
                return new InvalidResult(
                    $step,
                    ResultType::STEP,
                    self::REASON_DATA_SET_EMPTY
                );
            }
        }

        return new ValidResult($step);
    }

    private function validateStatementData(
        StepInterface $step,
        StatementInterface $action,
        DataParameterInterface $dataParameter
    ): ?ResultInterface {
        $stepData = $step->getData() ?? new DataSetCollection([]);

        foreach ($stepData as $dataSet) {
            $parameterName = $dataParameter->getProperty();

            if (false === $dataSet->hasParameterNames([$parameterName])) {
                return (new InvalidResult($step, ResultType::STEP, self::REASON_DATA_SET_INCOMPLETE))
                    ->withContext([
                        self::CONTEXT_DATA_SET => $dataSet,
                        self::CONTEXT_DATA_PARAMETER_NAME => $dataParameter->getProperty(),
                        self::CONTEXT_STATEMENT => $action,
                    ]);
            }
        }

        return null;
    }
}
