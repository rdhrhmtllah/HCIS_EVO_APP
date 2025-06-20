<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    //
    public function indexLembur(){
        return inertia('LemburFormModern');
    }

    public function addLembur(Request $request){
        $entries = $request->input('data');

        foreach ($entries as $entry) {
            $validator = Validator::make($entry, [
                'alasan' => 'required|string',
                'start' => 'required|date_format:H:i',
                'end' => 'required|date_format:H:i|after:start',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Lembur::create([
            //     'alasan' => $entry['alasan'],
            //     'start' => $entry['start'],
            //     'end' => $entry['end'],
            //     'date' => $entry['date'],
            // ]);
        }

        return response()->json($entries);
    }
}
