<?php

Class Trello {

	protected static $app_key           = 'e84053a367c64476aec7547f533736ca';
	protected static $api_endpoint_base = 'https://api.trello.com/1';
	
	protected static $user_token        = null;
	protected static $list_id           = null;
	protected static $url               = null;

	public function __construct( $user_token )
	{
		self::$user_token = $user_token;
	}

	public static function buildURL( $board_id )
	{
		return self::$api_endpoint_base . '/boards/' . $board_id . '?lists=open&list_fields=name&fields=name,desc&key=' . self::$app_key . '&token=' . self::$user_token;
	}

	public static function addCard( $title, $description, $board_id )
	{
		$card_data = array(

			'name'      => $title,
			'desc'      => $description,
			'labels'    => null,
			'due'       => null,
			'list_name' => 'Incoming',
			'pos'       => 'bottom',

		);

		$endpoint_url = self::buildURL( $board_id );
		
		$result = self::sendRequest( $endpoint_url, $card_data );

		return $result;
	}

	public static function sendRequest( $endpoint_url, $data )
	{
		$ch = curl_init();

		// Set query data here with the URL
		curl_setopt( $ch, CURLOPT_URL, $endpoint_url ); 
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 ); 
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, '25' );
		
		$content = trim(curl_exec( $ch ));
		
		curl_close( $ch );
		
		$board          = json_decode( $content );
		$lists          = $board->lists;
		$trello_list_id = $lists[0]->id;

		foreach ($lists as $list) 
		{
			if ($list->name == $data['list_name']) 
			{
				$trello_list_id = $list->id;
			}
		}

		if ( $trello_list_id ) 
		{
			
			$ch = curl_init( self::$api_endpoint_base . "/cards" );

			// 
			// Add validation key and token to the post
			// 
			$data['key']    = self::$app_key;
			$data['token']  = self::$user_token;
			$data['idList'] = $trello_list_id;
			
			curl_setopt_array( $ch, array(

			    CURLOPT_SSL_VERIFYPEER => false,
			    CURLOPT_RETURNTRANSFER => true, 	// So we can get the URL of the newly-created card
			    CURLOPT_POST           => true,
			    
			    // 
			    //	If you use an array without being wrapped in http_build_query, the Trello API server won't recognize your POST variables
			    // 
			    
			    CURLOPT_POSTFIELDS => http_build_query( $data ),
			
			));

			$result      = curl_exec( $ch );

			$TrelloCard = json_decode( $result );
			
			if (empty( $TrelloCard ))
			{
				return "Error adding card: " . $result;
			}

			return ( ($TrelloCard) ? $TrelloCard : false );			
		} 
		else 
		{
			return 'List not found';
		}
	}
}