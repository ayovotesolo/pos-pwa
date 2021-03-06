<?php
namespace App\GraphQL\Query\Reports;

use App\Helpers\TenantTable;
use App\Models\Item;
use Closure;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class ReportShiftSummary {

	public function run($args, Closure $getSelectFields) {
		$documents = TenantTable::parse('documents');
		$settings = TenantTable::parse('settings');
		$items = TenantTable::parse('items');

		$where = function ($query) use ($args, $documents, $items, $settings) {
			if (isset($args['from']) && isset($args['to'])) {
				if ($args['from'] !== "" && $args['to'] !== "") {
					$from = $args['from'] . ' 00:00:00';
					$to = $args['to'] . ' 23:59:59';
					$query->whereBetween($documents . '.date', [$from, $to]);
				}
			}
			if (isset($args['store'])) {
				$query->where($items . '.store_id', $args['store']);
			}
			if (isset($args['terminal'])) {
				$query->where($items . '.terminal_id', $args['terminal']);
			}
			if (isset($args['user'])) {
				$query->where($items . '.user_id', $args['user']);
			}

		};

		$item = Item::join($documents, $documents . '.id', '=', $items . '.trxn_id')
			->join('users', 'users.id', '=', $items . '.user_id')
			->where($items . '.type', 'item')
			->where($where);

		$query = Item::join($documents . ' as documents', 'documents.id', '=', $items . '.trxn_id')
			->join('users', 'users.id', '=', $items . '.user_id')
			->where($items . '.type', 'item')
			->where($where)
			->selectRaw('MIN(documents.date) as from_date, MAX(documents.date) as close_shift, users.name as item_name, documents.shift_id  as shift_id, sum(total_amount) as total_amount')
			->groupBy('users.name');

		if (isset($args['sort']) && isset($args['desc'])) {

			if (isset($args['desc']) && $args['desc'] === 'desc') {
				$query->orderBy($args['sort'], 'desc');
			} else {
				if ($args['sort'] !== '') {
					$query->orderBy($args['sort']);
				} else {
					$query->orderBy('users.name', 'desc');
				}

			}

		}

		$results = $query->paginate($args['limit'], ['*'], 'page', $args['page']);

		return ['summary' => ['count' => $item->count('trxn_id'), 'sum' => $item->sum('total_amount')], 'data' => $results];

	}
}
