<?php

namespace Pickappo\Finance\Services;

use Pickappo\Finance\DTOs\Wallets\CreateWalletDTO;
use Pickappo\Finance\DTOs\Wallets\GetWalletDTO;
use Pickappo\Finance\Entities\Wallet;
use Pickappo\Finance\Events\Wallet\WalletCreatedEvent;
use Pickappo\Finance\Repositories\WalletRepository;

class WalletService
{
    public function __construct(protected WalletRepository $walletRepository)
    {
    }

    /**
     * @param GetWalletDTO $dto
     * @param boolean|null $lockForUpdate
     * @return Wallet|null
     */
    public function getWallet(GetWalletDTO $dto, ?bool $lockForUpdate = false): ?Wallet
    {
        $wallet = $this->checkWalletCreation($dto);
        if(!$wallet) {
            $wallet = $this->walletRepository->getByOwner($dto, $lockForUpdate);
        }

        return $wallet;
    }

    /**
     * @param GetWalletDTO $dto
     * @return Wallet|null
     */
    private function checkWalletCreation(GetWalletDTO $dto): ?Wallet
    {
        $wallet = $this->walletRepository->getByOwner($dto);
        if (!$wallet) {
            event(new WalletCreatedEvent(
                $dto->ownerId,
                $dto->ownerType,
                $dto->referenceId
            ));
        }

        return $wallet;
    }

    /**
     * @param CreateWalletDTO $dto
     * @return Wallet
     */
    public function createWallet(CreateWalletDTO $dto): Wallet
    {
        return $this->walletRepository->create($dto);
    }
}
