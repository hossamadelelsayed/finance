<?php

use Pickappo\Finance\Entities\Wallet;
use Pickappo\Finance\Enums\Order\OrderPaymentType;
use Pickappo\Finance\Enums\wallet\WalletOwnerType;
use Pickappo\Finance\Enums\Wallet\WalletReferenceType;
use Pickappo\Finance\Services\OrderFinance\OrderCancelledByCaptainAfterBickup;

describe('CashPayAtResturantTest', function () {
    beforeEach(function () {
        setupOrderContext($this);
        $this->orderDTO->shouldReceive('getPaymentType')->andReturn(OrderPaymentType::CASH_PAY_AT_RESTURANT);
        $this->service = app(OrderCancelledByCaptainAfterBickup::class);
    });

    it('test_cash_pay_at_resturant_order_finance_when_order_cancelled_by_captain_after_pickup', function () {
        // arrange

        // act
        $this->service->calculateFinance($this->orderDTO);

        // assert
        $companyWallet = Wallet::where([
            'owner_id' => $this->companyId ,
            'owner_type' => WalletOwnerType::LOGISTIC_COMPANY,
            'reference_id' => WalletReferenceType::PICKAPPO
            ])->first();
        $captainWallet = Wallet::where([
            'owner_id' => $this->captainId,
            'owner_type' => WalletOwnerType::CAPTAIN,
            'reference_id' => WalletReferenceType::PICKAPPO
            ])->first();
        $providerWallet = Wallet::where([
            'owner_id' => $this->providerId,
            'owner_type' => WalletOwnerType::PROVIDER,
            'reference_id' => WalletReferenceType::PICKAPPO
            ])->first();

        expect($captainWallet->balance)->toEqual(-1 * $this->orderDeliveryCost);
        expect($companyWallet->balance)->toEqual($this->orderDeliveryCost - $this->companyPickappoCommission);
        expect($providerWallet->balance)->toEqual(-1 * $this->providerPickappoCommission);

        expect($captainWallet->transactions->sum('amount'))->toEqual(-1 * $this->orderDeliveryCost);
        expect($companyWallet->transactions->sum('amount'))->toEqual($this->orderDeliveryCost - $this->companyPickappoCommission);
        expect($providerWallet->transactions->sum('amount'))->toEqual(-1 * $this->providerPickappoCommission);
    });
});
