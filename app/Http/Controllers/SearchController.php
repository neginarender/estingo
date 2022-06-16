<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Search;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;
use App\User;
use Session;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $search = Search::where('query', $request->q)->first();
        if($search != null){
            $search->count = $search->count + 1;
            $search->save();
        }
        else{
            $search = new Search;
            $search->query = $request->q;
            $search->save();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function downloadCSV(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Sr No');
        $sheet->setCellValue('B1', 'Keyword');
        $sheet->setCellValue('C1', 'Customer IP');
        $sheet->setCellValue('D1', 'Customer Name');
        $date_from = date('Y-m-d',strtotime($request->date_from));
        $date_to = date('Y-m-d',strtotime($request->date_to));
        $searches = DB::table('search_history')->whereBetween(DB::raw('date(created_at)'),array($date_from,$date_to))->get();
        foreach($searches as $key => $search)
        {
        $customer_name = (!empty($search->customer_id) &&!is_null(User::find($search->customer_id))) ? User::find($search->customer_id)->name : "";
        $sheet->setCellValue('A'.($key+2), $key+1);
        $sheet->setCellValue('B'.($key+2), $search->search);
        $sheet->setCellValue('C'.($key+2), $search->customer_ip);
        $sheet->setCellValue('D'.($key+2), $customer_name);
        }
        $filename = "search_history.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);
        
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
        
        
    }

    public function search_list()
    {
        $searches = DB::table('search_history')->orderBy('id','desc')->paginate(20);
            return view('search_history.index', compact('searches')); 
    }

    public function delete_search(Request $request)
    {
       $delete = DB::table('search_history')->whereIn('id',$request->check)->delete();
        if($delete)
        {
            flash("Record deleted successfully")->success();
            return back();
        }
        flash("Record Not deleted")->error();
         return back();
    }
}
