<?php

namespace App\Http\Controllers\Fiscal;

use App\Domain\Fiscal\CertificadoDigital;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CertificadoDigitalController extends Controller
{
    public function index()
    {
        return CertificadoDigital::paginate();
    }

    public function show($id)
    {
        return CertificadoDigital::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'empresa_id' => 'required|uuid',
            'alias' => 'required|string',
            'arquivo_pfx' => 'required',
            'senha' => 'required|string',
            'subject' => 'required|string',
            'issuer' => 'required|string',
            'serial_number' => 'required|string',
            'data_validade_inicio' => 'required|date',
            'data_validade_fim' => 'required|date',
        ]);
        $cert = CertificadoDigital::create($data);

        return response()->json($cert, 201);
    }

    public function update(Request $request, $id)
    {
        $cert = CertificadoDigital::findOrFail($id);
        $data = $request->all();
        $cert->update($data);

        return $cert;
    }

    public function destroy($id)
    {
        $cert = CertificadoDigital::findOrFail($id);
        $cert->delete();

        return response()->noContent();
    }
}
