<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Search Operator
    |--------------------------------------------------------------------------
    |
    | This option controls the default search operator used when searching.
    | Supported operators: 'LIKE', '=', '>', '<', '>=', '<='
    |
    */
    'default_operator' => 'LIKE',

    /*
    |--------------------------------------------------------------------------
    | Case Sensitive Search
    |--------------------------------------------------------------------------
    |
    | This option determines whether the search should be case sensitive.
    |
    */
    'case_sensitive' => false,

    /*
    |--------------------------------------------------------------------------
    | Default Search Columns
    |--------------------------------------------------------------------------
    |
    | These are the default columns that will be searched if no columns are
    | specified in the model's $searchable property.
    |
    */
    'default_columns' => ['name', 'title', 'description'],

    /*
    |--------------------------------------------------------------------------
    | Search Term Minimum Length
    |--------------------------------------------------------------------------
    |
    | The minimum length of the search term before the search is performed.
    |
    */
    'min_term_length' => 2,
]; 