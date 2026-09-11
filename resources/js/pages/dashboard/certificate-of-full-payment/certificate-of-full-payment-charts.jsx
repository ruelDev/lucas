/* eslint-disable react/prop-types */
import CertificateOfFullPaymentCounts from './certificate-of-full-payment-counts';
import CertificateOfFullPaymentLogs from './certificate-of-full-payment-logs';

export default function CertificateOfFullPaymentCharts({data}) {

     const certificateOfFullPaymentData = data.certificateOfFullPayment
    return (
        <div className="flex flex-col gap-4">
            <CertificateOfFullPaymentCounts certificateOfFullPaymentData={certificateOfFullPaymentData}/>
            <CertificateOfFullPaymentLogs certificateOfFullPaymentData={certificateOfFullPaymentData}/>
        </div>
    );
}