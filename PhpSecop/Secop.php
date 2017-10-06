<?php

/**
 *
 * @author Tor Krill
 * @copyright 2017 Tor Krill tor@openproducts.se
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


class Secop
{
	private $sock;
	private $connected = false;
	private $tid = 0;

	function __construct()
	{
	}

	function __destruct()
	{
		if( $this->connected )
		{
			fclose( $this->sock );
			$this->connected = false;
		}
	}

	private function _connect()
	{
		if( ! $this->connected )
		{
			$this->sock = stream_socket_client("unix:///tmp/secop");
			$this->connected = !( $this->sock === FALSE );
		}
		return $this->connected;
	}

	private function _processreply( $res )
	{
		if( $res["status"]["value"] == 0 )
		{
			return array(true, $res);
		}
		else
		{
			return array(false, $res["status"]["desc"]);
		}
	}

	private function _dorequest($req)
	{
		if( !$this->_connect() )
		{
			return array(false, "Not connected");
		}

		$req["tid"] = $this->tid;
		$req["version"] = 1.0;

		fwrite($this->sock,json_encode($req, JSON_UNESCAPED_UNICODE ));

		$res=json_decode(fgets($this->sock,16384),true);

		return $this->_processreply($res);
	}

	public function status()
	{
		$req["cmd"] = "status";
		return $this->_dorequest($req);
	}


	public function sockauth()
	{
		$req["cmd"] = "auth";
		$req["type"] = "socket";
		return $this->_dorequest($req);
	}

	public function plainauth($user, $password)
	{
		$req["cmd"] = "auth";
		$req["type"] = "plain";
		$req["username"] = $user;
		$req["password"] = $password;

		return $this->_dorequest($req);
	}

	public function getusers()
	{
		$req["cmd"] = "getusers";
		return $this->_dorequest($req);
	}

	public function getusergroups( $user )
	{
		$req["cmd"] = "getusergroups";
		$req["username"] = $user;
		return $this->_dorequest($req);
	}

	public function getattributes( $user )
	{
		$req["cmd"] = "getattributes";
		$req["username"] = $user;
		return $this->_dorequest($req);
	}

	public function getattribute( $user, $attribute )
	{
		$req["cmd"] = "getattribute";
		$req["username"] = $user;
		$req["attribute"] = $attribute;
		return $this->_dorequest($req);
	}
}

