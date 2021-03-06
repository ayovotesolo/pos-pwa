<?php
namespace App\GraphQL\Query;

use App\Models\Setting;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class SettingsQuery extends Query {
	protected $attributes = [
		'name' => 'settings',
		'description' => 'A query of settings',
	];

	public function type(): Type {
		return GraphQL::paginate('settings');
	}
	// arguments to filter query
	public function args(): array{
		return [
			'id' => [
				'name' => 'id',
				'type' => Type::int(),
			],
			'name' => [
				'name' => 'name',
				'type' => Type::string(),
			],
			'description' => [
				'name' => 'description',
				'type' => Type::string(),
			],
			'type' => [
				'name' => 'type',
				'type' => Type::string(),
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
			'search' => [
				'name' => 'search',
				'type' => Type::string(),
			],

		];
	}
	public function resolve($root, $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields) {
		$where = function ($query) use ($args) {
			if (isset($args['type'])) {
				$query->where('type', $args['type']);
			}
			if (isset($args['id'])) {
				$query->where('id', $args['id']);
			}
			if (isset($args['search'])) {
				$query->where(function ($query) use ($args) {
					$query->orWhere('name', 'LIKE', '%' . $args['search'] . '%');
				});

			}
		};

		$fields = $getSelectFields();
		$q = Setting::with(array_keys($fields->getRelations()))
			->where($where)
			->select($fields->getSelect());

		if ($args['limit'] > 0) {
			$results = $q->paginate($args['limit'], ['*'], 'page', $args['page']);
		} else {
			$count = $q->count();
			$results = $q->paginate($count, ['*'], 'page', 1);
		}
		return $results;
	}
}