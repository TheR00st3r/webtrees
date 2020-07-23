<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2020 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Fisharebest\Webtrees\Http\RequestHandlers;

use Fisharebest\Webtrees\Services\MapDataService;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function redirect;
use function route;

/**
 * Delete a place location from the control panel.
 */
class MapDataDelete implements RequestHandlerInterface
{
    /** @var MapDataService */
    private $map_data_service;

    /**
     * Dependency injection.
     *
     * @param MapDataService $map_data_service
     */
    public function __construct(MapDataService $map_data_service)
    {
        $this->map_data_service = $map_data_service;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $place_id  = (int) $request->getQueryParams()['place_id'];

        $place  = $this->map_data_service->findById($place_id);
        $parent = $place->parent();

        if ($place_id !== 0) {
            $this->map_data_service->deleteRecursively($place_id);
        }

        // If after deleting there are no more places at this level then go up a level
        $siblings = DB::table('placelocation')
            ->where('pl_parent_id', '=', $parent->id())
            ->count();

        if ($siblings === 0) {
            $parent_id = $parent->parent()->id();
        }

        $url = route(MapDataList::class, ['parent_id' => $parent->id()]);

        return redirect($url);
    }
}
