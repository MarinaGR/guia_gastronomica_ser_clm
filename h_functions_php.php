<?php
//Change this value for your own array to prevent security issues
$h_default_encrypt_seed=array(array("A","!*!"),array("1","-*-"),array("O","***"),array("3","*?*"),array("z","*+*"));

function h_function_redirect_to_canonical($params)
{
	switch ($params["behaviour"])
	{
		case 'www_to_nonwww':
			if (substr($_SERVER["HTTP_HOST"], 0, 4) === 'www.')
			{
				$corrected_url = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
				if ($_SERVER["SERVER_PORT"] != "80")
				{
				    $corrected_url .= substr($_SERVER['SERVER_NAME'], 4).":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
				} 
				else 
				{
				    $corrected_url .= substr($_SERVER['SERVER_NAME'], 4).$_SERVER["REQUEST_URI"];
				}
				header($_SERVER["SERVER_PROTOCOL"].' 301 Moved Permanently');
				header('Location: '.$corrected_url);
				exit();
			}
		break;
		case 'nonwww_to_www':
			if (substr($_SERVER["HTTP_HOST"], 0, 4) != 'www.')
			{
				$corrected_url = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
				if ($_SERVER["SERVER_PORT"] != "80")
				{
				    $corrected_url .= "www.".$_SERVER['SERVER_NAME'].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
				} 
				else 
				{
				    $corrected_url .= "www.".$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
				}
				header($_SERVER["SERVER_PROTOCOL"].' 301 Moved Permanently');
				header('Location: '.$corrected_url);
				exit();
			}
		break;
		
		default:
			
		break;
	}	
	
}

function h_function_check_session($params)
{		
	if(!session_start())
	{
		$url_not_found=false;
				
		if(isset($params["relative_to_root_url_to_redirect_on_fail"]) || $params["relative_to_root_url_to_redirect_on_fail"]!="")
		{
			if(!file_exists($params["relative_to_root_url_to_redirect_on_fail"]))
			{
				$url_not_found=true;
			}
			header("Location: ".$params["relative_to_root_url_to_redirect_on_fail"]);
			exit();
		}		
		if(!isset($params["relative_to_root_url_to_redirect_on_fail"]) || $params["relative_to_root_url_to_redirect_on_fail"]=="" || $url_not_found)
		{
			return false;
		}
	}
	
	return true;
}

function h_function_set_session_vars($params)
{
	if($params["override_current_values"])
	{
		foreach($params["vars"] as $var)
		{
			$_SESSION[$var[0]]=$var[1];
		}
	}
	else
	{
		foreach($params["vars"] as $var)
		{
			if($_SESSION[$var[0]]=="" || !isset($_SESSION[$var[0]]))
			{
				$_SESSION[$var[0]]=$var[1];
			}
		}
	}
}

function h_function_set_languages($params)
{
	if($params["overwrite_current"] || !file_exists($params["languages_file_url"]))
	{
		$languages_file_content="";
		foreach($params["languages"] as $language)
		{
			$languages_file_content.=$language[0].";".$language[1].";".$language[2].";".$language[3]."**";
		}
		$languages_file_content=rtrim($languages_file_content,"**");
		
		$file_result=file_put_contents($params["languages_file_url"],$languages_file_content,LOCK_EX);
		if(!$file_result)
		{
			return false;			
		}
		return true;
	}
	
	return true;
	
}

function h_function_check_and_set_language($params)
{
	if(!file_exists($params["languages_file_url"]))
	{
		return false;
	}
	$languages_file_content=file_get_contents($params["languages_file_url"]);
	$languages_exploded=explode("**",$languages_file_content);
	foreach($languages_exploded as $lang)
	{
		$lang_values=explode(";",$lang);
		if($lang_values[0]==$params["language_to_check"])
		{
			if($lang_values[3]=="active")
			{
				return $lang_values[0];
			}
		}
		if($lang_values[2]=="default")
		{
			$default_lang=$lang_values[0];
		}
	}
	if(!isset($default_lang))
	{
		return false;
	}
	
	return $default_lang;
}

function h_function_get_db_connection($params)
{
	{	
		if(!isset($params["first_to_try"]))
		{
			$first="regular";
		}
		else
		{
			$first=$params["first_to_try"];
		}	
				
		if(isset($params["alternative_connection_values"]))
		{
				$host_a=$params["alternative_connection_values"]["host"];
				$user_a=$params["alternative_connection_values"]["user"];
				$password_a=$params["alternative_connection_values"]["password"];
				$dbname_a=$params["alternative_connection_values"]["dbname"];
				$port_a=$params["alternative_connection_values"]["port"];
				$socket_a=$params["alternative_connection_values"]["socket"];
				$alternative=true;
				$a_connection_string=$host_a.";".$user_a.";".$password_a.";".$dbname_a.";".$port_a.";".$socket_a;
		}
		else
		{
			$alternative=false;
			$a_connection_string="";
		}
		if(isset($params["connection_values"]))
		{
				$host=$params["connection_values"]["host"];
				$user=$params["connection_values"]["user"];
				$password=$params["connection_values"]["password"];
				$dbname=$params["connection_values"]["dbname"];
				$port=$params["connection_values"]["port"];
				$socket=$params["connection_values"]["socket"];
				$connection_string=$host.";".$user.";".$password.";".$dbname.";".$port.";".$socket;
				$regular=true;
		}
		else
		{
			$regular=false;
			$connection_string="";
		}		
	}

	{	
		if($params["overwrite_current_connection_file"] || !file_exists($params["connection_file_url"]))
		{
			if(!$regular)
			{
				if(!file_exists($params["relative_to_root_url_to_redirect_on_fail"]))
				{
					return false;
				}
				header("Location: ".$params["relative_to_root_url_to_redirect_on_fail"]);
				exit();	
				
			}
			if($first=="alternative" && !$alternative)
			{
				if(!file_exists($params["relative_to_root_url_to_redirect_on_fail"]))
				{
					return false;
				}
				header("Location: ".$params["relative_to_root_url_to_redirect_on_fail"]);
				exit();	
			}
			if($first=="alternative" && $alternative)
			{					
				if($port_a=="" && $socket_a=="")
				{
					$connection=new mysqli($host_a,$user_a,$password_a,$dbname_a,(int)$port_a,$socket_a);
				}
				elseif($port_a!="" && $socket_a=="")
				{
					$connection=new mysqli($host_a,$user_a,$password_a,$dbname_a,(int)$port_a);
				}
				else
				{
					$connection=new mysqli($host_a,$user_a,$password_a,$dbname_a);
				}
				if ($connection->connect_errno)
				{
					$connection_stat=false;
				}
				else
				{
					$connection_stat=true;
				}
					
				if(!$connection_stat)
				{
					if($port=="" && $socket=="")
					{
						$connection=new mysqli($host,$user,$password,$dbname,(int)$port,$socket);
					}
					elseif($port!="" && $socket=="")
					{
						$connection=new mysqli($host,$user,$password,$dbname,(int)$port);
					}
					else
					{
						$connection=new mysqli($host,$user,$password,$dbname,(int)$port);
					}
					if ($connection->connect_errno)
					{
						$connection_stat=false;
					}
					else
					{
						$connection_stat=true;
					}
				}
				
				if(!$connection_stat)
				{
					if(!file_exists($params["relative_to_root_url_to_redirect_on_fail"]))
					{
						return false;
					}
					header("Location: ".$params["relative_to_root_url_to_redirect_on_fail"]);
					exit();	
				}			
			}		
			if($first=="regular" && $alternative)
			{
				if($port=="" && $socket="")
				{
					$connection=new mysqli($host,$user,$password,$dbname,(int)$port,$socket);
				}
				elseif($port!="" && $socket="")
				{
					$connection=new mysqli($host,$user,$password,$dbname,(int)$port);
				}
				else
				{
					$connection=new mysqli($host,$user,$password,$dbname,(int)$port);
				}
				if ($connection->connect_errno)
				{
					$connection_stat=false;
				}
				else
				{
					$connection_stat=true;
				}			
				
				if(!$connection_stat)
				{
					if($port_a=="" && $socket_a="")
					{
						$connection=new mysqli($host_a,$user_a,$password_a,$dbname_a,(int)$port_a,$socket_a);
					}
					elseif($port_a!="" && $socket_a="")
					{
						$connection=new mysqli($host_a,$user_a,$password_a,$dbname_a,(int)$port_a);
					}
					else
					{
						$connection=new mysqli($host_a,$user_a,$password_a,$dbname_a,(int)$port_a);
					}
					if ($connection->connect_errno)
					{
						$connection_stat=false;
					}
					else
					{
						$connection_stat=true;
					}				
				}
				
				if(!$connection_stat)
				{
					if(!file_exists($params["relative_to_root_url_to_redirect_on_fail"]))
					{
						return false;
					}
					header("Location: ".$params["relative_to_root_url_to_redirect_on_fail"]);
					exit();
				}			
			}
			if($first=="regular" && !$alternative)
			{
				if($port=="" && $socket="")
				{
					$connection=new mysqli($host,$user,$password,$dbname,(int)$port,$socket);
				}
				elseif($port!="" && $socket="")
				{
					$connection=new mysqli($host,$user,$password,$dbname,(int)$port);
				}
				else
				{
					$connection=new mysqli($host,$user,$password,$dbname,(int)$port);
				}
				if ($connection->connect_errno)
				{
					$connection_stat=false;
				}
				else
				{
					$connection_stat=true;
				}			
				
				if(!$connection_stat)
				{
					if(!file_exists($params["relative_to_root_url_to_redirect_on_fail"]))
					{
						return false;
					}
					header("Location: ".$params["relative_to_root_url_to_redirect_on_fail"]);
					exit();	
				}			
			}
			
			$connection_file_contents=$a_connection_string."**".$connection_string."**".$first;
			if(!isset($params["encrypt_seed"]))
			{
				$params["encrypt_seed"]=$h_default_encrypt_seed;
			}
			$connection_file_contents_encoded=h_encrypt_decrypt_string(array(
				"mode"=>"encrypt",
				"string"=>$connection_file_contents,
				"seed"=>$params["encrypt_seed"]
			));
			
			$file_result=file_put_contents($params["connection_file_url"],$connection_file_contents_encoded,LOCK_EX);
			if(!$file_result)
			{
				if(!file_exists($params["relative_to_root_url_to_redirect_on_fail"]))
				{
					return false;
				}
				header("Location: ".$params["relative_to_root_url_to_redirect_on_fail"]);
				exit();	
			}
			
			return $connection;		
		}
	}

	{
		$connection_file_contents=file_get_contents($params["connection_file_url"]);
		if(!isset($params["encrypt_seed"]))
		{
			$params["encrypt_seed"]=$h_default_encrypt_seed;
		}
		$connection_file_contents_decoded=h_encrypt_decrypt_string(array(
			"mode"=>"decrypt",
			"string"=>$connection_file_contents,
			"seed"=>$params["encrypt_seed"]
		));
				
		$connection_exploded_values=explode("**",$connection_file_contents_decoded);
		$alternative_values=$connection_exploded_values[0];
		$regular_values=$connection_exploded_values[1];
		$first=$connection_exploded_values[2];
			
		$alternative=false;
		if($alternative_values!="")
		{
			$alternative_exploded_values=explode(";",$alternative_values);
			$host_a=$alternative_exploded_values[0];
			$user_a=$alternative_exploded_values[1];
			$password_a=$alternative_exploded_values[2];
			$dbname_a=$alternative_exploded_values[3];
			$port_a=$alternative_exploded_values[4];
			$socket_a=$alternative_exploded_values[5];
			$alternative=true;		
		}
		$regular=false;
		if($regular_values!="")
		{
			$regular_exploded_values=explode(";",$regular_values);
			$host=$regular_exploded_values[0];
			$user=$regular_exploded_values[1];
			$password=$regular_exploded_values[2];
			$dbname=$regular_exploded_values[3];
			$port=$regular_exploded_values[4];
			$socket=$regular_exploded_values[5];
			$regular=true;		
		}
			
		if(!$regular)
		{
			if(!file_exists($params["relative_to_root_url_to_redirect_on_fail"]))
			{
					return false;
			}
			header("Location: ".$params["relative_to_root_url_to_redirect_on_fail"]);
			exit();			
		}
		if($first=="alternative" && !$alternative)
		{
			if(!file_exists($params["relative_to_root_url_to_redirect_on_fail"]))
			{
				return false;
			}
			header("Location: ".$params["relative_to_root_url_to_redirect_on_fail"]);
			exit();		
		}
		if($first=="alternative" && $alternative)
		{					
			if($port_a=="" && $socket_a=="")
			{
				$connection=new mysqli($host_a,$user_a,$password_a,$dbname_a,(int)$port_a,$socket_a);
			}
			elseif($port_a!="" && $socket_a=="")
			{
				$connection=new mysqli($host_a,$user_a,$password_a,$dbname_a,(int)$port_a);
			}
			else
			{
				$connection=new mysqli($host_a,$user_a,$password_a,$dbname_a);
			}
			if ($connection->connect_errno)
			{
				$connection_stat=false;
			}
			else
			{
				$connection_stat=true;
			}
						
			if(!$connection_stat)
			{
				if($port=="" && $socket=="")
				{
					$connection=new mysqli($host,$user,$password,$dbname,(int)$port,$socket);
				}
				elseif($port!="" && $socket=="")
				{
					$connection=new mysqli($host,$user,$password,$dbname,(int)$port);
				}
				else
				{
					$connection=new mysqli($host,$user,$password,$dbname,(int)$port);
				}
				if ($connection->connect_errno)
				{
					$connection_stat=false;
				}
				else
				{
					$connection_stat=true;
				}
			}
				
			if(!$connection_stat)
			{
				if(!file_exists($params["relative_to_root_url_to_redirect_on_fail"]))
				{
					return false;
				}
				header("Location: ".$params["relative_to_root_url_to_redirect_on_fail"]);
				exit();
			}			
		}		
		if($first=="regular" && $alternative)
		{
			if($port=="" && $socket="")
			{
				$connection=new mysqli($host,$user,$password,$dbname,(int)$port,$socket);
			}
			elseif($port!="" && $socket="")
			{
				$connection=new mysqli($host,$user,$password,$dbname,(int)$port);
			}
			else
			{
				$connection=new mysqli($host,$user,$password,$dbname,(int)$port);
			}
			if ($connection->connect_errno)
			{
				$connection_stat=false;
			}
			else
			{
				$connection_stat=true;
			}			
			
			if(!$connection_stat)
			{
				if($port_a=="" && $socket_a="")
				{
					$connection=new mysqli($host_a,$user_a,$password_a,$dbname_a,(int)$port_a,$socket_a);
				}
				elseif($port_a!="" && $socket_a="")
				{
					$connection=new mysqli($host_a,$user_a,$password_a,$dbname_a,(int)$port_a);
				}
				else
				{
					$connection=new mysqli($host_a,$user_a,$password_a,$dbname_a,(int)$port_a);
				}
				if ($connection->connect_errno)
				{
					$connection_stat=false;
				}
				else
				{
					$connection_stat=true;
				}				
			}
				
			if(!$connection_stat)
			{
				if(!file_exists($params["relative_to_root_url_to_redirect_on_fail"]))
				{
					return false;
				}
				header("Location: ".$params["relative_to_root_url_to_redirect_on_fail"]);
				exit();
			}			
		}
		if($first=="regular" && !$alternative)
		{
			if($port=="" && $socket="")
			{
				$connection=new mysqli($host,$user,$password,$dbname,(int)$port,$socket);
			}
			elseif($port!="" && $socket="")
			{
				$connection=new mysqli($host,$user,$password,$dbname,(int)$port);
			}
			else
			{
				$connection=new mysqli($host,$user,$password,$dbname,(int)$port);
			}
			if ($connection->connect_errno)
			{
				$connection_stat=false;
			}
			else
			{
				$connection_stat=true;
			}			
			
			if(!$connection_stat)
			{
				if(!file_exists($params["relative_to_root_url_to_redirect_on_fail"]))
				{
					return false;
				}
				header("Location: ".$params["relative_to_root_url_to_redirect_on_fail"]);
				exit();
			}			
		}		
		
		return $connection;
	}	
}

