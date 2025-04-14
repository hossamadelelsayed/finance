<?php

use Pickappo\Finance\Entities\Wallet;
use Pickappo\Finance\Enums\Order\OrderPaymentType;
use Pickappo\Finance\Enums\wallet\WalletOwnerType;
use Pickappo\Finance\Enums\wallet\WalletReferenceType;
use Pickappo\Finance\Services\OrderFinance\OrderDeliveredFinancialService;

describe('OnlineAndPayAtResturantTest', function () {
    beforeEach(function () {
        setupOrderContext($this);
        $this->orderDTO->shouldReceive('getPaymentType')->andReturn(OrderPaymentType::ONLINE_PAY_AT_RESTURANT);
    });

    it('test_online_pay_at_resturant_order_finance_when_order_delivered', function () {
        // arrange
        // act
        $service = app(OrderDeliveredFinancialService::class);
        $service->calculateFinance($this->orderDTO);

        // assert
        $providerWallet = Wallet::where([
            'owner_id' => $this->providerId ,
            'owner_type' => WalletOwnerType::PROVIDER,
            'reference_id' => WalletReferenceType::PICKAPPO,
            ])->first();
        $captainWallet = Wallet::where([
            'owner_id' => $this->captainId ,
            'owner_type' => WalletOwnerType::CAPTAIN,
            'reference_id' => WalletReferenceType::PICKAPPO,
            ])->first();
        $companyWallet = Wallet::where([
            'owner_id' => $this->companyId ,
            'owner_type' => WalletOwnerType::LOGISTIC_COMPANY,
            'reference_id' => WalletReferenceType::PICKAPPO,
            ])->first();
        expect($providerWallet->balance)->toEqual((-1 * $this->totalOrderCost) - $this->providerPickappoCommission);
        expect($captainWallet->balance)->toEqual($this->orderCost);
        expect($companyWallet->balance)->toEqual($this->orderDeliveryCost - $this->companyPickappoCommission);

        expect($providerWallet->transactions->sum('amount'))->toEqual((-1 * $this->totalOrderCost) - $this->providerPickappoCommission);
        expect($captainWallet->transactions->sum('amount'))->toEqual($this->orderCost);
        expect($companyWallet->transactions->sum('amount'))->toEqual($this->orderDeliveryCost - $this->companyPickappoCommission);
    });
});
