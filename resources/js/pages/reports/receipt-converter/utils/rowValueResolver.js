export function getRowValue(source, row, header) {
    if (source === 'NEWGEN') {
        const fieldMap = {
            LOAN_NO: 'LOAN_NO',
            RECEIPT_MODE: 'RECEIPT_MODE',
            INSTRUMENT_NO: 'INSTRUMENT_NO',
            BANK_ID: 'BANK_ID',
            BRANCH_ID: 'BRANCH_ID',
            BANK_ACCOUNT: 'BANK_ACCOUNT',
            RECEIPT_DATE: 'RECEIPT_DATE',
            INSTRUMENT_DATE: 'INSTRUMENT_DATE',
            RECEIPT_AMOUNT: 'RECEIPT_AMOUNT',
            TDS_AMOUNT: 'TDS_AMOUNT',
            RECEIPT_NO: 'RECEIPT_NO',
            DEFAULT_BRANCH: 'DEFAULT_BRANCH',
            DEPOSIT_BANK: 'DEPOSIT_BANK',
            DEPOSIT_BANK_BRANCH: 'DEPOSIT_BANK_BRANCH',
            DEPOSIT_BANK_ACCOUNT: 'DEPOSIT_BANK_ACCOUNT',
            MAKER_REMARKS: 'MAKER_REMARKS',
            MIS_NO: 'MIS_NO',
            Customer_Name: 'Customer_Name',
            RECEIVED_FROM: 'RECEIVED_FROM',
            Ack_receipt_no: 'Ack_receipt_no',
            PDC_FLAG: 'PDC_FLAG',
        };
        return row[fieldMap[header]] ?? '';
    } else if (source === 'FINNONE_QR') {
        const fieldMap = {
            AGREEMENTNO: 'AGREEMENTNO',
            PAYMENT_MODE: 'PAYMENT_MODE',
            RECEIPT_DATE: 'RECEIPT_DATE',
            RECEIPT_NUM: 'RECEIPT_NUM',
            RECEIPT_CHANNEL: 'RECEIPT_CHANNEL',
            RECEIPT_AMT: 'RECEIPT_AMT',
            DEALING_BANKID: 'DEALING_BANKID',
        };
        return row[fieldMap[header]] ?? '';
    } else if (source === 'FINNONE_UA') {
        const fieldMap = {
            AGREEMENTNO: 'AGREEMENTNO',
            PAYMENT_MODE: 'PAYMENT_MODE',
            RECEIPT_DATE: 'RECEIPT_DATE',
            RECEIPT_NUM: 'RECEIPT_NUM',
            CHECK_NUMBER: 'CHECK_NUMBER',
            RECEIPT_CHANNEL: 'RECEIPT_CHANNEL',
            RECEIPT_AMT: 'RECEIPT_AMT',
            DEALING_BANKID: 'DEALING_BANKID',
            REMARKS: 'REMARKS',
            MIS_ACCOUNT: 'MIS_ACCOUNT',
            CUSTOMERNAME: 'CUSTOMERNAME',
        };
        return row[fieldMap[header]] ?? '';
    }

    return '';
}

export const hasColumnData = (source, header) => {
    if (source !== 'NEWGEN') return true;

    const emptyColumns = ['INSTRUMENT_NO', 'BANK_ID', 'BRANCH_ID', 'BANK_ACCOUNT', 'INSTRUMENT_DATE', 'TDS_AMOUNT', 'MIS_NO', 'Customer_Name'];

    return !emptyColumns.includes(header);
};