function h_encrypt_decrypt_string($params)
{
	if(!isset($params["seed"]))
	{
		$params["seed"]=$h_default_encrypt_seed;
	}	
		
	switch ($params["mode"])
	{
		case 'encrypt':
			
			$encoded_string=base64_encode($params["string"]);
			foreach($params["seed"] as $s_element)
			{
				$encoded_string=str_replace($s_element[0], $s_element[1], $encoded_string);
			}
			
			return $encoded_string;
			
		break;
		
		case 'decrypt':
			
			$decoded_string=$params["string"];
			
			foreach($params["seed"] as $s_element)
			{
				$decoded_string=str_replace($s_element[1], $s_element[0], $decoded_string);
			}
			$decoded_string=base64_decode($decoded_string);
			
			return $decoded_string;
			
		break;
		
		default:
			return $params["string"];
		break;
	}
}

function h_function_create_regular_table($params)
{
	switch ($params["type"])
	{
		case 'small':
			$query="
			  CREATE TABLE IF NOT EXISTS ".$params["name"]." (
			  id bigint(20) NOT NULL AUTO_INCREMENT ,
			  c1 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c2 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c3 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c4 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c5 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c6 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c7 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c8 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c9 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c10 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c11 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c12 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c13 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c14 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c15 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c16 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c17 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c18 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c19 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c20 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,			  		 		  
			  c41 datetime DEFAULT NULL ,
			  c42 datetime DEFAULT NULL ,
			  c43 datetime DEFAULT NULL ,
			  c44 datetime DEFAULT NULL ,
			  c45 datetime DEFAULT NULL ,		  		  
			  PRIMARY KEY (id)		  
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
			
			if(!$params["connection"]->query($query))
			{
				return false;
			}
			else
			{
				return true;
			}
		
		break;
		
		case 'small_no_dates':
			$query="
			  CREATE TABLE IF NOT EXISTS ".$params["name"]." (
			  id bigint(20) NOT NULL AUTO_INCREMENT ,
			  c1 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c2 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c3 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c4 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c5 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c6 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c7 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c8 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c9 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c10 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c11 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c12 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c13 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c14 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c15 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c16 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c17 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c18 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c19 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c20 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,			  		 		  
			  PRIMARY KEY (id)		  
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
			
			if(!$params["connection"]->query($query))
			{
				return false;
			}
			else
			{
				return true;
			}
		
		break;
		
		case 'medium':
			$query="
			  CREATE TABLE IF NOT EXISTS ".$params["name"]." (
			  id bigint(20) NOT NULL AUTO_INCREMENT ,
			  c1 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c2 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c3 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c4 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c5 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c6 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c7 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c8 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c9 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c10 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c11 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c12 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c13 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c14 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c15 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c16 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c17 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c18 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c19 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c20 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c21 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c22 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c23 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c24 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c25 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c26 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c27 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c28 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c29 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c30 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c41 datetime DEFAULT NULL ,
			  c42 datetime DEFAULT NULL ,
			  c43 datetime DEFAULT NULL ,
			  c44 datetime DEFAULT NULL ,
			  c45 datetime DEFAULT NULL ,		  		  
			  PRIMARY KEY (id)		  
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
			
			if(!$params["connection"]->query($query))
			{
				return false;
			}
			else
			{
				return true;
			}
		
		break;
		
		case 'medium_no_dates':
			$query="
			  CREATE TABLE IF NOT EXISTS ".$params["name"]." (
			  id bigint(20) NOT NULL AUTO_INCREMENT ,
			  c1 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c2 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c3 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c4 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c5 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c6 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c7 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c8 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c9 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c10 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c11 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c12 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c13 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c14 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c15 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c16 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c17 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c18 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c19 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c20 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c21 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c22 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c23 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c24 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c25 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c26 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c27 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c28 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c29 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c30 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  PRIMARY KEY (id)		  
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
			
			if(!$params["connection"]->query($query))
			{
				return false;
			}
			else
			{
				return true;
			}
		
		break;
		
		case 'large':
			$query="
			  CREATE TABLE IF NOT EXISTS ".$params["name"]." (
			  id bigint(20) NOT NULL AUTO_INCREMENT ,
			  c1 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c2 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c3 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c4 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c5 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c6 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c7 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c8 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c9 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c10 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c11 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c12 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c13 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c14 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c15 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c16 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c17 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c18 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c19 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c20 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c21 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c22 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c23 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c24 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c25 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c26 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c27 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c28 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c29 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c30 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c31 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c32 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c33 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c34 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c35 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c36 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c37 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c38 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c39 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c40 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,		 		  
			  c41 datetime DEFAULT NULL ,
			  c42 datetime DEFAULT NULL ,
			  c43 datetime DEFAULT NULL ,
			  c44 datetime DEFAULT NULL ,
			  c45 datetime DEFAULT NULL ,		  		  
			  PRIMARY KEY (id)		  
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
			
			if(!$params["connection"]->query($query))
			{
				return false;
			}
			else
			{
				return true;
			}
		
		break;		
		
		case 'large_no_dates':
			$query="
			  CREATE TABLE IF NOT EXISTS ".$params["name"]." (
			  id bigint(20) NOT NULL AUTO_INCREMENT ,
			  c1 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c2 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c3 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c4 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c5 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c6 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c7 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c8 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c9 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c10 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c11 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c12 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c13 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c14 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c15 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c16 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c17 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c18 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c19 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c20 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c21 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c22 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c23 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c24 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c25 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c26 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c27 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c28 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c29 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c30 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c31 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c32 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c33 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c34 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c35 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c36 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c37 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c38 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c39 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,
			  c40 varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL ,		 		  
			  PRIMARY KEY (id)		  
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
			
			if(!$params["connection"]->query($query))
			{
				return false;
			}
			else
			{
				return true;
			}
		
		break;
		
		default:
			return false;
		break;
	}	
}

function h_function_create_admin_user($params)
{
	{	
		$table_result=h_function_create_regular_table(array(
			"connection"=>$params["connection"],
			"type"=>"small_no_dates",
			"name"=>"h_admin_users",		
		));
		if(!$table_result)
		{
			return "[h_error_h_admin_users_table_creation_error]";
		}
	}
	
	{
		$query="SELECT * FROM h_admin_users WHERE c1='".urlencode($params["id"])."'";
		if(!$params["connection"]->query($query))
		{
			return "[h_error_sql_execution_error]";
		}
	}
	
	if($params["connection"]->affected_rows==0 && !$params["create_if_not_exists"])
	{
		return "OK";
	}
	
	if($params["connection"]->affected_rows==0 && $params["create_if_not_exists"])
	{
		{	
			$values["c1"]=array("c1",$params["id"]); //unique id
			$values["c2"]=array("c2",time()); // creation time_stamp
			$values["c3"]=array("c3",time()); // modification time_stamp
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}
			else
			{
				$values["c4"]=array("c4","1"); //By default active
			}
			
			$values["c5"]=array("c5","h_admin_users"); //Table of the item
		}
		
		{		
			if(!isset($params["password_encrypt_seed"]) && $params["password_encrypt_seed"]=="")
			{
				$params["password_encrypt_seed"]=$h_default_encrypt_seed;
			}
			$encrypted_password=h_encrypt_decrypt_string(array(
				"mode"=>"encrypt",
				"string"=>$params["password"],
				"seed"=>$params["encrypt_seed"]
			));	
			$values["c6"]=array("c6",$encrypted_password);					
			$values["c7"]=array("c7",$params["access_name"]);
			$values["c8"]=array("c8",$params["emails"]);
			$values["c9"]=array("c9",$params["phones"]);
			$values["c10"]=array("c10",$params["name"]);
			$values["c11"]=array("c11",$params["middlename"]);
			$values["c12"]=array("c12",$params["lastname"]);
			if(isset($params["admin_type"]) && $params["admin_type"]!="")
			{
				$values["c13"]=array("c13",$params["admin_type"]); // 0 will mean full access admin, 1 will mean limited access admin
			}
			else
			{
				$values["c13"]=array("c13","1"); //By default limited
			}			
		}
		
		{
			$insert_stat=h_function_manage_item_in_db(array(
				"mode"=>"insert",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_admin_users"			
			));
			if(!$insert_stat)
			{
				return "[h_error_sql_execution_insert_error]";
			}
		}
		
		return "OK";		
	}
	
	if($params["connection"]->affected_rows>=1 && $params["overwrite_current"])
	{
		{
			$values["c3"]=array("c3",time());
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}			
		}
		{
			if(isset($params["password"]) && $params["password"]!="")
			{	
				if(!isset($params["password_encrypt_seed"]) && $params["password_encrypt_seed"]=="")
				{
					$params["password_encrypt_seed"]=$h_default_encrypt_seed;
				}
				$encrypted_password=h_encrypt_decrypt_string(array(
					"mode"=>"encrypt",
					"string"=>$params["password"],
					"seed"=>$params["encrypt_seed"]
				));	
				$values["c6"]=array("c6",$encrypted_password);
			}
			if(isset($params["access_name"]) && $params["access_name"]!="")
			{					
				$values["c7"]=array("c7",$params["access_name"]);
			}
			if(isset($params["emails"]) && $params["emails"]!="")
			{
				$values["c8"]=array("c8",$params["emails"]);
			}
			if(isset($params["phones"]) && $params["phones"]!="")
			{
				$values["c9"]=array("c9",$params["phones"]);
			}
			if(isset($params["name"]) && $params["name"]!="")
			{
				$values["c10"]=array("c10",$params["name"]);
			}
			if(isset($params["middlename"]) && $params["middlename"]!="")
			{
				$values["c11"]=array("c11",$params["middlename"]);
			}
			if(isset($params["lastname"]) && $params["lastname"]!="")
			{
				$values["c12"]=array("c12",$params["lastname"]);
			}
			if(isset($params["admin_type"]) && $params["admin_type"]!="")
			{
				$values["c13"]=array("c13",$params["admin_type"]); // 0 will mean full access admin, 1 will mean limited access admin
			}			
		}

		{
			$update_stat=h_function_manage_item_in_db(array(
				"mode"=>"update",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_admin_users"			
			));
			if(!$update_stat)
			{
				return "[h_error_sql_execution_update_error]";
			}
		}
		
		return "OK";
	}
	if($params["connection"]->affected_rows>=1 && !$params["overwrite_current"])
	{
		return "OK";
	}
}

function h_function_manage_item_in_db($params)
{
	switch ($params["mode"])
	{
		case 'insert':
			$values=$params["values"];		
			$fields="(";
			$cols="(";
			foreach($values as $value)
			{
				if(substr($value[0],0,1)=="c")
				{
					$fields.=$value[0].",";
					$cols.="'".urlencode(trim($value[1]))."',";
				}
			}
			$fields=rtrim($fields, ",");
			$cols=rtrim($cols, ",");
			$fields.=")";
			$cols.=")";
			$query="INSERT INTO ".$params["table"]." ".$fields." VALUES ".$cols;
			$result=$params["connection"]->query($query);
			if(!$result)
			{
				return false;
			}
			return true;			
		break;
		
		case 'update':
			$values=$params["values"];	
			$fields="";
			foreach($values as $value)
			{
				if(substr($value[0],0,1)=="c")
				{
					$fields.=$value[0]."='".urlencode(trim($value[1]))."',";
				}
			}
			$fields=rtrim($fields, ",");
			$query="UPDATE ".$params["table"]." SET ".$fields." WHERE c1='".$values["c1"][1]."'";
			$result=$params["connection"]->query($query);
			if(!$result)
			{
				return false;
			}
			return true;
		break;
		
		default:
			return false;
		break;
	}
}

function h_function_manage_simple_text($params)
{
	$table_result=h_function_create_regular_table(array(
		"connection"=>$params["connection"],
		"type"=>"small_no_dates",
		"name"=>"h_simple_texts",		
	));
	if(!$table_result)
	{
		return "[h_error_h_simple_texts_table_creation_error]";
	}
	$query="SELECT * FROM h_simple_texts WHERE c1='".urlencode($params["id"])."'";
	$result=$params["connection"]->query($query);
	if(!$result)
	{
		return "[pfHE_06]";
	}
	if($params["connection"]->affected_rows==0 && $params["create_if_not_exists"])
	{
		$values["c1"]=array("c1",$params["id"]);
		$values["c2"]=array("c2",time());
		$values["c3"]=array("c3",time());
		if(isset($params["current_user_id"]) && $params["current_user_id"]!="")
		{
			$values["c4"]=array("c4",$params["current_user_id"]);
		}
		else
		{
			$values["c4"]=array("c4","jdoe");
		}
		
		if(isset($params["current_user_id"]) && $params["current_user_id"]!="")
		{
			$values["c5"]=array("c5",$params["current_user_id"]);
		}
		else
		{
			$values["c5"]=array("c5","jdoe");
		}
		
		if(isset($params["current_user_type"]) && $params["current_user_type"]!="")
		{
			$values["c6"]=array("c6",$params["current_user_type"]);
		}
		else
		{
			$values["c6"]=array("c6","unknown");
		}
		
		if(isset($params["current_user_type"]) && $params["current_user_type"]!="")
		{
			$values["c7"]=array("c7",$params["current_user_type"]);
		}
		else
		{
			$values["c7"]=array("c7","unknown");
		}
		
		if(isset($params["status"]) && $params["status"]!="")
		{
			$values["c8"]=array("c8",$params["status"]);
		}
		else
		{
			$values["c8"]=array("c8","active");
		}
		
		$values["c9"]=array("c9","h_simple_texts");
		
		foreach($params["values"] as $t_val)
		{
			$values["c10"]=array("c10",$t_val[0]);
			$values["c11"]=array("c11",$t_val[1]);
			$insert_stat=h_function_manage_item_in_db(array(
				"mode"=>"insert",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_simple_texts"			
			));
			if(!$insert_stat)
			{
				return "[pfHE_05]";
			}
			usleep(200000);
		}
		
		return $params["values"][$params["current_lang"]][1]; 
		
	}
	if($params["connection"]->affected_rows==0 && !$params["create_if_not_exists"])
	{
		return "[pfHE_07]";
	}
	while($row=$result->fetch_assoc())
	{
		if(urldecode($row["c10"])==$params["current_lang"])
		{
			$text_to_return=urldecode($row["c11"]);
			return $text_to_return;
		}
	}
		
	return "[pfHE_08]";
}

function h_function_create_html_head()
{
	
}

function h_function_load_add($params)
{
	{	
		$table_result=h_function_create_regular_table(array(
			"connection"=>$params["connection"],
			"type"=>"medium",
			"name"=>"h_advertising_items",		
		));
		if(!$table_result)
		{
			return "[h_error_h_advertising_items_table_creation_error]";
		}
	}
	
	{
		$query="SELECT * FROM h_advertising_items WHERE c1='".urlencode($params["id"])."'";
		if(!$params["connection"]->query($query))
		{
			return "[h_error_sql_execution_error]";
		}
	}
	
	if($params["connection"]->affected_rows==0 && !$params["create_if_not_exists"])
	{
		return "OK";
	}
	
	if($params["connection"]->affected_rows==0 && $params["create_if_not_exists"])
	{
		{	
			$values["c1"]=array("c1",$params["id"]); //unique id
			$values["c2"]=array("c2",time()); // creation time_stamp
			$values["c3"]=array("c3",time()); // modification time_stamp
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}
			else
			{
				$values["c4"]=array("c4","1"); //By default active
			}
			
			$values["c5"]=array("c5","h_advertising_items"); //Table of the item
		}
		
		{				
			$values["c6"]=array("c6",$params["featured_day"]);					
			$values["c7"]=array("c7",$params["number_of_impressions"]);
			$values["c8"]=array("c8",$params["destination_url"]);
			$values["c9"]=array("c9",$params["small_image_route"]);
			$values["c10"]=array("c10",$params["big_image_route"]);
			$values["c11"]=array("c11",$params["number_of_visits"]);
			/*$values["c12"]=array("c12",$params["phone"]);
			$values["c13"]=array("c13",$params["web"]);
			$values["c14"]=array("c14",$params["email"]);
			$values["c15"]=array("c15",$params["address"]);*/
			$values["c16"]=array("c16",$params["latlong"]);
			$values["c17"]=array("c17",$params["allow_geolocation"]);
			$values["c18"]=array("c18",$params["show_map"]);	
			$values["c19"]=array("c19",$params["update_time"]);		
			$values["c20"]=array("c20",$params["rating"]);				
			
			//$values["c21"]=array("c21",$params["id_provincia"]);		
			$values["c22"]=array("c22",$params["provincia"]);		
			//$values["c23"]=array("c23",$params["id_localidad"]);		
			$values["c24"]=array("c24",$params["localidad"]);		
			$values["c25"]=array("c25",$params["tipo"]);		
		}
		
		{
			$insert_stat=h_function_manage_item_in_db(array(
				"mode"=>"insert",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_advertising_items"			
			));
			if(!$insert_stat)
			{
				return "[h_error_sql_execution_insert_error]";
			}
		}
		
		return "OK";		
	}
	
	if($params["connection"]->affected_rows>=1 && $params["overwrite_current"])
	{
		{
			$values["c3"]=array("c3",time());
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}			
		}
		
		{
			if(isset($params["featured_day"]) && $params["featured_day"]!="")
			{					
				$values["c6"]=array("c6",$params["featured_day"]);
			}
			if(isset($params["number_of_impressions"]) && $params["number_of_impressions"]!="")
			{					
				$values["c7"]=array("c7",$params["number_of_impressions"]);
			}
			if(isset($params["destination_url"]) && $params["destination_url"]!="")
			{
				$values["c8"]=array("c8",$params["destination_url"]);
			}
			if(isset($params["small_image_route"]) && $params["small_image_route"]!="")
			{
				$values["c9"]=array("c9",$params["small_image_route"]);
			}
			if(isset($params["big_image_route"]) && $params["big_image_route"]!="")
			{
				$values["c10"]=array("c10",$params["big_image_route"]);
			}
			if(isset($params["number_of_visits"]) && $params["number_of_visits"]!="")
			{
				$values["c11"]=array("c11",$params["number_of_visits"]);
			}
			if(isset($params["phone"]) && $params["phone"]!="")
			{
				$values["c12"]=array("c12",$params["phone"]);
			}
			if(isset($params["web"]) && $params["web"]!="")
			{
				$values["c13"]=array("c13",$params["web"]); 
			}
			if(isset($params["email"]) && $params["email"]!="")
			{
				$values["c14"]=array("c14",$params["email"]); 
			}
			if(isset($params["address"]) && $params["address"]!="")
			{
				$values["c15"]=array("c15",$params["address"]); 
			}
			if(isset($params["latlong"]) && $params["latlong"]!="")
			{
				$values["c16"]=array("c16",$params["latlong"]); 
			}
			if(isset($params["allow_geolocation"]) && $params["allow_geolocation"]!="")
			{
				$values["c17"]=array("c17",$params["allow_geolocation"]); 
			}
			if(isset($params["show_map"]) && $params["show_map"]!="")
			{
				$values["c18"]=array("c18",$params["show_map"]); 
			}	
			if(isset($params["update_time"]) && $params["update_time"]!="")
			{
				$values["c19"]=array("c19",$params["update_time"]); 
			}	
			if(isset($params["rating"]) && $params["rating"]!="")
			{
				$values["c20"]=array("c20",$params["rating"]); 
			}	
			
			if(isset($params["id_provincia"]) && $params["id_provincia"]!="")
			{
				$values["c21"]=array("c21",$params["rating"]); 
			}	
			if(isset($params["provincia"]) && $params["provincia"]!="")
			{
				$values["c22"]=array("c22",$params["rating"]); 
			}	
			if(isset($params["id_localidad"]) && $params["id_localidad"]!="")
			{
				$values["c23"]=array("c23",$params["rating"]); 
			}	
			if(isset($params["localidad"]) && $params["localidad"]!="")
			{
				$values["c24"]=array("c24",$params["localidad"]); 
			}	
			if(isset($params["tipo"]) && $params["tipo"]!="")
			{
				$values["c25"]=array("c25",$params["tipo"]); 
			}	
									
		}

		{
			$update_stat=h_function_manage_item_in_db(array(
				"mode"=>"update",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_advertising_items"			
			));
			if(!$update_stat)
			{
				return "[h_error_sql_execution_update_error]";
			}
		}
		
		return "OK";
	}
	if($params["connection"]->affected_rows>=1 && !$params["overwrite_current"])
	{
		return "OK";
	}
}

function h_function_recover_random_add($params)
{
	{
		$values=array();

		$query="SELECT * FROM h_advertising_items ORDER BY c7 ASC"; // c20 ASC
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			return false;
		}
		$row=$result->fetch_assoc();		
		$values["c1"]=array("c1",urldecode($row["c1"]));
		$values["c7"]=array("c7",intval(urldecode($row["c7"]))+1);
		$update_stat=h_function_manage_item_in_db(array(
			"mode"=>"update",
			"connection"=>$params["connection"],
			"values"=>$values,
			"table"=>"h_advertising_items"			
		));				
		return $row;		
	}
	
}

function h_function_recover_add($params)
{
	{
		$values=array();

		$query="SELECT * FROM h_advertising_items WHERE c25='".$params["tipo"]."' ";
		if($params["provincia"]!="" && $params["localidad"]!="")
		{
			$query.=" AND c22='".$params["provincia"]."' AND c24='".urlencode($params["localidad"])."' ";
		}
		elseif($params["provincia"]!="")
		{
			$query.=" AND c22='".$params["provincia"]."' AND c24='' ";
		}
		$query.=" ORDER BY c7 ASC LIMIT 0,1"; 
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			return false;
		}
		if($params["connection"]->affected_rows==0)
		{
			if($params["tipo"])
			{
				$query2="SELECT * FROM h_advertising_items WHERE c25='".$params["tipo"]."' ";
				$query2.=" AND c22='".$params["provincia"]."' AND c24='' ";
				$query2.=" ORDER BY c7 ASC LIMIT 0,1"; 
				$result2=$params["connection"]->query($query2);
				if(!$result2)
				{
					return false;
				}
				$row2=$result2->fetch_assoc();		
				$values["c1"]=array("c1",urldecode($row2["c1"]));
				$values["c7"]=array("c7",intval(urldecode($row2["c7"]))+1);
				$update_stat=h_function_manage_item_in_db(array(
					"mode"=>"update",
					"connection"=>$params["connection"],
					"values"=>$values,
					"table"=>"h_advertising_items"			
				));				
				return $row2;
			}
			else
				return false;
		}
		$row=$result->fetch_assoc();		
		$values["c1"]=array("c1",urldecode($row["c1"]));
		$values["c7"]=array("c7",intval(urldecode($row["c7"]))+1);
		$update_stat=h_function_manage_item_in_db(array(
			"mode"=>"update",
			"connection"=>$params["connection"],
			"values"=>$values,
			"table"=>"h_advertising_items"			
		));				
		return $row;		
	}
	
}

