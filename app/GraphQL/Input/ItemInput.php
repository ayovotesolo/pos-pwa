<?php

namespace App\GraphQL\Input;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\InputType;

class ItemInput extends InputType {
	protected $attributes = [
		'name' => 'ItemInput',
		'description' => 'A review with a comment and a score (0 to 5)',
	];

	public function fields(): array
	{
		return [
			'line' => [
				'type' => Type::nonNull(Type::int()),
				'name' => 'line',
			],
			'type' => [
				'type' => Type::string(),
				'name' => 'type',
			],

			'item_id' => [
				'type' => Type::nonNull(Type::int()),
				'name' => 'item_id',
			],

			'discount' => [
				'type' => Type::string(),
				'name' => 'discount',
			],
			'discount_amount' => [
				'type' => Type::float(),
				'name' => 'discount_amount',
			],
			'tax_id' => [
				'type' => Type::nonNull(Type::int()),
				'name' => 'tax_id',
			],
			'tax_amount' => [
				'type' => Type::float(),
				'name' => 'tax_amount',
			],
			'price' => [
				'type' => Type::float(),
				'name' => 'price',
			],
			'qty' => [
				'type' => Type::float(),
				'name' => 'qty',
			],
			'refund_qty' => [
				'type' => Type::float(),
				'name' => 'refund_qty',
			],
			'refund_amount' => [
				'type' => Type::float(),
				'name' => 'refund_amount',
			],
			'total_amount' => [
				'type' => Type::float(),
				'name' => 'total_amount',
			],
			'note' => [
				'type' => Type::string(),
				'name' => 'note',
			],
			'properties' => [
				'type' => Type::string(),
				'name' => 'properties',
			],
			'user_id' => [
				'type' => Type::nonNull(Type::int()),
				'name' => 'user_id',
			],
			'shift_id' => [
				'type' => Type::int(),
				'name' => 'shift_id',
			],
			'terminal_id' => [
				'type' => Type::nonNull(Type::int()),
				'name' => 'terminal_id',
			],
			'store_id' => [
				'type' => Type::nonNull(Type::int()),
				'name' => 'store_id',
			],
		];
	}
}
