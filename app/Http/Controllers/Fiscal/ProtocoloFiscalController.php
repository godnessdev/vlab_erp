<?php

namespace App\Http\Controllers\Fiscal;

use App\Domain\Fiscal\ProtocoloFiscal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProtocoloFiscalController extends Controller
{
    public function index()
    {
        return ProtocoloFiscal::paginate();
    }

    public function show($id)
    {
        return ProtocoloFiscal::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'empresa_id' => 'required|uuid',
            'tipo_operacao' => 'required|string',
            'data_envio' => 'required|date',
            'status_resposta' => 'required|string',
            'xml_envio' => 'required|string',
        ]);
        $proto = ProtocoloFiscal::create($data);
        return response()->json($proto, 201);
    }

    public function update(Request $request, $id)
    {
        $proto = ProtocoloFiscal::findOrFail($id);
        $data = $request->all();
        $proto->update($data);
        return $proto;
    }

    public function destroy($id)
    {
        $proto = ProtocoloFiscal::findOrFail($id);
        $proto->delete();
        return response()->noContent();
    }
}