function h_function_load_rest($params)
{
	{	
		$table_result=h_function_create_regular_table(array(
			"connection"=>$params["connection"],
			"type"=>"small",
			"name"=>"h_restaurantes_items",		
		));
		if(!$table_result)
		{
			return "[h_error_h_rest_items_table_creation_error]";
		}
	}
	
	{
		$query="SELECT * FROM h_restaurantes_items WHERE c1='".urlencode($params["id"])."'"; 
		if(!$params["connection"]->query($query))
		{
			return "[h_error_sql_execution_error]";
		}
	}
	
	if($params["connection"]->affected_rows==0 && !$params["create_if_not_exists"])
	{
		return "OK";
	}
	
	if($params["connection"]->affected_rows==0 && $params["create_if_not_exists"])
	{
		{	
			$values["c1"]=array("c1",$params["id"]); //unique id
			$values["c2"]=array("c2",time()); // creation time_stamp
			$values["c3"]=array("c3",time()); // modification time_stamp
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}
			else
			{
				$values["c4"]=array("c4","1"); //By default active
			}
			
			$values["c5"]=array("c5","h_restaurantes_items"); //Table of the item
		}
		
		{				
			$values["c6"]=array("c6",$params["tlf"]);					
			$values["c7"]=array("c7",$params["web"]);
			$values["c8"]=array("c8",$params["mail"]);
			$values["c9"]=array("c9",$params["nombre"]);
			$values["c10"]=array("c10",$params["text"]);
			$values["c11"]=array("c11",$params["precio_medio"]);
			$values["c12"]=array("c12",$params["provincia"]);
			$values["c13"]=array("c13",$params["premium"]);
			$values["c15"]=array("c15",$params["tipo"]);
			$values["c17"]=array("c17",$params["videos"]);
			$values["c18"]=array("c18",$params["carta"]);
					
		}
		
		{
			$insert_stat=h_function_manage_item_in_db(array(
				"mode"=>"insert",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_restaurantes_items"			
			));
			if(!$insert_stat)
			{
				return "[h_error_sql_execution_insert_error]";
			}
		}
		
		return "OK";		
	}
	
	if($params["connection"]->affected_rows>=1 && $params["overwrite_current"])
	{
		{
			$values["c3"]=array("c3",time());
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}			
		}
			
		{
			if(isset($params["tlf"]) && $params["tlf"]!="")
			{					
				$values["c6"]=array("c6",$params["tlf"]);
			}
			if(isset($params["web"]) && $params["web"]!="")
			{					
				$values["c7"]=array("c7",$params["web"]);
			}
			if(isset($params["mail"]) && $params["mail"]!="")
			{
				$values["c8"]=array("c8",$params["mail"]);
			}
			if(isset($params["nombre"]) && $params["nombre"]!="")
			{
				$values["c9"]=array("c9",$params["nombre"]);
			}
			if(isset($params["text"]) && $params["text"]!="")
			{
				$values["c10"]=array("c10",$params["text"]);
			}
			if(isset($params["precio_medio"]) && $params["precio_medio"]!="")
			{
				$values["c11"]=array("c11",$params["precio_medio"]);
			}
			if(isset($params["provincia"]) && $params["provincia"]!="")
			{
				$values["c12"]=array("c12",$params["provincia"]);
			}
			if(isset($params["premium"]) && $params["premium"]!="")
			{
				$values["c13"]=array("c13",$params["premium"]);
			}
			if(isset($params["tipo"]) && $params["tipo"]!="")
			{
				$values["c15"]=array("c15",$params["tipo"]);
			}
			if(isset($params["videos"]) && $params["videos"]!="")
			{
				$values["c17"]=array("c17",$params["videos"]);
			}
			if(isset($params["carta"]) && $params["carta"]!="")
			{
				$values["c18"]=array("c18",$params["carta"]);
			}
			
			if(isset($params["id"]) && $params["id"]!="")
			{					
				$values["c1"]=array("c1",$params["id"]);
			}
								
		}

		{
			$update_stat=h_function_manage_item_in_db(array(
				"mode"=>"update",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_restaurantes_items"			
			));
			if(!$update_stat)
			{
				return "[h_error_sql_execution_update_error]";
			}
		}
		
		return "OK";
	}
	if($params["connection"]->affected_rows>=1 && !$params["overwrite_current"])
	{
		return "OK";
	}
}

