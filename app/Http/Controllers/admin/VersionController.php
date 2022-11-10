<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Version;
use Illuminate\Support\Facades\Validator;

class VersionController extends Controller
{

    public function list()
    {
        return view('admin.version.list');
    }


    public function getallversions(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'version',
            // 2 => 'created_at',
        );

        $filters = [
            'search' => $request->input('search.value'),
            'start'   => $request->input('start'),
            'limit'   => $request->input('length'),
            'orderby' => $columns[$request->input('order.0.column')],
            'dir'     => $request->input('order.0.dir')
        ];
        $totalData     = Version::GetCount();
        $alldata       = Version::GetData($filters);
        $totalFiltered = Version::GetCount($filters['search']);

        $responsedata = array();
        if (!empty($alldata)) {
            $i = $filters['start'] + 1;
            foreach ($alldata as $row) {

                if (strlen($row->description) > 25)
                    $row->description = substr($row->description, 0, 25) . '...';

                //$delete = adminurl('admin/whatsnew/delete', $row->id);
                $actionhtml  =  '
        <a href="' . route("version.show", $row->id) . '" title="View" class="btn btn-primary font-15 view" > <i class="fa fa-pencil"></i></a>';
        $actionhtml  .='<a href="' . route("version.edit", $row->id) . '" title="Edit" class="btn btn-primary font-15 view" > <i class="fa fa-pencil"></i></a>
        $actionhtml  .= <a href="' . route('version.delete' , $row->id) . '" title="View" class="btn btn-primary font-15 view" > <i class="fa fa-pencil"></i></a>';
                $nestedData['id']           =  $row->id;
                $nestedData['version']         =  $row->version;
                // $nestedData['created_at']   =  date("M j, y",strtotime($row->created_at));
                $nestedData['description'] =  $row->description;
                $nestedData['options']      =  $actionhtml;
                $responsedata[]             =  $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $responsedata,
        );
        echo json_encode($json_data);
    }

    public function add(Request $request)
    {
        $version = new Version();
        return view('admin.version.form', compact('version'));
    }

    public function edit(Request $request, $id)
    {
        $version = Version::find($id);
        if ($version) {
            return view('admin.version.form', compact('version'));
        }
    }

    public function show(Request $request, $id)
    {
        $version = Version::find($id);
        if ($version) {
            return view('admin.version.view', compact('version'));
        }
    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(["status" => "error", "message" => "Something went wrong"]);
        }

        if ($request->id) {
            $version = Version::find($request->id);
        } else {
            $version = new Version();
        }

        $version->version = $request->name;
        $version->description = $request->description;
        $version->save();

        $msg = 'Version created successfully';
        if ($request->id) {
            $msg = 'Version updated successfully';
        }
        return redirect('/admin/versions')->with('message', $msg);
    }

    public function delete(Request $request, $id)
    {
        $version = Version::find($id);
        if ($version) {
            $version->delete();
        }
        return redirect('/admin/versions')->with('message', 'Version deleted successfully');
    }




    /*
   public function delete(Request $request) {

        $validator=Validator::make($request->all(), [
                'id'=>'required',
            ]);
        if ($validator->fails()){
            return response()->json(["status"=>"error","message"=>"Something went wrong"]);
        }
        $table = Whatsnew::find($request->id);
        if($table){
        $table->delete();

        $this->reloadCache();
            $earray = ["hashid"=>$table->hashid];
            event(new \App\Events\GeneralEvents(["op"=>"delete","data"=>$earray]));
            return response()->json(["status"=>"success"]);
        }
    }
    */
}
