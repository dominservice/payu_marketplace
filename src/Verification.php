<?php

namespace Dominservice\PayuMarketplace;

class Verification extends Api
{
    const TYPE_PAYOUT_ACCOUNT_DATA = 'PAYOUT_ACCOUNT_DATA';
    const TYPE_FULL = 'FULL';
    const TYPE_UPDATE = 'UPDATE';
    const TYPE_REVERIFICATION = 'REVERIFICATION';
    const TYPE_PERSONAL_ID_TAX_ID_CHANGE = 'PERSONAL_ID_TAX_ID_CHANGE';

    const STATUS_WAITING_FOR_DATA = 'WAITING_FOR_DATA';
    const STATUS_WAITING_FOR_VERIFICATION = 'WAITING_FOR_VERIFICATION';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_POSITIVE = 'POSITIVE';
    const STATUS_NEGATIVE = 'NEGATIVE';
}