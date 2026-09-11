<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OfflinesearchLmsactive;
use App\Models\OfflinesearchLosrecord;
use App\Models\OfflinesearchClientrecord;
use App\Models\OfflinesearchClientacct;

class OfflineSearchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OfflinesearchLmsactive::create([
            'AgreementId' => '1',
            'AgreementNo' => 'TOMHEND',
            'Loan_Reference_no' => 'LOS-000002',
            'MIS_NO' => 987654321,
            'Date_Sold' => '1900-02-01',
            'First_Due_Date' => '1900-03-01',
            'Maturity_Date' => '2001-03-01',
            'Last_Payment_Date' => '1900-01-01',
            'Loan_Amount' => 45000,
            'Loan_Tenure' => 36,
            'EMI' => 4500,
            'Loan_Status' => 'active',
            'NPA_Stage' => 'PDP',
            'Finance' => 'BMI',
        ]);

        OfflinesearchLmsactive::create([
            'AgreementId' => '2',
            'AgreementNo' => 'TOBEND',
            'Loan_Reference_no' => 'LOS-000002',
            'MIS_NO' => 1111111111,
            'Date_Sold' => '1900-02-01',
            'First_Due_Date' => '1900-03-01',
            'Maturity_Date' => '2001-03-01',
            'Last_Payment_Date' => '1900-01-01',
            'Loan_Amount' => 45000,
            'Loan_Tenure' => 36,
            'EMI' => 4500,
            'Loan_Status' => 'active',
            'NPA_Stage' => 'PDP',
            'Finance' => 'BFC',
        ]);

        OfflinesearchLmsInactive::create([
            'AgreementId' => '1',
            'AgreementNo' => 'TOBFEND',
            'Loan_Reference_no' => 'LOS-000003',
            'Account_No' => 987654331,
            'Date_Sold' => '1900-04-01',
            'First_Due_Date' => '1900-05-01',
            'Maturity_Date' => '2001-05-01',
            'Last_Payment_Date' => '1900-01-01',
            'Loan_Amount' => 50000,
            'Loan_Tenure' => 48,
            'EMI' => 3500,
            'Loan_Status' => 'active',
            'NPA_Stage' => 'Writeoff',
            'Finance' => 'BFC',
        ]);

        OfflinesearchLmsInactive::create([
            'AgreementId' => '2',
            'AgreementNo' => 'TOHEND',
            'Loan_Reference_no' => 'LOS-000005',
            'Account_No' => 987654321,
            'Date_Sold' => '1950-05-01',
            'First_Due_Date' => '1980-05-01',
            'Maturity_Date' => '2011-05-01',
            'Last_Payment_Date' => '2001-01-01',
            'Loan_Amount' => 50000,
            'Loan_Tenure' => 48,
            'EMI' => 3500,
            'Loan_Status' => 'active',
            'NPA_Stage' => 'LEGAL',
            'Finance' => 'BMI',
        ]);

        OfflinesearchLosrecord::create([
            'Rlos_id' => 'LOS-000006',
            'Account_No' => '1111111111',
            'Financing' => 'BMI',
            'Date_Encoded' => '1980-05-01',
            'Date_Decision' => '1980-05-08',
            'Status' => 'REJECTED',
        ]);

        OfflinesearchLosrecord::create([
            'Rlos_id' => 'LOS-000008',
            'Account_No' => '987654322',
            'Financing' => 'BFC',
            'Date_Encoded' => '1980-09-01',
            'Date_Decision' => '1980-09-08',
            'Status' => 'REJECTED',
        ]);

        OfflinesearchClientrecord::create([
            'Last_Name' => 'Kun',
            'First_Name' => 'Mocchi',
            'Middle_Name' => '',
            'Suffix' => '',
            'Date_of_Birth' => '2025-12-25',
            'Address' => 'Sa Office'
        ]);

        OfflinesearchClientrecord::create([
            'Last_Name' => 'Kun',
            'First_Name' => 'Philip',
            'Middle_Name' => '',
            'Suffix' => '',
            'Date_of_Birth' => '2025-12-31',
            'Address' => 'Sa Sikend floor'
        ]);

        OfflinesearchClientacct::create([
            'Account_No' => '987654322',
            'Source' => 'NewGen',
            'Client_id' => '1',
        ]);

        OfflinesearchClientacct::create([
            'Account_No' => '1111111111',
            'Source' => 'NewGen',
            'Client_id' => '2',
        ]);
    }
}
