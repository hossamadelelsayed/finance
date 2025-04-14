<?php

use Pickappo\Finance\Entities\Wallet;
use Pickappo\Finance\Enums\Wallet\WalletOwnerType;
use Pickappo\Finance\Enums\Wallet\WalletReferenceType;
use Pickappo\Finance\Services\TipsFinancialService;

describe('TipsReceivedTest', function () {

    beforeEach(function () {
        setupOrderContext($this);
    });

    it('test_tips_finance_when_order_tips_received', function () {
        // arrange
        $tipsAmount = 10;
        // act
        $service = app(TipsFinancialService::class);
        $service->calculateFinance($this->orderDTO, $tipsAmount);

        // assert
        $captainWallet = Wallet::where([
            'owner_id' => $this->captainId,
            'owner_type' => WalletOwnerType::CAPTAIN,
            'reference_id' => WalletReferenceType::PICKAPPO
            ])->first();
    
        expect($captainWallet->balance)->toEqual($tipsAmount);
        expect($captainWallet->transactions->sum('amount'))->toEqual($tipsAmount);
    });
});
