<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WardAdjacency extends Model
{
    public $timestamps = false;

    protected $table = 'ward_adjacencies';

    protected $fillable = ['ward_id', 'adjacent_ward_id'];

    public static function getAdjacentWards(string $wardId): array
    {
        $adjacent = static::where('ward_id', $wardId)
            ->pluck('adjacent_ward_id')
            ->toArray();

        return array_values(array_unique(array_merge([$wardId], $adjacent)));
    }

    // TODO: Filter by county once county column is added to ward_adjacencies
    public static function getAdjacentWardMap(string $county): array
    {
        return DB::table('ward_adjacencies')
            ->get()
            ->groupBy('ward_id')
            ->map(fn ($rows) => $rows->pluck('adjacent_ward_id')->toArray())
            ->toArray();
    }
}
