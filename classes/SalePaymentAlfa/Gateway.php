<?php
namespace SalePaymentAlfa;

class Gateway extends \Sale\PaymentGateway\GatewayAbstract {
	
    const GATEWAY_URL = 'https://web.rbsuat.com/ab/rest/';
    
	public static function getInfo()
	{
		$t = \Cetera\Application::getInstance()->getTranslator();
		
		return [
			'name'        => 'Alfa',
			'description' => '',
			'icon'        => '/plugins/sale-payment-alfa/images/icon.png',
			'params' => [	
				[
					'name'       => 'login',
					'xtype'      => 'textfield',
					'fieldLabel' => $t->_('Логин магазина *'),
					'allowBlank' => false,
				],	
				[
					'name'       => 'password',
					'xtype'      => 'textfield',
					'fieldLabel' => $t->_('Пароль магазина *'),
					'allowBlank' => false,
				],				
			]			
		];
	}
	
	public function pay( $return = '' )
	{
        if (!$return) $return = \Cetera\Application::getInstance()->getServer()->getFullUrl();
        
        $data = [
            'userName'    => $this->params['login'],
            'password'    => $this->params['password'],
            'orderNumber' => urlencode($this->order->id),
            'amount'      => urlencode($this->order->getTotal()*100),
            'returnUrl'   => \Cetera\Application::getInstance()->getServer()->getFullUrl().'/plugins/sale-payment-alfa/callback.php?return='.urlencode($return)
        ];

        $response = $this->gateway('register.do', $data);
        
        if (isset($response['errorCode'])) { // В случае ошибки вывести ее
            echo 'Ошибка #' . $response['errorCode'] . ': ' . $response['errorMessage'];
            die();
        } else { // В случае успеха перенаправить пользователя на платежную форму
            header('Location: ' . $response['formUrl']);
            die();
        }        
        
	}

    private function gateway($method, $data) {
        $curl = curl_init(); // Инициализируем запрос
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::GATEWAY_URL.$method, // Полный адрес метода
            CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
            CURLOPT_POST => true, // Метод POST
            CURLOPT_POSTFIELDS => http_build_query($data) // Данные в запросе
        ));
        $response = curl_exec($curl); // Выполняем запрос
         
        $response = json_decode($response, true); // Декодируем из JSON в массив
        curl_close($curl); // Закрываем соединение
        return $response; // Возвращаем ответ
    }	

}