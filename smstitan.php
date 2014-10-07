<?php

/**
* SmsTitanAPI
* 
* Класс для работы с SMS HTTP API от smstitan.ru
* 
* @author Сидоров Кирилл Александрович (web.sidorov@gmail.com)
* @version 1.0
*/

class SmsTitanAPI {
	
	/**
	* Свойство класса
	* 
	* @var string API ключ
	*/
	private $Key = '';
	
	const APIUrl = "http://traffic.smstitan.ru/API:0.9/";
	
	/**
	* Конструктор класса
	* 
	* @param string $Key API ключ, выданный smstitan.ru
	* @return string
	*/
	public function __construct( $Key ) 
	{
		$this->Key = $Key;
	}
	
	/**
	* Метод для проверки и форматирования номера (только для России)
	* 
	* @param string $Phone номер телефона начиная с 8, +7, 7 или без кода страны
	* @return string
	*/
	public function FormatPhoneNumber( $Phone )
	{
		if ( !preg_match( '#^(\+7|8|7|)([0-9]{10})$#', $Phone, $Matches ) )
		{
			return false;	
		}
		
		return '7' . $Matches[2];
	}
	
	/**
	* Метод для получения списока со всеми доступными именами отправителей (словесными номерами)
	* 
	* @return stdClass объект с ответом сервера
	*/
	public function GetSenders()
	{
		$Data = Array(
					'APIKey' => $this->Key,
					'Command' => 'GetSenders'
				);
				
				
		$Response = $this->MakeRequest( $Data );
		
		return $Response;
	}
	
	/**
	* Метод для отправки единичного SMS сообщения
	* 
	* @param string $Phone номер телефона начиная с 8, +7, 7 или без кода страны
	* @param string $Text содержание SMS в кодировке UTF-8 (как для обычных, так и для Unicode SMS)
	* @param string $SourceAddress текст адреса отправителя (буквенный номер)
	* @param bool $Concatenated флаг разрещающий склеивание в пакет при привышении длины 1го SMS сообщения. По умолчанию true
	* @param bool $Unicode флаг, разрещающий использование символов не ищ алфавита GSM. По умолчанию true
	* @return stdClass объект с ответом сервера
	*/
	public function Send( $Phone, $Text, $SourceAddress, $Concatenated = true, $Unicode = true )
	{
		$Concatenated = (bool) $Concatenated;
		$Unicode = (bool) $Unicode;
		
		$Phone = $this->FormatPhoneNumber( $Phone );
		
		if ( $Phone === false )
		{
			return false;	
		}
		
		$Data = Array(
					'APIKey' => $this->Key,
					'Command' => 'SendOne',
					'Content' => $Text,
					'Number' => $Phone,
					'Sender' => $SourceAddress,
					'Concatenated' => (int) $Concatenated,
					'Unicode' => (int) $Unicode
				);
				
				
		$Response = $this->MakeRequest( $Data );
		
		return $Response;
		
	}

	/**
	* Метод для отправки SMS сообщений на группу адресов
	* 
	* @param array $Recipients массив с номерами телефона начиная с 8, +7, 7 или без кода страны
	* @param string $Text содержание SMS в кодировке UTF-8 (как для обычных, так и для Unicode SMS)
	* @param string $SourceAddress текст адреса отправителя (буквенный номер)
	* @param bool $Concatenated флаг разрещающий склеивание в пакет при привышении длины 1го SMS сообщения. По умолчанию true
	* @param bool $Unicode флаг, разрещающий использование символов не ищ алфавита GSM. По умолчанию true
	* @return stdObject объект с ответом сервера
	*/	
	public function SendDispatch( $Recipients, $Text, $SourceAddress, $Concatenated = true, $Unicode = true )
	{
		$RecipientsClear = Array();
		foreach( $Recipients as $RecipientNumber )
		{
			$Phone = $this->FormatPhoneNumber( $RecipientNumber );
			if ( $Phone !== false )
			{
				$RecipientsClear[] = $Phone;
			}
		}
		
		$Batch = $this->SendBatch( $Text, $SourceAddress, $Concatenated, $Unicode );
		$Response = $this->AddBatchRecipients( $Recipients, $Batch->BatchID );
		
		return $Response;
	}
	
	/**
	* Приватный метод для добавления номеров к рассылке 
	* 
	* @param string $Text содержание SMS в кодировке UTF-8 (как для обычных, так и для Unicode SMS)
	* @param string $SourceAddress текст адреса отправителя (буквенный номер)
	* @param bool $Concatenated флаг разрещающий склеивание в пакет при привышении длины 1го SMS сообщения. По умолчанию true
	* @param bool $Unicode флаг, разрещающий использование символов не ищ алфавита GSM. По умолчанию true
	* @return stdObject объект с ответом сервера
	*/	
	private function AddBatchRecipients( $Recipients, $BatchId )
	{
		if ( !is_array( $Recipients ) ) return false;
		
		$Recipients = json_encode( $Recipients );
		
		$Data = Array(
					'APIKey' => $this->Key,
					'Command' => 'AddBatchRecipients',
					'BatchID' => $BatchId,
					'Recipients' => $Recipients
				);
				
				
		$Response = $this->MakeRequest( $Data );
		
		return $Response;
	}

	/**
	* Приватный метод для создания SMS рассылки на группу адресов
	* 
	* @param string $Text содержание SMS в кодировке UTF-8 (как для обычных, так и для Unicode SMS)
	* @param string $SourceAddress текст адреса отправителя (буквенный номер)
	* @param bool $Concatenated флаг разрещающий склеивание в пакет при привышении длины 1го SMS сообщения. По умолчанию true
	* @param bool $Unicode флаг, разрещающий использование символов не ищ алфавита GSM. По умолчанию true
	* @return stdObject объект с ответом сервера
	*/	
	private function SendBatch( $Text, $SourceAddress, $Concatenated = true, $Unicode = true )
	{
		$Concatenated = (bool) $Concatenated;
		$Unicode = (bool) $Unicode;
		
		$Data = Array(
					'APIKey' => $this->Key,
					'Command' => 'SendBatch',
					'Content' => $Text,
					'Sender' => $SourceAddress,
					'Concatenated' => (int) $Concatenated,
					'Unicode' => (int) $Unicode
				);
				
				
		$Response = $this->MakeRequest( $Data );
		
		return $Response;
		
	}
	
	/**
	* Приватный метод для отправки POST запроса на сервер SMS HTTP API
	* 
	* @param array $Data массив с POST Data
	* @return stdObject объект с ответом сервера
	*/
	private function MakeRequest( $Data )
	{
		$ContextOptions = 
						Array('http' =>
							Array(
						        'method'  => 'POST',
						        'header'  => 'Content-type: application/x-www-form-urlencoded',
						        'content' => http_build_query( $Data )
						    )
						);					
		$Context  = stream_context_create( $ContextOptions );
		return json_decode( file_get_contents( self::APIUrl, false, $Context ) );
					
	}
	
}