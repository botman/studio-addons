<?php

namespace BotMan\Studio\Testing;

use PHPUnit\Framework\Assert as PHPUnit;

/**
 * Class QuestionTester.
 */
class ButtonTester
{

    protected $button;

    public function __construct(array $button)
    {
        $this->button = $button;
    }

    public function assertText($text)
    {
        PHPUnit::assertSame($text, $this->button['text']);

        return $this;
    }

    public function assertTextIsNot($text)
    {
        PHPUnit::assertNotSame($text, $this->button['text']);

        return $this;
    }

    public function assertName($name)
    {
        PHPUnit::assertSame($name, $this->button['name']);

        return $this;
    }

    public function assertNameIsNot($name)
    {
        PHPUnit::assertNotSame($name, $this->button['name']);

        return $this;
    }

    public function assertValue($value)
    {
        PHPUnit::assertSame($value, $this->button['value']);

        return $this;
    }

    public function assertValueIsNot($value)
    {
        PHPUnit::assertNotSame($value, $this->button['value']);

        return $this;
    }

    public function assertImage($image_url)
    {
        PHPUnit::assertSame($image_url, $this->button['image_url']);

        return $this;
    }

    public function assertImageIsNot($image_url)
    {
        PHPUnit::assertNotSame($image_url, $this->button['image_url']);

        return $this;
    }

    public function assertAdditional($additional)
    {
        PHPUnit::assertEqual($additional, $this->button['additional']);

        return $this;
    }

    public function assertAdditionalIsNot($additional)
    {
        PHPUnit::assertNotEqual($additional, $this->button['additional']);

        return $this;
    }
}