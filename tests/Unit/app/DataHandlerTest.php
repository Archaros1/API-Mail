<?php

namespace tests\Unit\App;

use App\DataHandler;
use App\Mail\Mail as BaseMail;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;

class DataHandlerTest extends TestCase
{
    public static $dispatchSuccess;
    private $handler;
    private $request;
    private $job;

    protected function setUp(): void
    {
        $this->handler = new DataHandler();

        $this->request = $this->getMockBuilder('Illuminate\Http\Request')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
    }

    /**
     * @dataProvider queueSMSProvider
     *
     * @param mixed      $phoneNumber
     * @param mixed      $text
     * @param mixed      $validateStatus
     * @param mixed      $phoneArray
     * @param mixed      $expectedResponse
     * @param mixed      $dispatchSuccess
     * @param mixed|null $expectedResponseButQueueFails
     * @param mixed      $expectedPhoneArray
     */
    public function testqueueSMS($phoneNumber, $text, $validateStatus, $expectedPhoneArray, $expectedResponse, $dispatchSuccess = true, $expectedResponseButQueueFails = null)
    {
        $this->request->tel = $phoneNumber;
        $this->request->text = $text;
        self::$dispatchSuccess = $dispatchSuccess;

        $validateSMS = self::getMethod('validateSMS');
        $dataToArray = self::getMethod('dataToArray');

        $responseValidateSMS = $validateSMS->invokeArgs($this->handler, [$phoneNumber, $text]);
        $this->assertEquals($validateStatus, $responseValidateSMS);

        if ('success' === $validateStatus['status']) {
            $phoneArray = $dataToArray->invokeArgs($this->handler, [$phoneNumber]);
            $this->assertEquals($expectedPhoneArray, $phoneArray);

            $this->request
                ->expects($this->once())
                ->method('get')
                ->with('log');
        }

        if (false === $dispatchSuccess) {
            $formattedExpectedResponse = [
                'status' => 'error',
                'datas' => [
                    'errors' => ['Failed queueing.'],
                ],
            ];
            $expectedResponse = $expectedResponseButQueueFails;
        }

        $response = $this->handler->queueSMS($this->request);
        $this->assertSame($expectedResponse->getContent(), $response->getContent());
    }

    /**
     * @dataProvider queueMailProvider
     *
     * @param mixed      $from
     * @param mixed      $to
     * @param mixed      $cc
     * @param mixed      $subject
     * @param mixed      $html
     * @param mixed      $validateStatus
     * @param mixed      $toArray
     * @param mixed      $ccArray
     * @param mixed      $expectedMail
     * @param mixed      $expectedResponse
     * @param mixed      $dispatchSuccess
     * @param mixed|null $expectedResponseButQueueFails
     * @param mixed      $expectedToArray
     * @param mixed      $expectedCcArray
     */
    public function testqueueMail($from, $to, $cc, $subject, $html, $validateStatus, $expectedToArray, $expectedCcArray, $expectedMail, $expectedResponse, $dispatchSuccess = true, $expectedResponseButQueueFails = null)
    {
        $this->request->from = $from;
        $this->request->to = $to;
        $this->request->cc = $cc;
        $this->request->subject = $subject;
        $this->request->html = $html;
        self::$dispatchSuccess = $dispatchSuccess;

        $validateMail = self::getMethod('validateMail');
        $dataToArray = self::getMethod('dataToArray');
        $makeMail = self::getMethod('makeMail');

        $responseValidateMail = $validateMail->invokeArgs($this->handler, [$from, $to, $cc, $subject, $html]);
        $this->assertEquals($validateStatus, $responseValidateMail);

        if ('success' === $validateStatus['status']) {
            $toArray = $dataToArray->invokeArgs($this->handler, [$to]);
            $this->assertEquals($expectedToArray, $toArray);

            if (!empty($cc) && '' !== $cc) {
                $ccArray = $dataToArray->invokeArgs($this->handler, [$cc]);
            } else {
                $ccArray = [];
            }
            $this->assertEquals($expectedCcArray, $ccArray);

            $responseMakeMail = $makeMail->invokeArgs($this->handler, [$from, $subject, $html]);
            $this->assertEquals($expectedMail->data, $responseMakeMail->data);

            $this->request
                ->expects($this->once())
                ->method('get')
                ->with('log');

            if (false === $dispatchSuccess) {
                $formattedExpectedResponse = [
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Failed queueing.'],
                    ],
                ];
                $expectedResponse = $expectedResponseButQueueFails;
            }
        }

