<?php
/**
 * @noinspection ReturnTypeCanBeDeclaredInspection
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace MyListerHub\Core\Concerns\API;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use MyListerHub\Core\Http\Request;
use MyListerHub\Core\QueryBuilder\QueryBuilder;

trait HandlesStandardOperations
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index()
    {
        $limit = request()?->input('limit');

        $query = $this->query()->when(
            request()?->has('page'),
            static fn (QueryBuilder|Builder $query) => $query->paginate($limit),
            static fn (QueryBuilder|Builder $query) => $query->when($limit, static fn ($q) => $q->limit($limit))->get()
        );

        return $this->response($query);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource
     *
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $data = $this->shouldValidateRequest() ? $request->validated() : $request->all();
        $instance = DB::transaction(fn () => $this->query()->create($data));

        if (isset($this->alwaysAppend)) {
            $instance->append($this->alwaysAppend);
        }

        return $this->response($instance);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show($id)
    {
        $instance = $this->query()->findOrFail($id);

        if (isset($this->alwaysAppend)) {
            $instance->append($this->alwaysAppend);
        }

        return $this->response($instance);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource
     *
     * @throws \Throwable
     */
    public function update(Request $request, $id)
    {
        $data = $this->shouldValidateRequest() ? $request->validated() : $request->all();
        $instance = $this->query()->findOrFail($id);

        DB::transaction(static fn () => $instance->update($data));

        if (isset($this->alwaysAppend)) {
            $instance->append($this->alwaysAppend);
        }

        return $this->response($instance);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $instance = $this->model::findOrFail($id);

        $instance->delete();

        return response()->noContent();
    }
}
