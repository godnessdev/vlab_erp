<?php

namespace App\Http\Controllers\Fiscal;

use App\Domain\Fiscal\Rps;
use App\Domain\Fiscal\Services\RpsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RpsController extends Controller
{
    protected $rpsService;

    public function __construct(RpsService $rpsService)
    {
        $this->rpsService = $rpsService;
    }

    public function index()
    {
        return Rps::paginate();
    }

    public function show($id)
    {
        return Rps::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fatura_id' => 'required|uuid',
        ]);
        $rps = $this->rpsService->gerarRps($data['fatura_id']);
        return response()->json($rps, 201);
    }

    public function update(Request $request, $id)
    {
        $rps = Rps::findOrFail($id);
        $data = $request->all();
        $rps->update($data);
        return $rps;
    }

    public function destroy($id)
    {
        $rps = Rps::findOrFail($id);
        $rps->delete();
        return response()->noContent();
    }
}
