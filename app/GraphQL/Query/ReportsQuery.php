<?php
namespace App\GraphQL\Query;

use App\Helpers\TenantTable;
use App\Models\Document;
use App\Models\Item;
use Closure;
use DB;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class ReportsQuery extends Query {
	protected $attributes = [
		'name' => 'SalesReport',
		'description' => 'A query of sales',
	];

	public function type(): Type {

		return GraphQL::type('report');
	}
	// arguments to filter query
	public function args(): array{
		return [
			'name' => [
				'type' => Type::string(),
			],
			'from' => [
				'type' => Type::string(),
			],
			'to' => [
				'type' => Type::string(),
			],
			'store' => [
				'type' => Type::int(),
			],
			'terminal' => [
				'type' => Type::int(),
			],
			'user' => [
				'type' => Type::int(),
			],
			'limit' => [
				'type' => Type::int(),
			],
			'page' => [
				'type' => Type::int(),
			],
			'sort' => [
				'type' => Type::string(),
			],
			'desc' => [
				'type' => Type::string(),
			],

		];
	}
	public function resolve($root, $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields) {

		$fields = $getSelectFields();
		$fieldList = $fields->getSelect();
		// unset($fieldList[array_search('sum', $fieldList)]);
		// unset($fieldList[array_search('count', $fieldList)]);

		if (isset($args['name'])) {
			$func = $args['name'];
			return $this->$func($args, $getSelectFields);
		}

	}

	public function payment_summary($args) {

		$documents = TenantTable::parse('documents');
		$items = TenantTable::parse('items');

		$where = function ($query) use ($args, $documents) {
			if (isset($args['from']) && isset($args['to'])) {
				if ($args['from'] !== "" && $args['to'] !== "") {
					$from = $args['from'] . ' 00:00:00';
					$to = $args['to'] . ' 23:59:59';
					$query->whereBetween($documents . '.date', [$from, $to]);
				}
			}
		};

		$results = Item::join($documents, $documents . '.id', '=', $items . '.trxn_id')
			->where($items . '.type', 'payment')
			->where($where)
			->selectRaw('CASE WHEN item_id = 1 THEN "Cash"
                                      WHEN item_id = 2 THEN "Card"
                                      WHEN item_id = 3 THEN "Transfer"
                                      WHEN item_id = 4 THEN "Boost"
                                      ELSE "undefined" END as item_name, sum(total_amount) as total_amount, count(' . $items . '.id) as count, sum(refund_amount) as refund_amount, sum(total_amount) - sum(refund_amount) as net')
			->groupBy('item_id')
			->paginate($args['limit'], ['*'], 'page', $args['page']);

		return $results;
	}

	public function commission_daily_summary($args) {

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
			->where($items . '.type', 'commission')
			->where($where);

		$results = Item::join($documents, $documents . '.id', '=', $items . '.trxn_id')
			->join('users', 'users.id', '=', $items . '.user_id')
			->where($items . '.type', 'commission')
			->where($where)
			->selectRaw('DATE(' . $documents . '.date) as item_date, users.name as item_name, sum(total_amount) as total_amount')
			->groupBy(DB::raw('DATE(' . $documents . '.date)'))
			->groupBy('users.name')
			->paginate($args['limit'], ['*'], 'page', $args['page']);

		return ['summary' => ['count' => $item->count('trxn_id'), 'sum' => $item->sum('total_amount')], 'data' => $results];

	}

	public function receipts($args, Closure $getSelectFields) {

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
				$query->where($documents . '.store_id', $args['store']);
			}
			if (isset($args['terminal'])) {
				$query->where($documents . '.terminal_id', $args['terminal']);
			}
			if (isset($args['user'])) {
				$query->where($documents . '.transact_by', $args['user']);
			}

		};
		$fields = $getSelectFields();

		$results = Document::with(array_keys($fields->getRelations()))
			->withTrashed()
			->where('type', 'receipt')
			->where($where)
			->orderBy('date', 'desc')
			->paginate($args['limit'], ['*'], 'page', $args['page']);

		return ['summary' => ['count' => 0, 'sum' => 0], 'data' => $results];

	}

}