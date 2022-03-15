<?php

class RobotController
{
	public function __construct($pdo)
	{
		$this->pdo = $pdo;
	}

	public function GetActive()
	{
		return [];
	}

}

?>