function h_function_load_dir($params)
{
	{	
		$table_result=h_function_create_regular_table(array(
			"connection"=>$params["connection"],
			"type"=>"small",
			"name"=>"h_direcciones_items",		
		));
		if(!$table_result)
		{
			return "[h_error_h_dir_items_table_creation_error]";
		}
	}
	
	{
		$query="SELECT * FROM h_direcciones_items WHERE c1='".urlencode($params["id"])."'"; 
		if(!$params["connection"]->query($query))
		{
			return "[h_error_sql_execution_error]";
		}
	}
	
	if($params["connection"]->affected_rows==0 && !$params["create_if_not_exists"])
	{
		return "OK";
	}
	
	if($params["connection"]->affected_rows==0 && $params["create_if_not_exists"])
	{
		{	
			$values["c1"]=array("c1",$params["id"]); //unique id
			$values["c2"]=array("c2",time()); // creation time_stamp
			$values["c3"]=array("c3",time()); // modification time_stamp
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}
			else
			{
				$values["c4"]=array("c4","1"); //By default active
			}
			
			$values["c5"]=array("c5","h_direcciones_items"); //Table of the item
		}
		
		{				
			$values["c6"]=array("c6",$params["id_restaurante"]);
			$values["c8"]=array("c8",$params["calle"]);
			$values["c9"]=array("c9",$params["nombre"]);
			$values["c10"]=array("c10",$params["geolocalizacion"]);
			$values["c12"]=array("c12",$params["provincia"]);
			$values["c13"]=array("c13",$params["localidad"]);					
			$values["c15"]=array("c15",$params["tipo"]);	
		}
		
		{
			$insert_stat=h_function_manage_item_in_db(array(
				"mode"=>"insert",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_direcciones_items"			
			));
			if(!$insert_stat)
			{
				return "[h_error_sql_execution_insert_error]";
			}
		}
		
		return "OK";		
	}
	
	if($params["connection"]->affected_rows>=1 && $params["overwrite_current"])
	{
		{
			$values["c3"]=array("c3",time());
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}			
		}
			
		{
			if(isset($params["id_restaurante"]) && $params["id_restaurante"]!="")
			{
				$values["c6"]=array("c6",$params["id_restaurante"]);
			}
			if(isset($params["calle"]) && $params["calle"]!="")
			{
				$values["c8"]=array("c8",$params["calle"]);
			}
			if(isset($params["nombre"]) && $params["nombre"]!="")
			{
				$values["c9"]=array("c9",$params["nombre"]);
			}
			if(isset($params["geolocalizacion"]) && $params["geolocalizacion"]!="")
			{
				$values["c10"]=array("c10",$params["geolocalizacion"]);
			}
			if(isset($params["provincia"]) && $params["provincia"]!="")
			{
				$values["c12"]=array("c12",$params["provincia"]);
			}
			if(isset($params["localidad"]) && $params["localidad"]!="")
			{
				$values["c13"]=array("c13",$params["localidad"]);
			}
			if(isset($params["tipo"]) && $params["tipo"]!="")
			{
				$values["c15"]=array("c15",$params["tipo"]);
			}
			
			if(isset($params["id"]) && $params["id"]!="")
			{					
				$values["c1"]=array("c1",$params["id"]);
			}
								
		}

		{
			$update_stat=h_function_manage_item_in_db(array(
				"mode"=>"update",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_direcciones_items"			
			));
			if(!$update_stat)
			{
				return "[h_error_sql_execution_update_error]";
			}
		}
		
		return "OK";
	}
	if($params["connection"]->affected_rows>=1 && !$params["overwrite_current"])
	{
		return "OK";
	}
}

function h_function_load_prod($params)
{
	{	
		$table_result=h_function_create_regular_table(array(
			"connection"=>$params["connection"],
			"type"=>"small",
			"name"=>"h_productos_items",		
		));
		if(!$table_result)
		{
			return "[h_error_h_prod_items_table_creation_error]";
		}
	}
	
	{
		$query="SELECT * FROM h_productos_items WHERE c1='".urlencode($params["id"])."'"; 
		if(!$params["connection"]->query($query))
		{
			return "[h_error_sql_execution_error]";
		}
	}
	
	if($params["connection"]->affected_rows==0 && !$params["create_if_not_exists"])
	{
		return "OK";
	}
	
	if($params["connection"]->affected_rows==0 && $params["create_if_not_exists"])
	{
		{	
			$values["c1"]=array("c1",$params["id"]); //unique id
			$values["c2"]=array("c2",time()); // creation time_stamp
			$values["c3"]=array("c3",time()); // modification time_stamp
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}
			else
			{
				$values["c4"]=array("c4","1"); //By default active
			}
			
			$values["c5"]=array("c5","h_productos_items"); //Table of the item
		}
		
		{				
			$values["c9"]=array("c9",$params["nombre"]);
			$values["c10"]=array("c10",$params["text"]);
			$values["c12"]=array("c12",$params["provincia"]);
			$values["c13"]=array("c13",$params["id_localidad"]);					
			$values["c14"]=array("c14",$params["localidad"]);
		}
		
		{
			$insert_stat=h_function_manage_item_in_db(array(
				"mode"=>"insert",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_productos_items"			
			));
			if(!$insert_stat)
			{
				return "[h_error_sql_execution_insert_error]";
			}
		}
		
		return "OK";		
	}
	
	if($params["connection"]->affected_rows>=1 && $params["overwrite_current"])
	{
		{
			$values["c3"]=array("c3",time());
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}			
		}
			
		{
			
			if(isset($params["nombre"]) && $params["nombre"]!="")
			{
				$values["c9"]=array("c9",$params["nombre"]);
			}
			if(isset($params["text"]) && $params["text"]!="")
			{
				$values["c10"]=array("c10",$params["text"]);
			}
			if(isset($params["provincia"]) && $params["provincia"]!="")
			{
				$values["c12"]=array("c12",$params["provincia"]);
			}
			if(isset($params["id_localidad"]) && $params["id_localidad"]!="")
			{
				$values["c13"]=array("c13",$params["id_localidad"]);
			}
			if(isset($params["localidad"]) && $params["localidad"]!="")
			{
				$values["c14"]=array("c14",$params["localidad"]);
			}
			
			if(isset($params["id"]) && $params["id"]!="")
			{					
				$values["c1"]=array("c1",$params["id"]);
			}
								
		}

		{
			$update_stat=h_function_manage_item_in_db(array(
				"mode"=>"update",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_productos_items"			
			));
			if(!$update_stat)
			{
				return "[h_error_sql_execution_update_error]";
			}
		}
		
		return "OK";
	}
	if($params["connection"]->affected_rows>=1 && !$params["overwrite_current"])
	{
		return "OK";
	}
}

