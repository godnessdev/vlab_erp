<?php

namespace App\Http\Controllers\Fiscal;

use App\Domain\Fiscal\Rps;
use App\Domain\Fiscal\Services\RpsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RpsController extends Controller
{
    protected $rpsService;

    public function __construct(RpsService $rpsService)
    {
        $this->rpsService = $rpsService;
    }

    public function index(Request $request)
    {
        $rps = Rps::query()
            ->orderByDesc('data_emissao')
            ->paginate($request->integer('per_page', 15));

        if ($request->expectsJson()) {
            return $rps;
        }

        return view('modules.list', [
            'title' => 'RPS',
            'description' => 'Recibos provisorios de servico gerados pelo faturamento.',
            'records' => $rps,
            'columns' => [
                ['label' => 'Numero', 'key' => 'numero_rps'],
                ['label' => 'Serie', 'key' => 'serie'],
                ['label' => 'Emissao', 'key' => 'data_emissao'],
                ['label' => 'Valor servicos', 'key' => 'valor_servicos', 'type' => 'money'],
                ['label' => 'Situacao', 'key' => 'situacao'],
            ],
        ]);
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
