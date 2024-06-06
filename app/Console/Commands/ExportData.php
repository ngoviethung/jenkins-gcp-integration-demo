<?php
/**
 * Created by PhpStorm.
 * User: hungnc
 * Date: 03/10/2022
 * Time: 14:45
 */

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;
use App\Http\Resources\Admin\Export\TaskResource;
use App\Models\CharacterModel;
use App\Models\GroupLevelTask;
use App\Models\GroupLevelTopic;
use App\Models\GroupLevelType;
use App\Models\Language;
use App\Models\Style;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\Topic;
use App\Models\Type;
use App\Models\Position;
use App\TaskRevision;
use App\TopicRevision;
use DB;
use Log;
use App\Models\Skin;
use App\OutfitRevision;
use Illuminate\Database\Eloquent\Builder;

use App\Http\Resources\Export\Hair as HairResource;
use App\Http\Resources\Export\Makeup as MakeupResource;
use App\Http\Resources\Export\Normal as NormalResource;
use App\Http\Resources\Export\Skin as SkinResource;
use App\Http\Resources\Export\Color as ColorResource;
use App\Http\Resources\Export\Collection as CollectionResource;
use App\Http\Resources\Export\Pattern as PatternResource;
use App\Http\Resources\Export\Material as MaterialResource;
use App\Http\Resources\Export\Type as TypeResource;
use App\Http\Resources\Export\Position as PositionResource;



class ExportData extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export data from Export All in CMS Task';


    CONST EXPORT_FOLDER = 'export_to_local';

    public $directory_seperator;
    public $uploadPath;

    public $exportPath;

    public function __construct()
    {
        ini_set('max_execution_time', 3000);
        ini_set('memory_limit', -1);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->directory_seperator = "\\";
        } else {
            $this->directory_seperator = DIRECTORY_SEPARATOR;
        }
        $this->uploadPath = public_path() . $this->directory_seperator . 'uploads';
        $this->exportPath = public_path() . $this->directory_seperator . 'export_to_local';

        parent::__construct();
    }



    public function handle()
    {

        $check = DB::table('exports')->where('status', 0)->orderBy('id', 'DESC')->get()->first();
        if(!$check){ //Khong co request nao
            return;
        }
        $id = $check->id;
        $check2 = DB::table('exports')->where('status', 1)->whereNull('file')->count();
        if($check2 > 0){ //Co 1 tien trinh dang chay
            return;
        }
        DB::table('exports')->where('id', $id)->update(['status' => 1]);
        DB::beginTransaction();

        try {

            $this->cleanFolder($this->exportPath);

            $this->generateData();
            $files = $this->exportPath;

            $datetime = date('Y-m-d_H:i:s');
            $file = $this->uploadPath . $this->directory_seperator . "$datetime.zip";
            exec("cd $files && zip -r $file *");

            DB::table('exports')->where('id', $id)->update(['file' => "uploads/$datetime.zip", 'updated_at' => date('Y-m-d H:i:s')]);
            DB::commit();

            $this->clearCDNCache();
            return response()->download($this->uploadPath . $this->directory_seperator . "$datetime.zip");

        } catch (\Exception $exception) {
            dd($exception);
            DB::rollback();
            return 0;
        }

    }
    protected function cleanFolder($folder)
    {
        exec('rm -rf ' . $folder. '/*');
    }

    protected function generateData()
    {

        $data = [
            'hairs' => $this->generateHairs(),
            'make_ups' => $this->generateMakeups(),
            'items' => $this->generateNormals(),
            'skins' => $this->generateSkins(),
            'colors' => $this->generateColors(),
            'collections' => $this->generateCollections(),
            'patterns' => $this->generatePatterns(),
            'materials' => $this->generateMaterials(),
            'types' => $this->generateTypes(),
            'positions' => $this->generatePositions(),
            'brands' => []
        ];

        $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_LINE_TERMINATORS);
        $data = str_replace('_skin_id_', '', $data);

        file_put_contents(public_path('export_to_local/data.json'), $data);

        return 1;

    }

    private function generatePositions(){
        $positions = Position::get();
        $data = PositionResource::collection($positions);

        return $data;
    }

    private function generateSkins(){
        $skins = Skin::get();
        $data = SkinResource::collection($skins);

        return $data;
    }
    private function generateColors(){
        $colors = DB::table('colors')->get();
        $data = ColorResource::collection($colors);

        return $data;
    }
    private function generateCollections(){
        $collections = DB::table('collections')->get();
        $data = CollectionResource::collection($collections);

        return $data;
    }
    private function generatePatterns(){
        $patterns = DB::table('patterns')->get();
        $data = PatternResource::collection($patterns);

        return $data;
    }
    private function generateMaterials(){
        $materials = DB::table('materials')->get();
        $data = MaterialResource::collection($materials);

        return $data;
    }
    private function generateTypes(){
        $types = Type::get();
        $data = TypeResource::collection($types);

        return $data;
    }

    private function generateHairs(){

        $items = Item::with('styles', 'grouplevelitem')->whereNotNull('hair_items')->get();
        $data = HairResource::collection($items);

        return $data;

    }
    private function generateMakeups(){

        $items = Item::with('grouplevelitem')->whereNotNull('makeup_items')->get();
        $data = MakeupResource::collection($items);

        return $data;

    }

    private function generateNormals(){

        $types = Type::with('children')->whereNull('parent_id')->get();
        $data = [];

        foreach ($types as $type){
            $arr_type_id = [];
            $arr_type_id[] = $type->id;
            $children = $type->children;
            if($children){
                foreach ($children as $child){
                    $arr_type_id[] = $child->id;
                }
            }
            $data[] = [
                'type_id' => $type->id,
                'list' => $this->getItemsNormalByType($arr_type_id)
            ];

        }

        return $data;

    }

    private function getItemsNormalByType($arr_type_id){
        $items = Item::with('styles', 'grouplevelitem')->whereIn('type_id', $arr_type_id)->whereNull('hair_items')->whereNull('makeup_items')->get();
        $data = NormalResource::collection($items);

        return $data;
    }

    protected function changeToBytes($fileName) {
        if($fileName) {
            return $fileName . '.bytes';
        }

        return $fileName;
    }
    public function clearCDNCache() {
        try {
            $curl = curl_init();

            $domain_cdn = urlencode(urlencode(env('URL_CDN').'export/*'));

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.bunny.net/purge?url=$domain_cdn",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array(
                    'AccessKey: f52db29c-cec9-469d-aaab-d39803be8d81f7a43e74-1604-48cc-abda-bf45c3af480f'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            echo $response;
        } catch (\Exception $exception) {
            Log::info('Exception on clear CDN cache');
        }
    }
}
