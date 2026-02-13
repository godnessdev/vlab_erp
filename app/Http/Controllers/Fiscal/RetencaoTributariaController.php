<?php

namespace App\Http\Controllers\Fiscal;

use App\Domain\Fiscal\RetencaoTributaria;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RetencaoTributariaController extends Controller
{
    public function index()
    {
        return RetencaoTributaria::paginate();
    }

    public function show($id)
    {
        return RetencaoTributaria::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nfse_id' => 'required|uuid',
            'tipo_retencao' => 'required|string',
            'base_calculo' => 'required|numeric',
            'aliquota' => 'required|numeric',
            'valor_retido' => 'required|numeric',
            'responsavel_retencao' => 'required|string',
        ]);
        $ret = RetencaoTributaria::create($data);
        return response()->json($ret, 201);
    }

    public function update(Request $request, $id)
    {
        $ret = RetencaoTributaria::findOrFail($id);
        $data = $request->all();
        $ret->update($data);
        return $ret;
    }

    public function destroy($id)
    {
        $ret = RetencaoTributaria::findOrFail($id);
        $ret->delete();
        return response()->noContent();
    }
}
