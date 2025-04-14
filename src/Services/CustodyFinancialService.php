<?php

namespace Pickappo\Finance\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pickappo\Finance\DTOs\Wallets\GetWalletDTO;
use Pickappo\Finance\Enums\MoneyCustodyType;
use Pickappo\Finance\Enums\Transaction\TransactionReason;
use Pickappo\Finance\Enums\Transaction\TransactionReferenceType;
use Pickappo\Finance\Enums\Wallet\WalletOwnerType;

class CustodyFinancialService
{
    public function __construct(protected WalletService $walletService)
    {
    }

    public function calculateFinance($custody)
    {
        try {
            DB::beginTransaction();

            $captainId = $custody->user_id;
            $companyId = $custody->company_id;
            $captainWalletDto = new GetWalletDTO($captainId, WalletOwnerType::CAPTAIN, $companyId);
            $wallet = $this->walletService->getWallet($captainWalletDto);
            
            switch ($custody->type) {
                case MoneyCustodyType::ADD_CUSTODY:
                    $wallet->debit(
                        $custody->amount,
                        $custody->id,
                        TransactionReferenceType::CUSTODY,
                        reason: TransactionReason::ADD_CUSTODY
                    );
                    break;
                case MoneyCustodyType::SETTLE_CUSTODY:
                    $wallet->credit(
                        $custody->amount,
                        $custody->id,
                        TransactionReferenceType::CUSTODY,
                        reason: TransactionReason::SETTLE_CUSTODY
                    );
                    break;
                default:
                    throw new \Exception("Invalid action custody : " . $custody->type);
            }
            DB::commit();
        } catch (\Exception|\Error $e) {
            DB::rollBack();
            Log::error("Custody {$custody->id} Transaction Failed", [
                'custody_id' => $custody->id,
                'user_id' => $custody->user_id,
                'company_id' => $custody->company_id,
                'amount' => $custody->amount,
                'exception' => $e->getMessage(),
            ]);
            throw new \Exception("Custody {$custody->id} Transaction Failed : ".$e->getMessage(), 0, $e);
        }
    }
}
