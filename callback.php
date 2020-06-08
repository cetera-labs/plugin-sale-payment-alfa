<?php
$application->connectDb();
$application->initSession();
$application->initPlugins();

try {
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['orderId'])) {
        
        $order = \Sale\Order::getById( $_GET['orderId'] );
        $gateway = $order->getPaymentGateway();
        
        $data = [
            'userName' => $gateway->params['login'],
            'password' => $gateway->params['password'],
            'orderId'  => $order->id
        ];  

        /**
        * ЗАПРОС СОСТОЯНИЯ ЗАКАЗА
        *      getOrderStatus.do
        *
        * ПАРАМЕТРЫ
        *      userName        Логин магазина.
        *      password        Пароль магазина.
        *      orderId         Номер заказа в платежной системе. Уникален в пределах системы.
        *
        * ОТВЕТ
        *      ErrorCode       Код ошибки. Список возможных значений приведен в таблице ниже.
        *      OrderStatus     По значению этого параметра определяется состояние заказа в платежной системе.
        *                      Список возможных значений приведен в таблице ниже. Отсутствует, если заказ не был найден.
        *
        *  Код ошибки      Описание
        *      0           Обработка запроса прошла без системных ошибок.
        *      2           Заказ отклонен по причине ошибки в реквизитах платежа.
        *      5           Доступ запрещён;
        *                  Пользователь должен сменить свой пароль;
        *                  Номер заказа не указан.
        *      6           Неизвестный номер заказа.
        *      7           Системная ошибка.
        *
        *  Статус заказа   Описание
        *      0           Заказ зарегистрирован, но не оплачен.
        *      1           Предавторизованная сумма захолдирована (для двухстадийных платежей).
        *      2           Проведена полная авторизация суммы заказа.
        *      3           Авторизация отменена.
        *      4           По транзакции была проведена операция возврата.
        *      5           Инициирована авторизация через ACS банка-эмитента.
        *      6           Авторизация отклонена.
        */
        $response = $gateway->gateway('getOrderStatus.do', $data);
        
        if ($response['OrderStatus'] == 2) {
            $order->paymentSuccess();
        }
    
    }

	header("HTTP/1.1 200 OK");
	print 'OK';		    
    
}
catch (\Exception $e) {
	
	header("HTTP/1.1 500 ".$e->getMessage());
	print $e->getMessage();
	
	file_put_contents(__DIR__.'/log_error'.time().'.txt', $e->getMessage());
	
}