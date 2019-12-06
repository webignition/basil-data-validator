<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\Unit\Test;

use webignition\BasilDataValidator\ResultType;
use webignition\BasilDataValidator\Test\ConfigurationValidator;
use webignition\BasilModels\Test\Configuration;
use webignition\BasilModels\Test\ConfigurationInterface;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ValidResult;

class ConfigurationValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigurationValidator
     */
    private $configurationValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationValidator = ConfigurationValidator::create();
    }

    /**
     * @dataProvider validateNotValidDataProvider
     */
    public function testValidateNotValid(ConfigurationInterface $configuration, InvalidResultInterface $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->configurationValidator->validate($configuration));
    }

    public function validateNotValidDataProvider(): array
    {
        return [
            'browser empty' => [
                'configuration' => new Configuration('', ''),
                'expectedResult' => new InvalidResult(
                    new Configuration('', ''),
                    ResultType::TEST_CONFIGURATION,
                    ConfigurationValidator::REASON_BROWSER_EMPTY
                ),
            ],
            'browser whitespace-only' => [
                'configuration' => new Configuration('   ', ''),
                'expectedResult' => new InvalidResult(
                    new Configuration('   ', ''),
                    ResultType::TEST_CONFIGURATION,
                    ConfigurationValidator::REASON_BROWSER_EMPTY
                ),
            ],
            'url is page url reference' => [
                'configuration' => new Configuration('chrome', 'page_import_name.url'),
                'expectedResult' => new InvalidResult(
                    new Configuration('chrome', 'page_import_name.url'),
                    ResultType::TEST_CONFIGURATION,
                    ConfigurationValidator::REASON_URL_IS_PAGE_URL_REFERENCE
                ),
            ],
        ];
    }

    public function testValidateIsValid()
    {
        $configuration = new Configuration('chrome', 'http://example.com/');

        $expectedResult = new ValidResult($configuration);

        $this->assertEquals($expectedResult, $this->configurationValidator->validate($configuration));
    }
}