<?php

namespace App\Http\Controllers\Admin;

use App\Country;
use App\Http\Requests\EventRequest;
use App\Models\Topic;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class EventCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class EventCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    use CloneOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Event');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/event');
        $this->crud->setEntityNameStrings('event', 'events');
    }

    protected function setupListOperation()
    {
        // TODO: remove setFromDb() and manually define Columns, maybe Filters
//        $this->crud->setFromDb();

        // Topics
        $topics = Topic::get(['name', 'id']);
        $topicOptions = [];
        foreach ($topics as $key => $topic) {
            $topicOptions[$topic->id] = $topic->name;
        }
        CRUD::addFilter([ // select2_multiple filter
            'name' => 'topic_filter',
            'type' => 'select2',
            'label' => 'Topic'
        ], function () use ($topicOptions) {
            return $topicOptions;
        }, function ($value) {
            $this->crud->query = $this->crud->query->where('topic_id', '=', $value);
        });

        CRUD::addFilter([ // select2_multiple filter
            'name' => 'from_to_filter',
            'type' => 'date_range',
            'label' => 'From - To'
        ], function ()  {
        }, function ($value) {
             $dates = json_decode($value);
             $this->crud->addClause('where', 'start_date', '>=', $dates->from . ' 00:00:00');
             $this->crud->addClause('where', 'end_date', '<=', $dates->to . ' 00:00:00');
        });

        $this->crud->addColumns([
            [
                'name' => 'id',
            ],
            [
                'name' => 'name',
            ],
            [
                'name' => 'description',
            ],
            [
                'name' => 'topic_id',
                'type' => 'select',
                'model' => Topic::class,
                'entity' => 'topic',
                'attribute' => 'name'
            ],
            [
                'name' => 'min_score',
            ],
            [
                'name' => 'max_score',
            ],
            [
                'name' => 'level_unlock',
            ],
            [
                'name' => 'countries',
                'type' => 'select_multiple',
                'model' => Country::class,
                'entity' => 'countries',
                'attribute' => 'country_name',
                'pivot' => true
            ],
            [
                'name' => 'banner',
                'type' => 'image',
                'width' => '150px',

            ],
            [
                'name' => 'icon',
                'type' => 'image',
                'width' => '150px',
            ],
            [
                'name' => 'start_date',
                'type' => 'datetime',
                'format' => 'Y-M-D'
            ],
            [
                'name' => 'end_date',
                'type' => 'datetime',
                'format' => 'Y-M-D'
            ],
            [
                'name' => 'vip',
                'type' => 'custom.check'
            ],
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(EventRequest::class);

        // TODO: remove setFromDb() and manually define Fields
//        $this->crud->setFromDb();

        $this->crud->addFields([
                [
                    'name' => 'name',
                ],
                [
                    'name' => 'code',
                ],
                [
                    'name' => 'description',
                ],
                [
                    'name' => 'topic_id',
                    'type' => 'select2',
                    'model' => Topic::class,
                    'entity' => 'topic',
                    'attribute' => 'name'
                ],
                [
                    'name' => 'min_score',
                ],
                [
                    'name' => 'max_score',
                ],
                [
                    'name' => 'unlock_by_ads',
                    'type' => 'event.checkbox'
                ],
                [
                    'name' => 'unlock_by_gem',
                ],
                [
                    'name' => 'unlock_by_ticket',
                ],
                [
                    'name' => 'entry_fee_gem',
                ],
                [
                    'name' => 'entry_by_ads',
                    'type' => 'event.checkbox'
                ],
                [
                    'name' => 'banner',
                    'type' => 'browse'
                ],
                [
                    'name' => 'icon',
                    'type' => 'browse'
                ],
                [
                    'name' => 'thumb',
                    'type' => 'browse'
                ],
                [
                    'name' => 'color',
                    'type' => 'color_picker'
                ],
                [
                    'name' => 'level_unlock',
                ],
                [
                    'name' => 'countries',
                    'type' => 'select2_multiple',
                    'model' => Country::class,
                    'entity' => 'countries',
                    'attribute' => 'country_name',
                    'pivot' => true
                ],
                [
                    'name' => 'start_date',
                    'type' => 'date_picker',
                    'datetime_picker_options' => [
                        'format'   => 'YYYY-MM-DD',
                        'language' => 'en',
                    ],
                ],
                [
                    'name' => 'end_date',
                    'type' => 'date_picker',
                    'datetime_picker_options' => [
                        'format'   => 'YYYY-MM-DD',
                        'language' => 'en',
                    ],
                ],
                [
                    'name' => 'vip',
                    'type' => 'checkbox'
                ],
                [   // repeatable
                    'name'  => 'rewards',
                    'label' => 'Default Items',
                    'type'  => 'repeatable',
                    'fields' => [
                        [
                            'name'    => 'from',
                            'label' => 'From',
                            'type'    => 'number'
                        ],
                        [
                            'name'    => 'to',
                            'label' => 'To',
                            'type'    => 'number'
                        ],
                        [
                            'name'    => 'reward_gem',
                            'label' => 'Reward Gem',
                            'type'    => 'number',
                        ],
                        [
                            'name'    => 'reward_ticket',
                            'label' => 'Reward Ticket',
                            'type'    => 'number',
                        ],
                    ],

                    // optional
                    'new_item_label'  => 'Add Reward', // customize the text of the button
                ],
            ]
        );
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // update the row in the db
        $item = $this->crud->update($request->get($this->crud->model->getKeyName()),
            $this->crud->getStrippedSaveRequest());

        $this->data['entry'] = $this->crud->entry = $item;

        $numDays = strtotime($item->end_date) - strtotime($item->start_date);
        $numDays = $numDays / (24 * 3600);
        $leaders = json_decode($item->leaderBoards);

        #if(!is_array($leaders) || count($leaders[0]->scores) != $numDays) {
            $numDays = strtotime($item->end_date) - strtotime($item->start_date);
            $numDays = $numDays / (24 * 3600);
            $json = $this->generatorJsonLeaderboard($numDays, $item->min_score, $item->max_score);
            $item->leaderBoards = json_encode($json['leaderboards']);
            $item->save();
        #}

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    public function generatorJsonLeaderboard($_numDay, $_minScoreInDay, $_maxScoreInDay)
    {
        $names = $this->getNames();
        $avatars = $this->getAvatars();
        shuffle($names);

        $leaderBoards = [];
        for($i = 0; $i < 100; $i++) {
            $scores = [];
            $tmp = 0;
            for($j = 0; $j < $_numDay; $j++) {
                $newScore = $tmp + rand($_minScoreInDay, $_maxScoreInDay);
                $scores [] = $newScore;
                $tmp = $newScore;
            }

            $avatar = $avatars[array_rand($avatars, 1)];
            $leaderBoards [] = [
                'name' => $names[$i],
                'scores' => $scores,
                'avatar' => $avatar
            ];
        }

        return [
            'leaderboards' => $leaderBoards
        ];
    }

    public function getNames()
    {
        return [
            "Jerick Llantada",
            "Việt Linh",
            "Djunaedi",
            "Katiee Bugg",
            "Dagmar Fritsch",
            "Chanelle Lang",
            "Yanie Rivera",
            "Tatiana Swan",
            "Nezza Nezza",
            "Loisa Berja",
            "Jean-Claude",
            "NT Thi",
            "Karson Sauer",
            "Ole RippinI",
            "Ayla Batz",
            "Asa Klein",
            "Sydnee Kohler",
            "Yanni Matubis",
            "Tasha Farmer",
            "Mic Krech",
            "Ka Maulana",
            "Steffanie Swift",
            "DelfinaMarlia",
            "Madelynn Littel",
            "James Smith",
            "Tui Là Sang Nè",
            "Yuri Pamaran",
            "Joyce Vinoya",
            "Priya Singh",
            "Nickki Hyun",
            "Ruby Rose",
            "Hoài Phương",
            "Iv Monceda",
            "Mai Lan",
            "Flourishine Lao",
            "Madelyn Keeling",
            "Alexandria",
            "Ransom Murphy",
            "Eldred Dibbert",
            "Katrine Schoen",
            "Ezra Beer",
            "Brody Senger",
            "Samantha Auer",
            "Aidan Gallagher",
            "Vena RobelII",
            "Don Cheo",
            "Celia FritschI",
            "Delta Carroll",
            "Kenyon Bergstrom",
            "Katarina Skiles",
            "Jessyca Lemke",
            "Rania Zbidi",
            "Creola Ferry",
            "Amaya Kreiger",
            "Ephraim Batz",
            "Kathleen Strosin",
            "Maria Jacobi",
            "Abby Jaway",
            "Amber Terry",
            "Estel Braun",
            "Adela Wintheiser",
            "Elfrieda Rath",
            "Max Swaniawski",
            "Esther Kautzer",
            "Mose O'Hara",
            "Keith Estiandan",
            "Hulda O'Connell",
            "Myles BarrowsI",
            "Grayce Feest",
            "Mabelle Rau",
            "Merritt Dooley",
            "Hosea Block",
            "Kim Laxian",
            "Amy Dietrich",
            "Alberto DuBuque",
            "Reece Welch",
            "Alyce Cronin",
            "Jesús Silva",
            "Maximo Kautzer",
            "Tremayne Jacobs",
            "Minhh Ngọc",
            "Elizabeth Valletta",
            "Yuuto Jinsoku",
            "Somnath Biswas",
            "Alison Wehner",
            "Orlando Lemke",
            "Hettie Tillman",
            "Maci Aufderhar",
            "Reese Simonis",
            "Eldred Barton",
            "Jeffry Pacocha",
            "Ethel Kuvalis",
            "Krystina West ",
            "Abdiel Parker",
            "Pearline Treutel",
            "Winston Windler",
            "Ivy Bernier",
            "Ernie Nicolas",
            "Dimitri Gottlieb",
            "Malika Koch",
            "Gary Graham",
            "Thu Hoài",
            "Soe Lay",
            "Nissa Baldonado",
            "Kimley Actoy",
            "Dong Thu Hang",
            "Jelai Ok",
            "Juliet Efca",
            "Ariana Drew",
            "Janice Rebite",
            "December Davis",
            "Be A Ra Tio",
            "Kelsey Jo",
            "Stiffany Velasco",
            "Noa Baum",
            "Mahima Agarwal",
            "Quang Minh",
            "Muhammad Azid",
            "Urska Maucic",
            "Sai Kaung",
            "Alex Cherri",
            "Trix Yang",
            "Michael Richie",
            "Yani Cosep",
            "Lyka Ibanez",
            "Joel Silva",
            "Myo Pa Pa",
            "Jenaira Labas",
            "Kristine Larua",
            "Thien Ha",
            "Tâm Tâm",
            "Sierra Conrad",
            "Kate",
            "Mae Ann",
            "Sian Alexi",
            "Tom Forsström",
            "Derek Lagergren",
            "Zin Ko",
            "Haya Naz",
            "Joanah Mae",
            "Bao Tran",
            "Jackson Tan",
            "Jhoana Diane",
            "Nwe Nwe",
            "Lorraine Agustin",
            "KingKing Valor",
            "Marissa Kenney",
            "Ryan Hartshorne",
            "Evadne Heau",
            "Minh Nhat",
            "Jemma Isherwood",
            "Ronya Buru",
            "Minh Ngoc",
            "Kayden Harisson",
            "Cao Quynh Anh",
            "Abee",
            "Zehra Aksoy",
            "Mai Mia",
            "Phạm Nhii",
            "Akery",
            "Jherline Anibe",
            "Andrea Kim",
            "Andrean Pilapil",
            "Gabrielle",
            "Sabita Sharma",
            "Ren",
            "Pallabi Das",
            "Dreke Wright",
            "Randolph",
            "Hoamo Hoamo",
            "Zharrenna Tuan",
            "Liz Beth",
            "Jesusa Marie",
            "Caitlyn Rowlands",
            "Ahida Flores",
            "Lés Léé",
            "Aarya Adhikari",
            "ZACH",
            "Parveen Kaur",
            "Shruti Kandel",
            "Karunanayaka",
            "Judy Cohouj",
            "Reeze Dalipe",
            "Harumi Mitsuko",
            "Aishwarya Patel",
            "Piyasa Mitra",
            "Yassel Marie",
            "Alana Nicolau",
            "Kendall Nedeau",
            "Danielle Silvia",
            "Hoàng Yến",
            "Gab Arela",
            "April Soria",
            "Phu Phu",
            "Jeff Pendergrass",
            "James Monty",
            "Paula",
            "Chimon Styles",
            "Swift Kim",
            "PyaePyae Zaw",
            "Shin Swift",
            "Taysten",
            "Ging",
            "Joye Tan",
            "Julie Bibeau",
            "Deni García",
            "Summer Ness",
            "Kim Ngaan",
            "Marina Makari",
            "Kayla Le",
            "Holly Sparks",
            "Susan Gray",
            "Sanvi Kaur",
        ];
    }

    public function getAvatars() {
        return [
            "blank-picture-holder",
            "Layer-1",
            "Layer-3",
            "Layer-4",
            "Layer-5",
            "Layer-6",
            "Layer-8",
            "Layer-9",
            "Layer-10"
        ];
    }

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // insert item in the db
        $item = $this->crud->create($this->crud->getStrippedSaveRequest());
        $this->data['entry'] = $this->crud->entry = $item;

        $numDays = strtotime($item->end_date) - strtotime($item->start_date);
        $numDays = $numDays / (24 * 3600);
        $json = $this->generatorJsonLeaderboard($numDays, $item->min_score, $item->max_score);
        $item->leaderBoards = json_encode($json['leaderboards']);
        $item->save();

        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }
}
