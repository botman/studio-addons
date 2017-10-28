<?php

namespace Tests;

use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\Drivers\Facebook\Extensions\ButtonTemplate;
use BotMan\Drivers\Facebook\Extensions\Element;
use BotMan\Drivers\Facebook\Extensions\ElementButton;
use BotMan\Drivers\Facebook\Extensions\GenericTemplate;
use BotMan\Drivers\Facebook\Extensions\ListTemplate;
use BotMan\Drivers\Facebook\Extensions\ReceiptAddress;
use BotMan\Drivers\Facebook\Extensions\ReceiptElement;
use BotMan\Drivers\Facebook\Extensions\ReceiptSummary;
use BotMan\Drivers\Facebook\Extensions\ReceiptTemplate;
use BotMan\Studio\Testing\ButtonTester;
use Mockery as m;
use BotMan\BotMan\BotMan;
use PHPUnit\Framework\TestCase;
use BotMan\BotMan\BotManFactory;
use BotMan\Studio\Testing\BotManTester;
use BotMan\BotMan\Drivers\Tests\FakeDriver;
use BotMan\BotMan\Messages\Attachments\File;
use BotMan\BotMan\Messages\Attachments\Audio;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Attachments\Video;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Attachments\Location;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;

class BotManTesterTest extends TestCase
{
    /** @var BotManTester */
    protected $tester;

    /** @var m\MockInterface */
    protected $driver;

    /** @var BotMan */
    protected $botman;

    public function tearDown()
    {
        m::close();
    }

    protected function setUp()
    {
        parent::setUp();

        $this->driver = new FakeDriver();

        $this->botman = BotManFactory::create([]);
        $this->botman->setDriver($this->driver);
        $this->tester = new BotManTester($this->botman, $this->driver);
    }

    /** @test */
    public function it_can_fake_incoming_messages()
    {
        $this->botman->hears('message', function ($bot) {
            $bot->reply('hello!');
        });

        $this->tester->receives('message');
        $messages = $this->tester->getMessages();

        $this->assertCount(1, $messages);
        $this->assertInstanceOf(OutgoingMessage::class, $messages[0]);
        $this->assertSame('hello!', $messages[0]->getText());
    }

    /** @test */
    public function it_can_fake_user_information()
    {
        $this->botman->hears('message', function ($bot) {
            $user = $bot->getUser();
            $bot->reply('ID: '.$user->getId());
            $bot->reply('First: '.$user->getFirstName());
            $bot->reply('Last: '.$user->getLastName());
            $bot->reply('User: '.$user->getUserName());
        });

        $this->tester->setUser([
            'id' => 5,
            'first_name' => 'Marcel',
            'last_name' => 'Pociot',
            'username' => 'marcelpociot',
        ]);
        $this->tester->receives('message');
        $messages = $this->tester->getMessages();

        $this->assertCount(4, $messages);
        $this->tester->assertReply('ID: 5');
        $this->tester->assertReply('First: Marcel');
        $this->tester->assertReply('Last: Pociot');
        $this->tester->assertReply('User: marcelpociot');
    }

    /** @test */
    public function it_can_assert_replies()
    {
        $this->botman->hears('message', function ($bot) {
            $bot->reply('hello!');
        });

        $this->tester->receives('message');
        $this->tester->assertReply('hello!');
    }

    /** @test */
    public function it_can_assert_replies_are_not()
    {
        $this->botman->hears('message', function ($bot) {
            $bot->reply('hello!');
        });

        $this->tester->receives('message');
        $this->tester->assertReplyIsNot('!olleh');
    }

    /** @test */
    public function it_can_assert_replies_are_in_an_array()
    {
        $this->botman->hears('message', function ($bot) {
            $bot->reply('hello!');
        });

        $this->tester->receives('message');
        $this->tester->assertReplyIn(['hello!']);
    }

    /** @test */
    public function it_can_assert_replies_are_not_in_an_array()
    {
        $this->botman->hears('message', function ($bot) {
            $bot->reply('hello!');
        });

        $this->tester->receives('message');
        $this->tester->assertReplyNotIn(['olleh!']);
    }

