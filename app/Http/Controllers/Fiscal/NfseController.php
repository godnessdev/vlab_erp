<?php

namespace App\Http\Controllers\Fiscal;

use App\Domain\Fiscal\Nfse;
use App\Domain\Fiscal\Rps;
use App\Domain\Fiscal\Services\NfseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NfseController extends Controller
{
    protected $nfseService;

    public function __construct(NfseService $nfseService)
    {
        $this->nfseService = $nfseService;
    }

    public function index(Request $request)
    {
        $nfse = Nfse::query()
            ->orderByDesc('data_emissao')
            ->paginate($request->integer('per_page', 15));

        if ($request->expectsJson()) {
            return $nfse;
        }

        return view('modules.list', [
            'title' => 'NFS-e',
            'description' => 'Notas fiscais de servico eletronicas geradas pelo modulo fiscal.',
            'records' => $nfse,
            'columns' => [
                ['label' => 'Numero', 'key' => 'numero_nfse'],
                ['label' => 'Codigo verificacao', 'key' => 'codigo_verificacao'],
                ['label' => 'Emissao', 'key' => 'data_emissao'],
                ['label' => 'Municipio', 'key' => 'municipio_prestacao'],
                ['label' => 'Status', 'key' => 'status'],
            ],
        ]);
    }

    public function show($id)
    {
        return Nfse::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rps_id' => 'required|uuid',
        ]);
        $rps = Rps::findOrFail($data['rps_id']);
        $nfse = $this->nfseService->converterRpsParaNfse($rps);

        return response()->json($nfse, 201);
    }

    public function update(Request $request, $id)
    {
        $nfse = Nfse::findOrFail($id);
        $data = $request->all();
        $nfse->update($data);

        return $nfse;
    }

    public function destroy($id)
    {
        $nfse = Nfse::findOrFail($id);
        $nfse->delete();

        return response()->noContent();
    }

    public function cancelar(Request $request, $id)
    {
        $nfse = Nfse::findOrFail($id);
        $data = $request->validate([
            'motivo' => 'required|string|min:15',
        ]);
        $this->nfseService->cancelarNfse($nfse, $data['motivo']);

        return response()->json(['status' => 'cancelada']);
    }
}
