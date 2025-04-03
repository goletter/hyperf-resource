<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Goletter\Resource;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Resource\Json\JsonResource;
use Hyperf\Stringable\Str;
use JetBrains\PhpStorm\ArrayShape;
use function Goletter\Utils\parseIncludes;

use function Hyperf\Tappable\tap;

class Resource extends JsonResource
{
    public static bool $relationLoaded = false;

    public int $codeStatus;

    protected static array $availableIncludes = [];

    protected static array $collectionAvailableIncludes = [];

    public function __construct($resource, $codeStatus = 200, public string $message = '')
    {
        parent::__construct($resource);

        // collection map 第二个参数传的是 key (int)
        $this->codeStatus = $codeStatus;

        if (! self::$relationLoaded && $resource instanceof Model) {
            $resource->loadMissing(static::getIncludeRelations(static::$availableIncludes));
            // self::$relationLoaded = true;
        }
    }

    public function toArray(): array
    {
        return parent::toArray();
    }

    public static function collection(
        $resource,
        $codeStatus = 200,
        string $message = '',
    ): Collection {
        if (! self::$relationLoaded) {
            $resource->loadMissing(static::getIncludeRelations(static::$collectionAvailableIncludes));
            self::$relationLoaded = true;
        }

        return tap(new Collection($resource, static::class, $codeStatus, $message), function ($collection) {
            if (property_exists(static::class, 'preserveKeys')) {
                $collection->preserveKeys = (new static([]))->preserveKeys;
            }
        });
    }

    public static function getIncludeRelations(array $availableIncludes = [], bool $filter = true): array
    {
        $includes = $filter ? array_intersect(parseIncludes($availableIncludes), parseIncludes()) : $availableIncludes;
        $relations = [];

        foreach ($includes as $relation) {
            $method = Str::camel(str_replace('.', '_', $relation)) . 'Query';
            if (method_exists(static::class, $method)) {
                $relations[$relation] = function ($query) use ($method) {
                    forward_static_call([static::class, $method], $query);
                };
                continue;
            }
            $relations[] = $relation;
        }

        return $relations;
    }

    #[ArrayShape(['success' => 'bool', 'status' => 'string', 'code' => 'int', 'message' => 'string'])]
    public function with(): array
    {
        return [
            'success' => true,
            'status' => 'success',
            'code' => 200,
            'message' => $this->message ?: '',
        ];
    }
}
