<?php
namespace App\GraphQL\Mutation;
use App\Models\Document;
use App\Models\Product;
use DB;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class NewReceiptMutation extends Mutation {
	protected $attributes = [
		'name' => 'NewReceipt',
	];
	public function type(): Type {
		return GraphQL::type('receipt');
	}
	public function args(): array{
		return [
			'reference' => [
				'name' => 'reference',
				'type' => Type::nonNull(Type::string()),
			],
			'terminal_id' => [
				'name' => 'terminal_id',
				'type' => Type::nonNull(Type::int()),
			],
			'store_id' => [
				'name' => 'store_id',
				'type' => Type::nonNull(Type::int()),
			],
			'shift_id' => [
				'name' => 'shift_id',
				'type' => Type::int(),
			],
			'account_id' => [
				'name' => 'account_id',
				'type' => Type::string(),
			],
			'transact_by' => [
				'name' => 'transact_by',
				'type' => Type::nonNull(Type::int()),
			],
			'type' => [
				'name' => 'type',
				'type' => Type::nonNull(Type::string()),
			],
			'status' => [
				'name' => 'status',
				'type' => Type::nonNull(Type::string()),
			],
			'date' => [
				'name' => 'date',
				'type' => Type::nonNull(Type::string()),
			],
			'discount' => [
				'name' => 'discount',
				'type' => Type::nonNull(Type::string()),
			],
			'discount_amount' => [
				'name' => 'discount_amount',
				'type' => Type::float(),
			],
			'service_charge' => [
				'name' => 'service_charge',
				'type' => Type::float(),
			],
			'tax_amount' => [
				'name' => 'tax_amount',
				'type' => Type::float(),
			],
			'charge' => [
				'name' => 'charge',
				'type' => Type::float(),
			],
			'received' => [
				'name' => 'received',
				'type' => Type::float(),
			],
			'rounding' => [
				'name' => 'rounding',
				'type' => Type::float(),
			],
			'change' => [
				'name' => 'change',
				'type' => Type::float(),
			],
			'refund' => [
				'name' => 'refund',
				'type' => Type::float(),
			],
			'note' => [
				'name' => 'note',
				'type' => Type::string(),
			],
			'items' => [
				'name' => 'items',
				'type' => Type::listOf(GraphQL::type('ItemInput')),
			],
			'payments' => [
				'name' => 'payments',
				'type' => Type::listOf(GraphQL::type('ItemInput')),
			],
			'commissions' => [
				'name' => 'commissions',
				'type' => Type::listOf(GraphQL::type('ItemInput')),
			],
			'properties' => [
				'name' => 'properties',
				'type' => Type::string(),
			],

		];
	}
	public function resolve($root, $args) {

		$success = false;
		$error = null;

		$commissions = [];

		if ($args['type'] === 'receipt') {

			foreach ($args['items'] as $item) {
				$amount = 0.00;
				$castedItem = Product::with(['commission'])->find($item['item_id']);
				$commission = $castedItem->commission;

				$amount = $this->calcCommission($item, $commission);

				$item['properties'] = json_decode($item['properties']);

				$prop = $item['properties'];

				if (!empty($prop) && property_exists($prop, 'shareWith')) {
					if ($prop->shareWith !== 0) {
						$amount = $amount / 2;
						$commissions[] = $this->row($item['line'], $item['item_id'], $args['store_id'], $args['terminal_id'], $args['shift_id'], $commission, $prop->shareWith, $amount);
					}

				}

				if (!empty($prop) && property_exists($prop, 'composites')) {

					foreach ($prop->composites as $key => $composed) {
						$service_item = Product::with(['commission'])->find($composed->item_id);
						$emp = $composed->perform_by;

						if (!empty($service_item) && !empty($emp)) {

							$service_amount = $this->calcCommission($service_item, $service_item->commission);

							$commissions[] = $this->row($item['line'], $service_item->id, $args['store_id'], $args['terminal_id'], $args['shift_id'], $service_item->commission, $emp, $service_amount);

							$amount -= $service_amount;
						}

					}
				}

				$commissions[] = $this->row($item['line'], $item['item_id'], $args['store_id'], $args['terminal_id'], $args['shift_id'], $commission, $item['user_id'], $amount);

			}

		}
		DB::beginTransaction();
		try {

			$document = Document::where('reference', $args['reference'])->first();

			if ($document) {
				//permenantly remove all ccommission

				$document->commissions()->where('type', 'commission')->forceDelete();
				$document->items()->where('type', 'item')->forceDelete();
				$document->update($args);
				$document->items()->createMany($args['items']);

			} else {
				$document = Document::create($args);
				$document->items()->createMany($args['items']);

			}

			$document->commissions()->createMany($commissions);

			// $document->items()->save($args->items);
			// $document->payments()->save($args->payments);
			// $document->commissions()->save($args->commissions);

			DB::commit();
			$success = true;
		} catch (\Exception $e) {

			$success = false;
			$error = $e;
			DB::rollback();
		}

		if (!$success) {
			return $error;
		}
		return $document;
	}

	protected function row($line, $item_id, $store_id, $terminal_id, $shift_id, $commission, $user, $amount) {

		return [
			'line' => $line,
			'type' => 'commission',
			'item_id' => $item_id,
			'terminal_id' => $terminal_id,
			'store_id' => $store_id,
			'shift_id' => $shift_id,
			'discount' => '{}',
			'discount_amount' => 0.00,
			'tax_id' => 1,
			'qty' => 1,
			'refund_qty' => 0.00,
			'refund_amount' => 0.00,
			'tax_amount' => 0.00,
			'user_id' => $user,
			'total_amount' => $amount,
			'note' => '',
		];
	}

	protected function calcCommission($item, $commission) {

		if ((int) $commission['properties']['type'] === 1) {
			return (float) $commission['properties']['rate'];
		} else {
			return $item['total_amount'] * ((float) $commission['properties']['rate']) / 100;
		}
	}
}
