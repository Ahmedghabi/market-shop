<?php

namespace App\Command;

use App\Entity\Country;
use App\Entity\Governorate;
use App\Entity\Locality;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed:reference-data', description: 'Seed countries, governorates and localities (Tunisia)')]
final class SeedReferenceDataCommand extends Command
{
    private const GOVERNORATES = [
        ['name' => 'Tunis', 'code' => 'TN-11', 'cities' => [
            ['name' => 'Tunis', 'postalCode' => '1000'],
            ['name' => 'La Marsa', 'postalCode' => '2070'],
            ['name' => 'Carthage', 'postalCode' => '2016'],
            ['name' => 'Le Bardo', 'postalCode' => '2000'],
            ['name' => 'Le Kram', 'postalCode' => '2015'],
            ['name' => 'Sidi Bou Saïd', 'postalCode' => '2026'],
            ['name' => 'El Menzah', 'postalCode' => '1004'],
            ['name' => 'El Omrane', 'postalCode' => '1005'],
            ['name' => 'Bab El Bhar', 'postalCode' => '1006'],
            ['name' => 'Bab Souika', 'postalCode' => '1008'],
            ['name' => 'Cité El Khadra', 'postalCode' => '1003'],
            ['name' => 'Hached', 'postalCode' => '1054'],
            ['name' => 'El Kabbaria', 'postalCode' => '1009'],
            ['name' => 'El Ouardia', 'postalCode' => '1007'],
            ['name' => 'Jebel Jelloud', 'postalCode' => '1002'],
            ['name' => 'Sijoumi', 'postalCode' => '1013'],
        ]],
        ['name' => 'Ariana', 'code' => 'TN-12', 'cities' => [
            ['name' => 'Ariana', 'postalCode' => '2080'],
            ['name' => 'Ettadhamen', 'postalCode' => '2041'],
            ['name' => 'Kalaat el-Andalous', 'postalCode' => '2022'],
            ['name' => 'Sidi Thabet', 'postalCode' => '2020'],
            ['name' => 'Raoued', 'postalCode' => '2056'],
            ['name' => 'Mnihla', 'postalCode' => '2091'],
            ['name' => 'Borj Louzir', 'postalCode' => '2074'],
            ['name' => 'Chotrana', 'postalCode' => '2037'],
            ['name' => 'Nasrallah', 'postalCode' => '2057'],
        ]],
        ['name' => 'Ben Arous', 'code' => 'TN-13', 'cities' => [
            ['name' => 'Ben Arous', 'postalCode' => '2013'],
            ['name' => 'El Mourouj', 'postalCode' => '2074'],
            ['name' => 'Hammam Lif', 'postalCode' => '2050'],
            ['name' => 'Hammam Chott', 'postalCode' => '2051'],
            ['name' => 'Bou Mhel el-Bassatine', 'postalCode' => '2095'],
            ['name' => 'Ezzahra', 'postalCode' => '2035'],
            ['name' => 'Radès', 'postalCode' => '2040'],
            ['name' => 'Mégrine', 'postalCode' => '2033'],
            ['name' => 'Fouchana', 'postalCode' => '2082'],
            ['name' => 'Mornag', 'postalCode' => '2060'],
            ['name' => 'Mohamedia', 'postalCode' => '2086'],
            ['name' => 'Nouvelle Médina', 'postalCode' => '2034'],
        ]],
        ['name' => 'La Manouba', 'code' => 'TN-14', 'cities' => [
            ['name' => 'La Manouba', 'postalCode' => '2010'],
            ['name' => 'Oued Ellil', 'postalCode' => '1145'],
            ['name' => 'Douar Hicher', 'postalCode' => '1120'],
            ['name' => 'Tebourba', 'postalCode' => '1130'],
            ['name' => 'El Battan', 'postalCode' => '1114'],
            ['name' => 'Borj El Amri', 'postalCode' => '1142'],
            ['name' => 'Jedaida', 'postalCode' => '1121'],
            ['name' => 'Mornaguia', 'postalCode' => '1110'],
            ['name' => 'Denden', 'postalCode' => '1100'],
        ]],
        ['name' => 'Nabeul', 'code' => 'TN-21', 'cities' => [
            ['name' => 'Nabeul', 'postalCode' => '8000'],
            ['name' => 'Hammamet', 'postalCode' => '8050'],
            ['name' => 'Dar Chaabane', 'postalCode' => '8012'],
            ['name' => 'Béni Khiar', 'postalCode' => '8013'],
            ['name' => 'Korba', 'postalCode' => '8070'],
            ['name' => 'Menzel Temime', 'postalCode' => '8080'],
            ['name' => 'Kelibia', 'postalCode' => '8090'],
            ['name' => 'Soliman', 'postalCode' => '8020'],
            ['name' => 'Takelsa', 'postalCode' => '8040'],
            ['name' => 'El Haouaria', 'postalCode' => '8030'],
            ['name' => 'Grombalia', 'postalCode' => '8030'],
            ['name' => 'Bou Argoub', 'postalCode' => '8040'],
            ['name' => 'Mida', 'postalCode' => '8060'],
            ['name' => 'Tazerka', 'postalCode' => '8093'],
            ['name' => 'Sidi Jedidi', 'postalCode' => '8022'],
        ]],
        ['name' => 'Zaghouan', 'code' => 'TN-22', 'cities' => [
            ['name' => 'Zaghouan', 'postalCode' => '1100'],
            ['name' => 'Zriba', 'postalCode' => '1120'],
            ['name' => 'El Fahs', 'postalCode' => '1110'],
            ['name' => 'Bir Mcherga', 'postalCode' => '1140'],
            ['name' => 'Ennadhour', 'postalCode' => '1130'],
            ['name' => 'Saouaf', 'postalCode' => '1150'],
            ['name' => 'Oued Souf', 'postalCode' => '1113'],
        ]],
        ['name' => 'Bizerte', 'code' => 'TN-23', 'cities' => [
            ['name' => 'Bizerte', 'postalCode' => '7000'],
            ['name' => 'Menzel Bourguiba', 'postalCode' => '7050'],
            ['name' => 'Mateur', 'postalCode' => '7030'],
            ['name' => 'Riniche', 'postalCode' => '7034'],
            ['name' => 'Sejnane', 'postalCode' => '7010'],
            ['name' => 'Ghar El Melh', 'postalCode' => '7016'],
            ['name' => 'Menzel Jemil', 'postalCode' => '7020'],
            ['name' => 'Bazina', 'postalCode' => '7025'],
            ['name' => 'El Alia', 'postalCode' => '7012'],
            ['name' => 'Ras Jebel', 'postalCode' => '7011'],
            ['name' => 'Tinja', 'postalCode' => '7040'],
            ['name' => 'Utique', 'postalCode' => '7024'],
            ['name' => 'Joumine', 'postalCode' => '7032'],
            ['name' => 'Ghezala', 'postalCode' => '7035'],
        ]],
        ['name' => 'Béja', 'code' => 'TN-31', 'cities' => [
            ['name' => 'Béja', 'postalCode' => '9000'],
            ['name' => 'Medjez el-Bab', 'postalCode' => '9070'],
            ['name' => 'Testour', 'postalCode' => '9040'],
            ['name' => 'Goubellat', 'postalCode' => '9080'],
            ['name' => 'Nefza', 'postalCode' => '9030'],
            ['name' => 'Téboursouk', 'postalCode' => '9050'],
            ['name' => 'Amdoun', 'postalCode' => '9020'],
            ['name' => 'Thibar', 'postalCode' => '9010'],
            ['name' => 'Zahret Medjen', 'postalCode' => '9060'],
            ['name' => 'Sidi Nsir', 'postalCode' => '9090'],
        ]],
        ['name' => 'Jendouba', 'code' => 'TN-32', 'cities' => [
            ['name' => 'Jendouba', 'postalCode' => '8100'],
            ['name' => 'Tabarka', 'postalCode' => '8110'],
            ['name' => 'Aïn Draham', 'postalCode' => '8120'],
            ['name' => 'Bou Salem', 'postalCode' => '8130'],
            ['name' => 'Fernana', 'postalCode' => '8140'],
            ['name' => 'Ghardimaou', 'postalCode' => '8150'],
            ['name' => 'Oued Mliz', 'postalCode' => '8160'],
            ['name' => 'Balta', 'postalCode' => '8170'],
            ['name' => 'Beni Mateur', 'postalCode' => '8115'],
        ]],
        ['name' => 'Le Kef', 'code' => 'TN-33', 'cities' => [
            ['name' => 'Le Kef', 'postalCode' => '7100'],
            ['name' => 'Dahmani', 'postalCode' => '7110'],
            ['name' => 'Jérissa', 'postalCode' => '7140'],
            ['name' => 'Kalaat es Senam', 'postalCode' => '7130'],
            ['name' => 'Nebeur', 'postalCode' => '7120'],
            ['name' => 'Sakiet Sidi Youssef', 'postalCode' => '7150'],
            ['name' => 'Tajerouine', 'postalCode' => '7160'],
            ['name' => 'Touiref', 'postalCode' => '7170'],
            ['name' => 'Ksour', 'postalCode' => '7114'],
            ['name' => 'Sers', 'postalCode' => '7180'],
        ]],
        ['name' => 'Siliana', 'code' => 'TN-34', 'cities' => [
            ['name' => 'Siliana', 'postalCode' => '6100'],
            ['name' => 'Bou Arada', 'postalCode' => '6110'],
            ['name' => 'Gaâfour', 'postalCode' => '6120'],
            ['name' => 'El Krib', 'postalCode' => '6130'],
            ['name' => 'Bargou', 'postalCode' => '6140'],
            ['name' => 'El Aroussa', 'postalCode' => '6150'],
            ['name' => 'Kesra', 'postalCode' => '6160'],
            ['name' => 'Makthar', 'postalCode' => '6170'],
            ['name' => 'Rouhia', 'postalCode' => '6180'],
            ['name' => 'Sidi Bou Rouis', 'postalCode' => '6190'],
        ]],
        ['name' => 'Kairouan', 'code' => 'TN-41', 'cities' => [
            ['name' => 'Kairouan', 'postalCode' => '3100'],
            ['name' => 'Chebika', 'postalCode' => '3150'],
            ['name' => 'Haffouz', 'postalCode' => '3110'],
            ['name' => 'El Alâa', 'postalCode' => '3120'],
            ['name' => 'Bou Hajla', 'postalCode' => '3130'],
            ['name' => 'Chrarda', 'postalCode' => '3140'],
            ['name' => 'Hajeb El Ayoun', 'postalCode' => '3160'],
            ['name' => 'Nasrallah', 'postalCode' => '3170'],
            ['name' => 'Oueslatia', 'postalCode' => '3180'],
            ['name' => 'Sbikha', 'postalCode' => '3190'],
            ['name' => 'Ain Jaloula', 'postalCode' => '3115'],
        ]],
        ['name' => 'Kasserine', 'code' => 'TN-42', 'cities' => [
            ['name' => 'Kasserine', 'postalCode' => '1200'],
            ['name' => 'Fériana', 'postalCode' => '1210'],
            ['name' => 'Foussana', 'postalCode' => '1220'],
            ['name' => 'Haïdra', 'postalCode' => '1230'],
            ['name' => 'Jedelienne', 'postalCode' => '1240'],
            ['name' => 'Magel Bel Abbès', 'postalCode' => '1250'],
            ['name' => 'Sbiba', 'postalCode' => '1260'],
            ['name' => 'Sbeitla', 'postalCode' => '1270'],
            ['name' => 'Thala', 'postalCode' => '1280'],
            ['name' => 'Thelepte', 'postalCode' => '1290'],
            ['name' => 'Ayoun', 'postalCode' => '1215'],
            ['name' => 'Ezzouhour', 'postalCode' => '1255'],
        ]],
        ['name' => 'Sidi Bouzid', 'code' => 'TN-43', 'cities' => [
            ['name' => 'Sidi Bouzid', 'postalCode' => '9100'],
            ['name' => 'Bir El Hafey', 'postalCode' => '9110'],
            ['name' => 'Cebbala Ouled Asker', 'postalCode' => '9120'],
            ['name' => 'Jilma', 'postalCode' => '9130'],
            ['name' => 'Menzel Bouzaiane', 'postalCode' => '9140'],
            ['name' => 'Meknassy', 'postalCode' => '9150'],
            ['name' => 'Mezzouna', 'postalCode' => '9160'],
            ['name' => 'Ouled Haffouz', 'postalCode' => '9170'],
            ['name' => 'Regueb', 'postalCode' => '9180'],
            ['name' => 'Sidi Ali Ben Aoun', 'postalCode' => '9190'],
            ['name' => 'Souk Jedid', 'postalCode' => '9115'],
            ['name' => 'Mazzouna', 'postalCode' => '9165'],
        ]],
        ['name' => 'Sousse', 'code' => 'TN-51', 'cities' => [
            ['name' => 'Sousse', 'postalCode' => '4000'],
            ['name' => 'Akouda', 'postalCode' => '4021'],
            ['name' => 'Hammam Sousse', 'postalCode' => '4011'],
            ['name' => 'Kalâa Kebira', 'postalCode' => '4060'],
            ['name' => 'Kalâa Sghira', 'postalCode' => '4070'],
            ['name' => 'Enfidha', 'postalCode' => '4030'],
            ['name' => 'Sidi Bou Ali', 'postalCode' => '4040'],
            ['name' => 'Hergla', 'postalCode' => '4050'],
            ['name' => 'Kondar', 'postalCode' => '4020'],
            ['name' => 'Bouficha', 'postalCode' => '4035'],
            ['name' => 'Msaken', 'postalCode' => '4010'],
            ['name' => 'Zaouiet Ksiba Thrayett', 'postalCode' => '4080'],
            ['name' => 'Ouled Chamekh', 'postalCode' => '4090'],
            ['name' => 'Sayada', 'postalCode' => '4025'],
            ['name' => 'Kattana', 'postalCode' => '4045'],
        ]],
        ['name' => 'Monastir', 'code' => 'TN-52', 'cities' => [
            ['name' => 'Monastir', 'postalCode' => '5000'],
            ['name' => 'Moknine', 'postalCode' => '5050'],
            ['name' => 'Jemmal', 'postalCode' => '5020'],
            ['name' => 'Ksar Hellal', 'postalCode' => '5070'],
            ['name' => 'Ksibet el-Médiouni', 'postalCode' => '5030'],
            ['name' => 'Lamta', 'postalCode' => '5090'],
            ['name' => 'Menzel Ennour', 'postalCode' => '5010'],
            ['name' => 'Ouerdanine', 'postalCode' => '5040'],
            ['name' => 'Sahline', 'postalCode' => '5060'],
            ['name' => 'Bembla', 'postalCode' => '5080'],
            ['name' => 'Bennane', 'postalCode' => '5012'],
            ['name' => 'Bouhjar', 'postalCode' => '5065'],
            ['name' => 'Sayada', 'postalCode' => '5035'],
            ['name' => 'Teboulba', 'postalCode' => '5085'],
            ['name' => 'Zéramdine', 'postalCode' => '5095'],
        ]],
        ['name' => 'Mahdia', 'code' => 'TN-53', 'cities' => [
            ['name' => 'Mahdia', 'postalCode' => '5100'],
            ['name' => 'Chebba', 'postalCode' => '5170'],
            ['name' => 'El Jem', 'postalCode' => '5160'],
            ['name' => 'Ksour Essef', 'postalCode' => '5110'],
            ['name' => 'Melloulèche', 'postalCode' => '5120'],
            ['name' => 'Bou Merdes', 'postalCode' => '5130'],
            ['name' => 'Chorbane', 'postalCode' => '5140'],
            ['name' => 'Hebira', 'postalCode' => '5150'],
            ['name' => 'Sidi Alouane', 'postalCode' => '5180'],
            ['name' => 'Sidi Zid', 'postalCode' => '5190'],
            ['name' => 'Ouled Chamekh', 'postalCode' => '5115'],
        ]],
        ['name' => 'Sfax', 'code' => 'TN-61', 'cities' => [
            ['name' => 'Sfax', 'postalCode' => '3000'],
            ['name' => 'Agareb', 'postalCode' => '3010'],
            ['name' => 'Bir Ali Ben Khelifa', 'postalCode' => '3020'],
            ['name' => 'El Amra', 'postalCode' => '3030'],
            ['name' => 'El Hencha', 'postalCode' => '3040'],
            ['name' => 'Gremda', 'postalCode' => '3050'],
            ['name' => 'Jebiniana', 'postalCode' => '3060'],
            ['name' => 'Kerkennah', 'postalCode' => '3070'],
            ['name' => 'Mahres', 'postalCode' => '3080'],
            ['name' => 'Menzel Chaker', 'postalCode' => '3090'],
            ['name' => 'Sakiet Eddaïer', 'postalCode' => '3012'],
            ['name' => 'Sakiet Ezzit', 'postalCode' => '3021'],
            ['name' => 'Skhira', 'postalCode' => '3035'],
            ['name' => 'Thyna', 'postalCode' => '3045'],
            ['name' => 'Chihia', 'postalCode' => '3002'],
        ]],
        ['name' => 'Gafsa', 'code' => 'TN-71', 'cities' => [
            ['name' => 'Gafsa', 'postalCode' => '2100'],
            ['name' => 'El Ksar', 'postalCode' => '2110'],
            ['name' => 'Mdhilla', 'postalCode' => '2120'],
            ['name' => 'Métlaoui', 'postalCode' => '2130'],
            ['name' => 'Moularès', 'postalCode' => '2140'],
            ['name' => 'Redeyef', 'postalCode' => '2150'],
            ['name' => 'Sened', 'postalCode' => '2160'],
            ['name' => 'Belkhir', 'postalCode' => '2170'],
            ['name' => 'Oum El Araies', 'postalCode' => '2180'],
            ['name' => 'Sidi Aich', 'postalCode' => '2190'],
            ['name' => 'Lela', 'postalCode' => '2115'],
        ]],
        ['name' => 'Tozeur', 'code' => 'TN-72', 'cities' => [
            ['name' => 'Tozeur', 'postalCode' => '2200'],
            ['name' => 'Degache', 'postalCode' => '2210'],
            ['name' => 'Hezoua', 'postalCode' => '2220'],
            ['name' => 'Nefta', 'postalCode' => '2230'],
            ['name' => 'Tameghza', 'postalCode' => '2240'],
            ['name' => 'Hamet Jerid', 'postalCode' => '2215'],
        ]],
        ['name' => 'Kébili', 'code' => 'TN-73', 'cities' => [
            ['name' => 'Kébili', 'postalCode' => '4200'],
            ['name' => 'Douz', 'postalCode' => '4210'],
            ['name' => 'El Golâa', 'postalCode' => '4220'],
            ['name' => 'El Faouar', 'postalCode' => '4230'],
            ['name' => 'Souk El Ahad', 'postalCode' => '4240'],
            ['name' => 'Jemna', 'postalCode' => '4215'],
            ['name' => 'Bazma', 'postalCode' => '4225'],
            ['name' => 'Rjim Maatoug', 'postalCode' => '4250'],
        ]],
        ['name' => 'Gabès', 'code' => 'TN-81', 'cities' => [
            ['name' => 'Gabès', 'postalCode' => '6000'],
            ['name' => 'El Hamma', 'postalCode' => '6020'],
            ['name' => 'Matmata', 'postalCode' => '6030'],
            ['name' => 'Metouia', 'postalCode' => '6040'],
            ['name' => 'Nouvelle Matmata', 'postalCode' => '6035'],
            ['name' => 'Ghannouch', 'postalCode' => '6010'],
            ['name' => 'Toujane', 'postalCode' => '6050'],
            ['name' => 'Sidi Boulbaba', 'postalCode' => '6060'],
            ['name' => 'Tekouine', 'postalCode' => '6070'],
            ['name' => 'Zrig', 'postalCode' => '6080'],
            ['name' => 'Chenini', 'postalCode' => '6033'],
            ['name' => 'Mareth', 'postalCode' => '6015'],
        ]],
        ['name' => 'Médenine', 'code' => 'TN-82', 'cities' => [
            ['name' => 'Médenine', 'postalCode' => '4100'],
            ['name' => 'Ben Gardane', 'postalCode' => '4160'],
            ['name' => 'Zarzis', 'postalCode' => '4170'],
            ['name' => 'Houmt Souk', 'postalCode' => '4180'],
            ['name' => 'Midoun', 'postalCode' => '4120'],
            ['name' => 'Ajim', 'postalCode' => '4130'],
            ['name' => 'Mezraya', 'postalCode' => '4115'],
            ['name' => 'Sidi Makhlouf', 'postalCode' => '4140'],
            ['name' => 'Beni Khedache', 'postalCode' => '4150'],
        ]],
        ['name' => 'Tataouine', 'code' => 'TN-83', 'cities' => [
            ['name' => 'Tataouine', 'postalCode' => '3200'],
            ['name' => 'Bir Lahmar', 'postalCode' => '3210'],
            ['name' => 'Dehiba', 'postalCode' => '3220'],
            ['name' => 'Ghomrassen', 'postalCode' => '3230'],
            ['name' => 'Remada', 'postalCode' => '3240'],
            ['name' => 'Smar', 'postalCode' => '3250'],
            ['name' => 'Ras Jedir', 'postalCode' => '3215'],
        ]],
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $existing = $this->em->getRepository(Country::class)->findAll();
        if (count($existing) > 0) {
            $output->writeln(sprintf('Reference data already seeded (%d countries found).', count($existing)));

            return Command::SUCCESS;
        }

        $tunisia = new Country(
            name: 'Tunisie',
            code: 'TN',
            phoneCode: '216',
        );
        $this->em->persist($tunisia);
        $output->writeln('  Created country "Tunisie" (TN, +216).');

        $totalCities = 0;
        foreach (self::GOVERNORATES as $data) {
            $governorate = new Governorate(
                country: $tunisia,
                name: $data['name'],
                code: $data['code'],
            );
            $this->em->persist($governorate);

            foreach ($data['cities'] as $cityData) {
                $locality = new Locality(
                    governorate: $governorate,
                    name: $cityData['name'],
                    postalCode: $cityData['postalCode'] ?? null,
                );
                $this->em->persist($locality);
                ++$totalCities;
            }

            $output->writeln(sprintf('  Created governorate "%s" with %d localities.', $data['name'], count($data['cities'])));
        }

        $this->em->flush();

        $output->writeln(sprintf('Seeded 1 country, %d governorates and %d localities.', count(self::GOVERNORATES), $totalCities));

        return Command::SUCCESS;
    }
}
