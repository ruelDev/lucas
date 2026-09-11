<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HeadOfficeGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('groups')->insert([
            ["name" => "Internal Audit Group", "code" => "IAG", "description" => "The Internal Audit Group determines whether the Bank's network of risk management, control, and governance processes, as designed and represented by Management, is adequate and functioning in a manner", "created_at" => now(), "updated_at" => now()],
            ["name" => "Compliance Group", "code" => "CG", "description" => "Oversees the identification and management of the Bank’s compliance risk and is mainly responsible for managing the over-all regulatory compliance issues and programs of the Bank. Compliance Group is headed by the Chief Compliance Officer (CCO) who functionally reports to the Audit and Compliance Committee (ACC). The CCO supervises the AML Department and Regulatory Compliance Department.", "created_at" => now(), "updated_at" => now()],
            ["name" => "Risk Management Group", "code" => "RMG", "description" => "Risk Management Group (RMG) – is an independent function from the Bank's risk taking operations and acts as the Risk Oversight Committee's (ROC) technical arm in the management of the Bank's risks. It oversees the risk-taking activities across the Bank, and evaluate whether or not these remain consistent with the Bank's risk appetite and strategic direction.", "created_at" => now(), "updated_at" => now()],
            ["name" => "Legal Group", "code" => "LG", "description" => "Ensures the protection of the bank's assets and reputation by managing and controlling all aspects of legal risk of all activities carried out by or on behalf of the Bank.", "created_at" => now(), "updated_at" => now()],
            ["name" => "Corporate & Retail Lending Group", "code" => "CRLG", "description" => "The group that handles marketing, solicitation and processing of all non-MCL (Motorcycle Loan) products of the Bank.", "created_at" => now(), "updated_at" => now()],
            ["name" => "Branch Banking Group", "code" => "BBG", "description" => "The Branch Banking Group handles & supervises daily day-to-day sales activities or branches & Branch and Central Operations Division manage the operational process. ", "created_at" => now(), "updated_at" => now()],
            ["name" => "Treasury Group", "code" => "TG", "description" => "Manages the Bank’s assets and liabilities and thereby, minimize the cost of funding; maximize the yield on excess funds and regulate exposures on liquidity and interest risks; Aims to further enhance its work flows to ensure the timely release of funds required by branches, operation groups and third parties, compliance with regulatory requirements and proper safekeeping of critical documents and collaterals. ", "created_at" => now(), "updated_at" => now()],
            ["name" => "Loans Operations Group – Consumer", "code" => "LOG-C", "description" => "Loans Operations Group – Consumer", "created_at" => now(), "updated_at" => now()],
            ["name" => "Repo Marketing & Disposal Management Group", "code" => "RMDMG", "description" => "Repo Marketing & Disposal Management Group", "created_at" => now(), "updated_at" => now()],
            ["name" => "Loans & Treasury Operations Group", "code" => "LTOG", "description" => "Loans & Treasury Operations Group", "created_at" => now(), "updated_at" => now()],
            ["name" => "Corporate Services Group", "code" => "CSG", "description" => "Corporate Services Group", "created_at" => now(), "updated_at" => now()],
            ["name" => "Information Technology Group", "code" => "ITG", "description" => "ITG is responsible for the evaluation, implementation and maintenance of all technologically related projects of the Bank. ", "created_at" => now(), "updated_at" => now()],
            ["name" => "Financial Planning & Control Group", "code" => "FPCG", "description" => "Financial Planning & Control Group", "created_at" => now(), "updated_at" => now()],
        ]);
    }
}
