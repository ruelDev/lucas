<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bank_accounts')->insert([
            ['bank_id' => 1,    'account_number' => '000140057374',     'depository_remarks' => 'BDO 374',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 2,    'account_number' => '3711003346',       'depository_remarks' => 'BPI 346',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 3,    'account_number' => '5427764057',       'depository_remarks' => 'DBP 057',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 4,    'account_number' => null,               'depository_remarks' => 'EB 022',   'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 5,    'account_number' => null,               'depository_remarks' => 'EW 274',   'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 6,    'account_number' => null,               'depository_remarks' => 'FCB 028',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 6,    'account_number' => null,               'depository_remarks' => 'FCB 644',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 6,    'account_number' => null,               'depository_remarks' => 'FCB 856',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 7,    'account_number' => null,               'depository_remarks' => 'FV 018',   'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 7,    'account_number' => '10529121',         'depository_remarks' => 'FV 758',   'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 7,    'account_number' => '10529001',         'depository_remarks' => 'FV 758',   'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 8,    'account_number' => '2602103083',       'depository_remarks' => 'LBP 083',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 8,    'account_number' => '2022101466',       'depository_remarks' => 'LBP 2-466', 'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 8,    'account_number' => '2092102478',       'depository_remarks' => 'LBP 478',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 8,    'account_number' => '2862101548',       'depository_remarks' => 'LBP 548',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 8,    'account_number' => '4532101811',       'depository_remarks' => 'LBP 811',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 9,   'account_number' => '3563356239297',    'depository_remarks' => 'MBTC 297', 'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 9,   'account_number' => '2277227525696',    'depository_remarks' => 'MBTC 696', 'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040560000081',     'depository_remarks' => 'ONB 081',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040830004157',     'depository_remarks' => 'ONB 157',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040188001191',     'depository_remarks' => 'ONB 191',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040100000215',     'depository_remarks' => 'ONB 215',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040508001234',     'depository_remarks' => 'ONB 234',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040318001334',     'depository_remarks' => 'ONB 334',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040168001345',     'depository_remarks' => 'ONB 345',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040118001396',     'depository_remarks' => 'ONB 396',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040860000419',     'depository_remarks' => 'ONB 419',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040220001480',     'depository_remarks' => 'ONB 480',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040760001499',     'depository_remarks' => 'ONB 499',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040258001527',     'depository_remarks' => 'ONB 527',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040330000543',     'depository_remarks' => 'ONB 543',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040390000574',     'depository_remarks' => 'ONB 574',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040580000613',     'depository_remarks' => 'ONB 613',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040478000617',     'depository_remarks' => 'ONB 617',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040200000721',     'depository_remarks' => 'ONB 721',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040668000738',     'depository_remarks' => 'ONB 738',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '041108001801',     'depository_remarks' => 'ONB 801',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 10,   'account_number' => '040450000915',     'depository_remarks' => 'ONB 915',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 11,   'account_number' => '403510001352',     'depository_remarks' => 'PNB 352',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 11,   'account_number' => '403110060672',     'depository_remarks' => 'PNB 672',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 11,   'account_number' => '142870002693',     'depository_remarks' => 'PNB 693',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 11,   'account_number' => '150670002744',     'depository_remarks' => 'PNB 744',  'created_at' => now(),  'updated_at' => now()],
            ['bank_id' => 12,   'account_number' => null,               'depository_remarks' => 'BYC',      'created_at' => now(),  'updated_at' => now()],
        ]);
    }
}
