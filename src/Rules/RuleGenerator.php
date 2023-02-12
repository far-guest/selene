<?php

namespace Selene\Rules;

use Selene;
use Illuminate\Database\Eloquent\Model;

class RuleGenerator
{
    protected $creation;
    protected $update;

    public function creation($rule)
    {
        $this->creation = $rule;
        return $this;
    }

    public function update($rule)
    {
        $this->update = $rule;
        return $this;
    }

    public function generateCreationRule()
    {
        return Selene::tap($this->creation);
    }

    public function generateUpdateRule(Model $model)
    {
        return Selene::tap($this->update, $model);
    }
}