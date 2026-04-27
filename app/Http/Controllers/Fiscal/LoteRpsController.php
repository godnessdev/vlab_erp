<?php

namespace App\Http\Controllers\Fiscal;

use App\Domain\Fiscal\LoteRps;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoteRpsController extends Controller
{
    public function index()
    {
        return LoteRps::paginate();
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
