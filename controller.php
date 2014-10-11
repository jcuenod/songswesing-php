<?php
require ("database.php");
require ("service_object.php");

class controller
{
	protected $db;
	protected $songList;

	public function __construct()
	{
		$this->db = new dbconnection();
	}

	public function getServices() //TODO: enable some kind of filtering...
	{
		$toreturn = [];
		$servicelist = $this->db->query_for_assoc("SELECT * FROM `services` ORDER BY `date` DESC LIMIT 10");
		foreach ($servicelist as $service) {
			$sung = [];
		 	$servicesongs = $this->db->query_for_assoc("SELECT `song_id` FROM `usage` WHERE `service_id`='".$service["id"]."'", "song_id");
		 	foreach ($servicesongs as $servicesong) {
		 		$sung[] = $this->getSongNameFromID($servicesong);
		 	}

		 	$toreturn[] = new service_object(
		 		$service["date"],
		 		$service["service_type_id"],
		 		$service["leader_id"],
		 		$sung
		 	);
		}
		return $toreturn;
	}

	public function storeService($service)
	{
		$service_id = $this->db->just_query(sprintf("INSERT INTO services (`date`, `leader_id`, `service_type_id`) VALUES (CAST('%s' AS DATE), '%s', '%s')",
			$service->getDate(),
			$service->getLeaderID(),
			$service->getServiceTypeID()
		));
		if ($service_id == -1)
			return false;

		$ret = true;
		foreach ($service->getSongs() as $song) {
			$v = sprintf("INSERT INTO `usage` (`song_id`, `service_id`) SELECT `songs`.`id`, '%s' FROM songs WHERE `songs`.`song_name` = '%s'",
				$service_id,
				$song
			);
			$ret &= $this->db->just_query($v);
		}
		return $ret;
	}

	public function getService($service)
	{
		return sprintf('<tr>
			<td class="date">%s</td>
			<td class="service_type">%s</td>
			<td class="leader"><a class="unborderedanchor" onclick="leaderAnchorClicked(this)">%s</a></td>
			<td class="songs">%s</td>
			</tr>',
            $service->getDate(),
            $this->getServiceTypeFromID($service->getServiceTypeID()),
            $this->getLeaderFromID($service->getLeaderID()),
            $this->songsToAnchoredString($service->getSongs())
        );
	}
	public function getServicesString()
	{
		$ret = "";
		foreach ($this->getServices() as $s) {
			$ret .= $this->getService($s);
		}
		return $ret;
	}

	public function getSongUsageTable()
	{
		$sql = "SELECT `songs`.`song_name`, COUNT(`usage`.`song_id`) AS `tally` FROM `songs`, `usage` WHERE `songs`.`id` = `usage`.`song_id` GROUP BY `usage`.`song_id` ORDER BY `tally` DESC, `song_name` ASC LIMIT 12";
		$result = $this->db->query_for_assoc($sql);

		$toprint = '<table class="table table-bordered table-condensed table-hover table-striped">';
		$toprint .= '<thead><th>Song</th><th>#</th></thead><tbody>';
		foreach ($result as $row) {
			$tags = $this->getSongTags($row['song_name']);
			$tagstring = "";
			if (!empty($tags))
			{
				foreach ($tags as $tag)
				{
					$tagstring .= "<a class='icontip' style='color: ".$tag["color"]."' title='".$tag["description"]."'><span class='glyphicon glyphicon-exclamation-sign'></span></a>";
				}
			}
			$toprint .= "<tr><td><a class='unborderedanchor' onclick='songAnchorClicked(this)'>".$row['song_name']."</a>".$tagstring."</td><td>".$row['tally']."</td></tr>";
		}
		$toprint .= "</tbody></table>";
		return $toprint;
	}

