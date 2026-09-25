<?php

namespace App\Models;

use Databse\Database\Factories\SearchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['city', 'country', 'temperature', 'condition'])]
class Search extends Model
{
    /** @use HasFactory<\Database\Factories\SearchFactory> */
    use HasFactory;

    /**
        * @return array<string, string>
    */
    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:1',
        ];
    }
}
