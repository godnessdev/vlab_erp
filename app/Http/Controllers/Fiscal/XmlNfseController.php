<?php

namespace App\Http\Controllers\Fiscal;

use App\Domain\Fiscal\XmlNfse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class XmlNfseController extends Controller
{
    public function index()
    {
        return XmlNfse::paginate();
    }

    public function show($id)
    {
        return XmlNfse::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nfse_id' => 'required|uuid',
            'versao_schema' => 'required|string',
            'xml_assinado' => 'required|string',
            'hash_sha256' => 'required|string',
            'tamanho_bytes' => 'required|integer',
            'data_armazenamento' => 'required|date',
        ]);
        $xml = XmlNfse::create($data);
        return response()->json($xml, 201);
    }

    public function update(Request $request, $id)
    {
        $xml = XmlNfse::findOrFail($id);
        $data = $request->all();
        $xml->update($data);
        return $xml;
    }

    public function destroy($id)
    {
        $xml = XmlNfse::findOrFail($id);
        $xml->delete();
        return response()->noContent();
    }
}
