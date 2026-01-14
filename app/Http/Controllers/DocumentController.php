<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * 仕様書PDFをダウンロード
     */
    public function downloadSpecification()
    {
        $filePath = public_path('documents/Fusic_finalproduct.pdf');

        if (!file_exists($filePath)) {
            abort(404, '仕様書が見つかりません');
        }

        return response()->download($filePath, 'Fusic_finalproduct.pdf');
    }
}