<?php

namespace Database\Seeders;

use App\Models\ChurchArea;
use App\Models\ChurchDistrict;
use Illuminate\Database\Seeder;

class ChurchAreaDistrictSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            'Achimota Area' => ['Achimota', 'Akweteyman', 'Ofankor', 'Taifa'],
            'Adenta Area' => ['Adenta', 'Amrahia', 'Dodowa', 'Frafraha'],
            'Ashaiman Area' => ['Ashaiman', 'Dawhenya', 'Michel Camp', 'Tema Newtown'],
            'Cape Coast Area' => ['Cape Coast', 'Elmina', 'Komenda', 'Mankessim'],
            'Dansoman Area' => ['Dansoman', 'Glefe', 'Mataheko', 'Sakaman'],
            'Ho Area' => ['Ho', 'Hohoe', 'Keta', 'Sogakope'],
            'Kaneshie Area' => ['Kaneshie', 'Awudome', 'Darkuman', 'Odorkor'],
            'Kasoa Area' => ['Kasoa', 'Awutu Bereku', 'Buduburam', 'Nyanyano'],
            'Koforidua Area' => ['Koforidua', 'Akwapim', 'Asamankese', 'Suhum'],
            'Kumasi Area' => ['Asokwa', 'Bantama', 'Krofrom', 'Suame'],
            'Madina Area' => ['Madina', 'Abokobi', 'Haatso', 'Legon'],
            'Nkawkaw Area' => ['Nkawkaw', 'Atibie', 'Mpraeso', 'Oda'],
            'Odorkor Area' => ['Odorkor', 'Anyaa', 'Lapaz', 'Mallam'],
            'Sunyani Area' => ['Sunyani', 'Berekum', 'Dormaa', 'Wenchi'],
            'Takoradi Area' => ['Takoradi', 'Axim', 'Tarkwa', 'Wassa'],
            'Tamale Area' => ['Tamale', 'Savelugu', 'Walewale', 'Yendi'],
            'Tema Area' => ['Tema', 'Community 1', 'Community 12', 'Sakumono'],
            'Teshie-Nungua Area' => ['Teshie', 'Nungua', 'La', 'Spintex'],
            'Wa Area' => ['Wa', 'Jirapa', 'Lawra', 'Tumu'],
            'Winneba Area' => ['Winneba', 'Apam', 'Gomoa', 'Swedru'],
        ];

        foreach ($areas as $areaName => $districts) {
            $area = ChurchArea::updateOrCreate(['name' => $areaName], ['status' => 'active']);

            foreach ($districts as $districtName) {
                ChurchDistrict::updateOrCreate(
                    ['church_area_id' => $area->id, 'name' => $districtName],
                    ['status' => 'active'],
                );
            }
        }
    }
}
