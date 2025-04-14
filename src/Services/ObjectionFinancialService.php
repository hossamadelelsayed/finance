<?php

namespace Pickappo\Finance\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pickappo\Finance\Contract\OrderDTOContract;
use Pickappo\Finance\DTOs\Wallets\GetWalletDTO;
use Pickappo\Finance\Enums\Order\OrderObjectionDecision;
use Pickappo\Finance\Enums\Transaction\TransactionReason;
use Pickappo\Finance\Enums\Transaction\TransactionReferenceType;
use Pickappo\Finance\Enums\Wallet\WalletOwnerType;
use Pickappo\Finance\Enums\Wallet\WalletReferenceType;

class ObjectionFinancialService
{
    public function __construct(protected WalletService $walletService)
    {
    }

    public function calculateFinance(OrderDTOContract $order, $objection)
    {
        try {
            DB::beginTransaction();

            $captainWalletDto = new GetWalletDTO($order->getAssignedCaptain(), WalletOwnerType::CAPTAIN, WalletReferenceType::PICKAPPO);
            $providerWalletDto = new GetWalletDTO($order->getPartnerId(), WalletOwnerType::PROVIDER, WalletReferenceType::PICKAPPO);

            $captainWallet = $this->walletService->getWallet($captainWalletDto);
            $providerWallet = $this->walletService->getWallet($providerWalletDto);

            switch ($objection->decision) {
                case OrderObjectionDecision::FULL_FEES:
                    break;

                case OrderObjectionDecision::NO_FEES:
                case OrderObjectionDecision::CUSTOM_FEES:
                    if($objection->partner_order_amount > 0) {

                        $captainWallet->credit(
                            $objection->partner_order_amount,
                            $order->getId(),
                            TransactionReferenceType::ORDER,
                            reason: TransactionReason::OBJECTION_ORDER_SETTLEMENT
                        );

                        $providerWallet->debit(
                            $objection->partner_order_amount,
                            $order->getId(),
                            TransactionReferenceType::ORDER,
                            reason: TransactionReason::OBJECTION_ORDER_SETTLEMENT
                        );
                    }

                    if($objection->partner_delivery_amount > 0) {
                        $captainWallet->credit(
                            $objection->partner_delivery_amount,
                            $order->getId(),
                            TransactionReferenceType::ORDER,
                            reason: TransactionReason::OBJECTION_DELIVERY_SETTLEMENT
                        );

                        $providerWallet->debit(
                            $objection->partner_delivery_amount,
                            $order->getId(),
                            TransactionReferenceType::ORDER,
                            reason: TransactionReason::OBJECTION_DELIVERY_SETTLEMENT
                        );
                    }
                    break;
                default:
                    throw new \Exception("Invalid objection decision : " . $objection->decision);
            }

            DB::commit();
        } catch (\Exception|\error $e) {
            DB::rollBack();
            Log::error("Objection {$objection->id} Transaction Failed", [
                'objection' => $objection->id,
                'order_id' => $order->getId(),
                'exception' => $e->getMessage()
            ]);
            throw new \Exception("Objection {$objection->id} Transaction Failed : ".$e->getMessage(), 0, $e);
        }
    }
}
