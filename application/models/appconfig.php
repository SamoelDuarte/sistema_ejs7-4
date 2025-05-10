<?php
class Appconfig extends CI_Model
{

	function exists($key)
	{
		$this->db->from('app_config');
		$this->db->where('app_config.key', $key);
		$query = $this->db->get();

		return ($query->num_rows() == 1);
	}

	function get_all()
	{
		$this->db->from('app_config');
		$this->db->order_by("key", "asc");
		return $this->db->get();
	}

	function get($key)
	{
		$query = $this->db->get_where('app_config', array('key' => $key), 1);

		if ($query->num_rows() == 1) {
			return $query->row()->value;
		}

		return "";
	}

	function save($key, $value)
	{
		$config_data = array(
			'key' => $key,
			'value' => $value
		);

		if (!$this->exists($key)) {
			return $this->db->insert('app_config', $config_data);
		}

		$this->db->where('key', $key);
		return $this->db->update('app_config', $config_data);
	}

	public function get_taxes()
	{
		$taxes = array();
		for ($i = 1; $i <= 5; $i++) {
			$name = $this->get('default_tax_' . $i . '_name');
			$percent = $this->get('default_tax_' . $i . '_rate');

			if ($name && is_numeric($percent)) {
				$taxes[] = $name . ' (' . $percent . '%)';
			}
		}
		return $taxes;
	}


	function batch_save($data)
	{
		$success = true;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
		foreach ($data as $key => $value) {
			if (!$this->save($key, $value)) {
				$success = false;
				break;
			}
		}

		$this->db->trans_complete();
		return $success;
	}

	function delete($key)
	{
		return $this->db->delete('app_config', array('key' => $key));
	}

	function delete_all()
	{
		return $this->db->empty_table('app_config');
	}
}
