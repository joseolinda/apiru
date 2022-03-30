<?php

namespace App\Http\Controllers;

use App\ReportWaste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportWasteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->date) {
            return response()->json([
                'message' => 'A data do relatório a ser listado não foi informada.'
            ], 202);
        }

        $date = $request->date;

        $report = ReportWaste::where('startDate', '<=', $date)->where('endDate', '>=', $date)->first();

        if (!$report) {
            return response()->json([
                'message' => 'Relatório não encontrado para data',
            ], 202);
        }

        return response()->json($report);
    }

    /**
     * Update the specified resource, or store a newly created resource, in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'date' => 'required|string',
            'content' => 'required|string'
        ]);

        $date = $request->date;
        $content = $request->content;

        $report = ReportWaste::where('startDate', '<=', $date)->where('endDate', '>=', $date)->first();

        if (!$report) {
            $report = new ReportWaste();
        }

        $date = new \DateTime($date);
        $date->modify('first day of this month');
        
        $startDate = $date->format('Y-m-d');

        $date->modify('last day of this month');
        
        $endDate = $date->format('Y-m-d');
        
        if ($startDate != $report->startDate || $endDate != $report->endDate) {
            $report->startDate = $startDate;
            $report->endDate = $endDate;
        }

        $report->content = $content;

        $report->save();

        return response()->json($report, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ReportWaste  $reportWaste
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReportWaste $reportWaste)
    {
        //
    }
}
