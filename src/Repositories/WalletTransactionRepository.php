<?php

namespace Pickappo\Finance\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Pickappo\Finance\DTOs\Transactions\CreateTransactionDTO;
use Pickappo\Finance\DTOs\Transactions\GetTransactionsDTO;
use Pickappo\Finance\DTOs\Transactions\TransactionsByOwnerTypeDTO;
use Pickappo\Finance\DTOs\Transactions\TransactionsByReasonDTO;
use Pickappo\Finance\Entities\WalletTransaction;

class WalletTransactionRepository
{
    /**
     * @param string $walletId
     * @param CreateTransactionDTO $dto
     * @return WalletTransaction
     */
    public function createTransaction(string $walletId, CreateTransactionDTO $dto): WalletTransaction
    {
        return WalletTransaction::create([
            'wallet_id' => $walletId,
            'amount' => $dto->amount,
            'reference_id' => $dto->referenceId,
            'reference_type' => $dto->referenceType,
            'reason' => $dto->reason,
            'sub_reason' => $dto->subReason
        ]);
    }

    /**
     * @param string $walletId
     * @param GetTransactionsDTO $dto
     */
    public function getWalletTransactions(string $walletId, GetTransactionsDTO $dto)
    {
        return WalletTransaction::where('wallet_id', $walletId)
        ->when($dto->type, fn ($query) => $query->where('reference_type', $dto->type))
        ->when($dto->reasons, function ($query) use ($dto) {
            $query->where(function ($q) use ($dto) {
                $q->WhereIn('reason', (array) $dto->reasons)
                  ->orWhereIn('sub_reason', (array) $dto->reasons);
            });
        })
        ->when($dto->fromDate, fn ($query) => $query->whereDate('created_at', '>=', $dto->fromDate))
        ->when($dto->toDate, fn ($query) => $query->whereDate('created_at', '<=', $dto->toDate))
        ->with(['reference','reference.company'])
        ->orderBy('created_at', 'desc')
        ->paginate();
    }

    /**
     * @param TransactionsByOwnerTypeDTO $dto
     * @return mixed
     */
    public function sumTransactionsByOwner(TransactionsByOwnerTypeDTO $dto): mixed
    {
        return DB::table('wallet_transactions as wt')
            ->join('wallets as w', 'wt.wallet_id', '=', 'w.id')
            ->where('w.owner_type', $dto->ownerType)
            ->where('w.reference_type', $dto->walletType)
            ->when($dto->startDate, fn ($query) => $query->whereDate('wt.created_at', '>=', $dto->startDate))
            ->when($dto->endDate, fn ($query) => $query->whereDate('wt.created_at', '<=', $dto->endDate))
            ->groupBy('wt.wallet_id')
            ->having('total_amount', '!=', 0)
            ->select('w.*', DB::raw('SUM(wt.amount) as total_amount'))
            ->get();
    }

    /**
     * @param string $walletId
     * @param TransactionsByReasonDTO $dto
     * @return integer|float
     */
    public function sumTransactionsByReasonType(string $walletId, TransactionsByReasonDTO $dto): int|float
    {
        return WalletTransaction::where('wallet_id', $walletId)
            ->where('reason', $dto->reasonType)
            ->when($dto->startDate, fn ($query) => $query->whereDate('created_at', '>=', $dto->startDate))
            ->when($dto->endDate, fn ($query) => $query->whereDate('created_at', '<=', $dto->endDate))
            ->sum('amount');
    }

    /**
     * @param string $walletId
     * @param array $reasons
     * @param [type] $startDate
     * @param [type] $endDate
     * @return Collection
     */
    public function sumTransactionsByReasons(string $walletId, array $reasons, $startDate = null, $endDate = null): Collection
    {
        $query1 = WalletTransaction::where('wallet_id', $walletId)
            ->whereIn('reason', $reasons)
            ->when($startDate, fn ($query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('created_at', '<=', $endDate))
            ->groupBy('reason')
            ->selectRaw('reason, SUM(amount) AS total_amount');

        $query2 = WalletTransaction::where('wallet_id', $walletId)
            ->whereIn('sub_reason', $reasons)
            ->when($startDate, fn ($query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('created_at', '<=', $endDate))
            ->groupBy('sub_reason')
            ->selectRaw('sub_reason AS reason, SUM(amount) AS total_amount');

        return $query1->unionAll($query2)->get();
    }

    /**
     * @param array $walletIds
     * @param TransactionsByReasonDTO $dto
     * @return integer|float
     */
    public function sumTransactionsByReferenceType(array $walletIds, TransactionsByReasonDTO $dto): int|float
    {
        return WalletTransaction::whereIn('wallet_id', $walletIds)
            ->where('reference_type', $dto->reasonType)
            ->when($dto->startDate, fn ($query) => $query->whereDate('created_at', '>=', $dto->startDate))
            ->when($dto->endDate, fn ($query) => $query->whereDate('created_at', '<=', $dto->endDate))
            ->sum('amount');
    }
}
