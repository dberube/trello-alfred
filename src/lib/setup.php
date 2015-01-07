<?php

class Setup {

	public $App;
	
	public $input      = array();
	public $data	   = array();
	public $step 	   = null;
	
	public $steps      =  array(
		0 => 'token',
		1 => 'username',
		2 => 'board',
		3 => 'list',
		4 => 'save'
	);

	public function __construct( $App )
	{
		$this->App   = $App;
	}

	public function make( $input )
	{
		$this->parseSetupRequest( $input );

		$this->routeSetup();
	}	

	public function parseSetupRequest( $input )
	{
		if (!empty( $input ) && is_array( $input ))
		{
			$input = $input[0];
		}
		else
		{
			$input = null;
		}

		$step_count = substr_count( $input, $this->App->option_separator_character );
		$this->step = $this->steps[ $step_count ];
		$inputs     = explode( $this->App->option_separator_character, $input );

		foreach ($inputs as $_input)
		{
			$this->input[] = trim(str_replace( $this->App->option_separator_character, '', $_input ) );
		}

		$this->data['query']           = trim( $input );
		$this->data['token']           = (empty( $this->input[0] )) ? null : trim( $this->input[0] );
		$this->data['username']        = (empty( $this->input[1] )) ? null : trim( $this->input[1] );
		$this->data['board_name']      = (empty( $this->input[2] )) ? null : trim( $this->input[2] );
		$this->data['list_name']       = (empty( $this->input[3] )) ? null : trim( $this->input[3] );
		$this->data['save_menu_query'] = (empty( $this->input[3] )) ? null : trim( $this->input[4] );

		if (!empty( $this->data['username'] ))
		{
			$_user                        = $this->App->getUserData( $this->data['username'] );
			$this->data['user_id']        = $_user['id'];
			$this->data['user_full_name'] = $_user['fullName'];
		}

		if (!empty( $this->data['board_name'] ))
		{
			$this->data['board_id']        = $this->App->getBoardId( $this->data['board_name'], $this->data['user_id'], $this->data['token'] );
		}

		if (!empty( $this->data['list_name'] ))
		{
			$this->data['list_id']        = $this->App->getListId( $this->data['list_name'], $this->data['board_id'], $this->data['token'] );
		}
	}

	public function routeSetup()
	{
		$this->menuMake( $this->step );
	}

	public function save( $data )
	{
		if (empty( $data['token'] ) || empty( $data['username'] ) || empty( $data['board_name'] ) || empty( $data['board_id'] ) || empty( $data['list_name'] ) || empty( $data['list_id'] ))
		{
			echo "Board & list were not properly set...";
			die;
		}

		date_default_timezone_set('America/New_York');

		$this->App->setUser( $data['username'], $data['token'] );

		$this->App->set('trello.board_id', $data['board_id'] );
		$this->App->set('trello.board_name', $data['board_name'] );
		$this->App->set('trello.board.updated_at', date('Y-m-d H:i:s', time()) );

		$this->App->set('trello.list_id', $data['list_id']);
		$this->App->set('trello.list_name', $data['list_name']);
		$this->App->set('trello.list.updated_at', date('Y-m-d H:i:s', time()) );

		$this->updateLabels( $data['board_id'], $data['token'] );

		echo "Your default board & list were set to: " . strtoupper($data['board_name']) . $this->App->option_separator . strtoupper($data['list_name']);
		die;
	}

	public function updateLabels( $trello_board_id=null, $token=null)
	{
		if (empty( $trello_board_id ))
		{
			$trello_board_id = $this->get('trello.board_id');
		}

		if (empty( $token ))
		{
			$token = $this->getToken();
		}

		$labels = $this->App->Trello->getLabels( $trello_board_id, $token );

		if (!empty( $labels ))
		{
			foreach ($labels as $label)
			{
				if (!empty( $label['name'] ))
				{
					$this->App->set( 'trello.labels.' . strtolower($label['name']), $label['color'] );
				}
			}
		}
	}

	public function menuMake( $step )
	{	
		$this->_menuStart();

		$continue = true;

		$_method = '_menu' . $step;

		if (method_exists( $this, $_method ))
		{
			$results = call_user_func_array( array( $this, $_method), array( $this->input ) );
		}

		return $this->_menuEnd();
	}

	private function _menuToken()
	{
		$this->App->buildResult( 

			$this->data['query'], 
			10, 
			$this->data['query'], 
			'Paste your Trello authorization token above, then press enter...',
			'If you do not have this, run the following command in Alfred: trello:auth',
			$this->App->makeFullPath( '/src/images/icon.png' ),
			'no',
			$this->App->buildQueryChain(array( $this->data['query'] ))

		);
	}

	private function _menuUsername()
	{
		$this->App->buildResult( 

			$this->data['query'], 
			10, 
			$this->data['query'], 
			'Set your username as: ' . $this->data['username'] . ' and continue to the next step...',
			'Press enter to continue to the next step',
			$this->App->makeFullPath( '/src/images/icon.png' ),
			'no',
			$this->App->buildQueryChain(array( $this->data['query'] ))

		);
	}

	private function _menuBoard()
	{
		$boards = $this->App->Trello->getBoards( $this->data['user_id'], $this->data['token'] );

		$i=0;

		foreach ($boards as $board)
		{
			$this->App->buildResult( 

				$this->data['board_name'],
				(10 + $i), 
				$board['name'], 
				'Set your default board as: ' . $board['name'],
				null,
				$this->App->makeFullPath( '/src/images/icon.png' ),
				'no',
				$this->App->buildQueryChain(array( $this->data['token'], $this->data['username'], $board['name'] ))

			);

			$i++;
		}
	}


	private function _menuList()
	{
		$lists = $this->App->Trello->getLists( $this->data['board_id'], $this->data['token'] );
		
		$i=0;

		foreach ($lists as $list)
		{
			$this->App->buildResult( 
				$this->data['list_name'],
				(10 + $i), 
				$list['name'], 
				'Set your default list as: ' . $this->data['board_name'] . $this->App->option_separator . $list['name'],
				null,
				$this->App->makeFullPath( '/src/images/icon.png' ),
				'no',
				$this->App->buildQueryChain(array( $this->data['token'], $this->data['username'], $this->data['board_name'], $list['name'] ))
			);

			$i++;
		}
	}

	private function _menuSave()
	{
		$this->App->buildResult( 
			$this->data['save_menu_query'],
			0, 
			'saveconfig "' . $this->data['token']. '" "' . $this->data['username']. '" "' . $this->data['user_id']. '" "' . $this->data['user_full_name']. '" "' . $this->data['board_name']. '" "' . $this->data['board_id']. '" "' . $this->data['list_name']. '" "' . $this->data['list_id']. '"',
			'Save Your Settings',
			'Save your account settings and the default board & list...',
			$this->App->makeFullPath( '/src/images/icon.png' ),
			'yes',
			null
		);

		$this->App->buildResult( 
			$this->data['save_menu_query'],
			1, 
			'cancel', 
			'Cancel Changes',
			'Cancel saving your settings and start over...',
			$this->App->makeFullPath( '/src/images/icon.png' ),
			'yes',
			null
		);
	}

	private function _menuStart()
	{
		return $this->App->getWorkflow();
	}

	private function _menuEnd()
	{
		return $this->App->returnResults( $this->App->getWorkflow() );
	}
}