function h_function_load_new($params)
{
	{	
		$table_result=h_function_create_regular_table(array(
			"connection"=>$params["connection"],
			"type"=>"small",
			"name"=>"h_news_items",		
		));
		if(!$table_result)
		{
			return "[h_error_h_news_items_table_creation_error]";
		}
	}
	
	{
		$query="SELECT * FROM h_news_items WHERE c1='".urlencode($params["id"])."'"; 
		if(!$params["connection"]->query($query))
		{
			return "[h_error_sql_execution_error]";
		}
	}
	
	if($params["connection"]->affected_rows==0 && !$params["create_if_not_exists"])
	{
		return "OK";
	}
	
	if($params["connection"]->affected_rows==0 && $params["create_if_not_exists"])
	{
		{	
			$values["c1"]=array("c1",$params["id"]); //unique id
			$values["c2"]=array("c2",time()); // creation time_stamp
			$values["c3"]=array("c3",time()); // modification time_stamp
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}
			else
			{
				$values["c4"]=array("c4","1"); //By default active
			}
			
			$values["c5"]=array("c5","h_news_items"); //Table of the item
		}
		
		{				
			$values["c6"]=array("c6",$params["day"]);					
			$values["c7"]=array("c7",$params["month"]);
			$values["c8"]=array("c8",$params["hour"]);
			$values["c9"]=array("c9",$params["title"]);
			$values["c10"]=array("c10",$params["text"]);
					
		}
		
		{
			$insert_stat=h_function_manage_item_in_db(array(
				"mode"=>"insert",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_news_items"			
			));
			if(!$insert_stat)
			{
				return "[h_error_sql_execution_insert_error]";
			}
		}
		
		return "OK";		
	}
	
	if($params["connection"]->affected_rows>=1 && $params["overwrite_current"])
	{
		{
			$values["c3"]=array("c3",time());
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}			
		}
			
		{
			if(isset($params["day"]) && $params["day"]!="")
			{					
				$values["c6"]=array("c6",$params["day"]);
			}
			if(isset($params["month"]) && $params["month"]!="")
			{					
				$values["c7"]=array("c7",$params["month"]);
			}
			if(isset($params["hour"]) && $params["hour"]!="")
			{
				$values["c8"]=array("c8",$params["hour"]);
			}
			if(isset($params["title"]) && $params["title"]!="")
			{
				$values["c9"]=array("c9",$params["title"]);
			}
			if(isset($params["text"]) && $params["text"]!="")
			{
				$values["c10"]=array("c10",$params["text"]);
			}
			
			if(isset($params["id"]) && $params["id"]!="")
			{					
				$values["c1"]=array("c1",$params["id"]);
			}
								
		}

		{
			$update_stat=h_function_manage_item_in_db(array(
				"mode"=>"update",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_news_items"			
			));
			if(!$update_stat)
			{
				return "[h_error_sql_execution_update_error]";
			}
		}
		
		return "OK";
	}
	if($params["connection"]->affected_rows>=1 && !$params["overwrite_current"])
	{
		return "OK";
	}
}




function h_function_load_ofer($params)
{
	{	
		$table_result=h_function_create_regular_table(array(
			"connection"=>$params["connection"],
			"type"=>"small",
			"name"=>"h_ofertas_items",		
		));
		if(!$table_result)
		{
			return "[h_error_h_rest_items_table_creation_error]";
		}
	}
	
	{
		$query="SELECT * FROM h_ofertas_items WHERE c1='".urlencode($params["id"])."'"; 
		if(!$params["connection"]->query($query))
		{
			return "[h_error_sql_execution_error]";
		}
	}
	
	if($params["connection"]->affected_rows==0 && !$params["create_if_not_exists"])
	{
		return "OK";
	}
	
	if($params["connection"]->affected_rows==0 && $params["create_if_not_exists"])
	{
		{	
			$values["c1"]=array("c1",$params["id"]); //unique id
			$values["c2"]=array("c2",time()); // creation time_stamp
			$values["c3"]=array("c3",time()); // modification time_stamp
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}
			else
			{
				$values["c4"]=array("c4","1"); //By default active
			}
			
			$values["c5"]=array("c5","h_ofertas_items"); //Table of the item
		}
		
		{				
			$values["c6"]=array("c6",$params["nombre"]);					
			$values["c7"]=array("c7",$params["id_rest_oferta"]);
			$values["c10"]=array("c10",$params["descripcion"]);
		}
		
		{
			$insert_stat=h_function_manage_item_in_db(array(
				"mode"=>"insert",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_ofertas_items"			
			));
			if(!$insert_stat)
			{
				return "[h_error_sql_execution_insert_error]";
			}
		}
		
		return "OK";		
	}
	
	if($params["connection"]->affected_rows>=1 && $params["overwrite_current"])
	{
		{
			$values["c3"]=array("c3",time());
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}			
		}
			
		{
			if(isset($params["nombre"]) && $params["nombre"]!="")
			{					
				$values["c6"]=array("c6",$params["nombre"]);
			}
			if(isset($params["id_rest_oferta"]) && $params["id_rest_oferta"]!="")
			{					
				$values["c7"]=array("c7",$params["id_rest_oferta"]);
			}
			if(isset($params["descripcion"]) && $params["descripcion"]!="")
			{
				$values["c10"]=array("c10",$params["descripcion"]);
			}
			
			if(isset($params["id"]) && $params["id"]!="")
			{					
				$values["c1"]=array("c1",$params["id"]);
			}
					
							
		}

		{
			$update_stat=h_function_manage_item_in_db(array(
				"mode"=>"update",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_ofertas_items"			
			));
			if(!$update_stat)
			{
				return "[h_error_sql_execution_update_error]";
			}
			
		}
		
		return "OK";
	}
	if($params["connection"]->affected_rows>=1 && !$params["overwrite_current"])
	{
		return "OK";
	}
}





function h_function_load_emba($params)
{
	{	
		$table_result=h_function_create_regular_table(array(
			"connection"=>$params["connection"],
			"type"=>"small",
			"name"=>"h_embajadores_items",		
		));
		if(!$table_result)
		{
			return "[h_error_h_rest_items_table_creation_error]";
		}
	}
	
	{
		$query="SELECT * FROM h_embajadores_items WHERE c1='".urlencode($params["id"])."'"; 
		if(!$params["connection"]->query($query))
		{
			return "[h_error_sql_execution_error]";
		}
	}
	
	if($params["connection"]->affected_rows==0 && !$params["create_if_not_exists"])
	{
		return "OK";
	}
	
	if($params["connection"]->affected_rows==0 && $params["create_if_not_exists"])
	{
		{	
			$values["c1"]=array("c1",$params["id"]); //unique id
			$values["c2"]=array("c2",time()); // creation time_stamp
			$values["c3"]=array("c3",time()); // modification time_stamp
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}
			else
			{
				$values["c4"]=array("c4","1"); //By default active
			}
			
			$values["c5"]=array("c5","h_ofertas_items"); //Table of the item
		}
		
		{				
			$values["c6"]=array("c6",$params["nombre"]);					
			$values["c10"]=array("c10",$params["descripcion"]);
			$values["c12"]=array("c12",$params["provincia"]);
			$values["c13"]=array("c13",$params["id_localidad"]);
			$values["c14"]=array("c14",$params["localidad"]);

		}
		
		{
			$insert_stat=h_function_manage_item_in_db(array(
				"mode"=>"insert",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_embajadores_items"			
			));
			if(!$insert_stat)
			{
				return "[h_error_sql_execution_insert_error]";
			}
		}
		
		return "OK";		
	}
	
	if($params["connection"]->affected_rows>=1 && $params["overwrite_current"])
	{
		{
			$values["c3"]=array("c3",time());
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}			
		}
			
		{
			if(isset($params["nombre"]) && $params["nombre"]!="")
			{					
				$values["c6"]=array("c6",$params["nombre"]);
			}
			
			if(isset($params["descripcion"]) && $params["descripcion"]!="")
			{
				$values["c10"]=array("c10",$params["descripcion"]);
			}
			
			if(isset($params["provincia"]) && $params["provincia"]!="")
			{
				$values["c12"]=array("c12",$params["provincia"]);
			}
			if(isset($params["id_localidad"]) && $params["id_localidad"]!="")
			{
				$values["c13"]=array("c13",$params["id_localidad"]);
			}
			if(isset($params["localidad"]) && $params["localidad"]!="")
			{
				$values["c14"]=array("c14",$params["localidad"]);
			}
			
			if(isset($params["id"]) && $params["id"]!="")
			{					
				$values["c1"]=array("c1",$params["id"]);
			}
					
							
		}

		{
			$update_stat=h_function_manage_item_in_db(array(
				"mode"=>"update",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_embajadores_items"			
			));
			if(!$update_stat)
			{
				return "[h_error_sql_execution_update_error]";
			}
			
		}
		
		return "OK";
	}
	if($params["connection"]->affected_rows>=1 && !$params["overwrite_current"])
	{
		return "OK";
	}
}




function h_function_show_news($params)
{
		$query="SELECT * FROM h_news_items ORDER BY id DESC LIMIT 0,".$params["max_number"];
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se han podido recuperar las noticias, disculpa las molestias.</div>";
		}
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>Ahora mismo no hay ninguna noticia de última hora.</div>";				
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding:10px;border-bottom:1px solid black">';
			echo '<div style="float:left;width:20%;text-align:right;font-size:2.3em;font-family:\'2\',Times New Roman;line-height: 0.8em;">';
			echo urldecode($row["c6"]);
			echo '<br>';
			echo '<span style="font-size:0.5em"><b>'.urldecode($row["c7"]).'</b></span>';
			echo '<br>';
			echo '<span style="font-size:0.4em">'.urldecode($row["c8"]).'</span>';
			echo '</div>';
			echo '<div style="float:right;width:70%;text-align:left;font-size:0.8em;font-family:\'1\',Arial">';
			echo '<span style="font-size:1.2em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c9"]).'</span>';
			echo '<br><br>';
			echo urldecode($row["c10"]);
			echo '</div>';
			echo '<div style="clear:both"></div>';
			echo '</div>';
		}
}
function h_function_show_news_edit($params)
{
		$query="SELECT * FROM h_news_items ORDER BY id DESC LIMIT 0,".$params["max_number"];
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se han podido recuperar las noticias, disculpa las molestias.</div>";
		}
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>Ahora mismo no hay ninguna noticia de última hora.</div>";				
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding:10px;border-bottom:1px solid black">';
			echo '<div style="float:left;width:6%;">';
			echo '<br><img src="../images/edit.png" onclick="edit_item(\''.urldecode($row["id"]).'\', \'noticia\')" title="Editar" width="26" style="cursor:pointer" /><br>';
			echo '<br><img src="../images/delete.png" onclick="delete_item(\''.urldecode($row["id"]).'\', \'noticia\')" title="Eliminar" width="26" style="cursor:pointer" /><br></div>';
			echo '<div style="float:left;width:18%;text-align:right;font-size:2.3em;font-family:\'2\',Times New Roman;line-height: 0.8em;">';
			echo urldecode($row["c6"]);
			echo '<br>';
			echo '<span style="font-size:0.5em"><b>'.urldecode($row["c7"]).'</b></span>';
			echo '<br>';
			echo '<span style="font-size:0.4em">'.urldecode($row["c8"]).'</span>';
			echo '</div>';
			echo '<div style="float:right;width:70%;text-align:left;font-size:0.8em;font-family:\'1\',Arial">';
			echo '<span style="font-size:1.2em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c9"]).'</span>';
			echo '<br><br>';
			echo urldecode($row["c10"]);
			echo '</div>';
			echo '<div style="clear:both"></div>';
			echo '</div>';
		}
}

function h_function_show_restaurants_edit($params)
{
		$query="SELECT * FROM h_restaurantes_items ORDER BY id DESC LIMIT 0,".$params["max_number"];
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se han podido recuperar los restaurantes, disculpa las molestias.</div>";
			return false;
		}
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No hay ningún restaurante.</div>";return false;
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding:10px;border-bottom:1px solid black">';
			echo '<div style="float:left;width:20%;">';
			echo '<img src="../images/edit.png" onclick="edit_item(\''.urldecode($row["id"]).'\', \'restaurante\')" title="Editar" width="26" style="cursor:pointer" /> ';
			echo ' <img src="../images/delete.png" onclick="delete_item(\''.urldecode($row["id"]).'\', \'restaurante\')" title="Eliminar" width="26" style="cursor:pointer" /><br></div>';
			echo '<div style="float:right;width:70%;text-align:left;font-size:0.8em;font-family:\'1\',Arial">';
			echo '<span style="font-size:1.2em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c9"]).' (ID: '.urldecode($row["c1"]).')</span>';
			echo '<br>';
			echo '</div>';
			echo '<div style="clear:both"></div>';
			echo '</div>';
		}
}

function h_function_show_products_edit($params)
{
		$query="SELECT * FROM h_productos_items ORDER BY id DESC LIMIT 0,".$params["max_number"];
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se han podido recuperar los productos, disculpa las molestias.</div>";
			return false;
		}
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No hay ningún producto.</div>";return false;
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding:10px;border-bottom:1px solid black">';
			echo '<div style="float:left;width:20%;">';
			echo '<img src="../images/edit.png" onclick="edit_item(\''.urldecode($row["id"]).'\', \'producto\')" title="Editar" width="26" style="cursor:pointer" /> ';
			echo ' <img src="../images/delete.png" onclick="delete_item(\''.urldecode($row["id"]).'\', \'producto\')" title="Eliminar" width="26" style="cursor:pointer" /><br></div>';
			echo '<div style="float:right;width:70%;text-align:left;font-size:0.8em;font-family:\'1\',Arial">';
			echo '<span style="font-size:1.2em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c9"]).' - '.urldecode($row["c14"]).' (ID: '.urldecode($row["c1"]).')</span>';
			echo '<br>';
			echo '</div>';
			echo '<div style="clear:both"></div>';
			echo '</div>';
		}
}

