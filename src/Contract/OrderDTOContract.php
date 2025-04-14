<?php

namespace Pickappo\Finance\Contract;

interface OrderDTOContract
{
    public function getId();

    public function getPartnerId();

    public function getAssignedCaptain();

    public function getCompanyId();

    public function getPaymentType();

    public function getOrderCost();

    public function getDeliveryCost();
}