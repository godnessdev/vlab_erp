<?php

namespace App\Http\Controllers\Fiscal;

use App\Domain\Fiscal\LogIntegracaoFiscal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LogIntegracaoFiscalController extends Controller
{
    public function index()
    {
        return LogIntegracaoFiscal::paginate();
    }

    public function show($id)
    {
        return LogIntegracaoFiscal::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'empresa_id' => 'required|uuid',
            'data_log' => 'required|date',
            'endpoint_url' => 'required|string',
            'metodo_http' => 'required|string',
            'status_http' => 'required|integer',
        ]);
        $log = LogIntegracaoFiscal::create($data);
        return response()->json($log, 201);
    }

    public function update(Request $request, $id)
    {
        $log = LogIntegracaoFiscal::findOrFail($id);
        $data = $request->all();
        $log->update($data);
        return $log;
    }

    public function destroy($id)
    {
        $log = LogIntegracaoFiscal::findOrFail($id);
        $log->delete();
        return response()->noContent();
    }
}
