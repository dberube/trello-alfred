<?php

class App extends Base {

	public $app_name               		= 'Trello for Alfred';
	public $version               		= 0.8;
	public $bundle                		= 'com.davidberube.trello-alfred';
	
	public $trello 				  		= array(

		'app_key'           => 'e84053a367c64476aec7547f533736ca',
		'api_endpoint_base' => 'https://api.trello.com/1',
		'auth_return_url'   => 'https://trello.com/1/token/approve/'

	);

	public $title_separator            	= ';';
	public $option_separator_character 	= '➔';
	public $option_separator           	= ' ➔ ';
	
	public $config_file                	= 'config.ini';
	
	public $available_commands         	= array( 'setup', 'openauthurl', 'saveconfig', 'addcard', 'updatelabels' );
	
	public $request                    	= array();
	public $input                      	= array();
	public $command                    	= null;
	public $query                      	= null;
	
	public $Config                     	= null;
	public $Trello                     	= null;
	public $Setup                      	= null;
	
	public $Workflow                   	= null;

	public function __construct()
	{		
		$this->Config = new Config( $this->getWorkflowPath(), $this->config_file );
		$this->Trello = new Trello( $this, $this->getToken() );
		$this->Setup  = new Setup( $this );
	}

	public function make( $request )
	{
		// Parse the raw request from user
		$this->parseRequest( $request );

		// Make sure the command pulled from parseRequest is valid
		$this->validateCommand();

		// Make the request to the right method and get the results
		$results = $this->routeRequest();

		echo $results;
		die;
	}

	public function parseRequest( $request )
	{
		$this->request = $request;

		// Get and set the command
		list($this->command) = explode( ' ', (empty( $request[1] )) ? null : str_replace( ':', '', $request[1] ));

		array_splice($request, 0, 1);

		if (!empty( $request ))
		{
			$request[0] = trim(str_replace( ':' . $this->command, '', $request[0] ));
			
			if (empty( $request[0] ))
			{
				array_shift( $request );
			}
			
			foreach ($request as $_request)
			{
				$this->input[] = trim( $_request );
				$this->query .= trim( $_request ) . ' ';
			}

			$this->query = trim( $this->query );
		}
	}

	public function validateCommand( $command=null )
	{
		if (empty( $command ))
		{
			$command = $this->command;
		}

		// Still if no command sent, show the default option menu
		if (empty( $command ))
		{
			$this->showMainMenu();
		}

		// If command is set and not valid, throw an error
		if (!in_array( $command, $this->available_commands ))
		{
			$this->showMainMenu();
		}
	}

	public function routeRequest()
	{
		// Route command to appropriate method
		if (method_exists( $this, $this->command ))
		{
			$results = call_user_func_array( array( $this, $this->command), array( $this->input ) );
		}
		else
		{
			echo "\r\n";
			echo "No method for that command...";
			echo "\r\n";
			die;
		}

		return $results;
	}

