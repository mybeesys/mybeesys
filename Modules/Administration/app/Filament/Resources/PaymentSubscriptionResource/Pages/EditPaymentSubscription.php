<?php

namespace Modules\Administration\Filament\Resources\PaymentSubscriptionResource\Pages;

use Modules\Administration\Filament\Resources\PaymentSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentSubscription extends EditRecord
{
    protected static string $resource = PaymentSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
