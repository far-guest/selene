<?php

namespace Selene\Types;


use Selene\Rules\RuleGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class Image extends Type
{
    protected $type = 'image';
    protected $sortable = false;

    protected $inheritedRules = ['image'];

    protected $disc = 'public';
    protected $path = '/';

    protected $nameGenerator = null;

    public function __construct($name)
    {
        parent::__construct($name);
        $this->inheritedRules[] = 'required_if:' . $this->getName() . ',upload';
    }

    /**
     * @return static
     */
    static function make($name, $column_name = null)
    {
        return (new static($name))->columnName(is_null($column_name) ? $name : $column_name);
    }

    function disc($disc)
    {
        $this->disc = $disc;
        return $this;
    }

    function path($path)
    {
        $this->path = $path;
        return $this;
    }

    function storeAs($nameGenerator)
    {
        $this->nameGenerator = $nameGenerator;
        return $this;
    }

    function getCreationRules()
    {
        return [
            $this->getName() . '_file' => array_map(function ($rule) {
                return $rule instanceof RuleGenerator ? $rule->generateCreationRule() : $rule;
            }, array_merge($this->inheritedRules, $this->rules, $this->creationRules))
        ];
    }

    function getUpdateRules(Model $model)
    {
        return [
            $this->getName() . '_file' => array_map(function ($rule) use ($model) {
                return $rule instanceof RuleGenerator ? $rule->generateUpdateRule($model) : $rule;
            }, array_merge($this->inheritedRules, $this->rules, $this->updateRules))
        ];
    }

    public function getRulesAttributes()
    {
        return parent::getRulesAttributes() + [
                $this->getName() . '_file' => $this->getTitle()
            ];
    }

    function resolveFor(Model $model, $pivot = false)
    {
        /**
         * @TODO: add pivot support
         */
        $image = $model->getAttribute($this->getName());
        return $image ? \Storage::disk($this->disc)->url($image) : $image;
    }

    function extractValuesFromRequest(Request $request, ?Model $model = null)
    {
        $value = $request->get($this->getName());
        $old = is_null($model) ? null : $model->getAttribute($this->getName());

        if (is_null($value)) {
            if (!is_null($model)) {
                $this->removeImage($old);
            }

            return [
                $this->getName() => null,
            ];
        } else if ($value == 'upload') {
            $path = $this->storeImage($request, $model);
            $this->removeImage($old);

            return [
                $this->getName() => $path,
            ];
        } else {
            return [
                $this->getName() => is_null($model) ? $value : $model->getAttribute($this->getName()),
            ];
        }
    }

    private function removeImage($path)
    {
        \Storage::disk($this->disc)->delete($path);
    }

    private function storeImage(Request $request, ?Model $model = null)
    {
        $file = $request->file($this->getName() . '_file');
        return $file->storePubliclyAs($this->path, $this->generateName($file, $model), $this->disc);
    }

    private function generateName(UploadedFile $file, ?Model $model = null)
    {
        if (!is_null($this->nameGenerator)) {
            return call_user_func_array($this->nameGenerator, [$file, $model]);
        }

        return Str::random(40) . '.' . $file->getClientOriginalExtension();
    }
}