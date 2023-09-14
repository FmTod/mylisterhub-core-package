<?php

namespace MyListerHub\Core\Http;

use Illuminate\Foundation\Http\FormRequest;

class Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // authorization is handled in controllers
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        if (! $this->route()) {
            return [];
        }

        if ($this->route()->getActionMethod() === 'store') {
            return array_merge($this->commonRules(), $this->storeRules());
        }

        if ($this->route()->getActionMethod() === 'batchStore') {
            return $this->buildBatchRules($this->storeRules(), $this->batchStoreRules());
        }

        if ($this->route()->getActionMethod() === 'update') {
            return array_merge($this->commonRules(), $this->updateRules());
        }

        if ($this->route()->getActionMethod() === 'batchUpdate') {
            return $this->buildBatchRules($this->updateRules(), $this->batchUpdateRules());
        }

        if ($this->route()->getActionMethod() === 'associate') {
            return array_merge(
                [
                    'related_key' => 'required',
                ],
                $this->associateRules()
            );
        }

        if ($this->route()->getActionMethod() === 'attach') {
            return array_merge(
                [
                    'resources' => 'present',
                    'duplicates' => ['sometimes', 'boolean'],
                ],
                $this->attachRules()
            );
        }

        if ($this->route()->getActionMethod() === 'detach') {
            return array_merge(
                [
                    'resources' => 'present',
                ],
                $this->detachRules()
            );
        }

        if ($this->route()->getActionMethod() === 'sync') {
            return array_merge(
                [
                    'resources' => 'present',
                    'detaching' => ['sometimes', 'boolean'],
                ],
                $this->syncRules()
            );
        }

        if ($this->route()->getActionMethod() === 'toggle') {
            return array_merge(
                [
                    'resources' => 'present',
                ],
                $this->toggleRules()
            );
        }

        if ($this->route()->getActionMethod() === 'updatePivot') {
            return array_merge(
                [
                    'pivot' => ['required', 'array'],
                ],
                $this->updatePivotRules()
            );
        }

        return $this->commonRules();
    }

    /**
     * Get custom attributes for validator errors that apply to the request.
     */
    public function messages(): array
    {
        if (! $this->route()) {
            return [];
        }

        if ($this->route()->getActionMethod() === 'store') {
            return array_merge($this->commonMessages(), $this->storeMessages());
        }

        if ($this->route()->getActionMethod() === 'batchStore') {
            return array_merge($this->commonMessages(), $this->storeMessages(), $this->batchStoreMessages());
        }

        if ($this->route()->getActionMethod() === 'update') {
            return array_merge($this->commonMessages(), $this->updateMessages());
        }

        if ($this->route()->getActionMethod() === 'batchUpdate') {
            return array_merge($this->commonMessages(), $this->updateMessages(), $this->batchUpdateMessages());
        }

        if ($this->route()->getActionMethod() === 'associate') {
            return $this->associateMessages();
        }

        if ($this->route()->getActionMethod() === 'attach') {
            return $this->attachMessages();
        }

        if ($this->route()->getActionMethod() === 'detach') {
            return $this->detachMessages();
        }

        if ($this->route()->getActionMethod() === 'sync') {
            return $this->syncMessages();
        }

        if ($this->route()->getActionMethod() === 'toggle') {
            return $this->toggleMessages();
        }

        if ($this->route()->getActionMethod() === 'updatePivot') {
            return $this->updatePivotMessages();
        }

        return $this->commonMessages();
    }

    /**
     * Default rules for the request.
     */
    public function commonRules(): array
    {
        return [];
    }

    /**
     * Rules for the "store" (POST) endpoint.
     */
    public function storeRules(): array
    {
        return [];
    }

    /**
     * Build rules for batch operations.
     *
     * @return \string[][]
     *
     * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
     */
    protected function buildBatchRules($definedRules, $definedBatchRules): array
    {
        $batchRules = [
            'resources' => ['array', 'required'],
        ];

        $mergedRules = array_merge($this->commonRules(), $definedRules, $definedBatchRules);

        foreach ($mergedRules as $ruleField => $fieldRules) {
            $batchRules["resources.*.{$ruleField}"] = $fieldRules;
        }

        return $batchRules;
    }

    /**
     * Rules for the "batch store" (POST) endpoint.
     */
    public function batchStoreRules(): array
    {
        return [];
    }

    /**
     * Rules for the "update" (PATCH|PUT) endpoint.
     */
    public function updateRules(): array
    {
        return [];
    }

    /**
     * Rules for the "batch update" (PATCH|PUT) endpoint.
     */
    public function batchUpdateRules(): array
    {
        return [];
    }

    /**
     * Rules for the "associate" endpoint.
     */
    public function associateRules(): array
    {
        return [];
    }

    /**
     * Rules for the "attach" endpoint.
     */
    public function attachRules(): array
    {
        return [];
    }

    /**
     * Rules for the "detach" endpoint.
     */
    public function detachRules(): array
    {
        return [];
    }

    /**
     * Rules for the "sync" endpoint.
     */
    public function syncRules(): array
    {
        return [];
    }

    /**
     * Rules for the "toggle" endpoint.
     */
    public function toggleRules(): array
    {
        return [];
    }

    /**
     * Rules for the "pivot" endpoint.
     */
    public function updatePivotRules(): array
    {
        return [];
    }

    /**
     * Default messages for the request.
     */
    public function commonMessages(): array
    {
        return [];
    }

    /**
     * Messages for the "store" (POST) endpoint.
     */
    public function storeMessages(): array
    {
        return [];
    }

    /**
     * Messages for the "batchstore" (POST) endpoint.
     */
    public function batchStoreMessages(): array
    {
        return [];
    }

    /**
     * Messages for the "update" (POST) endpoint.
     */
    public function updateMessages(): array
    {
        return [];
    }

    /**
     * Messages for the "batchUpdate" (POST) endpoint.
     */
    public function batchUpdateMessages(): array
    {
        return [];
    }

    /**
     * Messages for the "associate" endpoint.
     */
    public function associateMessages(): array
    {
        return [];
    }

    /**
     * Messages for the "attach" endpoint.
     */
    public function attachMessages(): array
    {
        return [];
    }

    /**
     * Messages for the "detach" endpoint.
     */
    public function detachMessages(): array
    {
        return [];
    }

    /**
     * Messages for the "sync" endpoint.
     */
    public function syncMessages(): array
    {
        return [];
    }

    /**
     * Messages for the "toggle" endpoint.
     */
    public function toggleMessages(): array
    {
        return [];
    }

    /**
     * Messages for the "pivot" endpoint.
     */
    public function updatePivotMessages(): array
    {
        return [];
    }
}
