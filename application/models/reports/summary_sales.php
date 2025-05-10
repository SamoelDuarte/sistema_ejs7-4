<?php
require_once("report.php");
class Summary_sales extends Report
{
	function __construct()
	{
		parent::__construct();
	}

	public function getDataColumns()
	{
		return array(
			$this->lang->line('reports_date'),
			$this->lang->line('reports_subtotal'),
			$this->lang->line('reports_total'),
			'Taxas', // NOVO
			$this->lang->line('reports_tax'),
			$this->lang->line('reports_profit')
		);
	}

	public function getData(array $inputs)
	{

		// Primeiro, pegamos as taxas do app_config
		$config = $this->Appconfig->get_all()->result_array();
		$config_assoc = array_column($config, 'value', 'key');

		$taxa_pix = floatval($config_assoc['taxa_pix_qrcode'] ?? 0);
		$taxa_credito = floatval($config_assoc['taxa_credito'] ?? 0);
		$taxa_debito = floatval($config_assoc['taxa_debito'] ?? 0);

		// Agora buscamos os dados por venda
		$this->db->select('sale_id, sale_date, SUM(subtotal) as subtotal, SUM(total) as total, SUM(tax) as tax, SUM(profit) as profit');
		$this->db->from('sales_items_temp');
		if ($inputs['sale_type'] == 'sales') {
			$this->db->where('quantity_purchased > 0');
		} elseif ($inputs['sale_type'] == 'returns') {
			$this->db->where('quantity_purchased < 0');
		}

		$this->db->group_by('sale_date, sale_id');
		$this->db->having('sale_date BETWEEN "' . $inputs['start_date'] . '" and "' . $inputs['end_date'] . '"');
		$this->db->order_by('sale_date');
		$sales = $this->db->get()->result_array();

		// Buscamos os tipos de pagamento por sale_id
		$this->db->select('sale_id, payment_type');
		$this->db->from('sales');
		$pagamentos = $this->db->get()->result_array();
		$pagamentos_assoc = array_column($pagamentos, 'payment_type', 'sale_id');

		// Agora inserimos a taxa calculada conforme o tipo de pagamento
		foreach ($sales as &$sale) {
			$sale_id = $sale['sale_id'];
			$tipo = strtolower($pagamentos_assoc[$sale_id] ?? '');

			switch (true) {
				case strpos($tipo, 'pix') !== false:
					$taxa = $sale['total'] * ($taxa_pix / 100);
					break;
				case strpos($tipo, 'credito') !== false || strpos($tipo, 'crédito') !== false:
					$taxa = $sale['total'] * ($taxa_credito / 100);
					break;
				case strpos($tipo, 'debito') !== false || strpos($tipo, 'débito') !== false:
					$taxa = $sale['total'] * ($taxa_debito / 100);
					break;
				default:
					$taxa = 0;
					break;
			}


			$sale['taxa_calc'] = $taxa;
		}

		return $sales;
	}


	public function getSummaryData(array $inputs)
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit');
		$this->db->from('sales_items_temp');
		$this->db->where('sale_date BETWEEN "' . $inputs['start_date'] . '" and "' . $inputs['end_date'] . '"');
		if ($inputs['sale_type'] == 'sales') {
			$this->db->where('quantity_purchased > 0');
		} elseif ($inputs['sale_type'] == 'returns') {
			$this->db->where('quantity_purchased < 0');
		}

		$summary = $this->db->get()->row_array();

		// Calcular total de taxas aqui
		$this->db->select('sum(total) as total, payment_type');
		$this->db->from('sales_items_temp');
		$this->db->where('sale_date BETWEEN "' . $inputs['start_date'] . '" and "' . $inputs['end_date'] . '"');
		if ($inputs['sale_type'] == 'sales') {
			$this->db->where('quantity_purchased > 0');
		} elseif ($inputs['sale_type'] == 'returns') {
			$this->db->where('quantity_purchased < 0');
		}
		$this->db->group_by('payment_type');
		$formas = $this->db->get()->result_array();

		// Primeiro, pegamos as taxas do app_config
		$config = $this->Appconfig->get_all()->result_array();
		$config_assoc = array_column($config, 'value', 'key');
		$taxa_pix = floatval($config_assoc['taxa_pix_qrcode'] ?? 0);
		$taxa_credito = floatval($config_assoc['taxa_credito'] ?? 0);
		$taxa_debito = floatval($config_assoc['taxa_debito'] ?? 0);

		$total_taxas = 0;

		foreach ($formas as $forma) {
			$tipo = strtolower($forma['payment_type']);
			if (strpos($tipo, 'pix') !== false) {
				$total_taxas += $forma['total'] * ($taxa_pix / 100);
			} elseif (strpos($tipo, 'credito') !== false || strpos($tipo, 'crédito') !== false) {
				$total_taxas += $forma['total'] * ($taxa_credito / 100);
			} elseif (strpos($tipo, 'debito') !== false || strpos($tipo, 'débito') !== false) {
				$total_taxas += $forma['total'] * ($taxa_debito / 100);
			}
		}

		$summary['total_taxas'] = $total_taxas;

		return $summary;
	}
}
