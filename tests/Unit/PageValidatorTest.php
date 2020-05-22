<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\Unit;

use webignition\BasilDataValidator\PageValidator;
use webignition\BasilDataValidator\ResultType;
use webignition\BasilModels\Page\Page;
use webignition\BasilModels\Page\PageInterface;
use webignition\BasilResolver\PageResolver;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ValidResult;

class PageValidatorTest extends \PHPUnit\Framework\TestCase
{
    private PageValidator $validator;

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
        $pageResolver = PageResolver::createResolver();

        return [
            'url only' => [
                'page' => new Page('import_name', 'http://example.com'),
            ],
            'url and identifiers' => [
                'page' => new Page('import_name', 'http://example.com', [
                    'form' => '$".form"',
                    'input' => '$".input"',
                ]),
            ],
            'url and identifiers, parent >> child' => [
                'page' => new Page('import_name', 'http://example.com', [
                    'form' => '$".form"',
                    'form_input' => '$form >> $".input"',
                ]),
            ],
            'url and identifiers, grandparent > parent > child' => [
                'page' => $pageResolver->resolve(new Page('import_name', 'http://example.com', [
                    'form' => '$".form"',
                    'form_container' => '$form >> $".container"',
                    'form_input' => '$form_container >> $".input"',
                ])),
            ],
            'identifier with position' => [
                'page' => $pageResolver->resolve(new Page('import_name', 'http://example.com', [
                    'form' => '$".form":3',
                ])),
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
                'page' => new Page('import_name', ''),
                'expectedResult' => new InvalidResult(
                    new Page('import_name', ''),
                    ResultType::PAGE,
                    PageValidator::REASON_URL_EMPTY
                ),
            ],
            'invalid identifiers: attribute identifier' => [
                'page' => new Page('import_name', 'http://example.com', [
                    'name' => '$".selector".attribute_name',
                ]),
                'expectedResult' => (new InvalidResult(
                    new Page('import_name', 'http://example.com', [
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
                'page' => new Page('import_name', 'http://example.com', [
                    'name' => '$elements.element_name',
                ]),
                'expectedResult' => (new InvalidResult(
                    new Page('import_name', 'http://example.com', [
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
