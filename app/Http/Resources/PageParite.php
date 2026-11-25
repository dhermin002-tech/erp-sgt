<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

final class PageParite
{
    public static function reponse(LengthAwarePaginator $page, ?callable $ligne = null, array $meta = [])
    {
        return response()->json([
            'data' => $ligne ? $page->getCollection()->map($ligne)->values() : $page->items(),
            'meta' => [
                'page' => $page->currentPage(),
                'par_page' => $page->perPage(),
                'derniere_page' => $page->lastPage(),
                'total' => $page->total(),
            ] + $meta,
        ]);
    }

    public static function ressources(LengthAwarePaginator $page, string $classe, Request $request, array $meta = [])
    {
        return self::reponse($page, fn ($modele) => $classe::make($modele)->resolve($request), $meta);
    }
}
