<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Device_model extends CI_Model
{

    protected $table_name = 'devices';

    public function __construct()
    {
        parent::__construct();
    }

    public function save($data)
    {

        $this->db->insert($this->table_name, $data);

        return $this->db->insert_id();
    }

    public function deleteRowsWithStatusNotOne()
    {
        // Delete all rows where status is not 1
        $this->db->where('status !=', 'AUTHENTICATED');
        $this->db->or_where('status', NULL);
        $this->db->delete('devices');

        // Check if any rows were affected
        $affected_rows = $this->db->affected_rows();

        // Get the last executed query for debugging
        $last_query = $this->db->last_query();

        return array(
            'affected_rows' => $affected_rows,
            'last_query' => $last_query
        );
    }

    public function update($data, $id)
    {


        // Atualiza os dados do dispositivo no banco de dados
        $this->db->where('id', $id);
        $this->db->update('devices', $data);

        // Retorna verdadeiro se a atualização for bem-sucedida
        return $this->db->affected_rows() > 0;
    }


    public function getSession()
    {
        // Verifica se há alguma linha na tabela devices com status AUTHENTICATED
        $this->db->where('status', 'AUTHENTICATED');
        $query = $this->db->get('devices');

        // Retorna verdadeiro se houver pelo menos uma linha com o status AUTHENTICATED
        return $query->num_rows() > 0;
    }

    public function getSessionId()
    {
        // Verifica se há alguma linha na tabela devices com status AUTHENTICATED
        $this->db->where('status', 'AUTHENTICATED');
        $query = $this->db->get('devices');

        // Retorna verdadeiro se houver pelo menos uma linha com o status AUTHENTICATED
        return $query->result_array();
    }
}
