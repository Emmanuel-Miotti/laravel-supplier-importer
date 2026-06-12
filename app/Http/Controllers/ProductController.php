<?php

namespace App\Http\Controllers;

use App\Services\ExcelImportService;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{

    // Inyecto el servicio mantiene el controlador chico y facilitar los tests
    public function __construct(
         private readonly ExcelImportService $importService
    ) {}


    public function import(Request $request): JsonResponse
    {
        // El límite de 10MB
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
             'provider' => 'nullable|string',
        ]);

        try {
            $file = $request->file('file');
            
            $result = $this->importService->import(
                $file->getRealPath(),
                $request->input('provider')
            );

            return response()->json([
                'message' => 'Importación finalizada con éxito',
                'data' => $result
            ]);

        } catch (\Throwable $e) {
            //Capturo cualquier fallo devolver un 422 controlado en vez de un 500 roto
            return response()->json([
                'message' => 'Error al procesar el archivo',
                'error' => $e->getMessage()
            ], 422);
        }
    }



    public function index(Request $request): JsonResponse
    {
        //loading de las relaciones para prevenir el problema de rendimiento
        $query = Product::with(['supplier', 'prices', 'taxes']);

        //  evito alterar la query
        if ($request->filled('brand')) {
            $query->where('brand', 'like', '%' . $request->brand . '%');
        }

        if ($request->filled('reference')) {
            $query->where('reference', $request->reference);
        }

        //proteger el consumo de memoria del servidor cuando la base de datos escale a miles de filas
        return response()->json($query->paginate(50));
    }


}