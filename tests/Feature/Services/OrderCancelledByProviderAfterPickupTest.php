<?php

use Pickappo\Finance\Entities\Wallet;
use Pickappo\Finance\Enums\Order\OrderPaymentType;
use Pickappo\Finance\Enums\wallet\WalletOwnerType;
use Pickappo\Finance\Enums\Wallet\WalletReferenceType;
use Pickappo\Finance\Services\OrderFinance\OrderCancelledByProviderAfterPickup;

describe('OrderCancelledByProviderAfterBickupTest', function () {
    beforeEach(function () {
        setupOrderContext($this);
        $this->service = app(OrderCancelledByProviderAfterPickup::class);
    });

    it('test_order_finance_when_captain_pay_at_resturant_and_order_cancelled_by_provider_after_pickup', function ($paymentType) {
        // arrange
        $this->orderDTO->shouldReceive('getPaymentType')->andReturn($paymentType);
        
        // act
        $this->service->calculateFinance($this->orderDTO);

        // assert
        $providerWallet = Wallet::where([
            'owner_id' => $this->providerId ,
            'owner_type' => WalletOwnerType::PROVIDER,
            'reference_id' => WalletReferenceType::PICKAPPO
            ])->first();
        $companyWallet = Wallet::where([
            'owner_id' => $this->companyId ,
            'owner_type' => WalletOwnerType::LOGISTIC_COMPANY,
            'reference_id' => WalletReferenceType::PICKAPPO
            ])->first();
        expect($providerWallet->balance)->toEqual((-1 * $this->orderDeliveryCost) - $this->providerPickappoCommission);
        expect($companyWallet->balance)->toEqual($this->orderDeliveryCost - $this->companyPickappoCommission);
        expect($providerWallet->transactions->sum('amount'))->toEqual((-1 * $this->orderDeliveryCost) - $this->providerPickappoCommission);
        expect($companyWallet->transactions->sum('amount'))->toEqual($this->orderDeliveryCost - $this->companyPickappoCommission);
    })->with([OrderPaymentType::ONLINE,OrderPaymentType::CASH]);

    it('test_order_finance_when_order_cancelled_by_provider_after_pickup', function ($paymentType) {
        // arrange
        $this->orderDTO->shouldReceive('getPaymentType')->andReturn($paymentType);
        
        // act
        $this->service->calculateFinance($this->orderDTO);

        // assert
        $providerWallet = Wallet::where([
            'owner_id' => $this->providerId ,
            'owner_type' => WalletOwnerType::PROVIDER,
            ])->first();
        $captainWallet = Wallet::where([
            'owner_id' => $this->captainId ,
            'owner_type' => WalletOwnerType::CAPTAIN,
            ])->first();
        $companyWallet = Wallet::where([
        'owner_id' => $this->companyId ,
        'owner_type' => WalletOwnerType::LOGISTIC_COMPANY,
        ])->first();
        expect($providerWallet->balance)->toEqual((-1 * $this->totalOrderCost) - $this->providerPickappoCommission);
        expect($captainWallet->balance)->toEqual($this->orderCost);
        expect($companyWallet->balance)->toEqual($this->orderDeliveryCost - $this->companyPickappoCommission);
        expect($providerWallet->transactions->sum('amount'))->toEqual((-1 * $this->totalOrderCost) - $this->providerPickappoCommission);
        expect($captainWallet->transactions->sum('amount'))->toEqual($this->orderCost);
        expect($companyWallet->transactions->sum('amount'))->toEqual($this->orderDeliveryCost - $this->companyPickappoCommission);
    })->with([OrderPaymentType::ONLINE_PAY_AT_RESTURANT,OrderPaymentType::CASH_PAY_AT_RESTURANT]);
});