	public function getSongDataJSON($song_name)
	{
		$colours = ["#E8D0A9", "#B7AFA3", "#C1DAD6", "#D5DAFA", "#ACD1E9", "#6D929B"];
		$sql = "SELECT count(`usage`.`song_id`) AS `tally`, `leaders`.`leader_name` as `name` FROM `usage`, `services`, `leaders`, `songs` WHERE `usage`.`service_id`=`services`.`id` AND `services`.`leader_id`=`leaders`.`id` AND `songs`.`song_name` LIKE('$song_name') AND `songs`.`id` = `usage`.`song_id` GROUP BY `leaders`.`id`";
		$result = $this->db->query_for_assoc($sql);

		$i = 0;
		$usagedata = array();
		foreach ($result as $row) {
			$usagedata[] = [ "value" => $row['tally']*1, "color" => $colours[$i++], "highlight" => "#FFC870", "label" => $row['name']];
		}

		$sql = "SELECT count(`usage`.`song_id`) AS `tally` FROM `usage`, `songs` WHERE `songs`.`song_name` LIKE('$song_name') AND `songs`.`id` = `usage`.`song_id`";
		$tally = $this->db->query_for_assoc($sql)[0]["tally"];

		$tags = $this->getSongTagsString($song_name);

		$sql = "SELECT `songs`.`license`, `songs`.`writers`, `songs`.`lyrics`, `songs`.`sample` FROM `songs` WHERE `songs`.`song_name` LIKE('$song_name')";
		$songinfo = $this->db->query_for_assoc($sql)[0];

		$miscdata = [
			"License / Copyright" => empty($songinfo["license"]) ? "No licensing data yet" : $songinfo["license"],
			"Writers" => empty($songinfo["writers"]) ? "No composer data yet" : $songinfo["writers"],
			"Lyrics"  => empty($songinfo["lyrics"]) ? "No lyrics yet" : "<a href='".$songinfo["lyrics"]."' tag target='_blank'>".$songinfo["lyrics"]."</a>",
			"Sample"  => empty($songinfo["sample"]) ? "No samples (youtube etc.) yet" : "<a href='".$songinfo["sample"]."' tag target='_blank'>".$songinfo["sample"]."</a>",
		];
		$detailsdata["tally"] = $tally;
		if (!empty($tags))
			$miscdata["Tags"] = $tags;

		$data = ["chartdata" => $usagedata, "misc" => $miscdata, "details" => $detailsdata];
		return json_encode($data);
	}

	public function getLeaderDataJSON($leader_name)
	{
		$leader_id = $this->getIDFromLeader($leader_name);
		$sql = "SELECT `songs`.`song_name`, COUNT(`usage`.`song_id`) AS `tally`, `totals`.`total` FROM `songs`, `usage`, `services`, (SELECT COUNT(`usage`.`song_id`) AS `total`, `usage`.`song_id` FROM `usage` GROUP BY `usage`.`song_id`) AS `totals` WHERE `usage`.`service_id` = `services`.`id` AND `services`.`leader_id` = '$leader_id' AND `songs`.`id` = `usage`.`song_id` AND `usage`.`song_id` = `totals`.`song_id` GROUP BY `usage`.`song_id` ORDER BY `song_name`";
		$result = $this->db->query_for_assoc($sql);

		$data = array();
		foreach ($result as $row) {
			$data[] = [ "song_name" => $row['song_name'], "tally" => $row['tally'], "total" => $row['total']];
		}
		return json_encode($data);
	}






	private function songsToAnchoredString($songs)
	{
    	return "<a class='borderedanchor' onclick='songAnchorClicked(this)'>" . join("</a><a class='borderedanchor' onclick='songAnchorClicked(this)'>", $songs) . "</a>";
	}


	public function getServiceTypes()
	{
		return $this->db->query_for_assoc("SELECT id, service_type FROM service_types ORDER BY weight");
	}
	public function getLeaders()
	{
		return $this->db->query_for_assoc("SELECT id, leader_name FROM leaders ORDER BY leader_name");
	}
	public function getSongs($withID = false)
	{
		if (!$withID)
			return $this->db->query_for_assoc("SELECT song_name FROM songs ORDER BY song_name", "song_name");
		else
			return $this->db->query_for_assoc("SELECT id, song_name FROM songs ORDER BY song_name");
	}

	public function getIDFromLeader($leader_name)
	{
		$r = $this->db->query_for_assoc("SELECT `id` FROM `leaders` WHERE `leader_name`='$leader_name' LIMIT 1", "id");
		return $r ? $r[0] : $leader_name;
	}
	public function getLeaderFromID($id)
	{
		$r = $this->db->query_for_assoc("SELECT `leader_name` FROM `leaders` WHERE `id`='$id' ORDER BY `leader_name` LIMIT 1", "leader_name");
		return $r ? $r[0] : $id;
	}
	public function getServiceTypeFromID($id)
	{
		$r = $this->db->query_for_assoc("SELECT `service_type` FROM `service_types` WHERE `id`='$id' ORDER BY `weight` LIMIT 1", "service_type");
		return $r ? $r[0] : $id;
	}
	public function getSongNameFromID($id)
	{
		if (!$this->songList)
		{
			$songList = $this->getSongs(true);
			foreach ($songList as $song) {
				$this->songList[$song["id"]] = $song["song_name"];
			}
		}
		return $this->songList[$id] ? $this->songList[$id] : false;
	}
	public function getSongTags($song_name)
	{
		$sql = "SELECT `tags`.`tag_name`, `tags`.`description`, `tags`.`color`, `songs`.`song_name` FROM `tags`, `tagged_songs`, `songs` WHERE `songs`.`id` = `tagged_songs`.`song_id` AND `tagged_songs`.`tag_id` = `tags`.`id` AND `songs`.`song_name` LIKE('$song_name')";
		return $this->db->query_for_assoc($sql);
	}
	public function getSongTagsString($song_name)
	{
		$taginfo = $this->getSongTags($song_name);
		$tagarray = array();
		foreach ($taginfo as $tag) {
			$tagarray[] = $tag["tag_name"];
		}
		return join(", ", $tagarray);
	}


