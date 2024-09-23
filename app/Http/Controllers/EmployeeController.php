<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Hobby;
use App\Models\HobbyMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $employees = Employee::with(['hobbies', 'category'])->orderBy('id', 'DESC')->get();

            return DataTables::of($employees)
                        ->addColumn('hobby', function ($data) {
                            $hobbies = [];
                            
                            foreach ($data->hobbies as $item) {
                                $hobbies[] = $item->hobby->title;
                            }

                            return implode(', ', $hobbies);
                        })
                        ->addColumn('category', function ($data) {
                            return $data->category->title;
                        })->make(true);
        }

        return view('users.employees.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $hobbies = Hobby::all();
        $categories = Category::all();

        return view('users.employees.create', compact('categories', 'hobbies'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_no' => 'required|digits:12',
            'hobbies' => 'array',
            'hobbies.*' => 'exists:hobbies,id',
            'category' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        } else {
            $profilePic = $request->file('profile_pic');

            if ($profilePic) {
                $profilePicName = $request->file('profile_pic')->store('profile_pics', 'public');
            }
            
            $employee = Employee::create([
                'name' => $request->name, 
                'contact_no' => $request->contact_no, 
                'category_id' => $request->category,
                'profile_pic' => $profilePicName ?? NULL
            ]);

            if ($request->hobbies) {
                foreach ($request->hobbies as $hobby) {
                    HobbyMapping::create([
                        'employee_id' => $employee->id,
                        'hobby_id' => $hobby
                    ]);
                }
            }
        }

        return response()->json(['success' => 'Form submitted successfully!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $employee)
    {
        $contactNoArray = str_split($employee->contact_no);
        $contactNoArrayChunked = array_chunk($contactNoArray, 3);
        $contactNo = implode(' ', array_map(function($chunk) {
            return implode('', $chunk);
        }, $contactNoArrayChunked));

        $employee->contact_no = '+' . $contactNo;

        $hobbies = Hobby::all();
        $categories = Category::all();
        $employeeHobbies = $employee->hobbies->pluck('hobby_id')->toArray();

        return view('users.employees.edit', compact('employee', 'categories', 'hobbies', 'employeeHobbies'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_no' => 'required|digits:12',
            'hobbies' => 'array',
            'hobbies.*' => 'exists:hobbies,id',
            'category' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        } else {
            $profilePic = $request->file('profile_pic');

            if ($profilePic) {
                $filePath = public_path('profile_pics') . '/' . $employee->profile_pic;

                if (file_exists($filePath)) {
                    @unlink($filePath);
                }

                $profilePicName = $request->file('profile_pic')->store('profile_pics', 'public');
            }
            
            $employee->update([
                'name' => $request->name, 
                'contact_no' => $request->contact_no, 
                'category_id' => $request->category,
                'profile_pic' => $profilePicName ?? NULL
            ]);

            if ($request->hobbies) {
                $oldHobbies = $employee->hobbies->pluck('hobby_id')->toArray();
                HobbyMapping::where('employee_id', $employee->id)->whereIn('hobby_id', array_diff($oldHobbies, $request->hobbies))->delete();

                foreach ($request->hobbies as $hobby) {
                    if (!in_array($hobby, $oldHobbies)) {
                        HobbyMapping::create([
                            'employee_id' => $employee->id,
                            'hobby_id' => $hobby
                        ]);
                    }
                }
            } else {
                HobbyMapping::where('employee_id', $employee->id)->delete();
            }
        }

        return response()->json(['success' => 'Form submitted successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        //
    }
}
