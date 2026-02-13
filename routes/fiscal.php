<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Fiscal\RpsController;
use App\Http\Controllers\Fiscal\LoteRpsController;
use App\Http\Controllers\Fiscal\NfseController;
use App\Http\Controllers\Fiscal\RetencaoTributariaController;
use App\Http\Controllers\Fiscal\ProtocoloFiscalController;
use App\Http\Controllers\Fiscal\EventoFiscalController;
use App\Http\Controllers\Fiscal\CertificadoDigitalController;
use App\Http\Controllers\Fiscal\LogIntegracaoFiscalController;
use App\Http\Controllers\Fiscal\XmlNfseController;

Route::prefix('fiscal')->group(function () {
    // RPS
    Route::get('rps', [RpsController::class, 'index']);
    Route::post('rps', [RpsController::class, 'store']);
    Route::get('rps/{id}', [RpsController::class, 'show']);
    Route::put('rps/{id}', [RpsController::class, 'update']);
    Route::delete('rps/{id}', [RpsController::class, 'destroy']);

    // Lote RPS
    Route::get('lotes', [LoteRpsController::class, 'index']);
    Route::post('lotes', [LoteRpsController::class, 'store']);
    Route::get('lotes/{id}', [LoteRpsController::class, 'show']);
    Route::put('lotes/{id}', [LoteRpsController::class, 'update']);
    Route::delete('lotes/{id}', [LoteRpsController::class, 'destroy']);

    // NFS-e
    Route::get('nfse', [NfseController::class, 'index']);
    Route::post('nfse', [NfseController::class, 'store']);
    Route::get('nfse/{id}', [NfseController::class, 'show']);
    Route::put('nfse/{id}', [NfseController::class, 'update']);
    Route::delete('nfse/{id}', [NfseController::class, 'destroy']);
    Route::post('nfse/{id}/cancelar', [NfseController::class, 'cancelar']);

    // Retenção Tributária
    Route::get('retencoes', [RetencaoTributariaController::class, 'index']);
    Route::post('retencoes', [RetencaoTributariaController::class, 'store']);
    Route::get('retencoes/{id}', [RetencaoTributariaController::class, 'show']);
    Route::put('retencoes/{id}', [RetencaoTributariaController::class, 'update']);
    Route::delete('retencoes/{id}', [RetencaoTributariaController::class, 'destroy']);

    // Protocolo Fiscal
    Route::get('protocolos', [ProtocoloFiscalController::class, 'index']);
    Route::post('protocolos', [ProtocoloFiscalController::class, 'store']);
    Route::get('protocolos/{id}', [ProtocoloFiscalController::class, 'show']);
    Route::put('protocolos/{id}', [ProtocoloFiscalController::class, 'update']);
    Route::delete('protocolos/{id}', [ProtocoloFiscalController::class, 'destroy']);

    // Evento Fiscal
    Route::get('eventos', [EventoFiscalController::class, 'index']);
    Route::post('eventos', [EventoFiscalController::class, 'store']);
    Route::get('eventos/{id}', [EventoFiscalController::class, 'show']);
    Route::put('eventos/{id}', [EventoFiscalController::class, 'update']);
    Route::delete('eventos/{id}', [EventoFiscalController::class, 'destroy']);

    // Certificado Digital
    Route::get('certificados', [CertificadoDigitalController::class, 'index']);
    Route::post('certificados', [CertificadoDigitalController::class, 'store']);
    Route::get('certificados/{id}', [CertificadoDigitalController::class, 'show']);
    Route::put('certificados/{id}', [CertificadoDigitalController::class, 'update']);
    Route::delete('certificados/{id}', [CertificadoDigitalController::class, 'destroy']);

    // Log Integração Fiscal
    Route::get('logs', [LogIntegracaoFiscalController::class, 'index']);
    Route::post('logs', [LogIntegracaoFiscalController::class, 'store']);
    Route::get('logs/{id}', [LogIntegracaoFiscalController::class, 'show']);
    Route::put('logs/{id}', [LogIntegracaoFiscalController::class, 'update']);
    Route::delete('logs/{id}', [LogIntegracaoFiscalController::class, 'destroy']);

    // XML NFS-e
    Route::get('xmls', [XmlNfseController::class, 'index']);
    Route::post('xmls', [XmlNfseController::class, 'store']);
    Route::get('xmls/{id}', [XmlNfseController::class, 'show']);
    Route::put('xmls/{id}', [XmlNfseController::class, 'update']);
    Route::delete('xmls/{id}', [XmlNfseController::class, 'destroy']);
});
