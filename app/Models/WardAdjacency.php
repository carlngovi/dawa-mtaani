<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WardAdjacency extends Model
{
    public $timestamps = false;

    protected $table = 'ward_adjacencies';

    protected $fillable = ['ward_id', 'adjacent_ward_id', 'county'];

    public static function getAdjacentWards(string $wardId): array
    {
        $adjacent = static::where('ward_id', $wardId)
            ->pluck('adjacent_ward_id')
            ->toArray();

        return array_values(array_unique(array_merge([$wardId], $adjacent)));
    }

    public static function getAdjacentWardMap(string $county = ''): array
    {
        $query = DB::table('ward_adjacencies');

        if ($county) {
            $query->where('county', $county);
        }

        return $query->get()
            ->groupBy('ward_id')
            ->map(fn ($rows) => $rows->pluck('adjacent_ward_id')->toArray())
            ->toArray();
    }
}
