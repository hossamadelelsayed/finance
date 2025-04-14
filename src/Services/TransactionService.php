<?php

namespace Pickappo\Finance\Services;

use Pickappo\Finance\DTOs\Transactions\CreateTransactionDTO;
use Pickappo\Finance\DTOs\Transactions\GetTransactionsDTO;
use Pickappo\Finance\DTOs\Transactions\TransactionsByReasonDTO;
use Pickappo\Finance\DTOs\Transactions\TransactionsResponseDTO;
use Pickappo\Finance\DTOs\Wallets\GetWalletDTO;
use Pickappo\Finance\Enums\Transaction\TransactionReason;
use Pickappo\Finance\Enums\Transaction\TransactionReferenceType;
use Pickappo\Finance\Enums\Transaction\TransactionSubReason;
use Pickappo\Finance\Repositories\WalletRepository;
use Pickappo\Finance\Repositories\WalletTransactionRepository;

class TransactionService
{
    public function __construct(
        protected WalletRepository $walletRepository,
        protected WalletService $walletService,
        protected WalletTransactionRepository $transactionRepository
    ) {
    }

    /**
     * @param CreateTransactionDTO $dto
     * @return void
     */
    public function create(CreateTransactionDTO $dto)
    {
        $walletDto = new GetWalletDTO(
            $dto->ownerId,
            $dto->ownerType,
            $dto->walletReferenceId
        );

        $wallet = $this->walletService->getWallet($walletDto);
        $transaction = $this->transactionRepository->createTransaction($wallet->id, $dto);
        return $transaction;
    }

    /**
     * @param GetTransactionsDTO $dto
     * @return TransactionsResponseDTO
     */
    public function getTransactions(GetTransactionsDTO $dto): TransactionsResponseDTO
    {
        $walletDto = new GetWalletDTO(
            $dto->ownerId,
            $dto->ownerType,
            $dto->referenceId
        );

        $wallet = $this->walletService->getWallet($walletDto);
        if(!$wallet) {
            throw new \Exception("Wallet not found");
        }

        if(!empty($dto->type) && !in_array($dto->type, TransactionReferenceType::all())) {
            $dto->reasons = $this->mapTypeToReasons($dto->type);
            $dto->type = null;
        }

        $transactions = $this->transactionRepository->getWalletTransactions($wallet->id, $dto);

        $statistics = $this->transactionRepository->sumTransactionsByReasons($wallet->id, [
            TransactionReason::ORDER_COST,
            TransactionReason::ORDER_DELIVERY_COST,
            TransactionReason::ADD_CUSTODY,
            TransactionReason::SETTLE_CUSTODY,
            TransactionReason::ADD_BONUS,
            TransactionReason::TIPS,
            TransactionSubReason::ORDER_COST_DEDUCTED_DUE_CANCELLATION,
            TransactionSubReason::ORDER_DELIVERY_COST_DEDUCTED_DUE_CANCELLATION
        ])->pluck('total_amount', 'reason');

        $totalCustody = array_sum([
            $statistics[TransactionReason::ADD_CUSTODY] ?? 0,
            $statistics[TransactionReason::SETTLE_CUSTODY] ?? 0
        ]);

        $totalDeductions = array_sum([
            $statistics[TransactionSubReason::ORDER_COST_DEDUCTED_DUE_CANCELLATION] ?? 0,
            $statistics[TransactionSubReason::ORDER_DELIVERY_COST_DEDUCTED_DUE_CANCELLATION] ?? 0
        ]);

        return new TransactionsResponseDTO(
            transactions: $transactions,
            totalBalance: $wallet->balance,
            totalOrderCost: $statistics[TransactionReason::ORDER_COST] ?? 0,
            totalOrderDeliveryCost: $statistics[TransactionReason::ORDER_DELIVERY_COST] ?? 0,
            totalCustody: $totalCustody,
            totalBonuses: $statistics[TransactionReason::ADD_BONUS] ?? 0,
            totalTips: $statistics[TransactionReason::TIPS] ?? 0,
            totalSubractions: $totalDeductions,
        );
    }

    /**
     * @param string $type
     * @return array
     */
    private function mapTypeToReasons(string $type): array
    {
        $reasons = [];
        switch ($type) {
            case 'order_delivery':
                $reasons[] = TransactionReason::ORDER_DELIVERY_COST;
                break;
            case 'tips':
                $reasons[] = TransactionReason::TIPS;
                break;
            case 'deduction':
                $reasons[] = TransactionSubReason::ORDER_COST_DEDUCTED_DUE_CANCELLATION;
                $reasons[] = TransactionSubReason::ORDER_DELIVERY_COST_DEDUCTED_DUE_CANCELLATION;
                break;
            default:
                break;
        }
        return $reasons;

    }
    /**
     * @return array
     */
    public function getTransactionTypes(): array
    {
        return TransactionReferenceType::all();
    }


    /**
     * @param string $companyId
     */
    public function sumBonusGivenByCompany(string $companyId)
    {
        $captainWallets = $this->walletRepository->getCompanyBasedCaptainWallets($companyId);
        $walletIds = $captainWallets->pluck('id')->toArray();
        return $this->transactionRepository->sumTransactionsByReferenceType(
            $walletIds,
            new TransactionsByReasonDTO(reasonType: TransactionReferenceType::BONUS)
        );
    }
}
