<?php

class Email {

    public $to = array();
    public $bcc = array();
    public $subject = '';
    public $anexos = array();
    public $from = null;
    public $message;
    protected $body = '';
    public $individual = true;
    public $reply = array();
    public $embed = true;
    public $embedExternal = true;
    public $format = 'text/html';

    public function __construct() {
        Yii::import('custom.extensions.mail.YiiMailMessage');
        $this->message = new YiiMailMessage;
    }

    public function getBody() {
        return $this->body;
    }

    //Classes que podem ser modificadas pelas classes especializadas
    public function beforeSend() {
        if ($this->embed)
            $this->embedImages();
    }

    public function afterSend() {
        
    }

    public function setDecorator($replacements) {
        $mailer = Yii::app()->mail->getMailer();
        $decorator = new Swift_Plugins_DecoratorPlugin($replacements);
        $mailer->registerPlugin($decorator);
    }

    public function renderBody($view, $params = array()) {
        $controller = Yii::app()->controller;
        $params = array_merge(array('email' => $this), $params);
        if ($controller instanceof CConsoleApplication)
            $this->body = $controller->renderInternal($view, $params, true);
        else
            $this->body = $controller->renderPartial($view, $params, true);
    }

    public function setBody($html) {
        $this->body = $html;
    }

    public function sendMail() {
        if ( defined('DESENV') && DESENV ) {
            $this->to = Yii::app()->params['adminEmail'];
        }
        $this->beforeSend();
        //Montando mensagem    
        $this->message->subject = $this->subject;
        $this->message->setBody($this->body, $this->format);

        //Adicionando destinatários
        $this->message->setTo(CVar::toArray($this->to));
        $this->message->setBcc($this->bcc);
        $this->message->setReplyTo($this->reply);

        $this->adicionaAnexos();

        $this->message->from = empty($this->from) ? Yii::app()->params['supportEmail'] : $this->from;
        if ($this->individual)
            $this->batchSend();
        else
            $this->send();
        $this->afterSend();
    }

    protected function adicionaAnexos() {
        CVar::toArray($this->anexos);
        //Adicionando anexos
        foreach ($this->anexos as $anexo) {
            if (is_array($anexo)) {
                $attach = Swift_Attachment::fromPath($anexo['path']);
                $attach->setFilename($anexo['filename']);
            } else {
                $attach = Swift_Attachment::fromPath($anexo);
            }
            $this->message->attach($attach);
        }
    }

    protected function send() {
        return Yii::app()->mail->send($this->message);
    }

    //Envia para todos
    protected function batchSend() {
        $numSent = 0;
        $this->message->setBcc(array());
        $this->message->setCc(array());
        foreach ($this->to as $address => $name) {
            if (is_int($address)) {
                $this->message->setTo($name);
            } else {
                $this->message->setTo(array($address => $name));
            }
            $numSent += $this->send();
        }
        return $numSent;
    }

    public function enviarEmail($content, $anexos = array(), $view = 'default') {
        $this->sendMail($view, array(
          'model' => $user,
          'content' => $content)
        );
    }

    //Envia email em HTML
    public static function sendDirect($to, $subject, $msg, $anexos = array()) {
        $email = new Email();
        $email->to = $to;
        $email->subject = $subject;
        $email->setBody($msg);
        $email->anexos = $anexos;
        $email->message->embed = false;
        $email->sendMail();
    }

    //Envia o email no formato de texto
    public static function sendText($to, $subject, $msg, $anexos = array()) {
        $email = new Email();
        $email->to = $to;
        $email->subject = $subject;
        $email->format = 'text';
        $email->setBody($msg);
        $email->anexos = $anexos;
        $email->message->embed = false;
        $email->sendMail();
    }

    //Irá receber um HTML e gerar o cid de todas os atributos src
    protected function embedImages() {
        $root = Helper::getWebroot(); //Diretório raiz do site
        $baseUrlFull = Helper::getBaseUrl(true);
        //Encontra todas as imagens
        $this->body = HtmlParser::normalizeImgs($this->body);
        //echo $this->body; exit;
        $imgs = HtmlParser::extrairImgs($this->body);
        //Faz todas as substituições de IMAGENS por CIDs
        foreach ($imgs as $img) {
            $urlFormat = ($img[0] == '/' && !is_file($img)); //No linux o caminho pode começar com /
            $path = ($urlFormat) ? dirname($root) . $img : $img; //Gera um path ou usa o que foi lido
            $internal = !(strpos($path, $baseUrlFull) !== 0 && strpos($path, 'http:') === 0 ); //É http e endereço é diferente
            if ($internal OR $this->embedExternal) { //Só adiciona externo se estive habilitado
                $cid = $this->message->message->embed(Swift_Image::fromPath($path));
                $this->body = str_replace($img, $cid, $this->body);
            }
        }
    }

}
