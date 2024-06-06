<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return redirect()->route('backpack');
});
Route::get('/home', function () {
    return redirect('/admin/template');
});
Route::get('/update-topic', function () {
    $a = DB::table('topic_types')->where('topic_id',2)->get()->toArray();
    $b = DB::table('topic_types')->select('topic_id')->distinct()->pluck('topic_id')->toArray();
    $c = DB::table('topics')->whereNotIn('id',$b)->get(['id'])->pluck('id')->toArray();

    foreach ($c as $id){
        foreach ($a as $topic){
            $data = [
                'topic_id' => $id,
                'type_id' => $topic->type_id,
                'factor' => $topic->factor,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            DB::table('topic_types')->insert($data);
        }
    }

});


Route::get('update-code-position', function () {

    $data = DB::table('positions')->get();
    foreach ($data as $value){
        $name = $value->name;
        $code  = vn_to_str(strtolower($name));
        $code = remove_special_chars($code);

        DB::table('positions')->where('id', $value->id)->update(['code' => $code]);

        echo $value->id.PHP_EOL;
    }
});


