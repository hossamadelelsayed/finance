<?php

namespace Pickappo\Finance\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pickappo\Finance\DTOs\Wallets\GetWalletDTO;
use Pickappo\Finance\Entities\PickappoInvoice;
use Pickappo\Finance\Enums\Invoice\PickappoInvoiceStatus;
use Pickappo\Finance\Enums\Invoice\PickappoInvoiceType;
use Pickappo\Finance\Enums\Transaction\TransactionReason;
use Pickappo\Finance\Enums\Transaction\TransactionReferenceType;
use Pickappo\Finance\Enums\Wallet\WalletReferenceType;

class InvoiceFinancialService
{
    public function __construct(protected WalletService $walletService)
    {
    }

    public function calculateFinance(PickappoInvoice $invoice)
    {
        try {
            DB::beginTransaction();
            if (!$this->validateInvoice($invoice)) {
                return;
            }

            $walletDto = new GetWalletDTO(
                $invoice->entity_id,
                $invoice->entity_type,
                WalletReferenceType::PICKAPPO
            );
            $wallet = $this->walletService->getWallet($walletDto);

            switch ($invoice->invoice_type) {
                case PickappoInvoiceType::PAYABLE:
                    $wallet->credit(
                        $invoice->amount,
                        $invoice->id,
                        TransactionReferenceType::PICKAPPO_INVOICE,
                        reason: TransactionReason::PAY_INVOICE
                    );
                    break;
                case PickappoInvoiceType::RECEIVABLE:
                    $wallet->debit(
                        $invoice->amount,
                        $invoice->id,
                        TransactionReferenceType::PICKAPPO_INVOICE,
                        reason: TransactionReason::COLLECT_INVOICE
                    );
                    break;
                default:
                    throw new \Exception("Invalid invoice type : " . $invoice->id);
            }
            DB::commit();
        } catch (\Exception|\Error $e) {
            DB::rollBack();
            Log::error("Invoice {$invoice->id} Transaction Failed", [
                'invoice_id' => $invoice->id,
                'exception' => $e->getMessage()    
            ]);
            throw new \Exception("Invoice {$invoice->id} Transaction Failed : ".$e->getMessage(), 0, $e);
        }
    }

    /**
     * @param PickappoInvoice $invoice
     * @return boolean
     */
    private function validateInvoice(PickappoInvoice $invoice): bool
    {
        if($invoice->status == PickappoInvoiceStatus::PAID && isset($invoice->paid_at)) {
            return true;
        }

        return false;
    }
}
