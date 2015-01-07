<?php

class Base {

	public function get( $key )
	{
		return $this->Config->get( $key );
	}

	public function set( $key, $value )
	{
		$this->Config->set( $key, $value );
		return $this->Config->save();
	}

	public function setToken( $token )
	{
		date_default_timezone_set('America/New_York');

		$this->Config->set( 'trello.user.token', $token );
		$this->Config->set( 'trello.user.updated_at', date('Y-m-d H:i:s', time()) );

		$this->Config->save();

		return true;
	}

	public function setUser( $username, $token=null )
	{
		date_default_timezone_set('America/New_York');

		$TrelloUser = $this->Trello->getMember( $username );
		
		if (!$TrelloUser)
		{
			echo 'no user found!'; die;
			return false;
		}

		$this->Config->set( 'trello.user.id', $TrelloUser['id'] );
		$this->Config->set( 'trello.user.name', $TrelloUser['fullName'] );
		$this->Config->set( 'trello.user.username', $TrelloUser['username'] );

		if (!empty( $token ))
		{
			$this->Config->set( 'trello.user.token', $token );
		}

		$this->Config->set( 'trello.user.updated_at', date('Y-m-d H:i:s', time()) );

		$this->Config->save();

		return true;
	}

	public function getToken()
	{
		return $this->get( 'trello.user.token' );
	}

	public function getUser()
	{
		$return['id'] = $this->get( 'trello.user.id' );

		if (empty( $return['id'] ))
		{
			return false;
		}

		$return['username'] = $this->get( 'trello.user.username' );

		return $return;
	}

	public function getWorkflow()
	{
		if (!empty( $this->Workflow ))
		{
			return $this->Workflow;
		}

		$this->Workflow = new Workflows( $this->bundle );

		return $this->Workflow;
	}

	public function getWorkflowPath()
	{
		$Workflow = $this->getWorkflow();
		return $Workflow->path();
	}

	public function makeFullPath( $file )
	{
		$Workflow = $this->getWorkflow();

		if ($file[0] == '/')
		{
			return $Workflow->path() . $file;
		}
		else
		{
			return $Workflow->path() . '/' . $file;
		}
	}

	public function buildResult( $input, $count, $arg, $title, $subtitle, $icon, $valid, $autocomplete=null ) 
	{
		$Workflow = $this->getWorkflow();

		$input = strtolower(trim( $input ));
		$arg   = strtolower(trim( $arg ));

	    $result = 0;

	    if (empty( $input ))
	    {
	    	$Workflow->result($count, $arg, $title, $subtitle, $icon, $valid, $autocomplete);
	        $result = 1;
	    }
	    else if(preg_match( "/.*" . $input . "/i", $arg ) === 1) 
	    {
	        $Workflow->result($count, $arg, $title, $subtitle, $icon, $valid, $autocomplete);
	        $result = 1;
	    }

	    return($result);
	}

	public function returnResults( $results, $format='xml' )
	{
		if (strtolower( $format ) == 'xml')
		{
			echo $results->toxml();
			die;
		}

		echo $results;
		return;
	}

	public function buildQueryChain( $items, $last_item=false )
	{
		$return = null;

		if (is_string( $items ))
		{
			$return = $items;

			if ($last_item)
			{
				return $return . $this->option_separator;
			}
			else
			{
				return $return;
			}
		}

		if (is_array( $items ))
		{
			$_last_item_in_foreach = end( $items );
		}

		foreach ($items as $item)
		{
			if ($item != $this->command)
			{
				$return .= $item;

				if ($_last_item_in_foreach != $item)
				{
					$return .= $this->option_separator;
				}
				else
				{
					if ($last_item === false)
					{
						$return .= $this->option_separator;		
					}
				}
			}
		}

		if ($return[0] === ':')
		{
			$return = substr( $return, 1 );
		}

		$return = ':' . $this->command . ' ' . $return;

		return $return;
	}

	public function getUserData( $username )
	{
		return $this->Trello->getMember( $username );
	}

	public function getBoardId( $board_name, $user_id, $token )
	{
		$boards = $this->Trello->getBoards( $user_id, $token );

		foreach ($boards as $board)
		{
			if (strtolower(trim( $board_name )) == strtolower(trim( $board['name'] )))
			{
				return $board['id'];
			}
		}

		return false;
	}

	public function getListId( $list_name, $board_id, $token )
	{
		$lists = $this->Trello->getLists( $board_id, $token );

		foreach ($lists as $list)
		{
			if (strtolower(trim( $list_name )) == strtolower(trim( $list['name'] )))
			{
				return $list['id'];
			}
		}

		return false;
	}

}