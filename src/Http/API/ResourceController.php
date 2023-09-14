<?php

namespace MyListerHub\Core\Http\API;

use Illuminate\Routing\Controller;
use MyListerHub\Core\Concerns\API\BuildsResponses;
use MyListerHub\Core\Concerns\API\HandlesStandardOperations;
use MyListerHub\Core\Concerns\API\HasAllowedAttributes;
use MyListerHub\Core\Concerns\API\HasAvailableAttributes;
use MyListerHub\Core\Concerns\API\HasMeta;
use MyListerHub\Core\Concerns\API\HasQueryBuilder;
use MyListerHub\Core\Concerns\API\HasRequest;
use MyListerHub\Core\Concerns\API\HasResource;
use MyListerHub\Core\Concerns\API\InitializesTraits;
use Spatie\QueryBuilder\AllowedSort;

/**
 * @template T of \Illuminate\Database\Eloquent\Model
 */
abstract class ResourceController extends Controller
{
    use BuildsResponses;
    use HandlesStandardOperations;

    use HasAllowedAttributes;

    use HasAvailableAttributes;
    use HasMeta;
    /** @use HasQueryBuilder<T> */
    use HasQueryBuilder;
    use HasRequest;
    use HasResource;
    use InitializesTraits;

    /**
     * @var class-string<T>
     */
    protected string $model;

    protected string $request;

    protected bool $validateRequest;

    protected ?string $resource = null;

    protected ?string $collectionResource = null;

    protected array $meta = [];

    protected array $alwaysInclude = [];

    protected array $alwaysAppend = [];

    protected ?array $allowedFilters = null;

    protected ?array $allowedSorts = null;

    protected ?array $allowedFields = null;

    protected ?array $allowedAppends = null;

    protected ?array $allowedIncludes = null;

    protected string|array|AllowedSort|null $defaultSort = null;
}
