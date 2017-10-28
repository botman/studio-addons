<?php

namespace BotMan\Studio\Testing;

use PHPUnit\Framework\Assert as PHPUnit;

/**
 * Class ElementTester.
 */
class ElementTester
{
    protected $element;

    public function __construct(array $element)
    {
        $this->element = $element;
    }

    public function assertTitle($title)
    {
        PHPUnit::assertSame($title, $this->element['title']);

        return $this;
    }

    public function assertTitleIsNot($title)
    {
        PHPUnit::assertNotSame($title, $this->element['title']);

        return $this;
    }

    public function assertSubtitle($subtitle)
    {
        PHPUnit::assertSame($subtitle, $this->element['subtitle']);

        return $this;
    }

    public function assertSubtitleIsNot($subtitle)
    {
        PHPUnit::assertNotSame($subtitle, $this->element['subtitle']);

        return $this;
    }

    public function assertImage($image_url)
    {
        PHPUnit::assertSame($image_url, $this->element['image_url']);

        return $this;
    }

    public function assertImageIsNot($image_url)
    {
        PHPUnit::assertNotSame($image_url, $this->element['image_url']);

        return $this;
    }

    public function assertButton($index, $closure)
    {
        $button = $this->element['buttons'][$index];
        call_user_func($closure, new ElementButtonTester($button));

        return $this;
    }

    public function assertFirstButton($closure)
    {
        return $this->assertButton(0, $closure);
    }

    public function assertLastButton($closure)
    {
        $last_index = count($this->element['buttons']) - 1;

        return $this->assertButton($last_index, $closure);
    }
}