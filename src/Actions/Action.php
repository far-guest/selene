<?php

namespace Selene\Actions;


use Selene\Types\Type;
use Illuminate\Support\Collection;
use Selene\Utilities\MessageWrapper;

abstract class Action
{
    protected $title;
    protected $fields = [];
    /**
     * @var null|MessageWrapper
     */
    protected $confirmation = null;

    public static function make()
    {
        return new static();
    }

    /** @todo fix typo :| */
    public abstract function handel(array $fields, Collection $models);

    public function init(Collection $models)
    {
        foreach ($this->fields($models) as $field) {
            $this->fields[$field->getName()] = $field;
        }

        $this->confirmation = $this->confirmationMessage($models);

        return $this;
    }

    public function fields(Collection $models)
    {
        return [

        ];
    }

    function confirmationMessage(Collection $models): ?MessageWrapper
    {
        return null;
    }

    function getFields()
    {
        return $this->fields;
    }

    function getRules()
    {
        return array_reduce($this->getFields(), function ($carry, Type $item) {
            $carry = $carry + $item->getCreationRules();
            return $carry;
        }, []);
    }

    function getRulesMessages()
    {
        return [];
    }

    function getRulesAttributes()
    {
        return array_reduce($this->getFields(), function ($carry, Type $item) {
            if ($item->getName() != $item->getTitle()) {
                $carry[$item->getName()] = $item->getTitle();
            }
            return $carry;
        }, []);
    }

    function export()
    {
        return [
            'title' => $this->title,
        ];
    }

    function exportInit()
    {
        return [
            'fields' => array_map(function (Type $type) {
                return $type->export();
            }, $this->getFields()),
            'confirmation' => $this->confirmation ? $this->confirmation->export() : null,
        ];
    }

    public static function download($content, $filename, $mimeType)
    {
        return [
            'action' => 'download',
            'download' => [
                'content' => base64_encode($content),
                'filename' => $filename,
                'mime' => $mimeType,
            ]
        ];
    }

    public static function message(MessageWrapper $message, $refresh = false)
    {
        return [
            'action' => 'message',
            'message' => $message->export(),
            'refresh' => $refresh,
        ];
    }

    public static function success($title, $text, $refresh = false)
    {
        return static::message(MessageWrapper::make($title, $text, 'بسیار خب!')->success(), $refresh);
    }

    public static function error($title, $text, $refresh = false)
    {
        return static::message(MessageWrapper::make($title, $text, 'بسیار خب!')->error(), $refresh);
    }

    public static function redirect($url, $newTab = false)
    {
        return [
            'action' => 'redirect',
            'redirect' => [
                'url' => $url,
                'new_tab' => $newTab,
            ]
        ];
    }

    public static function refresh()
    {
        return [
            'action' => 'refresh',
        ];
    }
}
