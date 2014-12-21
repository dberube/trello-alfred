<?php

Class TrelloWorkflow {

	protected $bundle	         = 'com.davidberube.alfred-trello';
	protected $version           = 0.8;
	protected $separator         = ';';
	
	protected $possible_commands = array( 'autocomplete', 'addcard' );

	public $request              = null;
	public $command              = null;
	public $trello_token	     = null;
	public $trello_board_id	     = null;
	public $query                = null;
	public $input                = null;

	public function parseRequest( $request )
	{
		// Make sure request is well formed
		$this->checkRequest( $request );

		$this->request = $request;
		$this->command = strtolower(trim( $request[1] ));
		
		$this->parseQuery( $request );

		// Make sure the command we pulled from the request is a valid command
		$this->checkCommand( $this->command );

		$input[] = explode( $this->separator, $this->query );

		$this->input = $input;
	}

	private function parseQuery( $request )
	{
		switch (strtolower( $this->command ))
		{
			case 'autocomplete':
			default:

				$this->query = $request[2];

			break;

			case 'addcard':

				$this->trello_token    = $request[2];
				$this->trello_board_id = $request[3];
				$this->query           = $request[4];

			break;
		}
	}

	public function make( $request )
	{
		// Parse the raw request from user
		$this->parseRequest( $request );

		if (method_exists( $this, $this->command ))
		{
			$results = call_user_func_array( array( $this, $this->command), $this->input );
		}
		else
		{
			$this->returnError();
		}

	}

	private function autoComplete( $data )
	{
		$Workflow = $this->initWorkflow();

		foreach ($data as $_data)
		{
			if (empty( $argument ))
			{
				$argument = $_data;
			}
			else
			{
				$argument .= $this->separator . $_data;
			}
		}

		
		//
		// 		$data Array definition:
		// 	
		// 			0	=> Title
		// 			1	=> Description
		// 			
		
		$subtitle = ( (empty(stripslashes(trim( $data[1] )))) ? 'Press enter to add card to Trello...' : 'Description: ' . stripslashes(trim( $data[1] )) . ' [Press enter to add this card to Trello]' );
		
		$item = array(
		    'uid'          => 'trello.card.add',
		    'arg'          => $argument,
		    'title'        => "Add card: " . stripslashes( $data[0] ) . " to Trello...",
		    'subtitle'     => $subtitle,
		    'icon'         => $Workflow->path() . '/src/images/icon.png',
		    'valid'        => 'yes',
		    'autocomplete' => 'autocomplete'
		);

		$Workflow->result( $item['uid'], $item['arg'], $item['title'], $item['subtitle'], $item['icon'], $item['valid'], $item['autocomplete'] );

		$this->returnResults( $Workflow );
	}

	private function addCard( $data )
	{
		$title       = stripslashes(trim( $data[0] ));
		$description = stripslashes(trim(( empty( $data[1] ) ? null : $data[1] )));

		$Trello  = new Trello( $this->trello_token );
		$TrelloCard = $Trello::addCard( $title, $description, $this->trello_board_id );

		return $this->returnNotification( $TrelloCard );
	}

	private function checkRequest( $request )
	{
		if (count( $request ) <= 1)
		{
			$this->returnError();
		}
	}

	private function checkCommand( $command=null )
	{
		if (is_null( $command ))
		{
			$command = $this->command;
		}

		if (!in_array( strtolower($command), $this->possible_commands ))
		{
			$this->returnError();	
		}
	}

	private function returnResults( $results, $format='xml' )
	{
		if (strtolower( $format ) == 'xml')
		{
			echo $results->toxml();
			die;
		}

		echo $results;
		die;
	}

	private function returnNotification( $TrelloCard )
	{		
		echo ( ($TrelloCard->url) ? '"' . $TrelloCard->name . '" added.' : 'Error adding card, please try again...' );
		die;
	}

	private function returnError( $message="Not a valid command or request..." )
	{
		throw new \Exception( $message );
	}

	private function initWorkflow()
	{
		return new Workflows( $this->bundle );
	}
}