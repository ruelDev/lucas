/* eslint-disable react/prop-types */
import ReceiptConverterCounts from "./receipt-converter-counts";
import ReceiptConverterLogs from "./receipt-converter-logs";

export default function ReceiptConverterCharts({ data }) {
    const receiptConverterData = data.receiptConverter

    return (
        <div className="flex flex-col space-y-4">
            <ReceiptConverterCounts receiptConverterData={receiptConverterData} />
            <ReceiptConverterLogs receiptConverterData={receiptConverterData} />
        </div>
    );
}
