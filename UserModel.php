<?php

/**
 * Model for users
 */
class UserModel
{
	// user detail
	public function getUser($id)
	{
		$result = dibi::query('SELECT c.UserId as id, c.UserName as username, p.Hodnota as email FROM [cns_clenove] c
				LEFT JOIN [cns_clenove-properties] p ON (c.UserId=p.UserId AND p.Pole="Email")
				WHERE [c.UserId] = %i', $id);
		return $result->fetch();
	}
	
	// all users
	public function getUsers($order = NULL, $where = NULL, $offset = NULL, $limit = NULL)
	{
		return dibi::query(
		    'SELECT UserID, UserName FROM [cns_clenove] WHERE 1=1
		     %if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
		    '%if', isset($order), 'ORDER BY %by', $order, '%end',
		    '%if', isset($limit), 'LIMIT %i %end', $limit,
		    '%if', isset($offset), 'OFFSET %i %end', $offset
		)->fetchAll();
	}
}

?>