<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_info extends CI_Model
{
	/*
		function getting user's partial information from 'halalkorea.basic_user'

		return type : String
		Writer : Knunu
	*/

	public function get_info($find_option = array(), $return_option = '*') {
		$this->db->select($return_option);
		$this->db->from('basic_user');
		if (count($find_option) != 0) $this->db->where($find_option);

		$query = $this->db->get();
		if ($query) {
			// one row
			if ($query->num_rows() == 1) {
				$query_result = $query->row_array();

				if ($return_option == '*' || strpos($return_option, ',')) {
					// multiple columns
					$final_result = $query_result;
				} else {
					// one column
					$final_result = $query_result[$return_option];
				}
			}
			// multiple rows
			else if ($query->num_rows() > 1) {
				$final_result = $query->result_array();
			}
			// zero row
			else {
				$final_result = '';
			}
		}

		return $final_result;
	}

	/**
	 * temporary function for community article list.
	 * THIS IS ONLY FOR GETTING ONE ROW OF RESULT
	 * @param array $find_option
	 * @param string $return_option
	 * @return string
	 */
	public function get($find_option = array(), $return_option = '*') {
		$this->db->select($return_option);
		$this->db->from('basic_user');
		$this->db->where($find_option);

		$query = $this->db->get();
		if ($query) {
			// one row
			$query_result = $query->row();

			if ($return_option == '*' || strpos($return_option, ',')) {
				// multiple columns
				$final_result = $query_result == NULL ? "" : $query_result;
			} else {
				// one column
				$final_result = $query_result == NULL ? "" : $query_result->$return_option;
			}
		}

		return $final_result;
	}
}