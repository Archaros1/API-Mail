<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\Mail as BaseMail;
use Illuminate\Support\Facades\Mail;


class JobsController extends Controller
{
    //
    public function queueMail(Request $request){

        $request->validate([
            'from' => 'required',
            'destinataires' => 'required',
            'copieCachee' => 'nullable',
            'objet' => 'nullable',
            'contenu' => 'required',
        ]);

        $newMail = $this->makeMail($request->from, $request->objet, $request->contenu);
        $destinatairesSansEspace = str_replace(' ', '', $request->destinataires);
        $tabDestinataires = explode(",", $destinatairesSansEspace);
        

        $mail = Mail::to($tabDestinataires);
        
        if (!empty($request->copieCachee)) {
            $CCSansEspace = str_replace(' ', '', $request->copieCachee);
            $tabCC = explode(",", $CCSansEspace);

            $mail->cc($tabCC); 
        }

        $mail->queue($newMail);
        // $mail->send($newMail);

    }

    private function makeMail($from, $subject, $content){

        $data = [
            'from'=>$from,
            'subject'=>$subject,
            'content'=>$content,
        ];

        $newMail = new BaseMail($data);

        return $newMail;
    }
}
