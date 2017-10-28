<?php

namespace BotMan\Studio\Testing;

use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Facebook\Extensions\Element;
use PHPUnit\Framework\Assert as PHPUnit;

/**
 * Class ElementButtonTester.
 */
class ElementButtonTester
{

    protected $button;

    public function __construct(array $button)
    {
        $this->button = $button;

        return $this;
    }

    public function assertTitle($title)
    {
        PHPUnit::assertSame($title, $this->button['title']);

        return $this;
    }

    public function assertTitleIsNot($title)
    {
        PHPUnit::assertNotSame($title, $this->button['title']);

        return $this;
    }

    public function assertType($type)
    {
        PHPUnit::assertSame($type, $this->button['type']);

        return $this;
    }

    public function assertTypeIsNot($type)
    {
        PHPUnit::assertNotSame($type, $this->button['type']);

        return $this;
    }

    public function assertUrl($url)
    {
        PHPUnit::assertSame($url, $this->button['url']);

        return $this;
    }

    public function assertUrlIsNot($url)
    {
        PHPUnit::assertNotSame($url, $this->button['url']);

        return $this;
    }

    public function assertPayload($payload)
    {
        PHPUnit::assertSame($payload, $this->button['payload']);

        return $this;
    }

    public function assertPayloadIsNot($payload)
    {
        PHPUnit::assertNotSame($payload, $this->button['payload']);

        return $this;
    }


    public function assertHeightRatio($webview_height_ratio)
    {
        PHPUnit::assertSame($webview_height_ratio, $this->button['webview_height_ratio']);

        return $this;
    }

    public function assertMessengerExtension($messenger_extensions = true)
    {
        PHPUnit::assertSame($messenger_extensions, $this->button['messenger_extensions']);

        return $this;
    }

    public function assertFallbackUrl($fallback_url)
    {
        PHPUnit::assertSame($fallback_url, $this->button['fallback_url']);

        return $this;
    }

    public function assertShareContents($closure)
    {
        $share_content = $this->button['share_contents'];
        call_user_func($closure, new TemplateTester($share_content));

        return $this;
    }
}