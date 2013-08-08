<?php
/**
 *P2P视频聊天逻辑处理
 *@author wangyang
 *@version p2p.php add by wangyang 2013.7.31
 *@version p2p.php modified by wangyang 2013.8.2
 */
class p2p
{
	/**
	 *@param object $db  //数据库连接实例
	 *@param string $query  //SQL
	 */
	public function validate( $username, $password )
	{
		/**
		 *@param string $username
		 *@param string $password
		 *
		 *@param array $row  //用于存储数据库返回的数据
		 *@param int $result  //验证操作执行状态
		 *0 => 验证通过
		 *1 => 用户名或密码错误
		 *2 => 用户名不存在
		 *@param object userinfo  //用户数据，包括userid和操作执行状态
		 *@return object $userinfo
		 */
		$db = new mysqli('172.22.224.173','wangyang','19911016','p2p') or die("Database Connect Error");
		$query = "select * from user where username = '$username'";

		//判断符合条件的用户名是否存在，并将相关记录赋值
		if( $row = $db->query($query)->fetch_assoc() )
		{
			//对传入密码的MD5加密值进行比较
			if( $row['password'] === md5($password) )
			{
				$result = 0;
			}
			else
			{
				$result = 1;
			}
		}
		else
		{
			$result = 2;
		}
		//构造object用于返回相关信息
		$userinfo->result = $result;
		$userinfo->userid = $row['id'];

		return $userinfo;
	}

	public function register( $username, $password )
	{
		/**
		 *@return int  //数据库操作的进行状态
		 *0 => 注册失败
		 *1 => 注册成功
		 */
		$password = md5($password);
		$db = new mysqli('172.22.224.173','wangyang','19911016','p2p') or die("Database Connect Error");
		$query = "insert into user (username,password) values ('$username','$password')";

		return $db->query($query) ? 1 : 0;
	}

	public function setUserData( $userid, $peerid, $status )
	{
		/**
		 *@param int userid  //用户的唯一标识符
		 *@param string peerid  //256bit，用于端与端之间的连接
		 *@param int status  //用户当前状态
		 *0 => 未登录
		 *1 => 已登录，等待邀请中
		 *2 => 已登录，正在邀请他人
		 *3 => 已登录，正在进行会话
		 *
		 *
		 *@return int  //数据库操作的进行状态
		 *0 => 更新失败
		 *1 => 更新成功
		 */
		$db = new mysqli('172.22.224.173','wangyang','19911016','p2p') or die("Database Connect Error");
		$query = "update user set peerid = '$peerid', status = '$status' where id = '$userid'";	

		return $db->query($query) ? 1 : 0;
	}

	public function getFriendList( $userid )
	{
		/**
		 *@param array friendlist  //用于获取用户的好友列表
		 *
		 *@return array friendlist
		 */
		$db = new mysqli('172.22.224.173','wangyang','19911016','p2p') or die("Database Connect Error");
		$query = "select user.id,user.username from relation,user where relation.friendid = user.id and relation.userid = '$userid'";

		$friendlist = array();
		if( $result = $db->query($query) )
		{
			//将数据库中的数据按条置入friendlist中
			while($row = $result->fetch_assoc())
			{
				array_push($friendlist,$row);
			}
		}
		return $friendlist;
	}

	public function getUserData( $userid )
	{
		/**
		 *@param array friendlist  //用于获取用户的好友列表
		 *
		 *@return array friendlist
		 */
		$db = new mysqli('172.22.224.173','wangyang','19911016','p2p') or die("Database Connect Error");
		$query = "select peerid from user where id = '$userid' and status = '1'";
		$result = $db->query($query)->fetch_assoc();
		return $result ? $result['peerid'] : "";
	}

	public function addFriend( $userid, $friendname )
	{
		/**
		 *@param string friendname  //待添加的好友姓名
		 *
		 *@return int status  //返回状态
		 *0 => 添加失败
		 *1 => 添加成功
		 *2 => 未找到指定名字的好友
		 *3 => 添加自己为好友
		 */
		$db = new mysqli('172.22.224.173','wangyang','19911016','p2p') or die("Database Connect Error");
		$query = "select id from user where username = '$friendname'";
		//指定名称的好友存在
		if( $result = $db->query($query)->fetch_assoc() )
		{
			$friendid = $result['id'];
			//指定的好友就是申请者本人
			if( $friendid == $userid )
			{
				$status = 3;
			}
			else
			{
				$query = "insert into relation (userid,friendid) values ('$userid','$friendid')";
				if( $db->query($query) )
				{
					$status = 1;
				}
				else
				{
					$status = 0;
				}
			}
		}
		//指定名称的好友不存在
		else
		{
			$status = 2;
		}

		return $status;
	}

	public function recordText( $sourceid, $targetid, $text )
	{
		$db = new mysqli('172.22.224.173','wangyang','19911016','p2p') or die("Database Connect Error");
		$query = "insert into text (sourceid,targetid,text) values ('$sourceid','$targetid','$text')";

		return $db->query($query) ? 1 : 0;
	}

	public function uploadFile( $sourceid, $targetid, $originname, $type, $data )
	{
		$db = new mysqli('172.22.224.173','wangyang','19911016','p2p') or die("Database Connect Error");

		$dir = "../upload/";
		$name = date("YmdHis").$type;
		$path = $dir.$name;
		if( !file_exists( $path ) )
		{
			$handle = fopen( $path, 'w' );
			fclose($handle);	
		}
		file_put_contents( $path, $data->data );
	}
}