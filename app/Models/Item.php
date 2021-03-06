<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Item extends Model {

	use Notifiable, SoftDeletes;
	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];
	protected $primaryKey = 'id'; // or null
	public $incrementing = true;
	protected $casts = [
		'properties' => 'array',
		'discount' => 'array',
	];
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'line', 'type', 'trxn_id', 'item_id', 'discount', 'discount_amount', 'price', 'qty', 'refund_qty', 'refund_amount', 'tax_id', 'tax_amount', 'tax_amount', 'total_amount', 'user_id', 'note', 'properties', 'user_id', 'shift_id', 'terminal_id', 'store_id',
	];

	protected $allowedFilters = ['line'];
	protected $allowedSorts = ['line'];

	public function __construct(array $attributes = []) {
		$id = Auth::id();
		$this->table = "user_{$id}_" . $this->getTable();
		parent::__construct($attributes);
	}

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [

	];

	public function document() {
		return $this->belongsTo('App\Models\Document', 'trxn_id');
	}

	public function item() {
		return $this->belongsTo('App\Models\Product', 'item_id');

	}
	public function receives() {
		return $this->hasMany('App\Models\Item', 'line')->where('type', 'ritem');

	}
	public function product() {
		return $this->belongsTo('App\Models\Product', 'item_id');
	}

	public function commission() {
		return $this->belongsTo('App\Models\Setting', 'item_id')->where('type', 'commission');
	}
	public function tax() {
		return $this->belongsTo('App\Models\Setting', 'tax_id')->where('type', 'tax');
	}
	public function saleBy() {
		return $this->belongsTo('App\Models\User', 'user_id');
	}
	public function user() {
		return $this->belongsTo('App\Models\User', 'user_id');
	}
	public function store() {
		return $this->belongsTo('App\Models\Setting', 'store_id');
	}
}
