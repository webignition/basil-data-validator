<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Test;

use webignition\BasilDataValidator\ResultType;
use webignition\BasilDataValidator\Step\StepValidator;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilModels\Test\TestInterface;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ResultInterface;
use webignition\BasilValidationResult\ValidResult;

class TestValidator
{
    public const REASON_CONFIGURATION_INVALID = 'test-configuration-invalid';
    public const REASON_NO_STEPS = 'test-no-steps';
    public const REASON_STEP_INVALID = 'test-step-invalid';
    public const CONTEXT_STEP_NAME = 'step-name';

    private ConfigurationValidator $configurationValidator;
    private StepValidator $stepValidator;

    public function __construct(ConfigurationValidator $configurationValidator, StepValidator $stepValidator)
    {
        $this->configurationValidator = $configurationValidator;
        $this->stepValidator = $stepValidator;
    }

    public static function create(): TestValidator
    {
        return new TestValidator(
            ConfigurationValidator::create(),
            StepValidator::create()
        );
    }

    public function validate(TestInterface $test): ResultInterface
    {
        $configurationValidationResult = $this->configurationValidator->validate($test->getConfiguration());
        if ($configurationValidationResult instanceof InvalidResultInterface) {
            return $this->createInvalidResult(
                $test,
                self::REASON_CONFIGURATION_INVALID,
                $configurationValidationResult
            );
        }

        $steps = $test->getSteps();
        if (0 === count($steps)) {
            return $this->createInvalidResult($test, self::REASON_NO_STEPS);
        }

        foreach ($steps as $name => $step) {
            if ($step instanceof StepInterface) {
                $stepValidationResult = $this->stepValidator->validate($step);

                if ($stepValidationResult instanceof InvalidResultInterface) {
                    return $this->createInvalidResult(
                        $test,
                        self::REASON_STEP_INVALID,
                        $stepValidationResult
                    )->withContext([
                        self::CONTEXT_STEP_NAME => $name,
                    ]);
                }
            }
        }

        $steps->rewind();

        return new ValidResult($test);
    }

    private function createInvalidResult(
        TestInterface $test,
        string $reason,
        ?InvalidResultInterface $invalidResult = null
    ): InvalidResultInterface {
        return new InvalidResult($test, ResultType::TEST, $reason, $invalidResult);
    }
}
