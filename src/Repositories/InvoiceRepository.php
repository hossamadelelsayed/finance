<?php

namespace Pickappo\Finance\Repositories;

use Pickappo\Finance\DTOs\Invoice\CreateInvoiceDTO;
use Pickappo\Finance\DTOs\Invoice\GetInvoicesDTO;
use Pickappo\Finance\Entities\PickappoInvoice;

class InvoiceRepository
{
    /**
     * @param CreateInvoiceDTO $dto
     * @return PickappoInvoice
     */
    public function create(CreateInvoiceDTO $dto): PickappoInvoice
    {
        return PickappoInvoice::updateOrCreate(
            [
                'entity_id' => $dto->entityId,
                'entity_type' => $dto->entityType,
                'start_date' => $dto->startDate,
                'end_date' => $dto->endDate,
            ],
            [
                'amount' => $dto->amount,
                'invoice_type' => $dto->invoiceType,
                'status' => $dto->status,
                'statistics' => $dto->statistics
            ]
        );
    }

    /**
     * @param GetInvoicesDTO $dto
     * @return mixed
     */
    public function getInvoices(GetInvoicesDTO $dto): mixed
    {
        return PickappoInvoice::where('entity_id', $dto->entityId)
            ->where('entity_type', $dto->entityType)
            ->paginate();
    }

    /**
     * @param GetInvoicesDTO $dto
     * @return PickappoInvoice|null
     */
    public function getLastInvoice(GetInvoicesDTO $dto): PickappoInvoice|null
    {
        return PickappoInvoice::where('entity_id', $dto->entityId)
            ->where('entity_type', $dto->entityType)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
