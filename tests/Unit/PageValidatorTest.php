<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\Unit;

use webignition\BasilDataValidator\PageValidator;
use webignition\BasilDataValidator\ResultType;
use webignition\BasilModels\Page\Page;
use webignition\BasilModels\Page\PageInterface;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ValidResult;

class PageValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PageValidator
     */
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = PageValidator::create();
    }

    /**
     * @dataProvider validateIsValidDataProvider
     */
    public function testValidateIsValid(PageInterface $page)
    {
        $this->assertEquals(new ValidResult($page), $this->validator->validate($page));
    }

    public function validateIsValidDataProvider(): array
    {
        return [
            'url only' => [
                'page' => new Page('http://example.com'),
            ],
            'url and identifiers' => [
                'page' => new Page('http://example.com', [
                    'form' => '$".form"',
                    'form_input' => '$"{{ form }} .input"',
                ]),
            ],
        ];
    }

    /**
     * @dataProvider validateNotValidDataProvider
     */
    public function testValidateNotValid(PageInterface $page, InvalidResultInterface $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->validator->validate($page));
    }

    public function validateNotValidDataProvider(): array
    {
        return [
            'url empty' => [
                'page' => new Page(''),
                'expectedResult' => new InvalidResult(
                    new Page(''),
                    ResultType::PAGE,
                    PageValidator::REASON_URL_EMPTY
                ),
            ],
            'invalid identifiers: attribute identifier' => [
                'page' => new Page('http://example.com', [
                    'name' => '$".selector".attribute_name',
                ]),
                'expectedResult' => (new InvalidResult(
                    new Page('http://example.com', [
                        'name' => '$".selector".attribute_name',
                    ]),
                    ResultType::PAGE,
                    PageValidator::REASON_IDENTIFIER_INVALID
                ))->withContext([
                    PageValidator::CONTEXT_NAME => 'name',
                    PageValidator::CONTEXT_IDENTIFIER => '$".selector".attribute_name',
                ]),
            ],
            'invalid identifiers: element reference' => [
                'page' => new Page('http://example.com', [
                    'name' => '$elements.element_name',
                ]),
                'expectedResult' => (new InvalidResult(
                    new Page('http://example.com', [
                        'name' => '$elements.element_name',
                    ]),
                    ResultType::PAGE,
                    PageValidator::REASON_IDENTIFIER_INVALID
                ))->withContext([
                    PageValidator::CONTEXT_NAME => 'name',
                    PageValidator::CONTEXT_IDENTIFIER => '$elements.element_name',
                ]),
            ],
        ];
    }
}
