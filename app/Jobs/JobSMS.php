<?php

namespace App\Jobs;

use App\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobSMS implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var array */
    protected $tel;
    /** @var string */
    protected $text;
    /** @var Log */
    protected $log;

    /**
     * Create a new job instance.
     *
     * @param mixed $tel
     * @param mixed $text
     * @param mixed $log
     *
     * @return void
     */
    public function __construct($tel, $text, $log)
    {
        $this->tel = $tel;
        $this->text = $text;
        $this->log = $log;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->tel as $singleTel) {
            try {
                $response = $this->sendSMS($singleTel, $this->text);
            } catch (\Throwable $th) {
                dump(new Response(json_encode([
                    'status' => 'error',
                    'datas' => ['errors' => ['Failed requesting.']],
                ]), 500));
            }
        }
        $log = Log::where('id', '=', $this->log->id)->update(['sent' => 1]);
    }

    public function sendSMS(string $recipient, string $message) // Guzzle version
    {
        $media = 'SMSLong';
        // Data arrays
        $auth = [
            'serviceId' => config('sfr.service_id'),
            'servicePassword' => config('sfr.password'),
            'spaceId' => config('sfr.espace_id'),
        ];
        $message = [
            //"phoneNumber"       => $recipient,
            'to' => $recipient,
            //"contentMsg"        => $message,
            'textMsg' => $message,
            'media' => $media,
        ];

        $auth_json = (json_encode($auth));
        $msg_json = (json_encode($message));

        $uri = 'https://www.dmc.sfr-sh.fr/DmcWS/1.5/JsonService/MessagesUnitairesWS/addSingleCall';

        $client = \App\getClient($uri);

        $response = $client->request('POST', $uri, [
            'form_params' => [
                'authenticate' => $auth_json,
                'messageUnitaire' => $msg_json,
            ],
        ]);

        return $response;
    }

    private function sendSMS_(string $recipient, string $message): string // without Guzzle
    {
        // PoC Sending SMS through SFR APIs using JSON
        // Authentications  and Message
        $media = 'SMSLong';
        // Data arrays
        $auth = [
            'serviceId' => config('sfr.service_id'),
            'servicePassword' => config('sfr.password'),
            'spaceId' => config('sfr.espace_id'),
        ];
        $message = [
            //"phoneNumber"       => $recipient,
            'to' => $recipient,
            //"contentMsg"        => $message,
            'textMsg' => $message,
            'media' => $media,
        ];
        // Convert into JSON format
        $auth_json = urlencode((string) json_encode($auth));
        $msg_json = urlencode((string) json_encode($message));
        // Request Arguments
        $query_data = 'authenticate='.$auth_json.'&messageUnitaire='.$msg_json;
        //echo 'query data : '.$query_data.'<br /><br />';
        // Build the URL Of the Service
        $send_query = 'https://www.dmc.sfr-sh.fr/DmcWS/1.5/JsonService/MessagesUnitairesWS/addSingleCall?'.$query_data;

        // Getting the result
        $result = (string) file_get_contents($send_query);

        return (string) json_decode($result);
    }
}
