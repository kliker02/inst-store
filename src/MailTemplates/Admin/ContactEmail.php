<?php


namespace App\MailTemplates\Admin;


use App\MailTemplates\TemplatesInterface;
use Symfony\Component\Mime\Email;

class ContactEmail implements TemplatesInterface
{
    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $body;


    function __construct($subject, $name, $email, $body)
    {
        $this->subject = $subject;
        $this->name = $name;
        $this->email = $email;
        $this->body = $body;
    }

    public function get()
    {
        $objEmail = new Email();
        return $objEmail->from('info@inst-store.ru')->to('alextokunow@yandex.ru')->
        subject('Новый запрос в техподдержку: '. $this->subject)->
        text('Новый запрос в техподдержку: '. $this->subject)->
        html(
            "
                                Новый запрос в тех поддержку!<br>
                                Тема           : $this->subject <br>
                                Имя            : $this->name    <br>
                                Email          : $this->email   <br>
                                Текст обращения: $this->body   <br>
                            "
        );
    }
}