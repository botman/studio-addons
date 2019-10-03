<?php

namespace Tests;

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

class TemplateFake
{
    public $text;

    public function __construct($text)
    {
        $this->text = $text;
    }
}

class BotManTesterTest extends TestCase
{
    /** @var BotManTester */
    protected $tester;

    /** @var m\MockInterface */
    protected $driver;

    /** @var BotMan */
    protected $botman;

    public function tearDown() :void
    {
        m::close();
    }

    protected function setUp() :void
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
    public function it_can_assert_a_template_class()
    {
        $this->botman->hears('message', function ($bot) {
            $bot->reply(new TemplateFake('my message'));
        });

        $this->tester->receives('message');
        $this->tester->assertTemplate(TemplateFake::class);
    }

    /** @test */
    public function it_can_assert_a_template_object()
    {
        $this->botman->hears('message', function ($bot) {
            $bot->reply(new TemplateFake('my message'));
        });

        $this->tester->receives('message');
        $this->tester->assertTemplate(new TemplateFake('my message'), true);
    }

    /** @test */
    public function it_can_assert_a_template_is_in_an_array()
    {
        $this->botman->hears('message', function ($bot) {
            $bot->reply(new TemplateFake('message1'));
        });

        $templates = [
            new TemplateFake('message1'),
            new TemplateFake('message2'),
            new TemplateFake('message3'),
        ];

        $this->tester->receives('message');
        $this->tester->assertTemplateIn($templates);
    }

    /** @test */
    public function it_can_assert_a_template_is_not_in_an_array()
    {
        $this->botman->hears('message', function ($bot) {
            $bot->reply(new TemplateFake('message4'));
        });

        $templates = [
            new TemplateFake('message1'),
            new TemplateFake('message2'),
            new TemplateFake('message3'),
        ];

        $this->tester->receives('message');
        $this->tester->assertTemplateNotIn($templates);
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
}
