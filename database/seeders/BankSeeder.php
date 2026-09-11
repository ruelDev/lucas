<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            ['name' => 'BDO Unibank, Inc.',                     'abbreviation' => 'BDO',    'description'   => null],
            ['name' => 'Bank of the Philippine Islands',        'abbreviation' => 'BPI',    'description'   => null],
            ['name' => 'Development Bank of the Philippines',   'abbreviation' => 'DBP',    'description'   => null],
            ['name' => 'Enterprise Bank, Inc.',                 'abbreviation' => 'EB',     'description'   => null],
            ['name' => 'East West Bank',                        'abbreviation' => 'EW',     'description'   => null],
            ['name' => 'First Consolidated Bank',               'abbreviation' => 'FCB',    'description'   => null],
            ['name' => 'First Valley Bank',                     'abbreviation' => 'FV',     'description'   => null],
            ['name' => 'Land Bank of the Philippines',          'abbreviation' => 'LBP',    'description'   => null],
            ['name' => 'Metropolitan Bank & Trust Co.',         'abbreviation' => 'MBTC',   'description'   => null],
            ['name' => 'BDO Network Bank',                      'abbreviation' => 'ONB',    'description'   => 'Formerly One Network Bank'],
            ['name' => 'Philippine National Bank',              'abbreviation' => 'PNB',    'description'   => null],
            ['name' => 'Bayad Center',                          'abbreviation' => 'BYC',    'description'   => null],
            // ['name' => 'Rural Bank of Pilar',                   'abbreviation' => 'RB',     'description'   => null],
            // ['name' => 'Rural Bank of Kiamba',                  'abbreviation' => 'RB',     'description'   => null],
        ];

        DB::table('banks')->insert($banks);
    }
}
