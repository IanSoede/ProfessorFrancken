<?php

declare(strict_types=1);

namespace Francken\Association\Borrelcie\Http;

use Illuminate\View\View;

final class BorrelcieController
{
    public function index() : View
    {
        return view('association.borrelcie.index')->with([
            'breadcrumbs' => [
                ['url' => action([self::class, 'index']), 'text' => 'Borrelcie'],
                ['url' => action([self::class, 'index']), 'text' => 'Activate account'],
            ],
        ]);
    }
}
