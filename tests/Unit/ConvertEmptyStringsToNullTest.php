<?php

namespace Tests\Unit;

use PressbooksMultiInstitution\Support\ConvertEmptyStringsToNull;
use WP_UnitTestCase;

class ConvertEmptyStringsToNullTest extends WP_UnitTestCase
{
    protected ConvertEmptyStringsToNull $handler;

    public function setUp(): void
    {
        $this->handler = app(ConvertEmptyStringsToNull::class);
    }

    /**
     * @test
     */
    public function it_casts_empty_strings_to_null(): void
    {
        $original = [
            'foo' => 'bar',
            'fizz' => '',
        ];

        $expected = [
            'foo' => 'bar',
            'fizz' => null,
        ];

        $this->assertEquals($expected, $this->handler->handle($original));
    }

    /**
     * @test
     */
    public function it_casts_nested_values_to_null(): void
    {
        $original = [
            'foo' => [
                'bar' => '',
                'fizz' => [
                    'buzz' => '',
                ],
            ]
        ];

        $expected = [
            'foo' => [
                'bar' => null,
                'fizz' => [
                    'buzz' => null,
                ]
            ],
        ];

        $this->assertEquals($expected, $this->handler->handle($original));
    }

    /**
     * @test
     */
    public function it_does_not_change_non_strings(): void
    {
        $original = [
            'foo' => [
                'bar' => '',
            ],
            'fuzz' => [
                42,
                'some regular text',
            ],
            'fizz' => false,
            'buzz' => null,
        ];

        $expected = [
            'foo' => [
                'bar' => null,
            ],
            'fuzz' => [
                42,
                'some regular text',
            ],
            'fizz' => false,
            'buzz' => null,
        ];

        $this->assertEquals($expected, $this->handler->handle($original));
    }

    /**
     * @test
     */
    public function it_sanitizes_text_strings(): void
    {
        $original = [
            'foo' => [
                'bar' => 'Some <strong>random</strong> string',
            ],
            'fuzz' => 'I\'m adding a <script>alert(\'hello\')</script> tag here',
        ];

        $expected = [
            'foo' => [
                'bar' => 'Some random string',
            ],
            'fuzz' => 'I\'m adding a tag here',
        ];

        $this->assertEquals($expected, $this->handler->handle($original));
    }
}