    /** @test */
    public function it_can_assert_generic_replies()
    {
        $this->botman->hears('message', function ($bot) {
            $bot->reply('1');
            $bot->reply('2');
            $bot->reply('3');
        });

        $this->tester->receives('message');
        $this->tester->reply(2)
            ->assertReply('3');
    }

    /** @test */
    public function it_can_assert_replies_are_not_present()
    {
        $this->botman->hears('message', function ($bot) {
        });

        $this->tester->receives('message');
        $this->tester->assertReplyNothing();
    }

    /** @test */
    public function it_can_assert_raw_replies()
    {
        $out = new OutgoingMessage('hello');
        $this->botman->hears('message', function ($bot) use ($out) {
            $bot->reply($out);
        });

        $this->tester->receives('message');
        $this->tester->assertRaw($out);
    }

    /** @test */
    public function it_can_assert_multiple_replies()
    {
        $this->botman->hears('message', function ($bot) {
            $bot->reply('message 1');
            $bot->reply('message 2');
            $bot->reply('message 3');
        });

        $this->tester->receives('message');
        $this->tester->assertReplies([
            'message 1',
            'message 2',
            'message 3',
        ]);
    }

    /** @test */
    public function it_can_fake_interactive_messages()
    {
        $this->botman->hears('message', function ($bot) {
            $bot->ask('question', function ($answer) {
                if ($answer->isInteractiveMessageReply()) {
                    $this->say('success');
                } else {
                    $this->say('failure');
                }
            });
        });

        $this->tester->receives('message');
        $this->tester->receivesInteractiveMessage('answer');
        $this->tester->assertReply('success');
    }

    /** @test */
    public function it_can_fake_locations()
    {
        $this->botman->hears(Location::PATTERN, function ($bot) {
            /** @var Location $location */
            $location = $bot->getMessage()->getLocation();
            $bot->reply('Lat: '.$location->getLatitude());
            $bot->reply('Lng: '.$location->getLongitude());
        });

        $this->tester->receivesLocation();
        $this->tester->assertReply('Lat: 24');
        $this->tester->assertReply('Lng: 57');
    }

    /** @test */
    public function it_can_fake_images()
    {
        $this->botman->hears(Image::PATTERN, function ($bot) {
            $images = $bot->getMessage()->getImages();
            $bot->reply('Image: '.$images[0]->getUrl());
        });

        $this->tester->receivesImages();
        $this->tester->assertReply('Image: https://via.placeholder.com/350x150');

        $this->tester->receivesImages(['https://botman.io/img/logo.png']);
        $this->tester->assertReply('Image: https://botman.io/img/logo.png');
    }

    /** @test */
    public function it_can_fake_videos()
    {
        $this->botman->hears(Video::PATTERN, function ($bot) {
            $videos = $bot->getMessage()->getVideos();
            $bot->reply('Video: '.$videos[0]->getUrl());
        });

        $this->tester->receivesVideos();
        $this->tester->assertReply('Video: https://www.youtube.com/watch?v=4zzSw-0IShE');

        $this->tester->receivesVideos(['https://botman.io/img/video.mp4']);
        $this->tester->assertReply('Video: https://botman.io/img/video.mp4');
    }

    /** @test */
    public function it_can_fake_audio()
    {
        $this->botman->hears(Audio::PATTERN, function ($bot) {
            $audio = $bot->getMessage()->getAudio();
            $bot->reply('Audio: '.$audio[0]->getUrl());
        });

        $this->tester->receivesAudio();
        $this->tester->assertReply('Audio: https://www.youtube.com/watch?v=4zzSw-0IShE');

        $this->tester->receivesAudio(['https://botman.io/img/audio.mp3']);
        $this->tester->assertReply('Audio: https://botman.io/img/audio.mp3');
    }

