<?php

use Pickappo\Finance\Entities\Wallet;
use Pickappo\Finance\Enums\Order\OrderPaymentType;
use Pickappo\Finance\Enums\wallet\WalletOwnerType;
use Pickappo\Finance\Enums\wallet\WalletReferenceType;
use Pickappo\Finance\Services\OrderFinance\OrderDeliveredFinancialService;

describe('CashOrderTest', function () {

    beforeEach(function () {
        setupOrderContext($this);
        $this->orderDTO->shouldReceive('getPaymentType')->andReturn(OrderPaymentType::CASH);
    });

    it('test_cash_order_finance_when_order_delivered', function () {
        // arrange

        // act
        $service = app(OrderDeliveredFinancialService::class);
        $service->calculateFinance($this->orderDTO);

        // assert
        $companyWallet = Wallet::where([
            'owner_id' => $this->companyId ,
            'owner_type' => WalletOwnerType::LOGISTIC_COMPANY,
            'reference_id' => WalletReferenceType::PICKAPPO
            ])->first();
        $providerWallet = Wallet::where([
            'owner_id' => $this->providerId,
            'owner_type' => WalletOwnerType::PROVIDER,
            'reference_id' => WalletReferenceType::PICKAPPO
            ])->first();
        $captainWallet = Wallet::where([
            'owner_id' => $this->captainId,
            'owner_type' => WalletOwnerType::CAPTAIN,
            'reference_id' => WalletReferenceType::PICKAPPO
            ])->first();

        expect($captainWallet->balance)->toEqual((-1 * $this->totalOrderCost));
        expect($providerWallet->balance)->toEqual($this->orderCost - $this->providerPickappoCommission);
        expect($companyWallet->balance)->toEqual($this->orderDeliveryCost - $this->companyPickappoCommission);

        expect($captainWallet->transactions->sum('amount'))->toEqual((-1 * $this->totalOrderCost));
        expect($providerWallet->transactions->sum('amount'))->toEqual($this->orderCost - $this->providerPickappoCommission);
        expect($companyWallet->transactions->sum('amount'))->toEqual($this->orderDeliveryCost - $this->companyPickappoCommission);
    });
});
