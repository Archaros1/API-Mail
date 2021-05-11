<?php

namespace App;

use App\Jobs\JobMail;
use App\Jobs\JobSMS;
use App\Mail\Mail as BaseMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DataHandler extends Model
{
    public function queueMail(Request $request): Response
    {
        $response = $this->validateMail($request->from, $request->to, $request->cc, $request->subject, $request->html);

        if ('error' === $response['status']) {
            return response((string) json_encode($response), 400);
        }

        $job = $this->prepareJobMail($request);

        return $this->dispatchJob($job, $response);
    }

    public function queueSMS(Request $request): Response
    {
        $response = $this->validateSMS($request->tel, $request->text);

        if ('error' === $response['status']) {
            return response((string) json_encode($response), 400);
        }

        $job = $this->prepareJobSMS($request);

        return $this->dispatchJob($job, $response);
    }

    private function prepareJobMail(Request $request): JobMail
    {
        $tabTo = $this->dataToArray($request->to);

        if (!empty($request->cc) && '' !== $request->cc) {
            $tabCC = $this->dataToArray($request->cc);
        } else {
            $tabCC = [];
        }

        $newMail = $this->makeMail($request->from, $request->subject, $request->html);

        $log = $request->get('log');

        $job = $this->makeJobMail($newMail, $tabTo, $tabCC, $log);

        return $job;
    }

    private function prepareJobSMS(Request $request): JobSMS
    {
        $tabTel = $this->dataToArray($request->tel);
        $log = $request->get('log');
        $job = $this->makeJobSMS($tabTel, $request->text, $log);

        return $job;
    }

    /**
     * @param string|null $subject
     */
    private function makeMail(string $from, $subject, string $html): BaseMail
    {
        $data = [
            'from' => $from,
            'subject' => $subject,
            'html' => $html,
        ];

        $newMail = new BaseMail($data);

        return $newMail;
    }

    /**
     * @param string            $from
     * @param array|string      $to
     * @param array|string|null $cc
     * @param string|null       $subject
     * @param string            $html
     */
    private function validateMail($from, $to, $cc, $subject, $html): array
    {
        $errorFound = false;
        $messageError = 'success';
        $tabError = [];

        if (empty($from) || !filter_var($from, \FILTER_VALIDATE_EMAIL)) {
            $errorFound = true;
            $messageError = 'Invalid from exception.';
            $tabError[] = $messageError;
        }

        if (empty($to)) {
            $errorFound = true; // to is required
            $messageError = 'Missing to exception.';
            $tabError[] = $messageError;
        } else { //if to exists
            $tabTo = $this->dataToArray($to);

            if (!$this->checkAllMailInArrayAreValid($tabTo)) {
                $errorFound = true;
                $messageError = 'Invalid to exception.';
                $tabError[] = $messageError;
            }
        }

        if (empty($cc)) {
            $cc = [];
        } else { //if cc exists
            $tabCC = $this->dataToArray($cc);
            if (!$this->checkAllMailInArrayAreValid($tabCC)) {
                $errorFound = true;
                $messageError = 'Invalid cc exception.';
                $tabError[] = $messageError;
            }
        }

        if (!(null === $subject || (\is_string($subject) && '' !== $subject))) {
            $errorFound = true;
            $messageError = 'Invalid subject exception.';
            $tabError[] = $messageError;
        }

        if (!\is_string($html) || '' === $html) {
            $errorFound = true;
            $messageError = 'Missing html exception.';
            $tabError[] = $messageError;
        }

        $response = [
            'status' => !$errorFound ? 'success' : 'error',
            'datas' => ['errors' => $tabError],
        ];

        return $response;
    }

    /**
     * @param array|string $phoneNumbers
     * @param string       $text
     */
    private function validateSMS($phoneNumbers, $text): array
    {
        $errorFound = false;
        $messageError = 'success';
        $tabError = [];

        $tabPhone = $this->dataToArray($phoneNumbers);

        if ([] === $phoneNumbers || '' === $phoneNumbers) {
            $errorFound = true;
            $messageError = 'Missing phone number exception.';
            $tabError[] = $messageError;
        } else {
            foreach ($tabPhone as $phoneNumber) {
                if (preg_match("#^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$#", $phoneNumber)) {
                    // dump($phoneNumber . " is valid.");
                } elseif ('' === $phoneNumber) {
                    $errorFound = true;
                    $messageError = 'Missing phone number exception.';
                    $tabError[] = $messageError;
                } else {
                    $errorFound = true;
                    $messageError = 'Invalid phone number exception : '.$phoneNumber.' is invalid.';
                    $tabError[] = $messageError;
                }
            }
        }

        if (!(\is_string($text) && '' !== $text)) {
            $errorFound = true;
            $messageError = 'Missing text exception.';
            $tabError[] = $messageError;
        }

        $response = [
            'status' => !$errorFound ? 'success' : 'error',
            'datas' => ['errors' => $tabError],
        ];

        return $response;
    }

    /**
     * @param array|string $data
     */
    private function dataToArray($data): array
    {
        $tabData = [];
        $errorFound = false;

        if (\is_string($data) && '' !== $data) {
            $dataSansEspace = str_replace(' ', '', $data);
            $tabData = explode(',', $dataSansEspace);
        } elseif (\is_array($data)) {
            foreach ($data as $singleData) {
                if (!\is_string($singleData)) {
                    $errorFound = true;
                }
            }
            if (!$errorFound) {
                $tabData = $data;
            }
        } else {
            $errorFound = true;
        }

        if (true === $errorFound) {
            $tabData = [];
        }

        return $tabData;
    }

    /**
     * @param Log $log
     */
    private function makeJobMail(BaseMail $newMail, array $tabTo, array $tabCC, $log): JobMail
    {
        return new JobMail($newMail, $tabTo, $tabCC, $log);
    }

    /**
     * @param Log $log
     */
    private function makeJobSMS(array $tabTel, string $text, $log): JobSMS
    {
        return new JobSMS($tabTel, $text, $log);
    }

    /**
     * @param JobMail|JobSMS $job
     */
    private function dispatchJob($job, array $responseSuccess): Response
    {
        try {
            dispatch($job);
        } catch (\Throwable $th) {
            return response((string) json_encode([
                'status' => 'error',
                'datas' => [
                    'errors' => ['Failed queueing.'],
                ],
            ]), 503); // should not happen if the DB is compatible
        }

        return response((string) json_encode($responseSuccess), 200);
    }

    private function checkAllMailInArrayAreValid(array $tab): bool
    {
        $noError = true;
        if ([] !== $tab) {
            foreach ($tab as $elem) {
                if (!filter_var($elem, \FILTER_VALIDATE_EMAIL)) { // if elem is not a mail
                    $noError = false;
                }
            }
        } else {
            $noError = false;
        }

        return $noError;
    }
}