function h_function_show_dires_edit($params)
{
		$query="SELECT * FROM h_direcciones_items ORDER BY id DESC LIMIT 0,".$params["max_number"];
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se han podido recuperar las direcciones, disculpa las molestias.</div>";
			return false;
		}
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No hay ninguna direccion.</div>";return false;
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding:10px;border-bottom:1px solid black">';
			echo '<div style="float:left;width:20%;">';
			echo '<img src="../images/edit.png" onclick="edit_item(\''.urldecode($row["id"]).'\', \'direccion\')" title="Editar" width="26" style="cursor:pointer" /> ';
			//echo ' <img src="../images/delete.png" onclick="delete_item(\''.urldecode($row["id"]).'\', \'direccion\')" title="Eliminar" width="26" style="cursor:pointer" /><br>';
			echo '</div>';
			echo '<div style="float:right;width:70%;text-align:left;font-size:0.8em;font-family:\'1\',Arial">';
			echo '<span style="font-size:1.2em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c9"]).' - '.urldecode($row["c8"]).' (ID: '.urldecode($row["c1"]).')</span>';
			echo '<br>';
			echo '</div>';
			echo '<div style="clear:both"></div>';
			echo '</div>';
		}
}

function h_function_show_recetas_edit($params)
{
		$query="SELECT * FROM h_recetas_items ORDER BY id DESC LIMIT 0,".$params["max_number"];
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se han podido recuperar las recetas, disculpa las molestias.</div>";
			return false;
		}
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No hay ninguna receta.</div>";return false;
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding:10px;border-bottom:1px solid black">';
			echo '<div style="float:left;width:20%;">';
			echo '<img src="../images/edit.png" onclick="edit_item(\''.urldecode($row["id"]).'\', \'receta\')" title="Editar" width="26" style="cursor:pointer" /> ';
			//echo ' <img src="../images/delete.png" onclick="delete_item(\''.urldecode($row["id"]).'\', \'receta\')" title="Eliminar" width="26" style="cursor:pointer" /><br>';
			echo '</div>';
			echo '<div style="float:right;width:70%;text-align:left;font-size:0.8em;font-family:\'1\',Arial">';
			echo '<span style="font-size:1.2em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c9"]).' (ID: '.urldecode($row["c1"]).')</span>';
			echo '<br>';
			echo '</div>';
			echo '<div style="clear:both"></div>';
			echo '</div>';
		}
}

function h_function_show_products_edit($params)
{
		$query="SELECT * FROM h_productos_items ORDER BY id DESC LIMIT 0,".$params["max_number"];
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se han podido recuperar los productos, disculpa las molestias.</div>";
			return false;
		}
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No hay ningún producto.</div>";return false;
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding:10px;border-bottom:1px solid black">';
			echo '<div style="float:left;width:20%;">';
			echo '<img src="../images/edit.png" onclick="edit_item(\''.urldecode($row["id"]).'\', \'producto\')" title="Editar" width="26" style="cursor:pointer" /> ';
			//echo ' <img src="../images/delete.png" onclick="delete_item(\''.urldecode($row["id"]).'\', \'producto\')" title="Eliminar" width="26" style="cursor:pointer" /><br>';
			echo '</div>';
			echo '<div style="float:right;width:70%;text-align:left;font-size:0.8em;font-family:\'1\',Arial">';
			echo '<span style="font-size:1.2em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c9"]).' - '.urldecode($row["c14"]).' (ID: '.urldecode($row["c1"]).')</span>';
			echo '<br>';
			echo '</div>';
			echo '<div style="clear:both"></div>';
			echo '</div>';
		}
}

function h_function_show_chef_edit($params)
{
		$query="SELECT * FROM h_chef_items ORDER BY id DESC LIMIT 0,".$params["max_number"];
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se han podido recuperar los chefs, disculpa las molestias.</div>";
			return false;
		}
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No hay ningún chef.</div>";return false;
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding:10px;border-bottom:1px solid black">';
			echo '<div style="float:left;width:20%;">';
			echo '<img src="../images/edit.png" onclick="edit_item(\''.urldecode($row["id"]).'\', \'chef\')" title="Editar" width="26" style="cursor:pointer" /> ';
			//echo ' <img src="../images/delete.png" onclick="delete_item(\''.urldecode($row["id"]).'\', \'chef\')" title="Eliminar" width="26" style="cursor:pointer" /><br>';
			echo '</div>';
			echo '<div style="float:right;width:70%;text-align:left;font-size:0.8em;font-family:\'1\',Arial">';
			echo '<span style="font-size:1.2em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c9"]).' (ID: '.urldecode($row["c1"]).')</span>';
			echo '<br>';
			echo '</div>';
			echo '<div style="clear:both"></div>';
			echo '</div>';
		}
}


function h_function_load_diary($params)
{
	
	{	
		$table_result=h_function_create_regular_table(array(
			"connection"=>$params["connection"],
			"type"=>"small",
			"name"=>"h_diary_items",		
		));
		if(!$table_result)
		{
			return "[h_error_h_diary_items_table_creation_error]";
		}
	}
	
	{
		$query="SELECT * FROM h_diary_items WHERE c1='".urlencode($params["id"])."'"; 
		if(!$params["connection"]->query($query))
		{
			return "[h_error_sql_execution_error]";
		}
	}
	
	if($params["connection"]->affected_rows==0 && !$params["create_if_not_exists"])
	{
		return "OK";
	}
	
	if($params["connection"]->affected_rows==0 && $params["create_if_not_exists"])
	{
		{	
			$values["c1"]=array("c1",$params["id"]); //unique id
			$values["c2"]=array("c2",time()); // creation time_stamp
			$values["c3"]=array("c3",time()); // modification time_stamp
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}
			else
			{
				$values["c4"]=array("c4","1"); //By default active
			}
			
			$values["c5"]=array("c5","h_diary_items"); //Table of the item
		}
		
		{				
			//$values["c6"]=array("c6",$params["date"]);
			//$values["c7"]=array("c7",$params["date_end"]);			
			
			$fecha_explode=explode("/",$params["date"]);
			$values["c6"]=array("c6",mktime(0,0,0,intval($fecha_explode[1]),intval($fecha_explode[0]),intval($fecha_explode[2])));
			$fecha_fin_explode=explode("/",$params["date_end"]);
			$values["c7"]=array("c7",mktime(0,0,0,intval($fecha_fin_explode[1]),intval($fecha_fin_explode[0]),intval($fecha_fin_explode[2])));
			
			$values["c8"]=array("c8",$params["hour"]);
			$values["c9"]=array("c9",$params["title"]);
			$values["c10"]=array("c10",$params["text"]);
			$values["c11"]=array("c11",$params["site"]);
			$values["c12"]=array("c12",$params["provincia"]);
			$values["c13"]=array("c13",$params["image"]);
					
		}
		
		{
			$insert_stat=h_function_manage_item_in_db(array(
				"mode"=>"insert",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_diary_items"			
			));
			if(!$insert_stat)
			{
				return "[h_error_sql_execution_insert_error]";
			}
		}
		
		return "OK";		
	}
	
	if($params["connection"]->affected_rows>=1 && $params["overwrite_current"])
	{
		{
			$values["c3"]=array("c3",time());
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}			
		}
			
		{
			if(isset($params["date"]) && $params["date"]!="")
			{					
				//$values["c6"]=array("c6",$params["date"]);
				$fecha_explode=explode("/",$params["date"]);
				$values["c6"]=array("c6",mktime(0,0,0,$fecha_explode[1],$fecha_explode[0],$fecha_explode[2]));
			
			}
			if(isset($params["date_end"]) && $params["date_end"]!="")
			{					
				//$values["c7"]=array("c7",$params["date_end"]);
				$fecha_fin_explode=explode("/",$params["date_end"]);
				$values["c7"]=array("c7",mktime(0,0,0,$fecha_fin_explode[1],$fecha_fin_explode[0],$fecha_fin_explode[2]));
			}
			
			if(isset($params["hour"]) && $params["hour"]!="")
			{
				$values["c8"]=array("c8",$params["hour"]);
			}
			if(isset($params["title"]) && $params["title"]!="")
			{
				$values["c9"]=array("c9",$params["title"]);
			}
			if(isset($params["text"]) && $params["text"]!="")
			{
				$values["c10"]=array("c10",$params["text"]);
			}
			if(isset($params["site"]) && $params["site"]!="")
			{
				$values["c11"]=array("c11",$params["site"]);
			}
			if(isset($params["provincia"]) && $params["provincia"]!="")
			{
				$values["c12"]=array("c12",$params["provincia"]);
			}
			if(isset($params["image"]) && $params["image"]!="")
			{
				$values["c13"]=array("c13",$params["image"]);
			}
			
			if(isset($params["id"]) && $params["id"]!="")
			{					
				$values["c1"]=array("c1",$params["id"]);
			}
								
		}

		{
			$update_stat=h_function_manage_item_in_db(array(
				"mode"=>"update",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_diary_items"			
			));
			if(!$update_stat)
			{
				return "[h_error_sql_execution_update_error]";
			}
		}
		
		return "OK";
	}
	if($params["connection"]->affected_rows>=1 && !$params["overwrite_current"])
	{
		return "OK";
	}
}
function h_function_show_diary($params)
{
		$query="SELECT * FROM h_diary_items ORDER BY id DESC LIMIT 0,".$params["max_number"];
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se ha podido recuperar el calendario, disculpa las molestias.</div>";
		}
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No hay ningún evento.</div>";				
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding:10px;border-bottom:1px solid black">';
			echo '<div style="float:left;width:20%;text-align:right;font-size:2.3em;font-family:\'2\',Times New Roman;line-height: 0.8em;">';
			if($row["c13"]!="")
			{
				echo '<img src="'.urldecode($row["c13"]).'"" alt="imagen" style="margin:15px 0" />';
				echo '<br>';
			}
			echo '</div>';
			echo '<div style="float:right;width:70%;text-align:left;font-size:0.9em;font-family:\'1\',Arial">';
			echo '<span style="font-size:1.1em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c9"]).'</span>';
			echo '<br><br>';
			echo '<span style="font-size:0.9em;font-style:italic">'.urldecode($row["c11"]).'</span>';
			echo '<br><br>';
			
			if($row["c6"]==$row["c7"])
				$dia=date('d/m/Y',urldecode($row["c6"]));
			else
				$dia=date('d/m/Y',urldecode($row["c6"])).' a '.date('d/m/Y',urldecode($row["c7"]));
				
			echo '<span style="font-size:0.9em;"><b>Día: </b>'.$dia.'</span><br>';		
			
			echo '<span style="font-size:0.9em;"><b>Lugar: </b>'.urldecode($row["c11"]).' ('.urldecode($row["c11"]).')</span><br>';
			echo '<span style="font-size:0.9em;"><b>Hora: </b>'.urldecode($row["c8"]).'</span><br>';
			echo '<br>';
			echo '</div>';
			echo '<div style="clear:both"></div>';
			echo '<div style="margin:5px auto; width:90%; font-size: 0.9em;">';
			echo urldecode($row["c10"]);
			echo '<br></div>';
			echo '<div style="clear:both"></div>';
			echo '</div>';
		}
}
function h_function_show_diary_edit($params)
{
	$query="SELECT * FROM h_diary_items ORDER BY c6 DESC";
	$result=$params["connection"]->query($query);
	if(!$result)
	{
		echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se ha podido recuperar la agenda, disculpa las molestias.</div>";
	}
	if($params["connection"]->affected_rows==0)
	{
		echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No hay ningún evento.</div>";				
	}
	while($row=$result->fetch_assoc())
	{
		echo '<div style="padding:10px;border-bottom:1px solid black">';
		echo '<div style="float:left;width:6%;">';
		echo '<br><img src="../images/edit.png" onclick="edit_item(\''.urldecode($row["id"]).'\', \'agenda\')" title="Editar" width="26" style="cursor:pointer" /><br>';
		//echo '<br><img src="../images/delete.png" onclick="delete_item(\''.urldecode($row["id"]).'\', \'agenda\')" title="Eliminar" width="26" style="cursor:pointer" /><br>';
		echo '</div>';
		echo '<div style="float:left;width:23%;text-align:right;font-size:2.3em;font-family:\'2\',Times New Roman;line-height: 0.8em;">';
		if($row["c13"]!="")
		{
			echo '<img src="'.urldecode($row["c13"]).'"" alt="imagen" style="margin:15px 0" />';
			echo '<br>';
		}
		echo '</div>';
		echo '<div style="float:right;width:65%;text-align:left;font-size:0.9em;font-family:\'1\',Arial">';
		echo '<span style="font-size:1.1em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c9"]).'</span>';
		echo '<br><br>';
		
		if($row["c6"]==$row["c7"])
				$dia=date('d/m/Y',urldecode($row["c6"]));
			else
				$dia=date('d/m/Y',urldecode($row["c6"])).' a '.date('d/m/Y',urldecode($row["c7"]));
				
		echo '<span style="font-size:0.9em;"><b>Día: </b>'.$dia.'</span><br>';		
			
		echo '<span style="font-size:0.9em;"><b>Lugar: </b>'.urldecode($row["c11"]).' ('.urldecode($row["c12"]).')</span><br>';
		echo '<span style="font-size:0.9em;"><b>Hora: </b>'.urldecode($row["c8"]).'</span><br>';
		echo '<br>';
		echo '</div>';
		echo '<div style="float:right; width:88%">';
		echo urldecode($row["c10"]);
		echo '<br></div>';
		echo '<div style="clear:both"></div>';
		echo '</div>';
	}
}

