<?php

namespace tests\Unit\App\Jobs;

use App\Jobs\JobSMS;
use App\Log;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Tests\AbstractJobTest;

class JobSMSTest extends AbstractJobTest
{
    public static $sms;
    public static $client;
    private static $job;
    private $log;

    protected function setUp(): void
    {
        $this->log = new Log();
        self::$client = null;
    }

    /**
     * @dataProvider sendSMSProvider
     *
     * @param string $phoneNumber
     * @param mixed  $expectedResponse
     */
    public function testsendSMS(string $recipient, string $message, $expectedResponse)
    {
        self::$job = new JobSMS($recipient, $message, $this->log);

        self::$sms = [
            'to' => $recipient,
            'textMsg' => $message,
            'media' => 'SMSLong',
        ];

        $client = $this->getMockClient();

        $client->method('request')
               ->willReturn($expectedResponse);

        $response = self::$job->sendSMS($recipient, $message);
        $this->assertSame($expectedResponse->getContent(), $response->getContent());
    }

    public function sendSMSProvider()
    {
        return [
            [ // 0 : pure success
                '0123456789',
                'lorem ipsum',
                new Response(json_encode([
                    'status' => 'success',
                    'datas' => ['errors' => []],
                ]), 200),
            ],
            [ // 1 : success cause the sfr API doesn't care about the phone number
                'not ok',
                'lorem ipsum',
                new Response(json_encode([
                    'status' => 'success',
                    'datas' => ['errors' => []],
                ]), 200),
            ],
        ];
    }

    public static function getMockClient()
    {
        if (null === self::$client) {
            $obj = new self();
            self::$client = $obj->createMock(Client::class);
        }

        return self::$client;
    }
}

namespace App\Jobs;

function config($param)
{
    $configParamsMap = [
        'sfr.service_id' => 'S-ID',
        'sfr.password' => '123',
        'sfr.espace_id' => 'E-ID',
    ];

    return $configParamsMap[$param];
}

namespace App;

use tests\Unit\App\Jobs\JobSMSTest;

function getClient($uri)
{
    return JobSMSTest::getMockClient();
}