	public function getCreateForm($object_type)
	{
		switch ($object_type) {
			case 'song':
				?>
<div><form class="form-horizontal" role="form" id="frm_create">
	<div class="form-group"><label class="col-sm-3 control-label">Title</label>  <div class="col-sm-9"><input class="form-control" type="text" name="song_name" required></div></div>
	<div class="form-group"><label class="col-sm-3 control-label">License</label><div class="col-sm-9"><input class="form-control" type="text" name="license" required></div></div>
	<div class="form-group"><label class="col-sm-3 control-label">Writers</label><div class="col-sm-9"><input class="form-control" type="text" name="writers" required></div></div>
	<div class="form-group"><label class="col-sm-3 control-label">Lyrics</label> <div class="col-sm-9"><input class="form-control" type="url" name="lyrics" required></div></div>
	<div class="form-group"><label class="col-sm-3 control-label">Sample</label> <div class="col-sm-9"><input class="form-control" type="url" name="sample" required></div></div>
	<button class="btn btn-primary btn-lg btn-block" type="button" onclick="createAnchorClicked('song')">Create Song</button>
</form></div>
				<?php
				break;
			case 'leader':
				?>
<div><form class="form-horizontal" role="form" id="frm_create">
	<div class="form-group"><label class="col-sm-4 control-label">Leader Name</label><div class="col-sm-8"><input class="form-control" type="text" name="leader_name" required></div></div>
	<button class="btn btn-primary btn-lg btn-block" type="button" onclick="createAnchorClicked('leader')">Create Leader</button>
</form></div>
				<?php
				break;
			case 'service_type':
				?>
<div><form class="form-horizontal" role="form" id="frm_create">
	<div class="form-group"><label class="col-sm-4 control-label">Service Name</label><div class="col-sm-8"><input class="form-control" type="text" name="service_type_name" required></div></div>
	<div class="form-group"><label class="col-sm-4 control-label">Weight</label><div class="col-sm-8"><input class="form-control" type="number" name="weight" value="0" required></div></div>
	<button class="btn btn-primary btn-lg btn-block" type="button" onclick="createAnchorClicked('service_type')">Create Service</button>
</form></div>
				<?php
				break;
		}
	}

	public function insertNew($object_data)
	{
		$object_type_to_table_name = [
			"leader" => "leaders",
			"service_type" => "service_types",
			"song" => "songs"];
		$object_type_to_fields = [
			"leader" => ["leader_name"],
			"service_type" => ["service_type", "weight"],
			"song" => ["song_name", "license", "writers", "lyrics", "sample"]];
		
		$sql = "INSERT INTO ".$object_type_to_table_name[$object_data["object_type"]]
			.$this->mapFieldsToSQL($object_type_to_table_name[$object_data["object_type"]], $object_type_to_fields[$object_data["object_type"]])
			." VALUES "
			.(isset($object_data['values'][0]) ? join(",", $this->mapValuesToSQL($object_data["values"])) : $this->mapValuesToSQL($object_data["values"]));
		$result = $this->db->just_query($sql);

		if ($result == -1)
			return "failed";

		$toret["type"] = $object_data["object_type"];
		switch ($object_data["object_type"]) {
			case 'song':
				$toret["newSong"] = $object_data["values"]["song_name"];
				break;
			case 'leader':
				$toret["newLeaderName"] = $object_data["values"]["leader_name"];
				$toret["newLeaderID"] = $result;
				break;
			case 'service_type':
				$toret["newServiceType"] = $object_data["values"]["service_type_name"];
				$toret["newServiceTypeID"] = $result;
				break;
		}

		return json_encode($toret);
	}
	private function mapFieldsToSQL($tablename, $fieldnames)
	{
		return "(`".join("`, `$tablename`.`", $fieldnames)."`)";
	}
	private function mapValuesToSQL($values)
	{
		return "('".join("', '", $values)."')";
	}
}
$controller = new controller();

?>