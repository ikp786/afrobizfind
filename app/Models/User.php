<?php

namespace App\Models;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;


class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use Billable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the calendars for this model.
     */
    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    /**
     * Get the Notifications for this model.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * The users that belong to the role.
     */
    public function favoriteCompanies()
    {
        return $this->belongsToMany(Company::class, 'favourites')->with('category');
    }

    public function stripePaymentMethods()
    {
        return $this->hasMany(StripePaymentMethods::class);
    }

    /* public function associatedTaskTemplatesForms_(){
        return $this->belongsToMany('App\Models\TaskTemplateForm', 'App\Models\TaskTemplatesToTaskTemplateForms','task_template_id','task_template_form_id' );      
    }*/
}
