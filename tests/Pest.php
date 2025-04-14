<?php

use Illuminate\Support\Facades\DB;
use Pickappo\Finance\Contract\OrderDTOContract;
use Pickappo\Finance\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function setupOrderContext($test): void
{
    $test->companyPickappoCommission = .25;
    $companyId = 'company-1'; 
    DB::table('companies')->insert([
        'id' => $companyId,
        'company_name' => 'company-1',
        'company_name_en' => 'company-1',
        'manager_email' => 'test',
        'pickappo_fees' => $test->companyPickappoCommission
    ]);
    
    $test->providerPickappoCommission = .5;
    $partnerId = 'provider-1';
    DB::table('partners')->insert([
        'id' => $partnerId,
        'name' => 'provider-1',
        'key' => 'provider-1',
        'pickappo_fees' => $test->providerPickappoCommission
    ]);

    $test->companyId = $companyId;
    $test->providerId = $partnerId;
    $test->captainId = 'captain-1';
    $test->orderCost = 100;
    $test->orderDeliveryCost = 15;
    $test->totalOrderCost = $test->orderCost + $test->orderDeliveryCost;

    $test->orderDTO = Mockery::mock(OrderDTOContract::class);

    $test->orderDTO->shouldReceive('getCompanyId')->andReturn($test->companyId);
    $test->orderDTO->shouldReceive('getAssignedCaptain')->andReturn($test->captainId);
    $test->orderDTO->shouldReceive('getId')->andReturn('order-1');
    $test->orderDTO->shouldReceive('getPartnerId')->andReturn($test->providerId);
    $test->orderDTO->shouldReceive('getOrderCost')->andReturn($test->orderCost);
    $test->orderDTO->shouldReceive('getDeliveryCost')->andReturn($test->orderDeliveryCost);
}
