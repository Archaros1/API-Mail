<?php

namespace tests\Unit\App\Jobs;

use App\Jobs\JobMail;
use App\Log;
use App\Mail\Mail as BaseMail;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Tests\AbstractJobTest;

class JobMailTest extends AbstractJobTest
{
    private $job;

    protected function setUp(): void
    {
        $this->mail = Mail::fake();
    }

    /**
     * @dataProvider sendMailProvider
     *
     * @param array $to
     * @param array $cc
     * @param mixed $expectedResponse
     */
    public function testsendMail(BaseMail $mail, $to, $cc, $expectedResponse)
    {
        $this->job = new JobMail($mail, $to, $cc, new Log());

        $this->mail->to($to)->send($mail);
        $this->mail->assertSent(BaseMail::class);

        $response = $this->job->sendMail();
        $this->assertSame($expectedResponse->getContent(), $response->getContent());
    }

    public function sendMailProvider()
    {
        $successResponse = new Response(json_encode([
            'status' => 'success',
            'datas' => ['errors' => []],
        ]), 200);

        $errorResponse500 = new Response(json_encode([
            'status' => 'error',
            'datas' => ['errors' => ['Failed sending.']],
        ]), 500);

        return [
            [ // 0 : pure success
                new BaseMail([
                    'from' => 'from@test.fr',
                    'subject' => 'lorem',
                    'html' => 'lorem ipsum',
                ]),
                'to@test.fr',
                'cc@test.fr',
                $successResponse,
            ],
        ];
    }
}

class FakeMail
{
}
