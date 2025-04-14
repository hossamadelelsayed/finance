<?php

use Pickappo\Finance\Entities\Wallet;
use Pickappo\Finance\Enums\Order\OrderPaymentType;
use Pickappo\Finance\Enums\wallet\WalletOwnerType;
use Pickappo\Finance\Enums\Wallet\WalletReferenceType;
use Pickappo\Finance\Services\OrderFinance\OrderCancelledByProviderBeforePickup;

describe('OrderCancelledByProviderBeforeBickupTest', function () {
    beforeEach(function () {
        setupOrderContext($this);
        $this->service = app(OrderCancelledByProviderBeforePickup::class);
    });

    it('test_order_finance_when_order_cancelled_by_provider_before_pickup', function ($paymentType) {
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
    })->with(OrderPaymentType::all());
});
