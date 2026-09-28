<?php

namespace App\Models;

use Database\Factories\FavouriteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['city', 'country'])]
class Favourite extends Model
{
    /** @use HasFactory<FavouriteFactory> */
    use HasFactory;
}
