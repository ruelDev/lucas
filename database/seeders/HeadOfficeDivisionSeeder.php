<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HeadOfficeDivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    private function getGroupId(String $name)
    {
        if ($name === 'N/A') {
            return null;
        }

        $group = Group::where('name', $name)->first();

        return $group->id;
    }

    public function run(): void
    {
        DB::table('divisions')->insert([
            ["name" => "Commercial Business Lending Division", "code" => "CBLD", "description" => "Handles all existing and new business loans applications", "group_id" => $this->getGroupId("Corporate & Retail Lending Group"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Real Estate & Consumer Lending Division", "code" => "RECLD", "description" => "Handles all real estate loans and consumer loans", "group_id" => $this->getGroupId("Corporate & Retail Lending Group"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Micro & Small Lending Division", "code" => "MSLD", "description" => "Micro & Small Lending Division", "group_id" => $this->getGroupId("Corporate & Retail Lending Group"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Management Information System Center", "code" => "MIS", "description" => "Serves as the central repository of all data/reports for the reporting to Top Management (Office of the President & Managing Director), Risk Oversight Committee (ROC) and Business Sector. Also serves as centerpoint for the coordination with support groups and for BSP requirements. The primary function of which is to efficiently maintain a centralized database of all MIS record of the business sector. It also provides wide administrative support mainly to the CRLG.", "group_id" => $this->getGroupId("Corporate & Retail Lending Group"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Area 1 South Luzon", "code" => "A1SL", "description" => "Area 1 South Luzon", "group_id" => $this->getGroupId("Branch Banking Group"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Area 2 North Luzon", "code" => "A2NL", "description" => "Area 2 North Luzon", "group_id" => $this->getGroupId("Branch Banking Group"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Area 3 Visayas-Mindanao", "code" => "A3VM", "description" => "Area 3 Visayas-Mindanao", "group_id" => $this->getGroupId("Branch Banking Group"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Area 4 Metro Manila 1", "code" => "A4MM1", "description" => "Area 4 Metro Manila 1", "group_id" => $this->getGroupId("Branch Banking Group"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Area 5 Metro Manila 2", "code" => "A5MM2", "description" => "Area 5 Metro Manila 2", "group_id" => $this->getGroupId("Branch Banking Group"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Remedial Collection Division", "code" => "RCD", "description" => "Remedial Collection Division", "group_id" => $this->getGroupId("N/A"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Credit Division", "code" => "CD", "description" => "Credit Division", "group_id" => $this->getGroupId("N/A"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Branch & Central Operations Division", "code" => "BCOD", "description" => "Manages over-all operations of Branch and Central Operations Division in accordance with Bank policies and procedures and government regulations; Recommends to Management new/revised policies for process improvemen", "group_id" => $this->getGroupId("N/A"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Business Application System Support Division", "code" => "BASSD", "description" => "responsible for the application development/ maintenance, quality assurance, help desk support, research and development, technical documentation support, and user technical training.", "group_id" => $this->getGroupId("Information Technology Group"), "created_at" => now(), "updated_at" => now()],
            ["name" => "IT Infrastructure Division", "code" => "ITID", "description" => "IT Infrastructure Division", "group_id" => $this->getGroupId("Information Technology Group"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Accounting Division", "code" => "AD", "description" => "The Accounting Division is responsible with the day-to-day transactions of the Bank. It ensures that proper accounting controls are in place to maintain the integrity of the Bank's general ledger and the financial records are in compliance with generally accepted accounting principles.", "group_id" => $this->getGroupId("Financial Planning & Control Group"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Corporate Planning & Regulatory Compliance Reports Division", "code" => "CPSRRD", "description" => "Corporate Planning, Sustainability, & Regulatory Reports Division", "group_id" => $this->getGroupId("Financial Planning & Control Group"), "created_at" => now(), "updated_at" => now()],
            ["name" => "Corporate Affairs Division", "code" => "CAD", "description" => "Corporate Affairs Division", "group_id" => $this->getGroupId("N/A"), "created_at" => now(), "updated_at" => now()],
        ]);
    }
}
