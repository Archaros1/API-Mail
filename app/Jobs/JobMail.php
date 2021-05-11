<?php

namespace App\Jobs;

use App\Log;
use App\Mail\Mail as Basemail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class JobMail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var BaseMail */
    public $basemail;
    /** @var array */
    public $to;
    /** @var array */
    public $cc;
    /** @var Log */
    public $log;

    /** @var Mail */
    public $mail;

    /**
     * Create a new job instance.
     *
     * @param mixed $to
     * @param mixed $cc
     * @param mixed $log
     *
     * @return void
     */
    public function __construct(BaseMail $basemail, $to, $cc, $log)
    {
        $this->basemail = $basemail;
        $this->to = $to;
        $this->cc = $cc;
        $this->log = $log;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = $this->sendMail();

        $log = Log::where('id', '=', $this->log->id)->update([
            'sent' => (200 === $response->getStatusCode()),
            'response' => $response->getContent(),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function sendMail(): Response
    {
        Mail::to($this->to)
            ->cc($this->cc)
            ->send($this->basemail);

        if (empty(Mail::failures())) {
            $response = new Response(json_encode([
                'status' => 'success',
                'datas' => ['errors' => []],
                ]), 200);
        } else {
            $response = new Response(json_encode([
                'status' => 'error',
                'datas' => ['errors' => ['Failed sending. Mail not sent to '.print_r(Mail::failures())]],
                ]), 500);
        }

        return $response;
    }
}
