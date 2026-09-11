export const PERMISSIONS = {
    MAIN_MODULE: {
        // Certificate of Full Payment
        CFP: ['certificate_fullpayment.view', 'certificate_fullpayment.export'],
        // Offline Search Facility
        OSF: ['offline_search_facility.view'],
        // Out Collection
        OC: ['out_collection.view'],
    },
    REPORTS: {
        OC: {
            // Out Collection Report
            OCR: ['out_collection_report.view'],
            // Daily Out Collection Report
            DCR: ['daily_collection_report.view'],
            // LMS Posting Report
            LPR: ['lms_posting_report.view'],
        },
        // Receipt Converter
        RC: ['receipt_converter.view'],
        // User Management Report
        UR: ['user_management.export'],
    },
    MASTER_SETUP: {
        // Organization
        ORG: {
            BM: ['branch_management.view'],
            DLM: ['dealer_management.view'],
            GM: ['group_management.view'],
            DVM: ['division_management.view'],
            DPM: ['department_management.view'],
            SM: ['section_management.view'],
            RP: ['report_generation.view'],
        },
        // Bank Management
        BM: ['bank_management.view'],
    },
    USER_SETTINGS: {
        // User Management
        UM: ['user_management.view'],
        // Role Management
        RM: ['role_management.view'],
        // Audit Logs
        AL: ['audit_logs.view'],
    },
};
