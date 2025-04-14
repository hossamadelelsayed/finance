<?php

namespace Pickappo\Finance\Services;

use Pickappo\Finance\DTOs\Custody\CreateCustodyDTO;
use Pickappo\Finance\DTOs\Custody\GetCustodyDTO;
use Pickappo\Finance\Events\CustodyCreatedEvent;
use Pickappo\Finance\Repositories\CustodyRepository;

class CustodyService
{
    public function __construct(protected CustodyRepository $custodyRepository)
    {
    }

    /**
     * @param GetCustodyDTO $dto
     * @return mixed
     */
    public function getCustodes(GetCustodyDTO $dto)
    {
        return $this->custodyRepository->get($dto);
    }
    /**
     * @param CreateCustodyDTO $dto
     * @return mixed
     */
    public function createCustody(CreateCustodyDTO $dto)
    {
        $custody = $this->custodyRepository->create($dto);
        event(new CustodyCreatedEvent($custody));
        return $custody;
    }

    public function sumCustodyPerCompany(string $companyId)
    {
        return $this->custodyRepository->sumCustodyPerCompany($companyId);
    }
}
