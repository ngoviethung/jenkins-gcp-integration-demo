<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Factory;

class GenerateLeaderBoard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:leaderboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Leader Board on Firebase';

    public $sGen = true;
    public $_minScoreInDay = 240;
    public $_maxScoreInDay = 500;
    public $_numday = 7;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $androidJson = __DIR__ . '/du3_android.json';
        $this->process($androidJson);
    }

    public function process($credential) {
        $factory = (new Factory)
            ->withServiceAccount($credential);
        $remoteConfig = $factory->createRemoteConfig();
        $template = $remoteConfig->get(); // Returns a Kreait\Firebase\RemoteConfig\Template

        $parameterGroups = $template->parameterGroups();
        $eventGroup = $parameterGroups['Event'];

        $eventGroupParameters = $eventGroup->parameters();
        $eventMode = $eventGroupParameters['event_mode'];

        /* @todo  thay dữ liệu event mode ở đây */

        $values = $eventMode->defaultValue();
        $values = json_decode($values->value());
        $listEventsActive = $values->listEventsActive;

        /**@todo: phan tich xem event nao duoc chay **/
        $allowNames = [];
        $utcTime = gmdate("d-m-Y H:i:s");
        echo $utcTime . PHP_EOL;
        foreach ($listEventsActive as $activeEvent) {
            if(strtotime($activeEvent->time_to . ' 00:00:00') <= strtotime($utcTime)) {
                echo "Checking" . $activeEvent->time_to . ' 00:00:00' . PHP_EOL;
                $allowNames [] = $activeEvent->event_name;
            }
        }

        if(!count($allowNames)) {
            return true;
        }

        $listEventsComplete = $values->listEventsComplete;
        foreach ($listEventsActive as &$activeEvent) {
            $eventName = $activeEvent->event_name;

            if(!in_array($eventName, $allowNames)) {
                continue;
            }

            $eventModel = \App\Models\Event::where('name', '=', $eventName)->first();
            if(!$eventModel) {
                echo $eventName . ' not found' . PHP_EOL;
                continue;
            }

            foreach ($listEventsComplete as &$completeEvent) {
                if($completeEvent->event_name == $eventName) {
                    $completeEvent->time_from = $activeEvent->time_from;
                    $completeEvent->time_to = $activeEvent->time_to;
                    break;
                }
            }

            $activeEvent->time_from = $activeEvent->time_to;
            /**@todo: loading số 7 từ config các event ra */
            $activeEvent->time_to = date('d-m-Y', strtotime($activeEvent->time_to) + 3600 * 24 * $eventModel->number_days);
        }

        $values->listEventsActive = $listEventsActive;
        $values->listEventsComplete = $listEventsComplete;

        $eventMode = $eventMode->withDefaultValue(json_encode($values));
        $eventGroup = $eventGroup->withParameter($eventMode);

        $events = \App\Models\Event::all();
        $eventGroupParameters = $eventGroup->parameters();
        foreach ($events as $event) {
            $code = $event->code;

            if(!in_array($event->name, $allowNames)) {
                continue;
            }

            $currentParameterLeaderBoard = $eventGroupParameters["leaderboard_" . $code."_current"];
            $currentLeaderValues = $currentParameterLeaderBoard->defaultValue();

            $lastTimeParameterLeaderBoard = $eventGroupParameters["leaderboard_" . $code."_lasttime"];
            $lastTimeParameterLeaderBoard = $lastTimeParameterLeaderBoard->withDefaultValue($currentLeaderValues->value());


            $newCurrentLeaderValues = $this->generatorJsonLeaderboard($event->number_days, $event->min_score, $event->max_score);
            $currentParameterLeaderBoard = $currentParameterLeaderBoard->withDefaultValue(json_encode($newCurrentLeaderValues));

            $eventGroup = $eventGroup->withParameter($lastTimeParameterLeaderBoard);
            $eventGroup = $eventGroup->withParameter($currentParameterLeaderBoard);
        }

        $template = $template->withParameterGroup($eventGroup);

        /** Kết thúc chỉnh sửa Event Mode */
        $remoteConfig->publish($template);

        return true;
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
}