    /** @test */
    public function it_can_fake_files()
    {
        $this->botman->hears(File::PATTERN, function ($bot) {
            $files = $bot->getMessage()->getFiles();
            $bot->reply('File: '.$files[0]->getUrl());
        });

        $this->tester->receivesFiles();
        $this->tester->assertReply('File: https://www.youtube.com/watch?v=4zzSw-0IShE');

        $this->tester->receivesFiles(['https://botman.io/img/file.zip']);
        $this->tester->assertReply('File: https://botman.io/img/file.zip');
    }

    /** @test */
    public function it_can_fake_events()
    {
        $this->botman->on('event', function ($payload, $bot) {
            $bot->reply('Payload: '.$payload);
        });

        $this->tester->receivesEvent('event', 'payload');
        $this->tester->assertReply('Payload: payload');
    }

    /** @test */
    public function it_can_test_questions()
    {
        $this->botman->hears('message', function ($bot) {
            $bot->ask(Question::create('question'), function ($answer) {
                $this->say('success');
            });
        });

        $this->tester->receives('message');
        $this->tester->assertQuestion();
        $this->tester->receives('answer');
        $this->tester->assertReply('success');

        $this->botman->hears('message', function ($bot) {
            $bot->ask(Question::create('question'), function ($answer) {
                $this->say('success');
            });
        });

        $this->tester->receives('message');
        $this->tester->assertQuestion('question');
        $this->tester->receives('answer');
        $this->tester->assertReply('success');
    }

    /** @test */
    public function it_can_test_question_with_closure()
    {
        $this->botman->hears('message', function ($bot) {
            $bot->ask(Question::create('question'), function ($answer) {
                $this->say('success');
            });
        });

        $this->tester->receives('message');
        $this->tester->assertQuestion(null, function($q) {
            $q->assertText('question');
        });
        $this->tester->receives('answer');
        $this->tester->assertReply('success');
    }

    /** @test */
    public function it_can_test_question_callback_and_fallback()
    {
        $question = Question::create('question')
            ->fallback('fallback')
            ->callbackId('callback_id');
        $this->botman->hears('message', function ($bot) use ($question) {
            $bot->ask($question, function ($answer) {
                $this->say('success');
            });
        });

        $this->tester->receives('message');
        $this->tester->assertQuestion(null, function($q) {
            $q->assertFallback('fallback');
            $q->assertCallbackId('callback_id');
        });
        $this->tester->receives('answer');
        $this->tester->assertReply('success');
    }

    /** @test */
    public function it_can_test_question_button_count()
    {
        $question = Question::create('question')
            ->addButtons([
                Button::create('First')->value('first'),
                Button::create('Second')->value('second'),
            ]);
        $this->botman->hears('message', function ($bot) use ($question) {
            $bot->ask($question, function ($answer) {
                $this->say('success');
            });
        });

        $this->tester->receives('message');
        $this->tester->assertQuestion(null, function($q) {
            $q->assertButtonCount(2);
        });
    }

    /** @test */
    public function it_can_test_question_specific_button()
    {
        $question = Question::create('question')
            ->addButtons([
                Button::create('First')->value('first'),
                Button::create('Second')->value('second'),
            ]);
        $this->botman->hears('message', function ($bot) use ($question) {
            $bot->ask($question, function ($answer) {
                $this->say('success');
            });
        });

        $this->tester->receives('message');
        $this->tester->assertQuestion(null, function($q) {
            $q->assertButton(0, function($b) {
                $b->assertText('First');
                $b->assertValue('first');
            });
            $q->assertButton(1, function($b) {
                $b->assertText('Second');
                $b->assertValue('second');
            });
        });
    }

    /** @test */
    public function it_can_test_question_specific_button_is_not()
    {
        $question = Question::create('question')
            ->addButtons([
                Button::create('First')->value('first'),
                Button::create('Second')->value('second'),
            ]);
        $this->botman->hears('message', function ($bot) use ($question) {
            $bot->ask($question, function ($answer) {
                $this->say('success');
            });
        });

        $this->tester->receives('message');
        $this->tester->assertQuestion(null, function($q) {
            $q->assertButton(0, function($b) {
                $b->assertTextisNot('Second');
                $b->assertValueisNot('second');
            });
            $q->assertButton(1, function($b) {
                $b->assertTextisNot('First');
                $b->assertValueisNot('first');
            });
        });
    }

