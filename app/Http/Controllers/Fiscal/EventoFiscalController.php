<?php

namespace App\Http\Controllers\Fiscal;

use App\Domain\Fiscal\EventoFiscal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EventoFiscalController extends Controller
{
    public function index()
    {
        return EventoFiscal::paginate();
    }

    public function show($id)
    {
        return EventoFiscal::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nfse_id' => 'required|uuid',
            'tipo_evento' => 'required|string',
            'data_evento' => 'required|date',
            'motivo' => 'required|string',
            'usuario_id' => 'required|uuid',
        ]);
        $evento = EventoFiscal::create($data);

        return response()->json($evento, 201);
    }

    public function update(Request $request, $id)
    {
        $evento = EventoFiscal::findOrFail($id);
        $data = $request->all();
        $evento->update($data);

        return $evento;
    }

    public function destroy($id)
    {
        $evento = EventoFiscal::findOrFail($id);
        $evento->delete();

        return response()->noContent();
    }
}
