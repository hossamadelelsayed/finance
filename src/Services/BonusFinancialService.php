<?php

namespace Pickappo\Finance\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pickappo\Finance\DTOs\Wallets\GetWalletDTO;
use Pickappo\Finance\Enums\Transaction\TransactionReason;
use Pickappo\Finance\Enums\Transaction\TransactionReferenceType;
use Pickappo\Finance\Enums\Wallet\WalletOwnerType;
use Qafeer\UsersManagement\Entities\Bonus;
use Qafeer\UsersManagement\Entities\User;

class BonusFinancialService
{
    public function __construct(protected WalletService $walletService)
    {
    }

    /**
     * @param $user
     * @param $bonus
     */
    public function calculateFinance($user,$bonus)
    {
        try {
            DB::beginTransaction();

            if (!$this->isTargetAchieved($user, $bonus)) {
                return;
            }

            $captainId = $user->id;
            $companyId = $user->company_id;
            $captainWalletDto = new GetWalletDTO($captainId, WalletOwnerType::CAPTAIN, $companyId);
            $wallet = $this->walletService->getWallet($captainWalletDto);

            $wallet->credit(
                amount : $bonus->bonus_value,
                referenceId: $bonus->id,
                referenceType: TransactionReferenceType::BONUS,
                reason: TransactionReason::ADD_BONUS
            );

            DB::commit();
        } catch (\Exception|\Error $e) {
            DB::rollBack();
            Log::error("Bonus {$bonus->id} For User {$user->id} Transaction Failed", [
                'user_id' => $user->id,
                'bonus_id' => $bonus->id,
                'company_id' => $user->company_id,
                'exception' => $e->getMessage(),
            ]);
            throw new \Exception("Bonus {$bonus->id} For User {$user->id} Transaction Failed : ".$e->getMessage(), 0, $e);
        }
    }

    /**
     * @param $user
     * @param $bonus
     * @return boolean
     */
    private function isTargetAchieved($user,$bonus): bool
    {
        $user->refresh();
        $existingBonusRecord = $user->bonuses()
                ->where('bonus_id', $bonus->id)
                ->wherePivot('created_at', '>=', $bonus->start_date)
                ->wherePivot('created_at', '<=', $bonus->end_date)
                ->first();

        $achievedTarget = $existingBonusRecord?->pivot->bonus_investigator;
        if($achievedTarget >= $bonus->bonus_target) {
            return true;
        }

        return false;
    }
}
