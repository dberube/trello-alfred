<?php

class Config {

	public $data = null;

	protected $path;
	protected $filename;
	protected $raw_config_data;

	public function __construct( $path = '/home/', $filename = 'config.ini' )
	{
		$_path_length = strlen( $path );
		if ( $path[ $_path_length - 1 ] != '/' )
		{
			$path .= '/';
		}
		
		$this->path          = $path;
		$this->filename      = $filename;
		
		$this->getConfigData();
	}

	public function set( $key, $value, $section=null )
	{
		$this->data[ $key ] = $value;

		return true;
	}

	public function get( $key )
	{
		if (empty( $this->data[ $key ]))
		{
			return false;
		}

		return $this->data[ $key ];
	}

	public function save()
	{
		$this->saveConfigData();

		return true;
	}

	private function saveConfigData( $data=null )
	{
		if (empty( $data ))
		{
			$data = $this->data;
		}


		$output = '';

		foreach ($data as $key => $value)
		{
			$output .= self::writeKeyValue( $key, $value );
		}

        $this->saveFile( $output );

        return true;
	}

	private function saveFile( $output )
	{
		$ConfigFile = fopen( $this->path . $this->filename, 'w' );
		fwrite($ConfigFile, $output);
		fclose($ConfigFile);

		return true;
	}

	private static function writeKeyValue( $key, $value )
	{
		$output = '';
		$output .= self::normalizeKey($key) . '=';

		if (is_string($value)) 
        {
            $output .= '"' . addslashes($value) .'"';
        } 
        elseif (is_bool($value)) 
        {
            $output .= $value ? 'true' : 'false';
        } 
        else 
        {
            $output .= $value;
        }

        $output .= "\n";

        return $output;
	}

	private static function writeSection( $data, $section='general' )
	{
		$subsections = array();
		$output      = '';

		if (empty( $section ))
		{
			throw new \Exception( "Section must be set..." );
		}

        foreach ($data as $key => $value) 
        {
            // if (is_array($value) || is_object($value)) 
            // {
            //     $key = $section . '.' . $key;
            //     $subsections[$key] = (array) $value;
            // } 

            $output .= self::normalizeKey($key) . '=';

            if (is_string($value)) 
            {
                $output .= '"' . addslashes($value) .'"';
            } 
            elseif (is_bool($value)) 
            {
                $output .= $value ? 'true' : 'false';
            } 
            else 
            {
                $output .= $value;
            }

            $output .= "\n";
        }

        // if ($subsections) 
        // {
        //     $output .= "\n";
        
        //     foreach ($subsections as $section => $data) 
        //     {
        //         $output .= self::writeSection( $data, $section );
        //     }
        // }

        return $output;
	}

	protected static function normalizeKey($key)
    {
        return str_replace( '=', '_', $key );
    }

	private function getConfigData()
	{
		$this->data            = @parse_ini_file( $this->path . $this->filename, true );
		$this->raw_config_data = @file_get_contents( $this->path . $this->filename );
        
     	return true;
	}
}