<?php
namespace App\GraphQL\Mutation\Account;
use App\Models\Account;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class TrashAccountMutation extends Mutation {
	protected $attributes = [
		'name' => 'TrashAccount',
	];
	public function type(): Type {
		return GraphQL::type('account');
	}
	public function args(): array{
		return [
			'id' => [
				'name' => 'id',
				'type' => Type::string(),
				'rules' => ['required'],
			],
		];
	}
	public function validationErrorMessages(array $args = []): array
	{
		return [
			'id.required' => 'Id required',
		];
	}
	public function resolve($root, $args) {

		$account = Account::find($args['id']);

		if (!$account->delete()) {
			return null;
		}
		return $account;
	}
}
