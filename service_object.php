<?php

class service_object
{
	protected $date;
	protected $serviceType_id;
	protected $leader_id;
	protected $songs;

	public function __construct ($date, $serviceType_id, $leader_id, array $songs)
	{
		$this->date = $date;
		$this->serviceType_id = $serviceType_id;
		$this->leader_id = $leader_id;
		$this->songs = $songs;
		sort($this->songs);
	}


    function getDate()
    {
    	return $this->date;
    }
    function getServiceTypeID()
    {
    	return $this->serviceType_id;
    }
    function getLeaderID()
    {
    	return $this->leader_id;
    }
    function getSongs()
    {
    	return $this->songs;
    }

    function songsToString()
    {
    	return join(", ", $this->songs);
    }
}

?>