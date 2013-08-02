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
		 *@return int result  //数据库操作的进行状态
		 *0 => 更新成功
		 *1 => 更新失败
		 */
		$db = new mysqli('172.22.224.173','wangyang','19911016','p2p') or die("Database Connect Error");
		$query = "update user set peerid = '$peerid', status = '$status' where id = '$userid'";	

		return $db->query($query) ? 0 : 1;
	}

	public function getUserData( $userid )
	{
		/**
		 *@param array friendlist  //用于获取用户的好友列表
		 *
		 *@return array friendlist
		 */
		$db = new mysqli('172.22.224.173','wangyang','19911016','p2p') or die("Database Connect Error");
		$query = "select user.username,user.peerid from relation,user where relation.friendid = user.id and relation.userid = '$userid' and user.status = '1'";
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
}