function h_function_show_calendar($params)
{
		$fecha_explode=explode("/",$params["date"]);
		
		$query="SELECT * FROM h_diary_items WHERE c6<='".mktime(0,0,0,$fecha_explode[1],$fecha_explode[0],$fecha_explode[2])."' AND c7>='".mktime(0,0,0,$fecha_explode[1],$fecha_explode[0],$fecha_explode[2])."' ORDER BY id DESC";
		
		//$query="SELECT * FROM h_diary_items ORDER BY id DESC LIMIT 0,".$params["max_number"]; 
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se ha podido recuperar el calendario, disculpa las molestias.</div>";
		}
		
		$fecha=$params["date"];
		$fecha_explode=explode("/",$fecha);
		
		$day=$fecha_explode[0];
		$month=$fecha_explode[1];
		$year=$fecha_explode[2];
		
		$firstdayofmonth=date("N",mktime(0,0,0,$month,1,$year));
		$totaldaysofmonth=date("j",mktime(0,0,0,$month+1,0,$year));
		
		$nameMiniDays=array("LU","MA","MI","JU","VI","SA","DO");
		$nameMonth=array("Enero","Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
		
		echo '<div style="max-width:700px;margin:auto;">';
		
			echo '<div id="calendar">';
			echo '<div class="title_calendar">';
			echo '<img src="./leftarrow.png" alt="&lt;" class="arrowleft_calendar" onclick="to_previous_month('.$month.')"/>';
			echo $nameMonth[(intval($month)-1)]." ".$year;
			echo '<img src="./rightarrow.png" alt="&gt;" class="arrowright_calendar" onclick="to_next_month('.$month.')"/>';
			echo '<div class="clear"></div>';
			echo '</div>';
			echo '<table class="container_calendar" >';
			echo '<tr>';
			for($i=0;$i<count($nameMiniDays);$i++)
			{
				echo '<th class="dayofweek">'.$nameMiniDays[$i].'</th>';
			}
			echo '</tr>';
			echo '<tr>';
			$k=1;
			for($i=1;$i<$firstdayofmonth;$i++)
			{
				echo '<td class="emptydayspot">&nbsp;</td>';	
				$k++;
			}
			
			if($k==8)
			{
				echo '</tr>';
				$k=1;
			}
			
			if($k==1)
				echo '<tr>';
			
			for($j=1;$j<=$totaldaysofmonth;$j++)
			{
				if($j==$day)
				{
					echo '<td class="today" onclick="to_select_day('.$day.')" >'.$j.'</td>';
				}
				else
				{
					echo '<td class="other_day" onclick="to_select_day('.$j.')">'.$j.'</td>';
				}
				
				$k++;

				if($k==8)
				{
					echo '</tr>';
					$k=1;
				}
				
				if($k==1)
					echo '<tr>';
					
				
			}
			echo '</tr>';
			
			echo '</table>';
		
		echo '</div>';
	
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;;font-size:12px;text-align:center'>".$fecha.": No hay ningún evento.</div>";				
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding:10px;border-bottom:1px solid black">';
			echo '<div style="float:left;width:20%;text-align:right;font-size:2.3em;font-family:\'2\',Times New Roman;line-height: 0.8em;">';
			if($row["c13"]!="")
			{
				echo '<img src="'.urldecode($row["c13"]).'"" alt="imagen" style="margin:15px 0" />';
				echo '<br>';
			}
			echo '</div>';
			echo '<div style="float:right;width:70%;text-align:left;font-size:0.9em;font-family:\'1\',Arial">';
			echo '<span style="font-size:1.1em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c9"]).'</span>';
			echo '<br><br>';
			echo '<span style="font-size:0.9em;font-style:italic">'.urldecode($row["c11"]).'</span>';
			echo '<br><br>';
			
			if($row["c6"]==$row["c7"])
				$dia=date('d/m/Y',urldecode($row["c6"]));
			else
				$dia=date('d/m/Y',urldecode($row["c6"])).' a '.date('d/m/Y',urldecode($row["c7"]));
				
			echo '<span style="font-size:0.9em;"><b>Día: </b>'.$dia.'</span><br>';		
			echo '<span style="font-size:0.9em;"><b>Lugar: </b>'.urldecode($row["c11"]).' ('.urldecode($row["c11"]).')</span><br>';
			echo '<span style="font-size:0.9em;"><b>Hora: </b>'.urldecode($row["c8"]).'</span><br>';
			echo '<br>';
			echo '</div>';
			echo '<div style="clear:both"></div>';
			echo '<div style="margin:5px auto; width:90%; font-size: 0.9em;">';
			echo urldecode($row["c10"]);
			echo '<br></div>';
			echo '<div style="clear:both"></div>';
			echo '</div>';
		}
}


function h_function_show_ofertas_edit($params)
{
		$query="SELECT * FROM h_ofertas_items ORDER BY id DESC LIMIT 0,".$params["max_number"];
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se han podido recuperar las ofertas, disculpa las molestias.</div>";
			return false;
		}
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No hay ninguna oferta.</div>";return false;
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding:10px;border-bottom:1px solid black">';
			echo '<div style="float:left;width:20%;">';
			echo '<img src="../images/edit.png" onclick="edit_item(\''.urldecode($row["id"]).'\', \'oferta\')" title="Editar" width="26" style="cursor:pointer" /> ';
			echo ' <img src="../images/delete.png" onclick="delete_item(\''.urldecode($row["id"]).'\', \'restaurante\')" title="Eliminar" width="26" style="cursor:pointer" /><br></div>';
			echo '<div style="float:right;width:70%;text-align:left;font-size:0.8em;font-family:\'1\',Arial">';
			echo '<span style="font-size:1.2em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c6"]).' (ID: '.urldecode($row["c1"]).')</span>';
			echo '<br>';
			echo '</div>';
			echo '<div style="clear:both"></div>';
			echo '</div>';
		}
}



function h_function_show_embajadores_edit($params)
{
		$query="SELECT * FROM h_embajadores_items ORDER BY id DESC LIMIT 0,".$params["max_number"];
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se han podido recuperar los embajadores, disculpa las molestias.</div>";
			return false;
		}
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No hay ningún embajador.</div>";return false;
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding:10px;border-bottom:1px solid black">';
			echo '<div style="float:left;width:20%;">';
			echo '<img src="../images/edit.png" onclick="edit_item(\''.urldecode($row["id"]).'\', \'embajador\')" title="Editar" width="26" style="cursor:pointer" /> ';
			echo ' <img src="../images/delete.png" onclick="delete_item(\''.urldecode($row["id"]).'\', \'embajador\')" title="Eliminar" width="26" style="cursor:pointer" /><br></div>';
			echo '<div style="float:right;width:70%;text-align:left;font-size:0.8em;font-family:\'1\',Arial">';
			echo '<span style="font-size:1.2em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c6"]).' (ID: '.urldecode($row["c1"]).')</span>';
			echo '<br>';
			echo '</div>';
			echo '<div style="clear:both"></div>';
			echo '</div>';
		}
}




function h_function_load_diary($params)
{
	
	{	
		$table_result=h_function_create_regular_table(array(
			"connection"=>$params["connection"],
			"type"=>"small",
			"name"=>"h_diary_items",		
		));
		if(!$table_result)
		{
			return "[h_error_h_diary_items_table_creation_error]";
		}
	}
	
	{
		$query="SELECT * FROM h_diary_items WHERE c1='".urlencode($params["id"])."'"; 
		if(!$params["connection"]->query($query))
		{
			return "[h_error_sql_execution_error]";
		}
	}
	
	if($params["connection"]->affected_rows==0 && !$params["create_if_not_exists"])
	{
		return "OK";
	}
	
	if($params["connection"]->affected_rows==0 && $params["create_if_not_exists"])
	{
		{	
			$values["c1"]=array("c1",$params["id"]); //unique id
			$values["c2"]=array("c2",time()); // creation time_stamp
			$values["c3"]=array("c3",time()); // modification time_stamp
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}
			else
			{
				$values["c4"]=array("c4","1"); //By default active
			}
			
			$values["c5"]=array("c5","h_diary_items"); //Table of the item
		}
		
		{				
			//$values["c6"]=array("c6",$params["date"]);
			//$values["c7"]=array("c7",$params["date_end"]);			
			
			$fecha_explode=explode("/",$params["date"]);
			$values["c6"]=array("c6",mktime(0,0,0,intval($fecha_explode[1]),intval($fecha_explode[0]),intval($fecha_explode[2])));
			$fecha_fin_explode=explode("/",$params["date_end"]);
			$values["c7"]=array("c7",mktime(0,0,0,intval($fecha_fin_explode[1]),intval($fecha_fin_explode[0]),intval($fecha_fin_explode[2])));
			
			$values["c8"]=array("c8",$params["hour"]);
			$values["c9"]=array("c9",$params["title"]);
			$values["c10"]=array("c10",$params["text"]);
			$values["c11"]=array("c11",$params["site"]);
			$values["c12"]=array("c12",$params["provincia"]);
			$values["c13"]=array("c13",$params["image"]);
					
		}
		
		{
			$insert_stat=h_function_manage_item_in_db(array(
				"mode"=>"insert",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_diary_items"			
			));
			if(!$insert_stat)
			{
				return "[h_error_sql_execution_insert_error]";
			}
		}
		
		return "OK";		
	}
	
	if($params["connection"]->affected_rows>=1 && $params["overwrite_current"])
	{
		{
			$values["c3"]=array("c3",time());
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}			
		}
			
		{
			if(isset($params["date"]) && $params["date"]!="")
			{					
				//$values["c6"]=array("c6",$params["date"]);
				$fecha_explode=explode("/",$params["date"]);
				$values["c6"]=array("c6",mktime(0,0,0,$fecha_explode[1],$fecha_explode[0],$fecha_explode[2]));
			
			}
			if(isset($params["date_end"]) && $params["date_end"]!="")
			{					
				//$values["c7"]=array("c7",$params["date_end"]);
				$fecha_fin_explode=explode("/",$params["date_end"]);
				$values["c7"]=array("c7",mktime(0,0,0,$fecha_fin_explode[1],$fecha_fin_explode[0],$fecha_fin_explode[2]));
			}
			
			if(isset($params["hour"]) && $params["hour"]!="")
			{
				$values["c8"]=array("c8",$params["hour"]);
			}
			if(isset($params["title"]) && $params["title"]!="")
			{
				$values["c9"]=array("c9",$params["title"]);
			}
			if(isset($params["text"]) && $params["text"]!="")
			{
				$values["c10"]=array("c10",$params["text"]);
			}
			if(isset($params["site"]) && $params["site"]!="")
			{
				$values["c11"]=array("c11",$params["site"]);
			}
			if(isset($params["provincia"]) && $params["provincia"]!="")
			{
				$values["c12"]=array("c12",$params["provincia"]);
			}
			if(isset($params["image"]) && $params["image"]!="")
			{
				$values["c13"]=array("c13",$params["image"]);
			}
			
			if(isset($params["id"]) && $params["id"]!="")
			{					
				$values["c1"]=array("c1",$params["id"]);
			}
								
		}

		{
			$update_stat=h_function_manage_item_in_db(array(
				"mode"=>"update",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_diary_items"			
			));
			if(!$update_stat)
			{
				return "[h_error_sql_execution_update_error]";
			}
		}
		
		return "OK";
	}
	if($params["connection"]->affected_rows>=1 && !$params["overwrite_current"])
	{
		return "OK";
	}
}
function h_function_show_diary($params)
{
		$query="SELECT * FROM h_diary_items ORDER BY id DESC LIMIT 0,".$params["max_number"];
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se ha podido recuperar el calendario, disculpa las molestias.</div>";
		}
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No hay ningún evento.</div>";				
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding:10px;border-bottom:1px solid black">';
			echo '<div style="float:left;width:20%;text-align:right;font-size:2.3em;font-family:\'2\',Times New Roman;line-height: 0.8em;">';
			if($row["c13"]!="")
			{
				echo '<img src="'.urldecode($row["c13"]).'"" alt="imagen" style="margin:15px 0" />';
				echo '<br>';
			}
			echo '</div>';
			echo '<div style="float:right;width:70%;text-align:left;font-size:0.9em;font-family:\'1\',Arial">';
			echo '<span style="font-size:1.1em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c9"]).'</span>';
			echo '<br><br>';
			echo '<span style="font-size:0.9em;font-style:italic">'.urldecode($row["c11"]).'</span>';
			echo '<br><br>';
			
			if($row["c6"]==$row["c7"])
				$dia=date('d/m/Y',urldecode($row["c6"]));
			else
				$dia=date('d/m/Y',urldecode($row["c6"])).' a '.date('d/m/Y',urldecode($row["c7"]));
				
			echo '<span style="font-size:0.9em;"><b>Día: </b>'.$dia.'</span><br>';		
			
			echo '<span style="font-size:0.9em;"><b>Lugar: </b>'.urldecode($row["c11"]).' ('.urldecode($row["c11"]).')</span><br>';
			echo '<span style="font-size:0.9em;"><b>Hora: </b>'.urldecode($row["c8"]).'</span><br>';
			echo '<br>';
			echo '</div>';
			echo '<div style="clear:both"></div>';
			echo '<div style="margin:5px auto; width:90%; font-size: 0.9em;">';
			echo urldecode($row["c10"]);
			echo '<br></div>';
			echo '<div style="clear:both"></div>';
			echo '</div>';
		}
}
function h_function_show_diary_edit($params)
{
	$query="SELECT * FROM h_diary_items ORDER BY c6 DESC";
	$result=$params["connection"]->query($query);
	if(!$result)
	{
		echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se ha podido recuperar la agenda, disculpa las molestias.</div>";
	}
	if($params["connection"]->affected_rows==0)
	{
		echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No hay ningún evento.</div>";				
	}
	while($row=$result->fetch_assoc())
	{
		echo '<div style="padding:10px;border-bottom:1px solid black">';
		echo '<div style="float:left;width:6%;">';
		echo '<br><img src="../images/edit.png" onclick="edit_item(\''.urldecode($row["id"]).'\', \'agenda\')" title="Editar" width="26" style="cursor:pointer" /><br>';
		echo '<br><img src="../images/delete.png" onclick="delete_item(\''.urldecode($row["id"]).'\', \'agenda\')" title="Eliminar" width="26" style="cursor:pointer" /><br></div>';
		echo '<div style="float:left;width:23%;text-align:right;font-size:2.3em;font-family:\'2\',Times New Roman;line-height: 0.8em;">';
		if($row["c13"]!="")
		{
			echo '<img src="'.urldecode($row["c13"]).'"" alt="imagen" style="margin:15px 0" />';
			echo '<br>';
		}
		echo '</div>';
		echo '<div style="float:right;width:65%;text-align:left;font-size:0.9em;font-family:\'1\',Arial">';
		echo '<span style="font-size:1.1em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c9"]).'</span>';
		echo '<br><br>';
		
		if($row["c6"]==$row["c7"])
				$dia=date('d/m/Y',urldecode($row["c6"]));
			else
				$dia=date('d/m/Y',urldecode($row["c6"])).' a '.date('d/m/Y',urldecode($row["c7"]));
				
		echo '<span style="font-size:0.9em;"><b>Día: </b>'.$dia.'</span><br>';		
			
		echo '<span style="font-size:0.9em;"><b>Lugar: </b>'.urldecode($row["c11"]).' ('.urldecode($row["c12"]).')</span><br>';
		echo '<span style="font-size:0.9em;"><b>Hora: </b>'.urldecode($row["c8"]).'</span><br>';
		echo '<br>';
		echo '</div>';
		echo '<div style="float:right; width:88%">';
		echo urldecode($row["c10"]);
		echo '<br></div>';
		echo '<div style="clear:both"></div>';
		echo '</div>';
	}
}