    /** @test */
    public function it_can_test_question_first_and_last_button()
    {
        $question = Question::create('question')
            ->addButtons([
                Button::create('First')->value('first'),
                Button::create('Second')->value('second'),
                Button::create('Third')->value('third'),
                Button::create('Forth')->value('forth'),
            ]);
        $this->botman->hears('message', function ($bot) use ($question) {
            $bot->ask($question, function ($answer) {
                $this->say('success');
            });
        });

        $this->tester->receives('message');
        $this->tester->assertQuestion(null, function($q) {
            $q->assertFirstButton( function($b) {
                $b->assertText('First');
            });
            $q->assertLastButton(function($b) {
                $b->assertText('Forth');
            });
        });
    }

    /** @test */
    public function it_can_test_template()
    {
        $this->botman->hears('generic', function ($bot){
            $bot->reply(GenericTemplate::create()
                ->addElement(Element::create('title'))
            );
        });

        $this->tester->receives('generic');
        $this->tester->assertTemplate(GenericTemplate::class);
    }

    /** @test */
    public function it_can_test_template_with_closure()
    {
        $this->botman->hears('button', function ($bot){
            $bot->reply(ButtonTemplate::create('text')
                ->addButton(ElementButton::create('First')->type('web_url')->url('www.botman.io'))
            );
        });

        $this->tester->receives('button');
        $this->tester->assertTemplate(ButtonTemplate::class, function($t) {
            $t->assertText('text');
        });
    }

    /** @test */
    public function it_can_test_template_image_aspect_ratio()
    {
        $this->botman->hears('generic', function ($bot){
            $bot->reply(GenericTemplate::create()
                ->addElement(Element::create('title'))
                ->addImageAspectRatio(GenericTemplate::RATIO_HORIZONTAL)
            );
        });

        $this->tester->receives('generic');
        $this->tester->assertTemplate(GenericTemplate::class, function($t) {
            $t->assertImageAspectRatio(GenericTemplate::RATIO_HORIZONTAL);
        });
    }

    /** @test */
    public function it_can_test_list_template_attributes()
    {
        $this->botman->hears('list', function ($bot){
            $bot->reply(ListTemplate::create()
                ->addElement(Element::create('title'))
                ->useCompactView()
                ->addGlobalButton(ElementButton::create('First'))
            );
        });

        $this->tester->receives('list');
        $this->tester->assertTemplate(ListTemplate::class, function($t) {
            $t->assertTopElementStyle('compact');
            $t->assertButtons(function ($b) {
                $b->assertTitle('First');
            });
        });
    }

    /** @test */
    public function it_can_test_template_attributes_is_not()
    {
        $this->botman->hears('button', function ($bot){
            $bot->reply(ButtonTemplate::create('text')
                ->addButton(ElementButton::create('First')->type('web_url')->url('www.botman.io'))
            );
        });

        $this->tester->receives('button');
        $this->tester->assertTemplate(ButtonTemplate::class, function($t) {
            $t->assertTextIsNot('txet');
        });
    }

    /** @test */
    public function it_can_test_template_element_count()
    {
        $this->botman->hears('generic', function ($bot){
            $bot->reply(GenericTemplate::create()
                ->addElements([
                    Element::create('First'),
                    Element::create('Second')
                ])
            );
        });

        $this->tester->receives('generic');
        $this->tester->assertTemplate(GenericTemplate::class, function($t) {
            $t->assertElementCount(2);
        });
    }

