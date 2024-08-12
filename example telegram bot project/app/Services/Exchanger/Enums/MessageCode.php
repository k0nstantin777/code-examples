<?php

namespace App\Services\Exchanger\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 *  @method static string WELCOME()
 *  @method static string SELECT_GIVEN_CURRENCY()
 *  @method static string SELECT_RECEIVED_CURRENCY()
 *  @method static string GIVEN_CURRENCY_SELECTED()
 *  @method static string RECEIVED_CURRENCY_SELECTED()
 *  @method static string SELECT_CALCULATING_SUM_CURRENCY()
 *  @method static string ENTER_FORM_ATTRIBUTE()
 *  @method static string PAYMENT_INSTRUCTIONS_HEAD()
 *  @method static string EXCHANGE_REQUEST_COMPLETED()
 *  @method static string OPERATOR_COMMENTED()
 *  @method static string EXCHANGE_REQUEST_MARKED_AS_PAID()
 *  @method static string EXCHANGE_REQUEST_CANCELLED()
 *  @method static string EXCHANGE_REQUEST_CREATED()
 *  @method static string ERROR_EXCHANGE_REQUEST_NOT_EXIST()
 *  @method static string ERROR_EXCHANGE_NOT_STARTED_YET()
 *  @method static string ERROR_EXCHANGE_NOT_FILLED_YET()
 *  @method static string ERROR_INVALID_BOT_ACTION()
 *  @method static string ERROR_COMMON_ERROR_OCCURRED()
 *  @method static string ERROR_PAYMENT_FORM_ERROR()
 */
enum MessageCode : string
{
    use InvokableCases;
    use Values;

    case WELCOME = 'welcome';
    case SELECT_GIVEN_CURRENCY = 'select_given_currency';
    case SELECT_RECEIVED_CURRENCY = 'select_received_currency';
    case GIVEN_CURRENCY_SELECTED = 'given_currency_selected';
    case RECEIVED_CURRENCY_SELECTED = 'received_currency_selected';
    case SELECT_CALCULATING_SUM_CURRENCY = 'select_calculating_sum_currency';
    case ENTER_FORM_ATTRIBUTE = 'enter_form_attribute';
    case PAYMENT_INSTRUCTIONS_HEAD = 'payment_instructions_head';
    case EXCHANGE_REQUEST_CREATED = 'exchange_request_created';
    case EXCHANGE_REQUEST_CANCELLED = 'exchange_request_cancelled';
    case EXCHANGE_REQUEST_COMPLETED = 'exchange_request_completed';
    case OPERATOR_COMMENTED = 'operator_commented';
    case EXCHANGE_REQUEST_MARKED_AS_PAID = 'exchange_request_marked_as_paid';
    case ERROR_EXCHANGE_REQUEST_NOT_EXIST = 'exchange_request_not_exist';
    case ERROR_EXCHANGE_NOT_STARTED_YET = 'exchange_not_started_yet';
    case ERROR_EXCHANGE_NOT_FILLED_YET = 'exchange_not_filled_yet';
    case ERROR_INVALID_BOT_ACTION = 'invalid_bot_action';
    case ERROR_COMMON_ERROR_OCCURRED = 'common_error_occurred';
    case ERROR_PAYMENT_FORM_ERROR = 'payment_form_error';
}