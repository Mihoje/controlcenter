<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\VatsimRating;
use App\Http\Controllers\Controller;
use App\Http\Requests\PositionRequest;
use App\Models\Area;
use App\Models\Event;
use App\Models\EventPosition;
use App\Models\Position;
use App\Services\PositionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request, PositionService $service, $area = null): View
    {
        $allAreas = Area::orderBy('name')->get();

        $accessibleAreas = $allAreas->filter(fn ($a) => $request->user()->can('viewAny', [Position::class, $a]));

        $currentArea = $area ? $allAreas->firstWhere('id', $area) : null;

        $this->authorize('viewAny', [Position::class, $currentArea]);

        $positions = $service->getPositions($currentArea, $accessibleAreas);
        $ratings = VatsimRating::getControllerRatings();

        return view('admin.positions.index', [
            'positions' => $positions,
            'ratings' => $ratings,
            'areas' => $accessibleAreas,
            'currentArea' => $currentArea,
        ]);
    }

    public function store(PositionRequest $request)
    {
        $this->authorize('create', new Position($request->all()));

        $position = Position::create($request->validated());

        EventPosition::create([
            'code' => $request->callsign,
            'callsign' => $request->name,
            'frequency' => $request->frequency,
        ]);

        return $this->redirectAfterMutation($request, $position->area_id)
            ->with('success', 'Position ' . $position->callsign . ' created successfully.');
    }

    public function update(PositionRequest $request, Position $position)
    {
        $this->authorize('update', $position);

        $validated = $request->validated();

        if ($validated['area_id'] !== $position->area_id) {
            $this->authorize('create', new Position(['area_id' => $validated['area_id']]));
        }

        $event_position = EventPosition::where('code', $position->callsign)->first();
        if($event_position){

            $event_position->code = $validated['callsign'];
            $event_position->callsign = $validated['name'];
            $event_position->frequency = $validated['frequency'];

            $event_position->save();

        }

        $position->update($validated);



        return $this->redirectAfterMutation($request, $position->area_id)
            ->with('success', 'Position ' . $position->callsign . ' updated successfully.');
    }

    public function destroy(Request $request, Position $position)
    {
        $this->authorize('delete', $position);

        $areaId = $position->area_id;

        $event_position = EventPosition::where('code', $position->callsign)->first();

        if($event_position){
            $event_position->delete();
        }

        $position->delete();

        return $this->redirectAfterMutation($request, $areaId)
            ->with('success', 'Position ' . $position->callsign . ' deleted successfully.');
    }

    private function redirectAfterMutation(Request $request, int $areaId): RedirectResponse
    {
        $route = $request->user()->accessibleAreasForPermission('fir.positions.manage')->isGlobal
            ? route('positions.index.area', $areaId)
            : route('positions.index');

        return redirect($route);
    }
}
