<?php

namespace Pickappo\Finance\Services;

use Carbon\Carbon;
use Pickappo\Finance\DTOs\Invoice\CreateInvoiceDTO;
use Pickappo\Finance\DTOs\Invoice\GetInvoicesDTO;
use Pickappo\Finance\DTOs\Invoice\InvoiceResponseDTO;
use Pickappo\Finance\DTOs\Transactions\GetTransactionsDTO;
use Pickappo\Finance\DTOs\Transactions\TransactionsByOwnerTypeDTO;
use Pickappo\Finance\DTOs\Transactions\TransactionsByReasonDTO;
use Pickappo\Finance\DTOs\Wallets\GetWalletDTO;
use Pickappo\Finance\Enums\Invoice\PickappoInvoiceStatus;
use Pickappo\Finance\Enums\Invoice\PickappoInvoiceType;
use Pickappo\Finance\Enums\Transaction\TransactionReason;
use Pickappo\Finance\Enums\Wallet\WalletOwnerType;
use Pickappo\Finance\Enums\Wallet\WalletReferenceType;
use Pickappo\Finance\Repositories\InvoiceRepository;
use Pickappo\Finance\Repositories\WalletTransactionRepository;

class InvoiceService
{
    public function __construct(
        protected InvoiceRepository $invoiceRepository,
        protected WalletTransactionRepository $transactionRepository,
        protected WalletService $walletService
    ) {
    }

    /**
     * @param GetInvoicesDTO $dto
     * @return mixed
     */
    public function getInvoices(GetInvoicesDTO $dto): mixed
    {
        return $this->invoiceRepository->getInvoices($dto);
    }

    /**
     * @param string $ownerType
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return void
     */
    public function generateInvoices(string $ownerType, Carbon $startDate, Carbon $endDate)
    {
        $transactionDto = new TransactionsByOwnerTypeDTO(
            $ownerType,
            WalletReferenceType::PICKAPPO,
            $startDate,
            $endDate
        );

        $wallets = $this->transactionRepository->sumTransactionsByOwner($transactionDto);
        foreach ($wallets as $wallet) {
            $statistics = $this->calculateStatistics($wallet, $startDate, $endDate);
            $dto = new CreateInvoiceDTO(
                entityId: $wallet->owner_id,
                entityType: $ownerType,
                amount: abs($wallet->total_amount),
                invoiceType: $wallet->total_amount < 0 ? PickappoInvoiceType::PAYABLE : PickappoInvoiceType::RECEIVABLE,
                status: PickappoInvoiceStatus::PENDING,
                startDate: $startDate,
                endDate: $endDate,
                statistics: $statistics
            );
            $this->invoiceRepository->create($dto);
        }
    }

    private function calculateStatistics($wallet, Carbon $startDate, Carbon $endDate)
    {
        $statistics = [];
        switch($wallet->owner_type) {
            case WalletOwnerType::LOGISTIC_COMPANY:
                $statistics = $this->getCompanyStatistics($wallet, $startDate, $endDate);
                break;
        }
        return $statistics;
    }

    private function getCompanyStatistics($wallet, Carbon $startDate, Carbon $endDate)
    {
        $statistics[TransactionReason::PICKAPPO_COMMISSION] = $this->transactionRepository->sumTransactionsByReasonType(
            $wallet->id,
            new TransactionsByReasonDTO(
                TransactionReason::PICKAPPO_COMMISSION,
                $startDate,
                $endDate
            )
        );
        $statistics[TransactionReason::ORDER_DELIVERY_COST] = $this->transactionRepository->sumTransactionsByReasonType(
            $wallet->id,
            new TransactionsByReasonDTO(
                TransactionReason::ORDER_DELIVERY_COST,
                $startDate,
                $endDate
            )
        );
        return $statistics;
    }

    /**
     * @param GetInvoicesDTO $dto
     * @return InvoiceResponseDTO
     */
    public function getCurrentInvoice(GetInvoicesDTO $dto): InvoiceResponseDTO
    {
        $fromDate = null;
        $invoice = $this->invoiceRepository->getLastInvoice($dto);
        if($invoice) {
            $fromDate = $invoice->end_date;
        }

        $walletDto = new GetWalletDTO(
            $dto->entityId,
            $dto->entityType,
            WalletReferenceType::PICKAPPO,
        );

        $wallet = $this->walletService->getWallet($walletDto);
        if(!$wallet) {
            throw new \Exception("Wallet not found");
        }

        $transactionsDto = new GetTransactionsDTO(
            ownerId: $dto->entityId,
            ownerType: $dto->entityType,
            referenceId: WalletReferenceType::PICKAPPO,
            fromDate: $fromDate
        );

        $transactions = $this->transactionRepository->getWalletTransactions($wallet->id, $transactionsDto);
        $statistics = $this->transactionRepository->sumTransactionsByReasons($wallet->id, [
            TransactionReason::ORDER_DELIVERY_COST,
            TransactionReason::PICKAPPO_COMMISSION,
        ])->pluck('total_amount', 'reason');

        $totalDeliveryFees = $statistics[TransactionReason::ORDER_DELIVERY_COST] ?? 0;
        $totalPickappoFees = $statistics[TransactionReason::PICKAPPO_COMMISSION] ?? 0;
        
        return new InvoiceResponseDTO(
            invoice: $invoice,
            transactions: $transactions,
            totalDeliveryFees: $totalDeliveryFees,
            totalPickappoFees: $totalPickappoFees,
            total : $totalDeliveryFees + $totalPickappoFees
        );

    }
}
