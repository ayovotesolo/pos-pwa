<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

class Setting extends Model {

	use Notifiable, HasJsonRelationships;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */

	protected $dates = ['deleted_at'];
	protected $primaryKey = 'id'; // or null\
	protected $casts = [
		'properties' => 'array',
	];

	/**
	 * @var
	 */
	protected $allowedFilters = ['name'];
	protected $allowedSorts = ['name'];

	public $incrementing = true;

	public function __construct(array $attributes = []) {
		$id = Auth::id();
		$this->table = "user_{$id}_" . $this->getTable();
		parent::__construct($attributes);
	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'description', 'type', 'data', 'properties', 'user_id',
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [

	];

	public function user() {
		return $this->belongsTo('App\Models\User', 'user_id');
	}

	public function store() {
		return $this->belongsTo('App\Models\Setting', 'properties->store_id')->where('type', 'store');
	}

	/**
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function scopeCategory(Builder $query) {
		return $query->where('type', 'category')->get();
	}

	/**
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function scopeTax(Builder $query) {
		return $query->select('id', 'name')->where('type', 'tax')->get();
	}

	/**
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function scopeCommission(Builder $query) {
		return $query->where('type', 'commission')->get();
	}

	/**
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function scopeStore(Builder $query) {
		return $query->where('type', 'store')->get();
	}

}
