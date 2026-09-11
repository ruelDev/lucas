export const COLUMN_WIDTHS = {
    NEWGEN: {
        fixed: {
            INSTRUMENT_NO: 85,
            BANK_ID: 85,
            BRANCH_ID: 85,
            BANK_ACCOUNT: 85,
            INSTRUMENT_DATE: 85,
            TDS_AMOUNT: 85,
            MIS_NO: 85,
            Customer_Name: 85,

            RECEIPT_MODE: 60,
            PDC_FLAG: 55,
            DEPOSIT_BANK: 65,
            DEPOSIT_BANK_BRANCH: 65,
            RECEIPT_NO: 75,
            RECEIVED_FROM: 75,
            Ack_receipt_no: 100,

            RECEIPT_DATE: 80,
            RECEIPT_AMOUNT: 80,
            DEFAULT_BRANCH: 90,
        },
        large: {
            LOAN_NO: 160,
            DEPOSIT_BANK_ACCOUNT: 150,
            MAKER_REMARKS: 150,
        },
        default: 160,
    },

    FINNONE_QR: {
        fixed: {
            PAYMENT_MODE: 90,
            DEALING_BANKID: 90,

            RECEIPT_DATE: 100,
            RECEIPT_NUM: 100,
            RECEIPT_CHANNEL: 100,
            RECEIPT_AMT: 100,
        },
        large: {
            AGREEMENTNO: 160,
        },
        default: 160,
    },

    FINNONE_UA: {
        fixed: {
            PAYMENT_MODE: 65,
            DEALING_BANKID: 65,
            CHECK_NUMBER: 65,
            RECEIPT_CHANNEL: 65,

            RECEIPT_DATE: 90,
            RECEIPT_NUM: 90,
            RECEIPT_AMT: 90,
            MIS_ACCOUNT: 110,
        },
        large: {
            AGREEMENTNO: 160,
            REMARKS: 180,
            CUSTOMERNAME: 150,
        },
        default: 180,
    },
};

export const getColumnWidth = (source, header) => {
    const config = COLUMN_WIDTHS[source];

    if (!config) return 160;

    return config.fixed?.[header] ?? config.large?.[header] ?? config.default;
};

export const calculateTableWidth = (source, headers) => {
    const rowColWidth = 100;
    const statusColWidth = 90;
    const remarksColWidth = 180;
    const dataColumnsWidth = headers.reduce((sum, header) => sum + getColumnWidth(source, header), 0);

    return rowColWidth + statusColWidth + dataColumnsWidth + remarksColWidth;
};
