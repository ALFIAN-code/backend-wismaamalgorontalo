<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Modules\Inventory\Http\Requests\StoreInventoryRequest;
use Modules\Inventory\Repositories\Contracts\InventoryRepositoryInterface;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Transformers\InventoryResource;

class InventoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function index()
    {
        $inventories = $this->inventoryRepository->getAll();
        return $this->apiSuccess(InventoryResource::collection($inventories), 'Data inventory berhasil diambil');
    }

    public function store(StoreInventoryRequest $request)
    {
        $inventory = $this->inventoryService->createInventory($request->validated());

        return $this->apiSuccess(
            new InventoryResource($inventory),
            'Barang berhasil ditambahkan',
            201
        );
    }
}
