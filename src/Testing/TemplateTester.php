<?php

namespace BotMan\Studio\Testing;

use PHPUnit\Framework\Assert as PHPUnit;

/**
 * Class TemplateTester.
 */
class TemplateTester
{
    protected $payload;

    public function __construct($template)
    {
        $this->payload = $template->toArray()['attachment']['payload'];
    }

    public function assertText($text)
    {
        PHPUnit::assertSame($text, $this->payload['text']);

        return $this;
    }

    public function assertTextIsNot($text)
    {
        PHPUnit::assertNotSame($text, $this->payload['text']);

        return $this;
    }

//    public function assertSharable($sharable)
//    {
//        PHPUnit::assertSame($sharable, $this->payload['sharable']);
//
//        return $this;
//    }

    public function assertImageAspectRatio($image_aspect_ratio)
    {
        PHPUnit::assertSame($image_aspect_ratio, $this->payload['image_aspect_ratio']);

        return $this;
    }

    public function assertTopElementStyle($top_element_style)
    {
        PHPUnit::assertSame($top_element_style, $this->payload['top_element_style']);

        return $this;
    }

    public function assertButtons($closure)
    {
        $button = $this->payload['buttons'][0];
        call_user_func($closure, new ElementButtonTester($button));

        return $this;
    }

    public function assertElementCount($count)
    {
        PHPUnit::assertCount($count, $this->payload['elements']);

        return $this;
    }

    public function assertElement($index, $closure)
    {
        $element = $this->payload['elements'][$index];
        call_user_func($closure, new ElementTester($element));

        return $this;
    }

    public function assertFirstElement($closure)
    {
        return $this->assertElement(0, $closure);
    }

    public function assertLastElement($closure)
    {
        $last_index = count($this->payload['elements']) - 1;

        return $this->assertElement($last_index, $closure);
    }

    public function assertAttributes($attributes)
    {
        foreach ($attributes as $key => $value) {
            PHPUnit::assertSame($value, array_get($this->payload, $key));
        }

        return $this;
    }
}