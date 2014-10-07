smstitan-sms-http-api-php
=========================

PHP класс для отправки SMS через HTTP API titansms.ru

Инициализация
-----

Для инициализации достаточно подключить файл с классом и получить экземпляр класса SmsTitanAPI.
В констукторе класса необходимо указать уникальный ключ API, который вы можете получить у вашего менеждера.

``` php
<?php
	require_once('smstitan.php');
	
	$APIKey = 'someapikey';
	
	$sms = new SmsTitanAPI($APIKey);
	
```

Получение списка отправителей
-----

``` php
	$Response = $sms->GetSenders() ;
	
	if ($Response->Error) {
		echo "Ошибка при получении списка адресов отправителя: " . $Response->Error;
	}else{
		print_r($Response->Senders);
	}
```

Отправка единичного сообщения 
-----

Сообщение "Hello, World!" от отправителя "SomeSender"

``` php
	
	$Response = $sms->Send('70000000001', 'Hello, World!', 'SomeSender');
	
	if ($Response->Error) {
		echo "Ошибка при отправке единичного сообщения: " . $Response->Error;
	}else{
		echo "Успешная отправка. ID отправленного SMS: " . $Response->MSSID;
	}
```

Отправка рассылки на группу номеров
-----

Рассылка "Hello, World!" от отправителя "SomeSender"

``` php
	$Recipients = Array( '70000000001', '70000000002', '70000000003' );
	
	$Response = $sms->SendDispatch( $Recipients, 'Hello, World!', 'SomeSender');
	
	if ($Response->Error) {
		echo "Ошибка при отправке рассылки: " . $Response->Error;
	}else{
		echo "Успешная отправка. ID отправленных SMS: ";
		foreach( $Response->MSSID as $Phone => $MSSID )
		{
		  echo "{$Phone} -> {$MSSID};";
		}
	}
```
