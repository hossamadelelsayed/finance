<?php

use Illuminate\Support\Facades\DB;
use Pickappo\Finance\Entities\Wallet;
use Pickappo\Finance\Enums\Order\OrderObjectionDecision;
use Pickappo\Finance\Enums\Wallet\WalletOwnerType;
use Pickappo\Finance\Enums\Wallet\WalletReferenceType;
use Pickappo\Finance\Services\ObjectionFinancialService;

describe('ObjectionFinancialServiceTest', function () {
    beforeEach(function () {
        setupOrderContext($this);
    });

    it('test_objection_finance_when_objection_completed_with_full_fees_descision', function () {
        // arrange
        DB::table('order_objections')->insert([
            'id' => 1,
            'order_id' => $this->orderDTO->getId(),
            'decision' => OrderObjectionDecision::FULL_FEES,
        ]);

        $objection = DB::table('order_objections')->where('id', 1)->first();
        
        // act
        $service = app(ObjectionFinancialService::class);
        $service->calculateFinance($this->orderDTO, $objection);

        // assert
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

        expect($captainWallet->balance)->toEqual(0);
        expect($providerWallet->balance)->toEqual(0);
        expect($captainWallet->transactions->sum('amount'))->toEqual(0);
        expect($providerWallet->transactions->sum('amount'))->toEqual(0);
    });

    it('test_objection_finance_when_objection_completed_with_no_fees_descision', function () {
        // arrange

        DB::table('order_objections')->insert([
            'id' => 1,
            'order_id' => $this->orderDTO->getId(),
            'decision' => OrderObjectionDecision::NO_FEES,
            'partner_order_amount' => $this->orderDTO->getOrderCost(),
            'partner_delivery_amount' => $this->orderDTO->getDeliveryCost()
        ]);

        $objection = DB::table('order_objections')->where('id', 1)->first();
        
        // act
        $service = app(ObjectionFinancialService::class);
        $service->calculateFinance($this->orderDTO, $objection);

        // assert
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

        $orderTotal = $this->orderDTO->getOrderCost() + $this->orderDTO->getDeliveryCost();
        expect($captainWallet->balance)->toEqual($orderTotal);
        expect($providerWallet->balance)->toEqual(-1 * $orderTotal);
        expect($captainWallet->transactions->sum('amount'))->toEqual($orderTotal);
        expect($providerWallet->transactions->sum('amount'))->toEqual(-1 * $orderTotal);
    });

    it('test_objection_finance_when_objection_completed_with_custom_fees_descision', function () {
        // arrange
        $customOrderAmount = 30;
        $customDeliveryAmount = 5;
        DB::table('order_objections')->insert([
            'id' => 1,
            'order_id' => $this->orderDTO->getId(),
            'decision' => OrderObjectionDecision::CUSTOM_FEES,
            'partner_order_amount' => $customOrderAmount,
            'partner_delivery_amount' => $customDeliveryAmount
        ]);

        $objection = DB::table('order_objections')->where('id', 1)->first();

        // act
        $service = app(ObjectionFinancialService::class);
        $service->calculateFinance($this->orderDTO, $objection);

        // assert
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

        $totalCustomFees = $customOrderAmount + $customDeliveryAmount;
        expect($captainWallet->balance)->toEqual($totalCustomFees);
        expect($providerWallet->balance)->toEqual(-1 * $totalCustomFees);
        expect($captainWallet->transactions->sum('amount'))->toEqual($totalCustomFees);
        expect($providerWallet->transactions->sum('amount'))->toEqual(-1 * $totalCustomFees);
    });
});
