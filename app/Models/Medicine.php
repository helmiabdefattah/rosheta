<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'medicines';

    protected $fillable = [
        'api_id',
        'name',
        'arabic',
        'shortage',
        'date_updated',
        'imported',
        'percentage',
        'pharmacology',
        'route',
        'in_eye',
        'units',
        'small_unit',
        'sold_times',
        'dosage_form',
        'barcode',
        'barcode2',
        'qrcode',
        'anyupdatedate',
        'new_added_date',
        'updtime',
        'fame',
        'cosmo',
        'dose',
        'repeated',
        'price',
        'oldprice',
        'newprice',
        'active_ingredient',
        'similars_id',
        'img',
        'description',
        'uses',
        'company',
        'reported',
        'reporter_name',
        'visits',
        'sim_visits',
        'ind_visits',
        'composition_visits',
        'order_visits',
        'app_visits',
        'shares',
        'last_visited',
        'lastupdatingadmin',
        'awhat_visits',
        'report_msg',
        'found_pharmacies_ids',
        'availability',
        'noimgid',
        'raw_data',
        'search_term',
        'batch_number',
    ];

    protected $casts = [
        'sold_times' => 'integer',
        'visits' => 'integer',
        'sim_visits' => 'integer',
        'ind_visits' => 'integer',
        'composition_visits' => 'integer',
        'order_visits' => 'integer',
        'app_visits' => 'integer',
        'shares' => 'integer',
        'availability' => 'integer',
        'price' => 'decimal:2',
        'oldprice' => 'decimal:2',
        'newprice' => 'decimal:2',
        'raw_data' => 'array',
        'in_eye' => 'boolean',
        'fame' => 'boolean',
        'cosmo' => 'boolean',
        'repeated' => 'boolean',
        'reported' => 'boolean',
    ];
}


