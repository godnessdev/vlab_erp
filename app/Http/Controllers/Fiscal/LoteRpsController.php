<?php

namespace App\Http\Controllers\Fiscal;

use App\Domain\Fiscal\LoteRps;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoteRpsController extends Controller
{
    public function index(Request $request)
    {
        $lotes = LoteRps::query()
            ->orderByDesc('data_geracao')
            ->paginate($request->integer('per_page', 15));

        if ($request->expectsJson()) {
            return $lotes;
        }

        return view('modules.list', [
            'title' => 'Lotes RPS',
            'description' => 'Lotes de RPS preparados para integracao fiscal.',
            'records' => $lotes,
            'columns' => [
                ['label' => 'Numero lote', 'key' => 'numero_lote'],
                ['label' => 'Geracao', 'key' => 'data_geracao'],
                ['label' => 'Quantidade', 'key' => 'quantidade_rps'],
                ['label' => 'Valor servicos', 'key' => 'valor_total_servicos', 'type' => 'money'],
                ['label' => 'Status', 'key' => 'status'],
            ],
        ]);
    }

    public function show($id)
    {
        return LoteRps::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'empresa_id' => 'required|uuid',
            'numero_lote' => 'required|integer',
            'data_geracao' => 'required|date',
            'quantidade_rps' => 'required|integer',
            'valor_total_servicos' => 'required|numeric',
            'inscricao_municipal' => 'required|string',
            'cnpj' => 'required|string',
        ]);
        $lote = LoteRps::create($data);

        return response()->json($lote, 201);
    }

    public function update(Request $request, $id)
    {
        $lote = LoteRps::findOrFail($id);
        $data = $request->all();
        $lote->update($data);

        return $lote;
    }

    public function destroy($id)
    {
        $lote = LoteRps::findOrFail($id);
        $lote->delete();

        return response()->noContent();
    }
}