        $response = $this->handler->queueMail($this->request);
        $this->assertSame($expectedResponse->getContent(), $response->getContent());
    }

    /**
     * @dataProvider makeMailProvider
     *
     * @param string      $from
     * @param string|null $subject
     * @param string      $html
     * @param mixed       $expectedResponse
     */
    /* public function testmakeMail(string $from, $subject, string $html, $expectedMail)
    {
        $makeMail = self::getMethod('makeMail');
        $dataHandler = new DataHandler();

        $mail = $makeMail->invokeArgs($dataHandler, [$from, $subject, $html]);
        $this->assertEquals($expectedMail->data, $mail->data);
    } */

    /**
     * @dataProvider validateMailProvider
     *
     * @param mixed $from
     * @param mixed $to
     * @param mixed $cc
     * @param mixed $subject
     * @param mixed $html
     * @param mixed $expectedResponse
     */
    /* public function testvalidateMail($from, $to, $cc, $subject, $html, $expectedResponse)
    {
        $validateMail = self::getMethod('validateMail');
        $dataHandler = new DataHandler();

        $response = $validateMail->invokeArgs($dataHandler, [$from, $to, $cc, $subject, $html]);
        $this->assertEquals($expectedResponse, $response);
    } */

    /**
     * @dataProvider validateSMSProvider
     *
     * @param array|string $phoneNumbers
     * @param string       $text
     * @param mixed        $expectedResponse
     */
    /* public function testvalidateSMS($phoneNumbers, $text, $expectedResponse)
    {
        $validateSMS = self::getMethod('validateSMS');
        $dataHandler = new DataHandler();

        $response = $validateSMS->invokeArgs($dataHandler, [$phoneNumbers, $text]);
        $this->assertEquals($expectedResponse, $response);
    } */

    /**
     * @dataProvider dataToArrayProvider
     *
     * @param array|string $data
     * @param mixed        $expectedResponse
     */
    /* public function testdataToArray($data, $expectedResponse)
    {
        $dataToArray = self::getMethod('dataToArray');
        $dataHandler = new DataHandler();

        $response = $dataToArray->invokeArgs($dataHandler, [$data]);
        $this->assertEquals($expectedResponse, $response);
    } */

    public function queueSMSProvider()
    {
        return
        [
            [ // 0 : pure success
                '0650346154, 0650346154',
                'aaa',
                [
                    'status' => 'success',
                    'datas' => ['errors' => []],
                ],
                [
                    '0650346154',
                    '0650346154',
                ],
                new Response(json_encode([
                    'status' => 'success',
                    'datas' => ['errors' => []], // the test can't dispatch jobs tho
                ]), 200),
            ],
            [ // 1 : pure success with array of number
                [
                    '0650346154',
                    '0650346154',
                ],
                'aaa',
                [
                    'status' => 'success',
                    'datas' => ['errors' => []],
                ],
                [
                    '0650346154',
                    '0650346154',
                ],
                new Response(json_encode([
                    'status' => 'success',
                    'datas' => ['errors' => []], // the test can't dispatch jobs tho
                ]), 200),
            ],
            [ // 2 : invalid phone number exception
                '0123456789999',
                'aaa',
                [
                    'status' => 'error',
                    'datas' => ['errors' => ['Invalid phone number exception : 0123456789999 is invalid.']],
                ],
                [],
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => ['errors' => ['Invalid phone number exception : 0123456789999 is invalid.']],
                ]), 400),
            ],
            [ // 3 : missing text exception
                '0123456789',
                '',
                [
                    'status' => 'error',
                    'datas' => ['errors' => ['Missing text exception.']],
                ],
                ['0123456789'],
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => ['errors' => ['Missing text exception.']],
                ]), 400),
            ],
            [ // 4 : many exceptions at the same time
                '0123456789999',
                '',
                [
                    'status' => 'error',
                    'datas' => ['errors' => [
                        'Invalid phone number exception : 0123456789999 is invalid.',
                        'Missing text exception.',
                    ]],
                ],
                [],
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => ['errors' => [
                        'Invalid phone number exception : 0123456789999 is invalid.',
                        'Missing text exception.',
                    ]],
                ]), 400),
            ],
            [ // 5 : one of the phone numbers is invalid, also text is missing
                '0123456789, 0123456789999',
                '',
                [
                    'status' => 'error',
                    'datas' => ['errors' => [
                        'Invalid phone number exception : 0123456789999 is invalid.',
                        'Missing text exception.',
                    ]],
                ],
                [],
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => ['errors' => [
                        'Invalid phone number exception : 0123456789999 is invalid.',
                        'Missing text exception.',
                    ]],
                ]), 400),
            ],
            [ // 6 : missing phone number exception as number is ''
                '',
                'aaa',
                [
                    'status' => 'error',
                    'datas' => ['errors' => [
                        'Missing phone number exception.', ]],
                    ],
                [],
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => ['errors' => [
                        'Missing phone number exception.', ]],
                ]), 400),
            ],
            [ // 7 : missing phone number exception as number []
                [],
                'aaa',
                [
                    'status' => 'error',
                    'datas' => ['errors' => [
                        'Missing phone number exception.', ]],
                    ],
                [],
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => ['errors' => [
                        'Missing phone number exception.', ]],
                ]), 400),
            ],
            [ // 8 : missing phone number exception as number []
                [
                    '0650346154',
                    '',
                ],
                'aaa',
                [
                    'status' => 'error',
                    'datas' => ['errors' => [
                        'Missing phone number exception.', ]],
                    ],
                [],
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => ['errors' => [
                        'Missing phone number exception.', ]],
                ]), 400),
            ],
            [ // 9 : fail queueing
                '0650346154, 0650346154',
                'aaa',
                [
                    'status' => 'success',
                    'datas' => ['errors' => []],
                ],
                [
                    '0650346154',
                    '0650346154',
                ],
                new Response(json_encode([
                    'status' => 'success',
                    'datas' => ['errors' => []],
                ]), 200),
                false,
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => ['errors' => ['Failed queueing.']],
                ]), 503),
            ],
        ];
    }

    public function queueMailProvider()
    {
        return [
            [ // 0 : pure success without cc
                'from@test.fr',
                'to@test.fr',
                '',
                'subject',
                'lorem ipsum',
                [
                    'status' => 'success',
                    'datas' => [
                        'errors' => [],
                    ],
                ],
                ['to@test.fr'],
                [],
                new BaseMail([
                    'from' => 'from@test.fr',
                    'subject' => 'subject',
                    'html' => 'lorem ipsum',
                ]),
                new Response(json_encode([
                    'status' => 'success',
                    'datas' => [
                        'errors' => [],
                    ],
                ]), 200),
            ],
            [ // 1 : pure success with array to and cc
                'from@test.fr',
                ['to@test.fr'],
                ['cc@test.fr'],
                'subject',
                'lorem ipsum',
                [
                    'status' => 'success',
                    'datas' => [
                        'errors' => [],
                    ],
                ],
                ['to@test.fr'],
                ['cc@test.fr'],
                new BaseMail([
                    'from' => 'from@test.fr',
                    'subject' => 'subject',
                    'html' => 'lorem ipsum',
                ]),
                new Response(json_encode([
                    'status' => 'success',
                    'datas' => [
                        'errors' => [],
                    ],
                ]), 200),
            ],
            [ // 2 : pure success with cc
                'from@test.fr',
                'to@test.fr',
                'cc@test.fr',
                'subject',
                'lorem ipsum',
                [
                    'status' => 'success',
                    'datas' => [
                        'errors' => [],
                    ],
                ],
                ['to@test.fr'],
                ['cc@test.fr'],
                new BaseMail([
                    'from' => 'from@test.fr',
                    'subject' => 'subject',
                    'html' => 'lorem ipsum',
                ]),
                new Response(json_encode([
                    'status' => 'success',
                    'datas' => [
                        'errors' => [],
                    ],
                ]), 200),
            ],
            [ // 3 : invalid from exception
                '',
                'to@test.fr',
                'cc@test.fr',
                'subject',
                'lorem ipsum',
                [
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Invalid from exception.'],
                    ],
                ],
                ['to@test.fr'],
                ['cc@test.fr'],
                new BaseMail([
                    'from' => '',
                    'subject' => 'subject',
                    'html' => 'lorem ipsum',
                ]),
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Invalid from exception.'],
                    ],
                ]), 400),
            ],
            [ // 4 : missing to exception
                'from@test.fr',
                '',
                'cc@test.fr',
                'subject',
                'lorem ipsum',
                [
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Missing to exception.'],
                    ],
                ],
                [],
                ['cc@test.fr'],
                new BaseMail([
                    'from' => 'from@test.fr',
                    'subject' => 'subject',
                    'html' => 'lorem ipsum',
                ]),
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Missing to exception.'],
                    ],
                ]), 400),
            ],
            [ // 5 : missing html exception
                'from@test.fr',
                'to@test.fr',
                'cc@test.fr',
                'subject',
                '',
                [
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Missing html exception.'],
                    ],
                ],
                ['to@test.fr'],
                ['cc@test.fr'],
                new BaseMail([
                    'from' => 'from@test.fr',
                    'subject' => 'subject',
                    'html' => '',
                ]),
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Missing html exception.'],
                    ],
                ]), 400),
            ],
            [ // 6 : test many exceptions at the same time
                '',
                '',
                '',
                4,
                '',
                [
                    'status' => 'error',
                    'datas' => [
                        'errors' => [
                            'Invalid from exception.',
                            'Missing to exception.',
                            'Invalid subject exception.',
                            'Missing html exception.',
                        ],
                    ],
                ],
                [],
                [],
                new BaseMail([
                    'from' => '',
                    'subject' => '',
                    'html' => '',
                ]),
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => [
                        'errors' => [
                            'Invalid from exception.',
                            'Missing to exception.',
                            'Invalid subject exception.',
                            'Missing html exception.',
                        ],
                    ],
                ]), 400),
            ],
            [ // 7 : invalid to exception
                'from@test.fr',
                'to.fr',
                '',
                'subject',
                'lorem ipsum',
                [
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Invalid to exception.'],
                    ],
                ],
                ['to.fr'],
                [],
                new BaseMail([
                    'from' => 'from@test.fr',
                    'subject' => 'subject',
                    'html' => 'lorem ipsum',
                ]),
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Invalid to exception.'],
                    ],
                ]), 400),
            ],
            [ // 8 : invalid to exception
                'from@test.fr',
                ['to@test.fr', 9],
                '',
                'subject',
                'lorem ipsum',
                [
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Invalid to exception.'],
                    ],
                ],
                ['to.fr'],
                [],
                new BaseMail([
                    'from' => 'from@test.fr',
                    'subject' => 'subject',
                    'html' => 'lorem ipsum',
                ]),
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Invalid to exception.'],
                    ],
                ]), 400),
            ],
            [ // 9 : invalid cc exception
                'from@test.fr',
                'to@test.fr',
                'cc.fr',
                'subject',
                'lorem ipsum',
                [
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Invalid cc exception.'],
                    ],
                ],
                ['to@test.fr'],
                ['cc.fr'],
                new BaseMail([
                    'from' => 'from@test.fr',
                    'subject' => 'subject',
                    'html' => 'lorem ipsum',
                ]),
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Invalid cc exception.'],
                    ],
                ]), 400),
            ],
            [ // 10 : invalid subject exception
                'from@test.fr',
                'to@test.fr',
                'cc@test.fr',
                '',
                'lorem',
                [
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Invalid subject exception.'],
                    ],
                ],
                ['to@test.fr'],
                ['cc@test.fr'],
                new BaseMail([
                    'from' => 'from@test.fr',
                    'subject' => '',
                    'html' => '',
                ]),
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Invalid subject exception.'],
                    ],
                ]), 400),
            ],
            [ // 11 : fail queueing
                'from@test.fr',
                'to@test.fr',
                '',
                'subject',
                'lorem ipsum',
                [
                    'status' => 'success',
                    'datas' => [
                        'errors' => [],
                    ],
                ],
                ['to@test.fr'],
                [],
                new BaseMail([
                    'from' => 'from@test.fr',
                    'subject' => 'subject',
                    'html' => 'lorem ipsum',
                ]),
                new Response(json_encode([
                    'status' => 'success',
                    'datas' => [
                        'errors' => [],
                    ],
                ]), 200),
                false,
                new Response(json_encode([
                    'status' => 'error',
                    'datas' => [
                        'errors' => ['Failed queueing.'],
                    ],
                ]), 503),
            ],
        ];
    }

    protected static function getMethod($name)
    {
        $class = new \ReflectionClass('\App\DataHandler');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /*     public function makeMailProvider()
        {
            return [
                [ // 0 : success
                    'test@test.fr',
                    'hello',
                    'lorem ipsum',
                    new BaseMail([
                        'from' => 'test@test.fr',
                        'subject' => 'hello',
                        'html' => 'lorem ipsum'
                    ])
                ],
            ];
        }

        public function validateMailProvider()
        {
            return [
                [ // 0 : pure success without cc
                    'from@test.fr',
                    'to@test.fr',
                    '',
                    'subject',
                    'lorem ipsum',
                    [
                        'status' => 'success',
                        'datas' => [
                            'errors' => [],
                        ],
                    ],
                ],
                [ // 1 : pure success with array to and cc
                    'from@test.fr',
                    ['to@test.fr'],
                    ['cc@test.fr'],
                    'subject',
                    'lorem ipsum',
                    [
                        'status' => 'success',
                        'datas' => [
                            'errors' => [],
                        ],
                    ],
                ],
                [ // 2 : pure success with cc
                    'from@test.fr',
                    'to@test.fr',
                    'cc@test.fr',
                    'subject',
                    'lorem ipsum',
                    [
                        'status' => 'success',
                        'datas' => [
                            'errors' => [],
                        ],
                    ]
                ],
                [ // 3 : invalid from exception
                    '',
                    'to@test.fr',
                    'cc@test.fr',
                    'subject',
                    'lorem ipsum',
                    [
                        'status' => 'error',
                        'datas' => [
                            'errors' => ['Invalid from exception.'],
                        ],
                    ],
                ],
                [ // 4 : missing to exception
                    'from@test.fr',
                    '',
                    'cc@test.fr',
                    'subject',
                    'lorem ipsum',
                    [
                        'status' => 'error',
                        'datas' => [
                            'errors' => ['Missing to exception.'],
                        ],
                    ],
                ],
                [ // 5 : missing html exception
                    'from@test.fr',
                    'to@test.fr',
                    'cc@test.fr',
                    'subject',
                    '',
                    [
                        'status' => 'error',
                        'datas' => [
                            'errors' => ['Missing html exception.'],
                        ],
                    ],
                ],
                [ // 6 : test many exceptions at the same time
                    '',
                    '',
                    '',
                    4,
                    '',
                    [
                        'status' => 'error',
                        'datas' => [
                            'errors' => [
                                'Invalid from exception.',
                                'Missing to exception.',
                                'Invalid subject exception.',
                                'Missing html exception.',
                            ],
                        ],
                    ],
                ],
                [ // 7 : invalid to exception
                    'from@test.fr',
                    'to.fr',
                    '',
                    'subject',
                    'lorem ipsum',
                    [
                        'status' => 'error',
                        'datas' => [
                            'errors' => ['Invalid to exception.'],
                        ],
                    ],
                ],
                [ // 8 : invalid cc exception
                    'from@test.fr',
                    'to@test.fr',
                    'cc.fr',
                    'subject',
                    'lorem ipsum',
                    [
                        'status' => 'error',
                        'datas' => [
                            'errors' => ['Invalid cc exception.'],
                        ],
                    ],
                ],
                [ // 9 : invalid subject exception
                    'from@test.fr',
                    'to@test.fr',
                    'cc@test.fr',
                    4,
                    'test',
                    [
                        'status' => 'error',
                        'datas' => [
                            'errors' => ['Invalid subject exception.'],
                        ],
                    ],
                ],
            ];
        }

        public function validateSMSProvider()
        {
            return [
                [ // 0 : pure success
                    '0650346154, 0650346154',
                    'aaa',
                    [
                        'status' => 'success',
                        'datas' => ['errors' => []],
                    ],
                ],
                [ // 1 : pure success with array of number
                    [
                        '0650346154',
                        '0650346154'
                    ],
                    'aaa',
                    [
                        'status' => 'success',
                        'datas' => ['errors' => []],
                    ],
                ],
                [ // 2 : invalid phone number exception
                    '0123456789999',
                    'aaa',
                    [
                        'status' => 'error',
                        'datas' => ['errors' => ['Invalid phone number exception : 0123456789999 is invalid.']],
                    ],
                ],
                [ // 3 : missing text exception
                    '0123456789',
                    '',
                    [
                        'status' => 'error',
                        'datas' => ['errors' => ['Missing text exception.']],
                    ],
                ],
                [ // 4 : many exceptions at the same time
                    '0123456789999',
                    '',
                    [
                        'status' => 'error',
                        'datas' => ['errors' => [
                            'Invalid phone number exception : 0123456789999 is invalid.',
                            'Missing text exception.',
                        ]],
                    ],
                ],
                [ // 5 : one of the phone numbers is invalid, also text is missing
                    '0123456789, 0123456789999',
                    '',
                    [
                        'status' => 'error',
                        'datas' => ['errors' => [
                            'Invalid phone number exception : 0123456789999 is invalid.',
                            'Missing text exception.',
                        ]],
                    ],
                ],
                [ // 6 : missing phone number exception
                    '',
                    'aaa',
                    [
                        'status' => 'error',
                        'datas' => ['errors' => [
                            'Missing phone number exception.',
                        ]],
                    ],
                ],
            ];
        }

        public function dataToArrayProvider()
        {
            return [
                [ // 0 : pure success
                    '0650346154, 0650346154',
                    [
                        '0650346154',
                        '0650346154',
                    ],
                ],
                [ // 1 : success with no change
                    [
                        '0650346154',
                        '0650346154',
                    ],
                    [
                        '0650346154',
                        '0650346154',
                    ],
                ],
                [ // 2 : success with only one number
                    '0650346154',
                    [
                        '0650346154',
                    ],
                ],
                [ // 3 : empty chain return array with empty chain
                    '',
                    [''],
                ],
                [ // 4 : weird things return empty array
                    5,
                    [],
                ],
                [ // 5 : weird things return empty array
                    true,
                    [],
                ],

            ];
        } */
}

namespace App;

use Illuminate\Http\Response;
use tests\Unit\App\DataHandlerTest;

function dispatch($job)
{
    if (DataHandlerTest::$dispatchSuccess) {
        return;
    }

    throw new Exception('Error Processing Request', 1);
}

function response($response)
{
    return new Response($response);
}