    /** @test */
    public function it_can_test_template_specific_element()
    {
        $this->botman->hears('generic', function ($bot){
            $bot->reply(GenericTemplate::create()
                ->addElements([
                    Element::create('First')->subtitle('This number is before "2"')->image('www.one.com/image'),
                    Element::create('Second')
                ])
            );
        });

        $this->tester->receives('generic');
        $this->tester->assertTemplate(GenericTemplate::class, function($t) {
            $t->assertElement(0, function($e) {
                $e->assertTitle('First');
                $e->assertSubtitle('This number is before "2"');
                $e->assertImage('www.one.com/image');
            });
        });
    }

    /** @test */
    public function it_can_test_template_nested_attributes()
    {
        $this->botman->hears('receipt', function ($bot){
            $bot->reply(ReceiptTemplate::create()
                ->recipientName('Marcel')
                ->addElements([
                    ReceiptElement::create('First')->price(1)->currency('EUR'),
                    ReceiptElement::create('Second')->price(2)->currency('EUR')
                ])
                ->addAddress(ReceiptAddress::create()->city('Barcelona'))
                ->addSummary(ReceiptSummary::create()->totalCost(3))
            );
        });

        $this->tester->receives('receipt');
        $this->tester->assertTemplate(ReceiptTemplate::class, function($t) {
            $t->assertAttributes([
                'recipient_name' => 'Marcel',
                'elements.0.title' => 'First',
                'elements.0.price' => 1,
                'elements.0.currency' => 'EUR',
                'elements.1.title' => 'Second',
                'address.city' => 'Barcelona',
                'summary.total_cost' => 3,
            ]);
        });
    }

    /** @test */
    public function it_can_test_template_first_and_last_element()
    {
        $this->botman->hears('generic', function ($bot){
            $bot->reply(GenericTemplate::create()
                ->addElements([
                    Element::create('First'),
                    Element::create('Second'),
                    Element::create('Third'),
                    Element::create('Forth'),
                ])
            );
        });

        $this->tester->receives('generic');
        $this->tester->assertTemplate(GenericTemplate::class, function($t) {
            $t->assertFirstElement(function($e) {
                $e->assertTitle('First');
            });
            $t->assertLastElement(function($e) {
                $e->assertTitle('Forth');
            });
        });
    }

    /** @test */
    public function it_can_test_template_specific_element_specific_button()
    {
        $this->botman->hears('generic', function ($bot){
            $bot->reply(GenericTemplate::create()
                ->addElements([
                    Element::create('First')
                        ->addButton(ElementButton::create('First')
                            ->type('web_url')
                            ->url('www.botman.io')
                            ->enableExtensions()
                            ->heightRatio(ElementButton::RATIO_COMPACT)
                            ->fallbackUrl('www.botman.io/fallback')
                        ),
                    Element::create('Second')
                ])
            );
        });

        $this->tester->receives('generic');
        $this->tester->assertTemplate(GenericTemplate::class, function($t) {
            $t->assertElement(0, function($e) {
                $e->assertButton(0, function($b) {
                    $b->assertTitle('First')
                        ->assertType('web_url')
                        ->assertUrl('www.botman.io')
                        ->assertHeightRatio(ElementButton::RATIO_COMPACT)
                        ->assertMessengerExtension(true)
                        ->assertFallbackUrl('www.botman.io/fallback');
                });
            });
        });
    }

    /** @test */
    public function it_can_test_template_specific_element_first_and_last_button()
    {
        $this->botman->hears('generic', function ($bot){
            $bot->reply(GenericTemplate::create()
                ->addElements([
                    Element::create('First')->addButtons([
                        ElementButton::create('First')->type('web_url')->url('www.botman.io'),
                        ElementButton::create('Second')->type('web_url')->url('www.botman.io'),
                        ElementButton::create('Third')->type('web_url')->url('www.botman.io'),
                    ]),
                    Element::create('Second')
                ])
            );
        });

        $this->tester->receives('generic');
        $this->tester->assertTemplate(GenericTemplate::class, function($t) {
            $t->assertElement(0, function($e) {
                $e->assertFirstButton(function($b) {
                    $b->assertTitle('First');
                });
                $e->assertLastButton(function($b) {
                    $b->assertTitle('Third');
                });
            });
        });
    }
}
