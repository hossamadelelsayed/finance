<?php

namespace Pickappo\Finance\Repositories;

use Pickappo\Finance\DTOs\Custody\CreateCustodyDTO;
use Pickappo\Finance\DTOs\Custody\GetCustodyDTO;
use Pickappo\Finance\Entities\Custody;
use Pickappo\Finance\Enums\MoneyCustodyType;

class CustodyRepository
{
    /**
     * @param CreateCustodyDTO $dto
     * @return Custody
     */
    public function create(CreateCustodyDTO $dto): Custody
    {
        return Custody::create([
            'company_id' => $dto->companyId,
            'user_id' => $dto->userId,
            'created_by' => $dto->creatorId,
            'amount' => $dto->amount,
            'type' => $dto->type,
            'notes' => $dto->notes,
            'attachments' => $dto->attachments,
        ]);
    }

    /**
     * @param GetCustodyDTO $dto
     * @return mixed
     */
    public function get(GetCustodyDTO $dto): mixed
    {
        return Custody::where('company_id', $dto->companyId)
            ->when($dto->userId, fn ($q) => $q->where('user_id', $dto->userId))
            ->when($dto->creatorId, fn ($q) => $q->where('created_by', $dto->creatorId))
            ->when($dto->type, fn ($q) => $q->where('type', $dto->type))
            ->when($dto->fromDate, fn ($q) => $q->whereDate('created_at', '>=', $dto->fromDate))
            ->when($dto->toDate, fn ($q) => $q->whereDate('created_at', '<=', $dto->toDate))
            ->with(
                'user:id,name,username,email,phone',
                'creator:id,name,username,email,phone'
            )->paginate();
    }

    /**
     * @param string $companyId
     */
    public function sumCustodyPerCompany(string $companyId)
    {
        return Custody::where('company_id', $companyId)
            ->selectRaw("SUM(CASE 
                WHEN type = ? THEN amount 
                WHEN type = ? THEN amount * -1 
                ELSE amount 
            END) AS total_amount", [
                MoneyCustodyType::ADD_CUSTODY,
                MoneyCustodyType::SETTLE_CUSTODY
            ])
        ->value('total_amount');
    }

}
