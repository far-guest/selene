<?php

namespace Selene\Controllers;

use Illuminate\Support\Facades\DB;
use Selene\Actions\Action;
use Selene\Metrics\Metric;
use Selene\Repositories\ResourceModelRepository;
use Selene\Resources\Resource;
use Selene\Types\Type;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Selene;

class SeleneResourceController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    function __construct()
    {
        $this->middleware(function (Request $request, \Closure $next) {
            if (count($files = array_keys($request->allFiles())) > 0) {
                $request->merge(array_map(function ($val) {
                    return json_decode($val, true);
                }, array_diff_key($request->all(), array_flip($files))));
            }
            return $next($request);
        });
    }

    function index()
    {
        return view('selene.index');
    }

    function paginate(Request $request, $resource)
    {
        $user = $request->user();

        /** @var Resource $resource */
        $resource = selene::getResource($resource);
        $fields = $resource->visibleFieldsOnIndex();

        $query = ResourceModelRepository::make($resource)
            ->fields($fields)
            ->filters(json_decode($request->get('filters', '[]'), true))
            ->searchFor(trim($request->get('search')))
            ->sortBy(trim($request->get('sort')), $request->has('desc'))
            ->getQueryWithRelations();

        if ($default_sort = $resource->getDefaultSort()) {
            if (is_array($default_sort)) {
                $query->orderBy($default_sort[0], $default_sort[1]);
            } elseif (is_callable($default_sort)) {
                call_user_func($default_sort, $query);
            } else {
                $query->orderBy($default_sort);
            }
        }

        $paginate = $query->paginate(20);

        $data = [];

        foreach ($paginate as $item) {
            $row = [];

            foreach ($fields as $field) {
                $row[$field->getName()] = $field->displayFor($item);
            }

            $row['__perms'] = [
                'view' => $resource->view($item, $user),
                'edit' => $resource->edit($item, $user),
                'delete' => $resource->delete($item, $user),
            ];

            $data[] = $row;
        }

        return [
            'data' => $data,
            'total' => $paginate->total(),
            'per_page' => $paginate->perPage(),
            'current_page' => $paginate->currentPage(),
        ];
    }

    function typeRetrieve(Request $request, $resource, $type)
    {
        $user = $request->user();

        /** @var Resource $resource */
        $resource = selene::getResource($resource);

        /** @var Type $type */
        $type = $resource->getField($type);

        $model = $request->get('model');

        if (!is_null($model)) {
            $model = $resource->getQuery()->findOrFail($model);

            if (\Gate::getPolicyFor($resource->model)) {
                $this->authorize('view', $model);
            }

            if (!$resource->view($model, $user)) {
                abort(403);
            }
        }

        return $type->handleRetrieveRequest($request, $resource, $model);
    }

    function details(Request $request, $resource, $model)
    {
        $user = $request->user();

        /** @var Resource $resource */
        $resource = selene::getResource($resource);

        if ($resource->isDetailsPanelDisabled()) {
            abort(403);
        }

        $fields = $resource->visibleFieldsOnDetails();

        $model = ResourceModelRepository::make($resource)
            ->fields($fields)
            ->getQueryWithRelations()
            ->findOrFail($model);

        if (\Gate::getPolicyFor($resource->model)) {
            $this->authorize('view', $model);
        }

        if (!$resource->view($model, $user)) {
            abort(403);
        }

        $data = [];

        foreach ($fields as $field) {
            $data[$field->getName()] = $field->displayFor($model);
        }

        return response()->json($data);
    }

    function create(Request $request, $resource)
    {
        /** @var Resource $resource */
        $resource = selene::getResource($resource);

        if ($resource->isCreatePanelDisabled()) {
            abort(403);
        }

        if (\Gate::getPolicyFor($resource->model)) {
            $this->authorize('create', $resource->model);
        }

        $this->validate($request, $resource->getCreationRules(), $resource->getRulesMessages(), $resource->getRulesAttributes());

        $fields = $resource->visibleFieldsOnCreate();
        $model = new $resource->model();

        $afterSaveJobs = [];
        $appendJob = function (\Closure $job) use (&$afterSaveJobs) {
            $afterSaveJobs[] = $job;
        };

        foreach ($fields as $field) {
            $field->fillFromRequest($request, $model, $appendJob);
        }

        $resource->fireCreating($request, $model);
        $model->save();

        foreach ($afterSaveJobs as $job) {
            call_user_func_array($job, [$request, $model]);
        }

        $resource->fireCreated($request, $model);

        return response()->json(true);
    }

    function edit(Request $request, $resource, $model)
    {
        $user = $request->user();

        /** @var Resource $resource */
        $resource = selene::getResource($resource);

        if ($resource->isEditPanelDisabled()) {
            abort(403);
        }

        $fields = $resource->visibleFieldsOnEdit();

        $model = ResourceModelRepository::make($resource)
            ->fields($fields)
            ->getQueryWithRelations()
            ->findOrFail($model);

        if (\Gate::getPolicyFor($resource->model)) {
            $this->authorize('update', $model);
        }

        if (!$resource->edit($model, $user)) {
            abort(403);
        }

        $data = [];

        foreach ($fields as $field) {
            $data[$field->getName()] = $field->resolveFor($model);
        }

        return response()->json($data);
    }

    function update(Request $request, $resource, $model)
    {
        $user = $request->user();

        /** @var Resource $resource */
        $resource = selene::getResource($resource);

        if ($resource->isEditPanelDisabled()) {
            abort(403);
        }

        $model = $resource->getQuery()->findOrFail($model);

        if (\Gate::getPolicyFor($resource->model)) {
            $this->authorize('update', $model);
        }

        if (!$resource->edit($model, $user)) {
            abort(403);
        }

        $this->validate($request, $resource->getUpdateRules($model), $resource->getRulesMessages(), $resource->getRulesAttributes());

        $fields = $resource->visibleFieldsOnEdit();

        $afterSaveJobs = [];
        $appendJob = function (\Closure $job) use (&$afterSaveJobs) {
            $afterSaveJobs[] = $job;
        };

        foreach ($fields as $field) {
            $field->fillFromRequest($request, $model, $appendJob);
        }

        $resource->fireUpdating($request, $model);
        $model->save();

        foreach ($afterSaveJobs as $job) {
            call_user_func_array($job, [$request, $model]);
        }

        $resource->fireUpdated($request, $model);

        return response()->json(true);
    }

    function destroy(Request $request, $resource, $model)
    {
        $user = $request->user();

        /** @var Resource $resource */
        $resource = selene::getResource($resource);

        if ($resource->isDeleteOptionDisabled()) {
            abort(403);
        }

        $model = $resource->getQuery()->findOrFail($model);

        if (\Gate::getPolicyFor($resource->model)) {
            $this->authorize('delete', $model);
        }

        if (!$resource->delete($model, $user)) {
            abort(403);
        }

        $resource->fireDeleting($request, $model);
        $result = $model->delete();
        $resource->fireDeleted($request, $model);

        return response()->json($result);
    }

    function typeAction(Request $request, $resource, $model, $type)
    {
        $user = $request->user();

        /** @var Resource $resource */
        $resource = selene::getResource($resource);

        $model = $resource->getQuery()->findOrFail($model);

        if (\Gate::getPolicyFor($resource->model)) {
            $this->authorize('update', $model);
        }

        if (!$resource->edit($model, $user)) {
            abort(403);
        }

        /** @var Type $type */
        $type = $resource->getField($type);

        return $type->handleActionRequest($request, $resource, $model);
    }

    function initAction(Request $request, $resource, $action)
    {
        /** @var Resource $resource */
        $resource = selene::getResource($resource);

        $models = $resource->getQuery()->findMany($request->get('models'));

        /** @var Action $action */
        $action = $resource->getAction($action)->init($models);

        return $action->exportInit();
    }

    function handleAction(Request $request, $resource, $action)
    {
        /** @var Resource $resource */
        $resource = selene::getResource($resource);

        $models = $resource->getQuery()->findMany($request->get('models'));

        /** @var Action $action */
        $action = $resource->getAction($action)->init($models);

        $fields = $action->getFields();

        $this->validate($request, $action->getRules(), $action->getRulesMessages(), $action->getRulesAttributes());

        $data = [];

        foreach ($fields as $field) {
            $data = $data + $field->extractValuesFromRequest($request, null);
        }

        return $action->handel($data, $models);
    }

    function metric(Request $request, $resource, $metricIndex)
    {
        /** @var Resource $resource */
        $resource = selene::getResource($resource);

        /** @var Metric $metric */
        $metric = $resource->getMetric($metricIndex);

        return $metric->handelRequest($request, $resource);
    }

    function rearrange(Request $request, $resource)
    {
        /** @var Resource $resource */
        $resource = selene::getResource($resource);

        if (!$resource->isRearrangeable()) {
            abort(403);
        }

        $key = $resource->getPrimaryKey();
        $title = is_callable($resource->title) ? function ($item) use ($resource) {
            return call_user_func($resource->title, $item);
        } : function ($item) use ($resource) {
            return $item->getAttribute($resource->title);
        };

        return $resource->getQuery()->orderBy($resource->getRearrange())->get()->map(function ($item) use ($title, $key) {
            return [
                'key' => $item->getAttribute($key),
                'title' => $title($item)
            ];
        });
    }

    function doRearrange(Request $request, $resource)
    {
        /** @var Resource $resource */
        $resource = selene::getResource($resource);

        if (!$resource->isRearrangeable()) {
            abort(403);
        }

        $values = $request->validate([
            'keys' => 'required|array'
        ]);

        $key = $resource->getPrimaryKey();
        $rearrange = $resource->getRearrange();

        DB::beginTransaction();

        $i = 1;
        foreach ($values['keys'] as $value) {
            $resource->getQuery()->where($key, $value)->update([$rearrange => $i++]);
        }

        DB::commit();

        return response()->json(true);
    }
}
