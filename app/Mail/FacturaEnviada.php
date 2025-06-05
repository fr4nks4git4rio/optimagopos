<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class FacturaEnviada extends Mailable
{
    use Queueable, SerializesModels;

    protected $pdf;
    protected $url_xml;
    protected $name_docs;
    protected $evidencias;
    protected $asunto;
    protected $usuario;
    public $cuerpo;

    /**
     * Create a new message instance.
     *
     * @param string $pdf
     * @param string $url_xml
     * @param array $evidencias
     * @param string $cuerpo
     * @param string $asunto
     */
    public function __construct($pdf, $url_xml, $name_docs, $evidencias, $usuario, $cuerpo, $asunto = '')
    {
        $this->pdf = $pdf;
        $this->url_xml = $url_xml;
        $this->name_docs = $name_docs;
        $this->evidencias = $evidencias;
        $this->cuerpo = $cuerpo;
        $this->usuario = $usuario;
        $this->asunto = $asunto;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $build = $this->view('emails.factura_enviada_cliente')
            ->from(config('mail.from.address'), $this->usuario->nombre_completo)
            ->subject($this->asunto)
            ->attach($this->pdf, [
                'as' => "$this->name_docs.pdf",
                'mime' => 'application/pdf',
            ]);
        if(Storage::disk('public')->exists($this->url_xml)){
            $build->attach(Storage::path("public/$this->url_xml"), [
                'as' => "$this->name_docs.xml"
            ]);
        }
        if(count($this->evidencias) > 0){
            foreach($this->evidencias as $evidencia){
                $build->attach(Storage::path("public/$evidencia"));
            }
        }
        return $build;

    }
}
