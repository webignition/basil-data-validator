<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\Unit;

use webignition\BasilDataValidator\LiteralValueIdentifier;

class LiteralValueIdentifierTest extends \PHPUnit\Framework\TestCase
{
    private LiteralValueIdentifier $literalValueIdentifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->literalValueIdentifier = new LiteralValueIdentifier();
    }

    /**
     * @dataProvider isDataProvider
     */
    public function testIs(string $value, bool $expectedIs)
    {
        $this->assertSame($this->literalValueIdentifier->is($value), $expectedIs);
    }

    public function isDataProvider(): array
    {
        return [
            'empty' => [
                'value' => '',
                'expectedIs' => false,
            ],
            'whitespace' => [
                'value' => '   ',
                'expectedIs' => false,
            ],
            'unquoted' => [
                'value' => 'value',
                'expectedIs' => false,
            ],
            'no ending quote' => [
                'value' => '"value',
                'expectedIs' => false,
            ],
            'no starting quote' => [
                'value' => 'value"',
                'expectedIs' => false,
            ],
            'no ending quote; has escaped quotes' => [
                'value' => '"va\"lu\"e\"',
                'expectedIs' => false,
            ],
            'no starting quote; has escaped quotes' => [
                'value' => '\"va\"lu\"e\""',
                'expectedIs' => false,
            ],
            'quoted' => [
                'value' => '"value"',
                'expectedIs' => true,
            ],
            'quoted; has escaped quotes' => [
                'value' => '"va\"lu\"e"',
                'expectedIs' => true,
            ],
        ];
    }
}