	protected function showMainMenu()
	{
		$Workflow           = $this->getWorkflow();
		$_trello_config_set = $this->Config->get('trello.user.id');

		if (empty( $_trello_config_set ))
		{
			$this->buildResult(

				$this->query, 
				0, 
				"openauthurl",
				'Authorize Trello to work with Alfred' . $this->Config->get('trello.user.id'),
				"Get a unique token from Trello to give to authorize Alfred...", 
				$Workflow->path() . '/src/images/icon.png', 
				'yes',
				'autocomplete'
			);

			$this->buildResult(

				$this->query, 
				1, 
				"setup",
				'Setup Trello to Work With Alfred',
				"Do this after you've copied your Trello token", 
				$Workflow->path() . '/src/images/icon.png', 
				'no',
				':setup '
			);

		}
		else
		{
			$_labels_match = array();
			$labels        = null;

			$regex_pattern = '((?:#){1}[\w\d-_]{2,140})';
			preg_match_all( $regex_pattern, $this->query, $_labels_match);

			if (!empty( $_labels_match[0] ) && is_array( $_labels_match[0] ))
			{
				foreach ($_labels_match[0] as $_label)
				{
					$labels .= $_label . ',';
				}
			}

			$_board_name_match = array();
			$board_name        = null;

			$regex_pattern = '((?:!@){1}[\w\d-_]{2,140})';
			preg_match_all( $regex_pattern, $this->query, $_board_name_match);

			if (!empty( $_board_name_match[0] ) && is_array( $_board_name_match[0] ))
			{
				foreach ($_board_name_match[0] as $_board_name)
				{
					$board_name = $_board_name;
				}
			}

			if (!empty( $board_name ))
			{
				$this->query = str_replace( $board_name, '', $this->query );
				$board_name  = str_replace( '!@', '', $board_name );
			}
			else
			{
				$board_name = $this->get('trello.board_name');
			}


			$query       = explode( $this->title_separator, $this->query );
			$title       = $query[0];
			$description = (empty($query[1])) ? null : $query[1];
			$subtitle    = 'Add this card to ' . ucwords( $board_name) . $this->option_separator . ucwords( $this->get('trello.list_name') );

			if (!empty( $labels ))
			{
				$_labels = trim(str_replace(',', ', ', str_replace('#', '', $labels) ));
				
				if (substr($_labels, -1, 1) == ',')
				{
					$_labels = substr($_labels, 0, -1);
				}

				$subtitle .= ' with labels: ' . strtoupper($_labels);
			}

			$this->buildResult(
				null, 
				0, 
				'addcard "' . $title . '" "' . $description . '" "' . $labels . '" "' . $board_name . '"',
				'Add card: ' . stripslashes( $title ) . ' to Trello...',
				$subtitle, 
				$Workflow->path() . '/src/images/icon.png', 
				'yes',
				null
			);
		}

		$this->returnResults( $Workflow );
	}

	public function openAuthUrl( $input )
	{
		$url = "https://trello.com/1/connect?key=" . $this->trello['app_key'] . "&name=" . $this->app_name . "&response_type=token&scope=read,write&expiration=never";
		
		exec("open '{$url}'");
		echo "Authorize Trello, copy the token and run trello:setup";
		die;
	}

	public function setup( $input=null )
	{
		$results = $this->Setup->make( $input );

		return $results;
	}

	public function saveConfig( $input )
	{
		$data['token']      = $this->request[2];
		$data['username']   = $this->request[3];
		$data['user_id']    = $this->request[4];
		$data['user_name']  = $this->request[5];
		$data['board_name'] = $this->request[6];
		$data['board_id']   = $this->request[7];
		$data['list_name']  = $this->request[8];
		$data['list_id']    = $this->request[9];

		return $this->Setup->save( $data );
	}

	public function addCard( $data )
	{
		if ($data[0] == $this->command)
		{
			array_shift($data);
		}

		$title       = $data[0];
		$description = $data[1];
		$_labels 	 = (empty( $data[2] ) ? null : explode(',', str_replace('#', '', $data[2])) );
		$labels      = null;
		$list_name   = (empty( $data[3] ) ? null : trim( $data[3] ) );


		if (!empty( $_labels ))
		{
			foreach ($_labels as $_label)
			{
				$_label = str_replace('#', '', $_label);
				$_label_color = $this->get('trello.labels.' . $_label);

				if (!empty($_label_color))
				{
					$labels .= $_label_color . ',';
				}
			}

			if (substr($labels, -1, 1) == ',')
			{
				$labels = substr($labels, 0, -1);
			}
		}

		if (!empty( $list_name ))
		{
			$list_id = $this->getListId( $list_name, $this->get('trello.board_id'), $this->getToken() );

			if (!$list_id)
			{
				$list_id   = $this->get('trello.list_id');
				$list_name = $this->get('trello.list_name');
			}
		}
		else
		{
			$list_id   = $this->get('trello.list_id');
			$list_name = $this->get('trello.list_name');
		}

		$this->Trello->addCard( $title, $description, $labels, $this->get('trello.board_id'), $list_id, $this->getToken() );
		
		echo "Card {$title} added to " . ucwords($this->get('trello.board_name')) . $this->option_separator . ucwords( $list_name );
		die;
	}
}