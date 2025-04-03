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

use Hyperf\Resource\Json\AnonymousResourceCollection;

class Collection extends AnonymousResourceCollection
{
    public function __construct(
        $resource,
        string $collects,
        $codeStatus = 200,
        public string $message = ''
    ) {
        parent::__construct($resource, $collects);
    }

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