function h_function_show_calendar($params)
{
		$fecha_explode=explode("/",$params["date"]);
		
		$query="SELECT * FROM h_diary_items WHERE c6<='".mktime(0,0,0,$fecha_explode[1],$fecha_explode[0],$fecha_explode[2])."' AND c7>='".mktime(0,0,0,$fecha_explode[1],$fecha_explode[0],$fecha_explode[2])."' ORDER BY id DESC";
		
		//$query="SELECT * FROM h_diary_items ORDER BY id DESC LIMIT 0,".$params["max_number"]; 
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:'3';font-size:12px;text-align:center'>No se ha podido recuperar el calendario, disculpa las molestias.</div>";
		}
		
		$fecha=$params["date"];
		$fecha_explode=explode("/",$fecha);
		
		$day=$fecha_explode[0];
		$month=$fecha_explode[1];
		$year=$fecha_explode[2];
		
		$firstdayofmonth=date("N",mktime(0,0,0,$month,1,$year));
		$totaldaysofmonth=date("j",mktime(0,0,0,$month+1,0,$year));
		
		$nameMiniDays=array("LU","MA","MI","JU","VI","SA","DO");
		$nameMonth=array("Enero","Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
		
		echo '<div style="max-width:700px;margin:auto;">';
		
			echo '<div id="calendar">';
			echo '<div class="title_calendar">';
			echo '<img src="./leftarrow.png" alt="&lt;" class="arrowleft_calendar" onclick="to_previous_month('.$month.')"/>';
			echo $nameMonth[(intval($month)-1)]." ".$year;
			echo '<img src="./rightarrow.png" alt="&gt;" class="arrowright_calendar" onclick="to_next_month('.$month.')"/>';
			echo '<div class="clear"></div>';
			echo '</div>';
			echo '<table class="container_calendar" >';
			echo '<tr>';
			for($i=0;$i<count($nameMiniDays);$i++)
			{
				echo '<th class="dayofweek">'.$nameMiniDays[$i].'</th>';
			}
			echo '</tr>';
			echo '<tr>';
			$k=1;
			for($i=1;$i<$firstdayofmonth;$i++)
			{
				echo '<td class="emptydayspot">&nbsp;</td>';	
				$k++;
			}
			
			if($k==8)
			{
				echo '</tr>';
				$k=1;
			}
			
			if($k==1)
				echo '<tr>';
			
			for($j=1;$j<=$totaldaysofmonth;$j++)
			{
				if($j==$day)
				{
					echo '<td class="today" onclick="to_select_day('.$day.')" >'.$j.'</td>';
				}
				else
				{
					echo '<td class="other_day" onclick="to_select_day('.$j.')">'.$j.'</td>';
				}
				
				$k++;

				if($k==8)
				{
					echo '</tr>';
					$k=1;
				}
				
				if($k==1)
					echo '<tr>';
					
				
			}
			echo '</tr>';
			
			echo '</table>';
		
		echo '</div>';
	
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;;font-size:12px;text-align:center'>".$fecha.": No hay ningún evento.</div>";				
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding:10px;border-bottom:1px solid black">';
			echo '<div style="float:left;width:20%;text-align:right;font-size:2.3em;font-family:\'2\',Times New Roman;line-height: 0.8em;">';
			if($row["c13"]!="")
			{
				echo '<img src="'.urldecode($row["c13"]).'"" alt="imagen" style="margin:15px 0" />';
				echo '<br>';
			}
			echo '</div>';
			echo '<div style="float:right;width:70%;text-align:left;font-size:0.9em;font-family:\'1\',Arial">';
			echo '<span style="font-size:1.1em;font-weight:bold;font-family:\'2\',Times New Roman;">'.urldecode($row["c9"]).'</span>';
			echo '<br><br>';
			echo '<span style="font-size:0.9em;font-style:italic">'.urldecode($row["c11"]).'</span>';
			echo '<br><br>';
			
			if($row["c6"]==$row["c7"])
				$dia=date('d/m/Y',urldecode($row["c6"]));
			else
				$dia=date('d/m/Y',urldecode($row["c6"])).' a '.date('d/m/Y',urldecode($row["c7"]));
				
			echo '<span style="font-size:0.9em;"><b>Día: </b>'.$dia.'</span><br>';		
			echo '<span style="font-size:0.9em;"><b>Lugar: </b>'.urldecode($row["c11"]).' ('.urldecode($row["c11"]).')</span><br>';
			echo '<span style="font-size:0.9em;"><b>Hora: </b>'.urldecode($row["c8"]).'</span><br>';
			echo '<br>';
			echo '</div>';
			echo '<div style="clear:both"></div>';
			echo '<div style="margin:5px auto; width:90%; font-size: 0.9em;">';
			echo urldecode($row["c10"]);
			echo '<br></div>';
			echo '<div style="clear:both"></div>';
			echo '</div>';
		}
}


function h_function_recover_offers($params)
{
	$query="SELECT * FROM h_advertising_items WHERE c10<>'no' ORDER BY id DESC LIMIT 0,10";
		$result=$params["connection"]->query($query);
		if(!$result)
		{
			echo "<div style='padding:10px;font-family:Arial;font-size:12px;text-align:center'>No se han podido recuperar las ofertas, disculpa las molestias.</div>";
		}
		if($params["connection"]->affected_rows==0)
		{
			echo "<div style='padding:10px;font-family:Arial;font-size:12px;text-align:center'>Ahora mismo no hay ninguna oferta disponible.</div>&nbsp;&nbsp;";				
		}
		while($row=$result->fetch_assoc())
		{
			echo '<div style="padding-bottom:20px;">';
			echo '<img src="'.$params["root_path"].urldecode($row["c10"]).'" style="width:100%;max-width:500px;display:block;margin:auto" alt="imagen no encontrada"/>';
			echo '</div>';
		}
}

function h_function_load_event_to_track($params)
{
	{	
		$table_result=h_function_create_regular_table(array(
			"connection"=>$params["connection"],
			"type"=>"small",
			"name"=>"h_tracking_events",		
		));
		if(!$table_result)
		{
			return "[h_error_h_news_items_table_creation_error]";
		}
	}
	
	{
		$query="SELECT * FROM h_tracking_events WHERE c1='".urlencode($params["id"])."'";
		if(!$params["connection"]->query($query))
		{
			return "[h_error_sql_execution_error]";
		}
	}
	
	if($params["connection"]->affected_rows==0 && !$params["create_if_not_exists"])
	{
		return "OK";
	}
	
	if($params["connection"]->affected_rows==0 && $params["create_if_not_exists"])
	{
		{	
			$values["c1"]=array("c1",$params["id"]); //unique id
			$values["c2"]=array("c2",time()); // creation time_stamp
			$values["c3"]=array("c3",time()); // modification time_stamp
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}
			else
			{
				$values["c4"]=array("c4","1"); //By default active
			}
			
			$values["c5"]=array("c5","h_tracking_events"); //Table of the item
		}
		
		{				
			$values["c6"]=array("c6",$params["name"]);					
			$values["c7"]=array("c7",$params["current_latlong"]);
			$values["c8"]=array("c8",$params["current_timestamp"]);
			$values["c9"]=array("c9",$params["previous_latlong"]);
			$values["c10"]=array("c10",$params["previous_timestamp"]);
					
		}
		
		{
			$insert_stat=h_function_manage_item_in_db(array(
				"mode"=>"insert",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_tracking_events"			
			));
			if(!$insert_stat)
			{
				return "[h_error_sql_execution_insert_error]";
			}
		}
		
		return "OK";		
	}
	
	if($params["connection"]->affected_rows>=1 && $params["overwrite_current"])
	{
		{
			$values["c3"]=array("c3",time());
			if(isset($params["status"]) && $params["status"]!="")
			{
				$values["c4"]=array("c4",$params["status"]); //status (1-Active,0-Suspended)
			}			
		}
		
		{
			if(isset($params["id"]) && $params["id"]!="")
			{					
				$values["c1"]=array("c1",$params["id"]);
			}
			if(isset($params["name"]) && $params["name"]!="")
			{					
				$values["c6"]=array("c6",$params["name"]);
			}
			if(isset($params["current_latlong"]) && $params["current_latlong"]!="")
			{					
				$values["c7"]=array("c7",$params["current_latlong"]);
			}
			if(isset($params["current_timestamp"]) && $params["current_timestamp"]!="")
			{
				$values["c8"]=array("c8",$params["current_timestamp"]);
			}
			if(isset($params["previous_latlong"]) && $params["previous_latlong"]!="")
			{
				$values["c9"]=array("c9",$params["previous_latlong"]);
			}
			if(isset($params["previous_timestamp"]) && $params["previous_timestamp"]!="")
			{
				$values["c10"]=array("c10",$params["previous_timestamp"]);
			}
								
		}

		{
			$update_stat=h_function_manage_item_in_db(array(
				"mode"=>"update",
				"connection"=>$params["connection"],
				"values"=>$values,
				"table"=>"h_tracking_events"			
			));
			if(!$update_stat)
			{
				return "[h_error_sql_execution_update_error]";
			}
		}
		
		return "OK";
	}
	if($params["connection"]->affected_rows>=1 && !$params["overwrite_current"])
	{
		return "OK";
	}
}

function h_function_recover_tracking_event($params)
{
	$query="SELECT * FROM h_tracking_events WHERE c1='".urlencode($params["id"])."'";
	$result=$params["connection"]->query($query);
	if(!$result)
	{
		return false;
	}
	if($params["connection"]->affected_rows==0)
	{
		return false;			
	}
	$row=$result->fetch_assoc();
	return $row;
}
?>