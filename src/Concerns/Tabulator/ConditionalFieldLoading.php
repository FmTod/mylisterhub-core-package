<?php

namespace MyListerHub\Core\Concerns\Tabulator;

trait ConditionalFieldLoading
{
    protected string $queryKey = 'hidden';

    protected function ajaxParams(): string
    {
        return <<<JS
        function ajaxParams() {
             const $this->queryKey = this.getColumnLayout()
                .filter(column => (column.hasOwnProperty("visible") && column.visible === false) && !!column.field)
                .map(column => column.field);

            return { $this->queryKey }
        }
        JS;
    }

    protected function fieldHidden(string $field): bool
    {
        if (! $this->request->has($this->queryKey)) {
            return false;
        }

        return $this->request->collect($this->queryKey)->contains($field);
    }
}
