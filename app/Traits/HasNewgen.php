<?php

namespace App\Traits;

trait HasNewgen
{
    public function getClientRecord($source)
    {
        if ($source === 'Newgen BFC' || $source === 'Newgen BMI') {
            return "
                SELECT DISTINCT TOP 50000
                    A.MIS_NO as account_number,
                    B.FN as first_name,
                    B.MN as middle_name,
                    B.SRNM as last_name,
                    B.DATE_OF_BIRTH as date_of_birth,
                    C.ADDRESS_LINE1 as address
                FROM CR_LOAN_DTL A WITH (NOLOCK)
                LEFT JOIN (
                    SELECT FN, MN, SRNM, CAST(DOB AS DATE) DATE_OF_BIRTH, WINAME
                    FROM DM_CBG
                    WHERE ATYPE = 'PRINCIPAL BORROWER'
                    ) B ON A.LOAN_REFERENCE_NO = B.WINAME
                LEFT JOIN (
                    SELECT BPID, ADDRESS_LINE1
                    FROM COM_ADDRESS_M WITH (NOLOCK)
                    ) C ON A.LOAN_CUSTOMER_ID = C.BPID
                WHERE FN IS NOT NULL
                AND A.MIS_NO != ''
                AND A.MIS_NO NOT LIKE '%[^0-9]%'
                AND A.REC_STATUS IN ('A','C')
                AND B.DATE_OF_BIRTH IS NOT NULL
                AND C.ADDRESS_LINE1 IS NOT NULL
                AND A.LOAN_DISBURSAL_DATE >= DATEADD(YEAR, -5, A.LOAN_DISBURSAL_DATE)
            ";
        }

        return "
            SELECT DISTINCT
                FILENO account_number,
                FNAME first_name,
                MNAME middle_name,
                LNAME last_name,
                DOB date_of_birth,
                ADDRESS address
            FROM FINNONEGCD.NBFC_CUSTOMER_M A
            LEFT JOIN (
                SELECT ADDRESS, BPID
                FROM FINNONEGCD.NBFC_ADDRESS_M) B
                ON A.CUSTOMERID = B.BPID
                INNER JOIN FINNONELEA.LEA_AGREEMENT_DTL C
                ON A.CUSTOMERID = C.LESSEEID
            WHERE FILENO IS NOT NULL
            AND A.FNAME IS NOT NULL
            AND A.LNAME IS NOT NULL
            AND A.DOB IS NOT NULL
            AND ADDRESS IS NOT NULL
            AND REGEXP_LIKE(FILENO, '^[0-9]+$')
            AND ROWNUM <= 500000
        ";
    }


    public function getClientAccount($source)
    {
        if ($source === 'Newgen BFC' || $source === 'Newgen BMI') {
            return "
                SELECT
                MIS_NO account_number
                FROM CR_LOAN_DTL
                WHERE MIS_NO != ''
                AND MIS_NO NOT LIKE '%[^0-9]%'
            ";
        }

        return "
            SELECT FILENO account_number
            FROM FINNONELEA.LEA_AGREEMENT_DTL
            WHERE FILENO IS NOT NULL
            AND REGEXP_LIKE(FILENO, '^[0-9]+$')
        ";
    }

    public function getLmsRecords()
    {
        return "
            SELECT TOP 500000
                A.MIS_NO mis_no,
                A.LOAN_REFERENCE_NO reference_no,
                A.LOAN_CUSTOMER_ID agreement_id,
                A.LOAN_NO agreement_no,
                A.RELEASE_DATE date_sold,
                A.NEXT_DUE_DATE first_due_date,
                A.LOAN_MATURITY_DATE maturity_date,
                B.RECEIVED_DATE last_payment_date,
                A.LOAN_LOAN_AMOUNT loan_amount,
                A.LOAN_TENURE loan_term,
                A.LOAN_EMI_AMOUNT emi,
                A.REC_STATUS loan_status,
                A.NPA_FLAG npa_stage
            FROM CR_LOAN_DTL A
            LEFT JOIN (
                SELECT TXNID, RECEIVED_DATE
                FROM (
                    SELECT
                        TXNID, CAST(RECEIVED_DATE AS DATE) RECEIVED_DATE,
                        ROW_NUMBER() OVER (PARTITION BY TXNID ORDER BY RECEIVED_DATE DESC) AS RN
                    FROM CR_INSTRUMENT_DTL
                    WHERE INSTRUMENT_TYPE = 'R'
                    AND REC_STATUS = 'R'
                    ) X WHERE X.RN = 1
                ) B ON A.LOAN_ID = B.TXNID
            WHERE NPA_FLAG IN ('CURRENT', 'REPO', 'PDP', 'PDNP', 'WRITEOFF', 'LEGAL', 'SALE')
            AND A.REC_STATUS IN ('A','C')
            AND A.MIS_NO != ''
            AND A.MIS_NO NOT LIKE '%[^0-9]%'
            AND A.LOAN_DISBURSAL_DATE >= DATEADD(YEAR, -5, A.LOAN_DISBURSAL_DATE)
        ";
    }

    public function getLosRecords()
    {
        return "
            SELECT TOP 500000
                TID rlos_id,
                CAST(ENDATE AS DATE) date_encoded,
                DESCR status,
                B.PVCIVRR remarks,
                A.CIEXDATE date_decision
            FROM EXT_LOS A WITH (NOLOCK)
            LEFT JOIN (
                SELECT WINAME, PVCIVRR
                FROM (
                    SELECT
                        RANK () OVER (PARTITION BY WINAME ORDER BY INSERTIONORDERID DESC) CNT,
                        WINAME,
                        PVCIVRR
                    FROM CVMPV
                ) A WHERE CNT = 1
            ) B ON A.TID = B.WINAME
            WHERE ENDATE >= DATEADD(YEAR, -5, A.ENDATE)
            AND TID IS NOT NULL
        ";
    }
}
