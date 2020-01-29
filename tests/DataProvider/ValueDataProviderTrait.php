<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\DataProvider;

use webignition\BasilDataValidator\ValueValidator;

trait ValueDataProviderTrait
{
    public function invalidValueDataProvider(): array
    {
        return [
            'element reference' => [
                'value' => '$elements.element_name',
                'expectedReason' => ValueValidator::REASON_INVALID,
            ],
            'attribute reference' => [
                'value' => '$elements.element_name.attribute_name',
                'expectedReason' => ValueValidator::REASON_INVALID,
            ],
            'page element reference' => [
                'value' => '$page_import_name.elements.element_name',
                'expectedReason' => ValueValidator::REASON_INVALID,
            ],
            'invalid page property' => [
                'value' => '$page.foo',
                'expectedReason' => ValueValidator::REASON_PROPERTY_INVALID,
            ],
            'invalid browser property name' => [
                'value' => '$browser.foo',
                'expectedReason' => ValueValidator::REASON_PROPERTY_INVALID,
            ],
            'unquoted' => [
                'value' => 'value',
                'expectedReason' => ValueValidator::REASON_INVALID,
            ],
            'misquoted' => [
                'value' => '"value',
                'expectedReason' => ValueValidator::REASON_INVALID,
            ],
        ];
    }

    public function validValueDataProvider(): array
    {
        return [
            'quoted literal' => [
                'value' => '"value"',
            ],
            'escaped quoted literal' => [
                'value' => '"va\"l\"ue"',
            ],
            'data parameter' => [
                'value' => '$data.value',
            ],
            'page property, url' => [
                'value' => '$page.url',
            ],
            'page property, title' => [
                'value' => '$page.title',
            ],
            'browser property, size' => [
                'value' => '$browser.size',
            ],
            'element dom identifier' => [
                'value' => '$".selector"',
            ],
            'attribute dom identifier' => [
                'value' => '$".selector".attribute_name',
            ],
            'descendant dom identifier' => [
                'value' => '$"{{ $".parent" }} .child"',
            ],
            'environment parameter' => [
                'value' => '$env.KEY',
            ],
        ];
    }
}
