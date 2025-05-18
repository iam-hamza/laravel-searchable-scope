<?php

namespace HamzaEjaz\SearchableScope\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;

trait Searchable
{
    /**
     * Search scope for models
     *
     * @param Builder $query
     * @param string|null $searchTerm
     * @param array $columns
     * @param array $relations
     * @return Builder
     */
    public function scopeSearch(Builder $query, ?string $searchTerm, array $columns = [], array $relations = []): Builder
    {
        if (!$searchTerm || strlen($searchTerm) < Config::get('searchable-scope.min_term_length', 2)) {
            return $query;
        }

        $operator = Config::get('searchable-scope.default_operator', 'LIKE');
        $caseSensitive = Config::get('searchable-scope.case_sensitive', false);

        // Resolve columns and relations
        if (empty($columns)) {
            if (property_exists($this, 'searchable')) {
                $columns = $this->searchable['columns'] ?? [];
                $relations = $this->searchable['relations'] ?? [];
            }

            if (empty($columns)) {
                $columns = Config::get('searchable-scope.default_columns', []);
            }
        }

        // Apply search on main model and related models
        $query->where(function ($query) use ($columns, $relations, $searchTerm, $operator, $caseSensitive) {
            $this->applySearchConditions($query, $columns, $searchTerm, $operator, $caseSensitive);

            foreach ($relations as $relation => $relationColumns) {
                $query->orWhereHas($relation, function ($query) use ($relationColumns, $searchTerm, $operator, $caseSensitive) {
                    $this->applySearchConditions($query, $relationColumns, $searchTerm, $operator, $caseSensitive);
                });
            }
        });

        return $query;
    }

    /**
     * Apply search conditions to a query for a given set of columns
     *
     * @param Builder $query
     * @param array $columns
     * @param string $searchTerm
     * @param string $operator
     * @param bool $caseSensitive
     */
    protected function applySearchConditions(Builder $query, array $columns, string $searchTerm, string $operator, bool $caseSensitive): void
    {
        $query->where(function ($q) use ($columns, $searchTerm, $operator, $caseSensitive) {
            foreach ($columns as $column) {
                $value = $caseSensitive ? $searchTerm : strtolower($searchTerm);
                if (!$caseSensitive) {
                    $q->orWhereRaw('LOWER(' . $column . ') ' . $operator . ' ?', [
                        $operator === 'LIKE' ? "%{$value}%" : $value
                    ]);
                } else {
                    $q->orWhere($column, $operator, $operator === 'LIKE' ? "%{$value}%" : $value);
                }
            }
        });
    }
}
