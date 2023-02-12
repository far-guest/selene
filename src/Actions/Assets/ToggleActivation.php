<?php

namespace Selene\Actions\Assets;

use Selene\Actions\Action;
use Models\Asset;
use Illuminate\Support\Collection;

class ToggleActivation extends Action
{
    protected $title = 'فعال/غیرفال';

    public function handel(array $fields, Collection $models)
    {
        foreach ($models as $model) {
            $model->forceFill([
                'status' => $model->status == Asset::STATUS_PENDING ? Asset::STATUS_ACTIVE : Asset::STATUS_PENDING
            ])->save();
        }

        return $this->refresh();
    }
}