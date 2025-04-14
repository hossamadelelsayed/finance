<?php

namespace Pickappo\Finance\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pickappo\Finance\Contract\OrderDTOContract;
use Pickappo\Finance\DTOs\Wallets\GetWalletDTO;
use Pickappo\Finance\Enums\Transaction\TransactionReason;
use Pickappo\Finance\Enums\Transaction\TransactionReferenceType;
use Pickappo\Finance\Enums\Wallet\WalletOwnerType;
use Pickappo\Finance\Enums\Wallet\WalletReferenceType;


class TipsFinancialService
{
    public function __construct(protected WalletService $walletService)
    {
    }

    public function calculateFinance(OrderDTOContract $order, $amount)
    {
        try {
            DB::beginTransaction();

            $captainId = $order->getAssignedCaptain();
            $captainWalletDto = new GetWalletDTO($captainId, WalletOwnerType::CAPTAIN, WalletReferenceType::PICKAPPO);
            $wallet = $this->walletService->getWallet($captainWalletDto);
            $wallet->credit(
                $amount,
                $order->getId(),
                TransactionReferenceType::ORDER,
                reason: TransactionReason::TIPS,
            );

            DB::commit();
        } catch (\Exception|\Error $e) {
            DB::rollBack();
            Log::error("Tips For Order {$order->getId()} Transaction Failed", [
                'order_id' => $order->getId(),
                'amount' => $amount,
                'exception' => $e->getMessage(),
            ]);
            throw new \Exception("Tips For Order {$order->getId()} Transaction Failed : ".$e->getMessage(), 0, $e);
        }
    }
}
