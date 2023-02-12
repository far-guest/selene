<?php


namespace Selene\Utilities;


class MessageWrapper
{
    protected $icon = 'info';

    protected $title;
    protected $text;

    protected $buttonText = 'ok';
    protected $cancelButton = false;
    protected $cancelButtonText;

    function info()
    {
        $this->icon = 'info';
        return $this;
    }

    function warning()
    {
        $this->icon = 'warning';
        return $this;
    }

    function success()
    {
        $this->icon = 'success';
        return $this;
    }

    function question()
    {
        $this->icon = 'question';
        return $this;
    }

    function error()
    {
        $this->icon = 'error';
        return $this;
    }

    function withCancelOption($text)
    {
        $this->cancelButton = true;
        $this->cancelButtonText = $text;
        return $this;
    }

    function export()
    {
        return [
            'icon' => $this->icon,
            'title' => $this->title,
            'text' => $this->text,
            'buttonText' => $this->buttonText,
            'cancelButton' => $this->cancelButton,
            'cancelButtonText' => $this->cancelButtonText,
        ];
    }

    static function make($title, $text, $buttonText = 'ok')
    {
        $message = new static();

        $message->title = $title;
        $message->text = $text;
        $message->buttonText = $buttonText;

        return $message;
    }
}