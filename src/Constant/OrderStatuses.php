<?php

namespace App\Constant;

class OrderStatuses
{
    public const MAP = [
        self::TEST => 'Тестовый',
        self::TRASH => 'Трэш',
        self::PROCESSED => 'Оформлен',
        self::CREATED => 'Создан в нашей системе',
        self::ERP_CREATED => 'Создан в ЕРП',
        self::ERP_CREATING_ERROR => 'Ошибка создания в ЕРП',
        self::WAITING_PAYMENT => 'Ожидается оплата',
        self::PAYMENT_CREATED => 'Оплата создана',
        self::PAYMENT_CREATING_ERROR => 'Ошибка создания оплаты',
        self::PAYMENT_COMPLETED => 'Оплата выполнена (прошла успешно)'
    ];
    /**
     * Тестовый
     */
    public const TEST = 'test';

    /**
     * Трэш
     */
    public const TRASH = 'trash';

    /**
     * Оформлен
     */
    public const PROCESSED = 'processed';

    /**
     * Создан в нашей системе
     */
    public const CREATED = 'created';

    /**
     * Создан в ЕРП
     */
    public const ERP_CREATED = 'erp_created';

    /**
     * Ошибка создания в ЕРП
     */
    public const ERP_CREATING_ERROR = 'erp_creating_error';

    /**
     * Ожидается оплата
     */
    public const WAITING_PAYMENT = 'waiting_payment';

    /**
     * Оплата создана
     */
    public const PAYMENT_CREATED = 'payment_created';

    /**
     * Ошибка создания оплаты
     */
    public const PAYMENT_CREATING_ERROR = 'payment_creating_error';

    /**
     * Оплата выполнена (прошла успешно)
     */
    public const PAYMENT_COMPLETED = 'payment_completed';
}
