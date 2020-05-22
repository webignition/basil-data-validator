<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\Unit\Test;

use webignition\BasilDataValidator\ResultType;
use webignition\BasilDataValidator\Step\StepValidator;
use webignition\BasilDataValidator\Test\ConfigurationValidator;
use webignition\BasilDataValidator\Test\TestValidator;
use webignition\BasilModels\Step\Step;
use webignition\BasilModels\Test\Configuration;
use webignition\BasilModels\Test\Test;
use webignition\BasilModels\Test\TestInterface;
use webignition\BasilParser\Test\TestParser;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ValidResult;

class TestValidatorTest extends \PHPUnit\Framework\TestCase
{
    private TestValidator $testValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testValidator = TestValidator::create();
    }

    /**
     * @dataProvider validateNotValidDataProvider
     */
    public function testValidateNotValid(TestInterface $test, InvalidResultInterface $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->testValidator->validate($test));
    }

    public function validateNotValidDataProvider(): array
    {
        $configurationWithEmptyBrowser = new Configuration('', '');
        $testWithInvalidConfiguration = new Test($configurationWithEmptyBrowser, []);

        $validConfiguration = new Configuration('chrome', 'http://example.com/');

        $testWithNoSteps = new Test($validConfiguration, []);

        $invalidStep = new Step([], []);
        $testWithInvalidStep = new Test($validConfiguration, [
            'invalid step name' => $invalidStep,
        ]);

        return [
            'invalid configuration' => [
                'test' => $testWithInvalidConfiguration,
                'expectedResult' => new InvalidResult(
                    $testWithInvalidConfiguration,
                    ResultType::TEST,
                    TestValidator::REASON_CONFIGURATION_INVALID,
                    new InvalidResult(
                        $configurationWithEmptyBrowser,
                        ResultType::TEST_CONFIGURATION,
                        ConfigurationValidator::REASON_BROWSER_EMPTY
                    )
                ),
            ],
            'no steps' => [
                'test' => $testWithNoSteps,
                'expectedResult' => new InvalidResult(
                    $testWithNoSteps,
                    ResultType::TEST,
                    TestValidator::REASON_NO_STEPS
                ),
            ],
            'invalid step' => [
                'test' => $testWithInvalidStep,
                'expectedResult' => (new InvalidResult(
                    $testWithInvalidStep,
                    ResultType::TEST,
                    TestValidator::REASON_STEP_INVALID,
                    new InvalidResult(
                        $invalidStep,
                        ResultType::STEP,
                        StepValidator::REASON_NO_ASSERTIONS
                    )
                ))->withContext([
                    TestValidator::CONTEXT_STEP_NAME => 'invalid step name',
                ]),
            ],
        ];
    }

    public function testValidateIsValid()
    {
        $testParser = TestParser::create();

        $test = $testParser->parse([
            'config' => [
                'browser' => 'chrome',
                'url' => 'http://example.com',
            ],
            'step name' => [
                'actions' => [
                    'click $".selector"',
                ],
                'assertions' => [
                    '$page.title is "Example"',
                ],
            ],
        ]);

        $expectedResult = new ValidResult($test);

        $this->assertEquals($expectedResult, $this->testValidator->validate($test));
    }
}
