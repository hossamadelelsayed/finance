<?php

namespace Pickappo\Finance\Services\OrderFinance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pickappo\Finance\Contract\OrderDTOContract;
use Pickappo\Finance\DTOs\Wallets\GetWalletDTO;
use Pickappo\Finance\Entities\Wallet;
use Pickappo\Finance\Enums\Order\OrderPaymentType;
use Pickappo\Finance\Enums\Transaction\TransactionReason;
use Pickappo\Finance\Enums\Transaction\TransactionReferenceType;
use Pickappo\Finance\Enums\Wallet\WalletOwnerType;
use Pickappo\Finance\Enums\Wallet\WalletReferenceType;
use Pickappo\Finance\Services\WalletService;

abstract class OrderFinancialBase
{
    protected Wallet $providerWallet;
    protected Wallet $captainWallet;
    protected Wallet $companyWallet;
    public function __construct(protected WalletService $walletService)
    {
    }
    public function calculateFinance(OrderDTOContract $order)
    {
        try {
            DB::beginTransaction();

            $providerWalletDto = new GetWalletDTO($order->getPartnerId(), WalletOwnerType::PROVIDER, WalletReferenceType::PICKAPPO);
            $captainWalletDto = new GetWalletDTO($order->getAssignedCaptain(), WalletOwnerType::CAPTAIN, WalletReferenceType::PICKAPPO);
            $companyWalletDto =  new GetWalletDTO($order->getCompanyId(), WalletOwnerType::LOGISTIC_COMPANY, WalletReferenceType::PICKAPPO);

            $this->providerWallet = $this->walletService->getWallet($providerWalletDto);
            $this->captainWallet = $this->walletService->getWallet($captainWalletDto);
            $this->companyWallet = $this->walletService->getWallet($companyWalletDto);

            $paymentType = $order->getPaymentType();

            switch ($paymentType) {
                case OrderPaymentType::ONLINE:
                    $this->handleOnline($order);
                    break;
                case OrderPaymentType::ONLINE_PAY_AT_RESTURANT:
                    $this->handleOnlineAndPayAtResturant($order);
                    break;
                case OrderPaymentType::CASH:
                    $this->handleCash($order);
                    break;
                case OrderPaymentType::CASH_PAY_AT_RESTURANT:
                    $this->handleCashPayAtResturant($order);
                    break;
                default:
                    throw new \Exception("Invalid Payment Type: " . $paymentType);
            }

            $this->applyPickappoCommission($this->providerWallet, $order);
            $this->applyPickappoCommission($this->companyWallet, $order);

            DB::commit();
        } catch (\Exception|\Error $e) {
            DB::rollBack();
            Log::error('Order Finance Transaction Failed', [
                'order_id'    => $order->getId(),
                'captain_id'  => $order->getAssignedCaptain() ,
                'company_id'  => $order->getCompanyId(),
                'exception'   => $e->getMessage(),
            ]);
            throw new \Exception("Order {$order->getId()} Finance Transaction Failed : ".$e->getMessage(), 0, $e);
        }
    }

    /**
     * @param Wallet $wallet
     * @param OrderDTO $order
     * @return void
     */
    private function applyPickappoCommission(Wallet $wallet, OrderDTOContract $order)
    {
        $owner = $wallet->owner;
        if(isset($owner->pickappo_fees) && $owner->pickappo_fees > 0) {
            $wallet->debit(
                $owner->pickappo_fees,
                $order->getId(),
                TransactionReferenceType::ORDER,
                reason: TransactionReason::PICKAPPO_COMMISSION
            );
        }
    }

    abstract protected function handleOnline(OrderDTOContract $order): void;
    abstract protected function handleOnlineAndPayAtResturant(OrderDTOContract $order): void;
    abstract protected function handleCash(OrderDTOContract $order): void;
    abstract protected function handleCashPayAtResturant(OrderDTOContract $order): void;
}
