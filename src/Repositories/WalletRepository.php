<?php

namespace Pickappo\Finance\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Pickappo\Finance\DTOs\Wallets\CreateWalletDTO;
use Pickappo\Finance\DTOs\Wallets\GetWalletDTO;
use Pickappo\Finance\Entities\Wallet;
use Pickappo\Finance\Enums\Wallet\WalletOwnerType;
use Pickappo\Finance\Enums\Wallet\WalletReferenceType;

class WalletRepository
{
    /**
     * @param CreateWalletDTO $dto
     * @param boolean $lockForUpdate
     * @return Wallet|null
     */
    public function getByOwner(GetWalletDTO $dto, $lockForUpdate = false): Wallet|null
    {
        return Wallet::where([
            'owner_id' => $dto->ownerId,
            'owner_type' => $dto->ownerType,
            'reference_id' => $dto->referenceId
        ])->when($lockForUpdate, function ($query) {
            return $query->lockForUpdate();
        })->first();
    }

    /**
     * @param string $walletId
     * @param boolean $lockForUpdate
     * @return Wallet
     */
    public function getById(string $walletId, $lockForUpdate = false): Wallet
    {
        return Wallet::when($lockForUpdate, function ($query) {
            return $query->lockForUpdate();
        })->where('id', $walletId)
        ->first();
    }

    /**
     * @param CreateWalletDTO $dto
     * @return Wallet
     */
    public function create(CreateWalletDTO $dto): Wallet
    {
        return Wallet::create([
            'owner_id' => $dto->ownerId,
            'owner_type' => $dto->ownerType,
            'reference_id' => $dto->referenceId,
            'reference_type' => $dto->referenceId == WalletReferenceType::PICKAPPO
            ? WalletReferenceType::PICKAPPO
            : WalletReferenceType::COMPANY
        ]);
    }

    /**
     * @param string $companyId
     * @return Collection
     */
    public function getCompanyBasedCaptainWallets(string $companyId): Collection
    {
        return Wallet::where('reference_type', WalletReferenceType::COMPANY)
        ->where('reference_id', $companyId)
        ->where('owner_type', WalletOwnerType::CAPTAIN)
        ->select('id', 'balance')
        ->get();
    }


}
