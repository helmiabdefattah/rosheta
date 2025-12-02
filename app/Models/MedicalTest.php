<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalTest extends Model
{
    protected $table = 'medical_tests';

    protected $fillable = [
        'test_name_en',
        'test_name_ar',
        'test_description',
        'conditions',
    ];

    /**
     * Relation: A medical test appears in many request lines
     */
    public function requestLines()
    {
        return $this->hasMany(MedicalTestRequestLine::class, 'medical_test_id');
    }

    /**
     * Relation: A medical test appears in many offers from many labs
     */
    public function offers()
    {
        return $this->hasMany(MedicalTestOffer::class, 'medical_test_id');
    }

    /**
     * (Optional) Relation: A medical test has many offer lines through offers
     */
//    public function offerLines()
//    {
//        return $this->hasManyThrough(
//            MedicalTestOfferLine::class,
//            MedicalTestOffer::class,
//            'medical_test_id', // Foreign key on MedicalTestOffer
//            'medical_test_offer_id', // Foreign key on MedicalTestOfferLine
//            'id', // Local key on MedicalTest
//            'id' // Local key on MedicalTestOffer
//        );
//    }
}
